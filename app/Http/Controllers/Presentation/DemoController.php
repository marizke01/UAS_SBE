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
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');
            $chartSales[] = (float) DB::table('sales')
                ->whereDate('created_at', $date)
                ->where('sale_status', 'completed')
                ->sum('grand_total');
        }

        $bestProducts = DB::table('sale_items as si')
            ->select('si.product_name', 'si.variant_name', DB::raw('SUM(si.qty) as total_qty'), DB::raw('SUM(si.subtotal) as total_sales'))
            ->groupBy('si.product_name', 'si.variant_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'monthRevenue',
            'totalTransactions',
            'totalStock',
            'lowStock',
            'latestSales',
            'chartLabels',
            'chartSales',
            'bestProducts'
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

        return view('admin.invoices', compact('invoices'));
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

        return view('admin.reports', compact('dailySales'));
    }

    public function ai()
    {
        $products = $this->productRows();
        $lowStock = $products->filter(fn($row) => $row->stock <= $row->minimum_stock)->values();
        $avgDailyRevenue = (float) DB::table('sales')
            ->where('created_at', '>=', Carbon::today()->subDays(7))
            ->sum('grand_total') / 7;

        $projection = $avgDailyRevenue * 7;

        return view('admin.ai', compact('products', 'lowStock', 'projection'));
    }

    private function productRows()
    {
        return DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('branch_stocks as bs', 'bs.product_variant_id', '=', 'pv.id')
            ->select(
                'pv.id',
                'p.name',
                'p.slug',
                'p.short_description',
                'pv.variant_name',
                'pv.sku',
                'pv.barcode',
                'pv.selling_price',
                'pv.reseller_price',
                'pv.purchase_price',
                'pv.minimum_stock',
                DB::raw('COALESCE(SUM(bs.stock - bs.reserved_stock), 0) as stock'),
                DB::raw('COALESCE(c.name, "Produk") as category_name')
            )
            ->where('p.status', 'active')
            ->where('pv.status', 'active')
            ->groupBy('pv.id', 'p.name', 'p.slug', 'p.short_description', 'pv.variant_name', 'pv.sku', 'pv.barcode', 'pv.selling_price', 'pv.reseller_price', 'pv.purchase_price', 'pv.minimum_stock', 'c.name')
            ->orderBy('pv.weight_gram')
            ->get();
    }
}
