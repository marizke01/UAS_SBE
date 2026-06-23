<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
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
            'todayTransactions', 'avgOrderValue', 'monthProfit', 'invoiceCount',
            'lowStock', 'latestSales', 'chartLabels', 'chartSales', 'chartTransactions',
            'bestProducts', 'pageTitle'
        ));
    }

    public function inventory()
    {
        $stocks = DB::table('branch_stocks as bs')
            ->join('branches as b', 'b.id', '=', 'bs.branch_id')
            ->join('product_variants as pv', 'pv.id', '=', 'bs.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'pv.supplier_id')
            ->select('bs.*', 'b.name as branch_name', 'p.name as product_name', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.minimum_stock', 'pv.selling_price', 'pv.reseller_price', 's.name as supplier_name')
            ->orderBy('p.name')
            ->orderBy('pv.weight_gram')
            ->get();

        return view('admin.inventory', compact('stocks'));
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
        $lowStock = $products->filter(fn ($row) => (int) $row->stock <= (int) $row->minimum_stock)->values();

        $salesTrend = collect(range(13, 0))->map(function ($day) {
            $date = Carbon::today()->subDays($day);
            $row = DB::table('sales')
                ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as revenue')
                ->whereDate('created_at', $date)
                ->where('sale_status', 'completed')
                ->first();

            return (object) [
                'label' => $date->translatedFormat('d M'),
                'date' => $date->toDateString(),
                'transactions' => (int) $row->transactions,
                'revenue' => (float) $row->revenue,
            ];
        });

        $topProducts = DB::table('sale_items as si')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->select(
                'si.product_name',
                'si.variant_name',
                DB::raw('SUM(si.qty) as total_qty'),
                DB::raw('SUM(si.subtotal) as revenue'),
                DB::raw('SUM((si.price - COALESCE(pv.purchase_price, si.price * 0.6)) * si.qty) as profit')
            )
            ->where('si.created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('si.product_name', 'si.variant_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $slowProducts = $products->map(function ($product) {
            $sold = (int) DB::table('sale_items')
                ->where('product_variant_id', $product->id)
                ->where('created_at', '>=', Carbon::today()->subDays(30))
                ->sum('qty');

            $product->sold_30_days = $sold;

            return $product;
        })->sortBy('sold_30_days')->take(5)->values();

        $forecastProducts = $products->map(function ($product) {
            $sold14Days = (int) DB::table('sale_items')
                ->where('product_variant_id', $product->id)
                ->where('created_at', '>=', Carbon::today()->subDays(14))
                ->sum('qty');

            $averageDaily = $sold14Days / 14;
            $daysLeft = $averageDaily > 0 ? floor((int) $product->stock / $averageDaily) : null;
            $recommendedRestock = max((int) $product->minimum_stock * 3, (int) ceil($averageDaily * 14 - (int) $product->stock));

            $product->avg_daily_sales = $averageDaily;
            $product->days_until_stockout = $daysLeft;
            $product->recommended_restock = max(0, $recommendedRestock);

            return $product;
        })->sortBy(fn ($product) => $product->days_until_stockout ?? 9999)->values();

        $totalRevenue = (float) DB::table('sales')->where('sale_status', 'completed')->sum('grand_total');
        $totalTransactions = DB::table('sales')->where('sale_status', 'completed')->count();
        $estimatedProfit = (float) DB::table('sale_items as si')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->sum(DB::raw('(si.price - COALESCE(pv.purchase_price, si.price * 0.6)) * si.qty'));

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
                'pv.id', 'p.name', 'p.slug', 'p.short_description', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.weight_gram',
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
