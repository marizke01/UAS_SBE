<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PosController extends Controller
{
    public function index()
    {
        return $this->showPos('admin.pos');
    }

    public function publicCheckout(Request $request)
    {
        $request->merge(['checkout_context' => 'public']);

        return $this->checkout($request);
    }

    private function showPos(string $view)
    {
        $products = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('branch_stocks as bs', function ($join) {
                $join->on('bs.product_variant_id', '=', 'pv.id')->where('bs.branch_id', '=', 1);
            })
            ->select('pv.id', 'p.name', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.weight_gram', 'pv.selling_price', 'pv.minimum_stock', DB::raw('COALESCE(bs.stock - bs.reserved_stock, 0) as stock'))
            ->where('p.status', 'active')
            ->where('pv.status', 'active')
            ->orderBy('pv.weight_gram')
            ->get();

        return view($view, compact('products'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'cart_payload' => ['required', 'string'],
            'payment_method' => ['required', 'in:cash,qris,transfer,payment_gateway'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'checkout_context' => ['nullable', 'in:admin,public'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
        ]);

        $items = json_decode($data['cart_payload'], true);
        if (!is_array($items) || count($items) === 0) {
            return back()->withErrors(['cart_payload' => 'Keranjang masih kosong.']);
        }

        try {
            $result = DB::transaction(function () use ($items, $data) {
                $branchId = 1;
                $userId = 1;
                $subtotal = 0;
                $resolvedItems = [];

                foreach ($items as $item) {
                    $variantId = (int) ($item['id'] ?? 0);
                    $qty = max(1, (int) ($item['qty'] ?? 1));

                    $variant = DB::table('product_variants as pv')
                        ->join('products as p', 'p.id', '=', 'pv.product_id')
                        ->select('pv.*', 'p.name as product_name')
                        ->where('pv.id', $variantId)
                        ->lockForUpdate()
                        ->first();

                    abort_if(!$variant, 422, 'Produk tidak ditemukan.');

                    $stock = DB::table('branch_stocks')
                        ->where('branch_id', $branchId)
                        ->where('product_variant_id', $variantId)
                        ->lockForUpdate()
                        ->first();

                    $available = $stock ? ($stock->stock - $stock->reserved_stock) : 0;
                    abort_if($available < $qty, 422, "Stok {$variant->sku} tidak mencukupi. Sisa: {$available}");

                    $lineSubtotal = (float) $variant->selling_price * $qty;
                    $subtotal += $lineSubtotal;
                    $resolvedItems[] = compact('variant', 'qty', 'lineSubtotal', 'stock');
                }

                $discount = (float) ($data['discount'] ?? 0);
                $tax = (float) ($data['tax'] ?? 0);
                $grandTotal = max(0, $subtotal - $discount + $tax);
                $paidAmount = (float) ($data['paid_amount'] ?? $grandTotal);
                if ($data['payment_method'] !== 'cash') {
                    $paidAmount = $grandTotal;
                }

                $context = $data['checkout_context'] ?? 'admin';
                $customerName = trim((string) ($data['customer_name'] ?? ''));
                $customerPhone = trim((string) ($data['customer_phone'] ?? ''));
                $orderSource = $context === 'public' ? 'Website publik' : 'POS admin';
                $note = $orderSource;
                if ($customerName !== '' || $customerPhone !== '') {
                    $note .= ' | Pembeli: ' . ($customerName !== '' ? $customerName : '-');
                    $note .= ' | WA: ' . ($customerPhone !== '' ? $customerPhone : '-');
                }
                if ($context === 'public') {
                    $note .= ' | Status Pesanan: Menunggu Konfirmasi';
                }

                $transactionNumber = 'TRX-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));

                $saleId = DB::table('sales')->insertGetId([
                    'transaction_number' => $transactionNumber,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'customer_id' => null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'paid',
                    'sale_status' => 'completed',
                    'paid_amount' => $paidAmount,
                    'change_amount' => max(0, $paidAmount - $grandTotal),
                    'note' => $note,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($resolvedItems as $row) {
                    $variant = $row['variant'];
                    $qty = $row['qty'];
                    $lineSubtotal = $row['lineSubtotal'];
                    $stockBefore = $row['stock']->stock;
                    $stockAfter = $stockBefore - $qty;

                    DB::table('sale_items')->insert([
                        'sale_id' => $saleId,
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product_name,
                        'variant_name' => $variant->variant_name,
                        'sku' => $variant->sku,
                        'qty' => $qty,
                        'price' => $variant->selling_price,
                        'subtotal' => $lineSubtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('branch_stocks')
                        ->where('id', $row['stock']->id)
                        ->update(['stock' => $stockAfter, 'updated_at' => now()]);

                    DB::table('stock_movements')->insert([
                        'branch_id' => $branchId,
                        'product_variant_id' => $variant->id,
                        'user_id' => $userId,
                        'movement_type' => 'out',
                        'qty' => $qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'sale',
                        'reference_id' => $saleId,
                        'note' => 'Pengurangan stok otomatis dari ' . $orderSource,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $paymentId = DB::table('payments')->insertGetId([
                    'payment_number' => 'PAY-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                    'invoice_id' => null,
                    'order_id' => null,
                    'sale_id' => $saleId,
                    'customer_id' => null,
                    'amount' => $grandTotal,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'verified',
                    'payment_date' => now(),
                    'note' => 'Pembayaran ' . $orderSource,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $invoiceId = DB::table('invoices')->insertGetId([
                    'invoice_number' => 'INV-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4)),
                    'customer_id' => null,
                    'order_id' => null,
                    'sale_id' => $saleId,
                    'issued_by' => $userId,
                    'issue_date' => Carbon::today()->toDateString(),
                    'due_date' => Carbon::today()->toDateString(),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $grandTotal,
                    'status' => 'paid',
                    'note' => 'Invoice otomatis dari ' . $orderSource,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($resolvedItems as $row) {
                    $variant = $row['variant'];
                    DB::table('invoice_items')->insert([
                        'invoice_id' => $invoiceId,
                        'product_variant_id' => $variant->id,
                        'description' => $variant->product_name . ' ' . $variant->variant_name,
                        'qty' => $row['qty'],
                        'price' => $variant->selling_price,
                        'subtotal' => $row['lineSubtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('payments')->where('id', $paymentId)->update(['invoice_id' => $invoiceId]);

                DB::table('activity_logs')->insert([
                    'user_id' => $userId,
                    'action' => 'checkout',
                    'module' => 'pos',
                    'description' => 'Transaksi dibuat: ' . $transactionNumber . ' via ' . $orderSource,
                    'subject_type' => 'sale',
                    'subject_id' => $saleId,
                    'new_values' => json_encode(['grand_total' => $grandTotal]),
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 500),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return compact('transactionNumber', 'grandTotal', 'saleId', 'invoiceId');
            });

            if (($data['checkout_context'] ?? 'admin') === 'public') {
                return redirect()
                    ->route('public.checkout.page', ['checkout' => 'success'])
                    ->with('success', 'Checkout berhasil: ' . $result['transactionNumber'] . ' | Total Rp ' . number_format($result['grandTotal'], 0, ',', '.'))
                    ->with('invoice_url', route('public.invoice.download', $result['invoiceId']));
            }

            return redirect()->route('admin.pos')->with('success', 'Transaksi berhasil: ' . $result['transactionNumber'] . ' | Total Rp ' . number_format($result['grandTotal'], 0, ',', '.'));
        } catch (Throwable $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
