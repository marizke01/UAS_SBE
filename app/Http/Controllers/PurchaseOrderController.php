<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = DB::table('purchase_orders')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($purchaseOrders as $po) {
            $po->total_items = DB::table('purchase_order_items')->where('purchase_order_id', $po->id)->count();
            $po->total_qty = DB::table('purchase_order_items')->where('purchase_order_id', $po->id)->sum('qty') ?: 0;
        }

        $pageTitle = 'Daftar Purchase Order';

        return view('admin.purchase_order.index', compact('purchaseOrders', 'pageTitle'));
    }

    public function create()
    {
        $products = DB::table('products')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $pageTitle = 'Buat Purchase Order Baru';

        return view('admin.purchase_order.create', compact('products', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $items = $request->input('items', []);
        $hasItems = false;
        $poId = null;

        foreach ($items as $productId => $data) {
            if (isset($data['checked']) && $data['checked'] == '1' && isset($data['qty']) && $data['qty'] > 0) {
                if (!$poId) {
                    $poId = DB::table('purchase_orders')->insertGetId([
                        'po_number' => 'PO-' . time(),
                        'status' => 'draft',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'product_id' => $productId,
                    'qty' => $data['qty'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $hasItems = true;
            }
        }

        if (!$hasItems) {
            return back()->with('error', 'Silakan pilih minimal satu produk dengan jumlah pemesanan yang valid.');
        }

        return redirect("/admin/purchase-order/$poId")->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function generate(Request $request)
    {
        $poId = DB::table('purchase_orders')->insertGetId([
            'po_number' => 'PO-' . time(),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        foreach ($request->items as $item) {
            DB::table('purchase_order_items')->insert([
                'purchase_order_id' => $poId,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect("/admin/purchase-order/$poId")->with('success', 'Purchase Order otomatis berhasil dibuat.');
    }

    public function show($id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            abort(404, 'Purchase Order tidak ditemukan.');
        }

        $items = DB::table('purchase_order_items')
            ->join('products', 'products.id', '=', 'purchase_order_items.product_id')
            ->where('purchase_order_id', $id)
            ->select('products.name', 'purchase_order_items.qty')
            ->get();

        $pageTitle = 'Detail Purchase Order: ' . $po->po_number;

        return view('admin.purchase_order.show', compact('po', 'items', 'pageTitle'));
    }

    public function pdf($id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            abort(404);
        }

        $items = DB::table('purchase_order_items')
            ->join('products', 'products.id', '=', 'purchase_order_items.product_id')
            ->where('purchase_order_id', $id)
            ->select('products.name', 'purchase_order_items.qty')
            ->get();

        $pdf = Pdf::loadView('pdf.purchase_order', compact('po', 'items'));

        return $pdf->download("PO-$po->po_number.pdf");
    }

    public function sendEmail(Request $request, $id)
    {
        $po = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$po) {
            abort(404);
        }

        $email = $request->input('email', env('PRODUCTION_EMAIL', 'produksi@tifanny.com'));

        try {
            Mail::raw("Purchase Order baru: $po->po_number", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Purchase Order Amplang Tifanny');
            });
        } catch (\Exception $e) {
            // Silently catch mail server errors in development
        }

        DB::table('purchase_orders')
            ->where('id', $id)
            ->update([
                'status' => 'sent',
                'updated_at' => now()
            ]);

        return back()->with('success_email', "Purchase Order $po->po_number berhasil dikirim ke email: $email!");
    }
}