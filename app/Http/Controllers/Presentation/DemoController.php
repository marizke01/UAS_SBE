<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
    public function home()
    {
        $products = $this->productRows();
        $settings = DB::table('settings')->pluck('setting_value', 'setting_key');

        return view('public.home', compact('products', 'settings'));
    }

    public function checkout()
    {
        $settings = DB::table('settings')->pluck('setting_value', 'setting_key');

        return view('public.checkout', compact('settings'));
    }

    public function dashboard()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales = (float) DB::table('sales')
            ->where('sale_status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('grand_total');

        $monthRevenue = (float) DB::table('sales')
            ->where('sale_status', 'completed')
            ->where('created_at', '>=', $monthStart)
            ->sum('grand_total');

        $totalTransactions = DB::table('sales')->where('sale_status', 'completed')->count();
        $totalStock = (int) DB::table('branch_stocks')->sum(DB::raw('stock - reserved_stock'));
        $todayTransactions = DB::table('sales')
            ->where('sale_status', 'completed')
            ->whereDate('created_at', $today)
            ->count();
        $todayWebsiteTransactions = DB::table('sales')
            ->where('sale_status', 'completed')
            ->whereDate('created_at', $today)
            ->where('note', 'like', '%Website publik%')
            ->count();
        $todayPosTransactions = max(0, $todayTransactions - $todayWebsiteTransactions);
        $websiteTransactions = DB::table('sales')
            ->where('sale_status', 'completed')
            ->where('note', 'like', '%Website publik%')
            ->count();
        $posTransactions = max(0, $totalTransactions - $websiteTransactions);
        $avgOrderValue = $totalTransactions > 0
            ? (float) DB::table('sales')->where('sale_status', 'completed')->avg('grand_total')
            : 0;
        $monthProfit = (float) DB::table('sale_items as si')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->where('si.created_at', '>=', $monthStart)
            ->sum(DB::raw('(si.price - COALESCE(pv.purchase_price, si.price * 0.6)) * si.qty'));
        $invoiceCount = DB::table('invoices')->count();

        $lowStock = DB::table('branch_stocks as bs')
            ->join('product_variants as pv', 'pv.id', '=', 'bs.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('p.name', 'pv.variant_name', 'pv.sku', 'bs.stock', 'pv.minimum_stock')
            ->whereColumn('bs.stock', '<=', 'pv.minimum_stock')
            ->orderBy('bs.stock')
            ->get();

        $latestSales = DB::table('sales as s')
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->select('s.*', DB::raw('COALESCE(c.name, "Walk-in Customer") as customer_name'))
            ->latest('s.id')
            ->limit(8)
            ->get();

        $chartLabels = [];
        $chartSales = [];
        $chartTransactions = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');
            $chartSales[] = (float) DB::table('sales')
                ->whereDate('created_at', $date)
                ->where('sale_status', 'completed')
                ->sum('grand_total');
            $chartTransactions[] = DB::table('sales')
                ->whereDate('created_at', $date)
                ->where('sale_status', 'completed')
                ->count();
        }

        $bestProducts = DB::table('sale_items as si')
            ->select('si.product_name', 'si.variant_name', DB::raw('SUM(si.qty) as total_qty'), DB::raw('SUM(si.subtotal) as total_sales'))
            ->groupBy('si.product_name', 'si.variant_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
        $pageTitle = 'Dashboard ERP';

        return view('admin.dashboard', compact(
            'todaySales', 'monthRevenue', 'totalTransactions', 'totalStock',
            'lowStock', 'latestSales', 'chartLabels', 'chartSales', 'bestProducts'
        ));
    }

    public function inventory()
    {
        $stocks = DB::table('branch_stocks as bs')
            ->join('branches as b', 'b.id', '=', 'bs.branch_id')
            ->join('product_variants as pv', 'pv.id', '=', 'bs.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('bs.*', 'b.name as branch_name', 'p.name as product_name', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.minimum_stock', 'pv.selling_price', 'pv.reseller_price')
            ->orderBy('p.name')
            ->orderBy('pv.weight_gram')
            ->get();

        $stockMovements = DB::table('stock_movements as sm')
            ->join('product_variants as pv', 'pv.id', '=', 'sm.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('sm.*', 'p.name as product_name', 'pv.variant_name', 'pv.sku')
            ->latest('sm.id')
            ->limit(20)
            ->get();

        return view('admin.inventory', compact('stocks', 'stockMovements'));
    }

    public function stockModalData()
    {
        return response()->json([
            'products' => $this->stockRows()->map(fn ($row) => [
                'product_id' => (int) $row->product_variant_id,
                'name' => $row->product_name . ' ' . $row->variant_name,
                'sku' => $row->sku,
                'stock' => (int) $row->stock,
                'minimum_stock' => (int) $row->minimum_stock,
            ]),
            'summary' => $this->stockDashboardSummary(),
        ]);
    }

    public function updateStock(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'new_stock' => ['required', 'integer', 'min:0'],
        ]);

        $branchId = 1;
        $variantId = (int) $data['product_id'];
        $newStock = (int) $data['new_stock'];

        $stock = DB::table('branch_stocks')
            ->where('branch_id', $branchId)
            ->where('product_variant_id', $variantId)
            ->first();

        $stockBefore = $stock ? (int) $stock->stock : 0;

        if ($stock) {
            DB::table('branch_stocks')
                ->where('id', $stock->id)
                ->update([
                    'stock' => $newStock,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('branch_stocks')->insert([
                'branch_id' => $branchId,
                'product_variant_id' => $variantId,
                'stock' => $newStock,
                'reserved_stock' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('stock_movements')->insert([
            'branch_id' => $branchId,
            'product_variant_id' => $variantId,
            'user_id' => 1,
            'movement_type' => 'adjustment',
            'qty' => abs($newStock - $stockBefore),
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
            'reference_type' => 'inventory_update',
            'reference_id' => $variantId,
            'note' => 'Update stok manual dari dashboard inventaris',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('activity_logs')->insert([
            'user_id' => 1,
            'action' => 'update_stock',
            'module' => 'inventory',
            'description' => 'Stok produk diperbarui dari ' . $stockBefore . ' menjadi ' . $newStock,
            'subject_type' => 'product_variant',
            'subject_id' => $variantId,
            'old_values' => json_encode(['stock' => $stockBefore]),
            'new_values' => json_encode(['stock' => $newStock]),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Stok berhasil diperbarui.',
            'product_id' => $variantId,
            'new_stock' => $newStock,
            'summary' => $this->stockDashboardSummary(),
        ]);
    }

    public function websiteOrders()
    {
        $orders = DB::table('sales as s')
            ->leftJoin('sale_items as si', 'si.sale_id', '=', 's.id')
            ->select(
                's.*',
                DB::raw('COUNT(si.id) as item_lines'),
                DB::raw('COALESCE(SUM(si.qty), 0) as total_qty')
            )
            ->where('s.note', 'like', '%Website publik%')
            ->groupBy('s.id', 's.transaction_number', 's.branch_id', 's.user_id', 's.customer_id', 's.subtotal', 's.discount', 's.tax', 's.grand_total', 's.payment_method', 's.payment_status', 's.sale_status', 's.paid_amount', 's.change_amount', 's.note', 's.created_at', 's.updated_at')
            ->latest('s.id')
            ->get()
            ->map(function ($order) {
                $order->order_status = $this->orderStatusFromNote($order->note);
                $order->customer_name_text = $this->noteValue($order->note, 'Pembeli') ?: 'Walk-in Customer';
                $order->customer_phone_text = $this->noteValue($order->note, 'WA') ?: '-';

                return $order;
            });

        $statuses = $this->orderStatuses();
        $summary = [
            'total' => $orders->count(),
            'pending' => $orders->where('order_status', 'Menunggu Konfirmasi')->count(),
            'processing' => $orders->whereIn('order_status', ['Diproses', 'Dikirim'])->count(),
            'done' => $orders->where('order_status', 'Selesai')->count(),
        ];

        return view('admin.website-orders', compact('orders', 'statuses', 'summary'));
    }

    public function updateWebsiteOrderStatus(Request $request, int $sale)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $this->orderStatuses())],
        ]);

        $order = DB::table('sales')
            ->where('id', $sale)
            ->where('note', 'like', '%Website publik%')
            ->first();

        abort_if(!$order, 404);

        $oldStatus = $this->orderStatusFromNote($order->note);
        $newStatus = $data['status'];
        $saleStatus = match ($newStatus) {
            'Dibatalkan' => 'cancelled',
            default => 'completed',
        };
        $paymentStatus = $newStatus === 'Dibatalkan' ? 'cancelled' : $order->payment_status;

        DB::table('sales')->where('id', $sale)->update([
            'note' => $this->replaceOrderStatusNote($order->note, $newStatus),
            'sale_status' => $saleStatus,
            'payment_status' => $paymentStatus,
            'updated_at' => now(),
        ]);

        DB::table('activity_logs')->insert([
            'user_id' => 1,
            'action' => 'update_order_status',
            'module' => 'website_orders',
            'description' => 'Status pesanan website diubah dari ' . $oldStatus . ' menjadi ' . $newStatus,
            'subject_type' => 'sale',
            'subject_id' => $sale,
            'old_values' => json_encode(['status' => $oldStatus]),
            'new_values' => json_encode(['status' => $newStatus]),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function exportSalesCsv()
    {
        $rows = DB::table('sales')
            ->select('transaction_number', 'created_at', 'grand_total', 'payment_method', 'payment_status', 'sale_status', 'note')
            ->latest('id')
            ->get();

        return $this->csvDownload('laporan-penjualan.csv', ['No Transaksi', 'Tanggal', 'Total', 'Metode', 'Payment Status', 'Sale Status', 'Catatan'], $rows->map(fn ($row) => [
            $row->transaction_number,
            $row->created_at,
            $row->grand_total,
            $row->payment_method,
            $row->payment_status,
            $row->sale_status,
            $row->note,
        ]));
    }

    public function exportStockCsv()
    {
        $rows = $this->stockRows();

        return $this->csvDownload('laporan-stok.csv', ['Produk', 'SKU', 'Stok', 'Minimum Stok', 'Status'], $rows->map(fn ($row) => [
            $row->product_name . ' ' . $row->variant_name,
            $row->sku,
            $row->stock,
            $row->minimum_stock,
            (int) $row->stock <= (int) $row->minimum_stock ? 'Stok Rendah' : 'Aman',
        ]));
    }

    public function exportBestProductsCsv()
    {
        $rows = DB::table('sale_items as si')
            ->select('si.product_name', 'si.variant_name', DB::raw('SUM(si.qty) as total_qty'), DB::raw('SUM(si.subtotal) as total_sales'))
            ->groupBy('si.product_name', 'si.variant_name')
            ->orderByDesc('total_qty')
            ->get();

        return $this->csvDownload('produk-terlaris.csv', ['Produk', 'Varian', 'Qty Terjual', 'Revenue'], $rows->map(fn ($row) => [
            $row->product_name,
            $row->variant_name,
            $row->total_qty,
            $row->total_sales,
        ]));
    }

    private function csvDownload(string $filename, array $headers, $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function orderStatuses(): array
    {
        return ['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
    }

    private function orderStatusFromNote(?string $note): string
    {
        if ($note && preg_match('/Status Pesanan:\s*([^|]+)/', $note, $matches)) {
            return trim($matches[1]);
        }

        return 'Menunggu Konfirmasi';
    }

    private function replaceOrderStatusNote(?string $note, string $status): string
    {
        $note = trim((string) $note);
        if (preg_match('/Status Pesanan:\s*([^|]+)/', $note)) {
            return trim(preg_replace('/Status Pesanan:\s*([^|]+)/', 'Status Pesanan: ' . $status, $note));
        }

        return trim($note . ' | Status Pesanan: ' . $status);
    }

    private function noteValue(?string $note, string $key): ?string
    {
        if (!$note || !preg_match('/' . preg_quote($key, '/') . ':\s*([^|]+)/', $note, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function stockRows()
    {
        return DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('branch_stocks as bs', function ($join) {
                $join->on('bs.product_variant_id', '=', 'pv.id')->where('bs.branch_id', '=', 1);
            })
            ->select(
                'pv.id as product_variant_id',
                'p.name as product_name',
                'pv.variant_name',
                'pv.sku',
                'pv.minimum_stock',
                DB::raw('COALESCE(bs.stock, 0) as stock')
            )
            ->where('p.status', 'active')
            ->where('pv.status', 'active')
            ->orderBy('p.name')
            ->orderBy('pv.weight_gram')
            ->get();
    }

    private function stockDashboardSummary(): array
    {
        $lowStock = DB::table('branch_stocks as bs')
            ->join('product_variants as pv', 'pv.id', '=', 'bs.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('p.name', 'pv.variant_name', 'pv.sku', 'bs.stock', 'pv.minimum_stock')
            ->whereColumn('bs.stock', '<=', 'pv.minimum_stock')
            ->orderBy('bs.stock')
            ->get();

        return [
            'total_stock' => (int) DB::table('branch_stocks')->sum(DB::raw('stock - reserved_stock')),
            'low_stock_count' => $lowStock->count(),
            'low_stock_text' => $lowStock->take(4)->map(fn ($x) => $x->name . ' ' . $x->variant_name . ' tinggal ' . $x->stock . ' pack')->join(', ') . ($lowStock->count() > 4 ? ', dan lainnya' : ''),
        ];
    }

    public function invoices()
    {
        $invoices = DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('sales as s', 's.id', '=', 'i.sale_id')
            ->select('i.*', DB::raw('COALESCE(c.name, "Walk-in Customer") as customer_name'), 's.transaction_number')
            ->latest('i.id')
            ->limit(50)
            ->get();

        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->toDateString();
        $pageTitle = 'Invoice';

        return view('admin.invoices', compact('invoices', 'today', 'monthStart', 'monthEnd', 'pageTitle'));
    }

    public function reports()
    {
        $dailySales = DB::table('sales')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as transactions, SUM(grand_total) as revenue, SUM(grand_total - subtotal * 0.6) as estimated_profit')
            ->where('sale_status', 'completed')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('date')
            ->limit(14)
            ->get();

        $pageTitle = 'Laporan Bisnis';

        return view('admin.reports', compact('dailySales', 'pageTitle'));
    }

    public function ai()
    {
        $products = $this->productRows();
        $lowStock = $products->filter(fn ($row) => $row->stock <= $row->minimum_stock)->values();
        $avgDailyRevenue = (float) DB::table('sales')
            ->where('created_at', '>=', Carbon::today()->subDays(7))
            ->where('sale_status', 'completed')
            ->sum('grand_total') / 7;

        $projection = $avgDailyRevenue * 7;
        $insights = $this->businessInsights($topProducts, $slowProducts, $forecastProducts, $salesTrend, $projection);
        $pageTitle = 'AI Analytics Dashboard';

        return view('admin.ai', compact(
            'products',
            'lowStock',
            'projection',
            'salesTrend',
            'topProducts',
            'slowProducts',
            'forecastProducts',
            'totalRevenue',
            'totalTransactions',
            'estimatedProfit',
            'insights',
            'pageTitle'
        ));
    }

    private function businessInsights($topProducts, $slowProducts, $forecastProducts, $salesTrend, float $projection)
    {
        $insights = [];
        $currentWeek = (float) $salesTrend->slice(7)->sum('revenue');
        $previousWeek = (float) $salesTrend->slice(0, 7)->sum('revenue');

        if ($previousWeek > 0) {
            $change = (($currentWeek - $previousWeek) / $previousWeek) * 100;
            $direction = $change >= 0 ? 'meningkat' : 'menurun';
            $insights[] = 'Penjualan minggu ini ' . $direction . ' ' . number_format(abs($change), 1, ',', '.') . '% dibanding minggu lalu.';
        } elseif ($currentWeek > 0) {
            $insights[] = 'Penjualan minggu ini mulai terbentuk dengan revenue Rp ' . number_format($currentWeek, 0, ',', '.') . '.';
        }

        if ($topProducts->isNotEmpty()) {
            $top = $topProducts->first();
            $insights[] = 'Produk ' . $top->product_name . ' ' . $top->variant_name . ' menjadi produk terlaris dengan ' . (int) $top->total_qty . ' pack terjual dalam 30 hari.';
        }

        $slow = $slowProducts->first();
        if ($slow && (int) $slow->sold_30_days <= 2) {
            $insights[] = 'Produk ' . $slow->name . ' ' . $slow->variant_name . ' memiliki penjualan rendah, disarankan promosi, bundling, atau sampling.';
        }

        $stockRisk = $forecastProducts->first(fn ($product) => $product->days_until_stockout !== null && $product->days_until_stockout <= 7);
        if ($stockRisk) {
            $insights[] = 'Stok ' . $stockRisk->name . ' ' . $stockRisk->variant_name . ' diperkirakan habis dalam ' . (int) $stockRisk->days_until_stockout . ' hari.';
        }

        if ($projection > 0) {
            $insights[] = 'Forecast revenue 7 hari ke depan sekitar Rp ' . number_format($projection, 0, ',', '.') . ' berdasarkan moving average.';
        }

        return collect($insights)->whenEmpty(fn ($rows) => $rows->push('Belum cukup data transaksi untuk insight otomatis. Lakukan checkout POS agar AI Analytics membaca pola penjualan.'));
    }

    private function productRows()
    {
        return DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('branch_stocks as bs', 'bs.product_variant_id', '=', 'pv.id')
            ->select(
                'pv.id', 'p.name', 'p.slug', 'p.short_description', 'pv.variant_name', 'pv.sku', 'pv.barcode',
                'pv.selling_price', 'pv.reseller_price', 'pv.purchase_price', 'pv.minimum_stock',
                DB::raw('COALESCE(SUM(bs.stock - bs.reserved_stock), 0) as stock'),
                DB::raw('COALESCE(c.name, "Produk") as category_name')
            )
            ->where('p.status', 'active')
            ->where('pv.status', 'active')
            ->groupBy('pv.id', 'p.name', 'p.slug', 'p.short_description', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.weight_gram', 'pv.selling_price', 'pv.reseller_price', 'pv.purchase_price', 'pv.minimum_stock', 'c.name')
            ->orderBy('pv.weight_gram')
            ->get();
    }
}
