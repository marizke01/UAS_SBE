<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $cashierId = $this->cashierId();

        $todayTransactions = DB::table('sales')
            ->where('sale_status', 'completed')
            ->whereDate('created_at', $today)
            ->where('note', 'like', '%POS kasir%')
            ->where('user_id', $cashierId)
            ->count();

        $todaySales = (float) DB::table('sales')
            ->where('sale_status', 'completed')
            ->whereDate('created_at', $today)
            ->where('note', 'like', '%POS kasir%')
            ->where('user_id', $cashierId)
            ->sum('grand_total');

        $todayProducts = (int) DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.sale_status', 'completed')
            ->whereDate('s.created_at', $today)
            ->where('s.note', 'like', '%POS kasir%')
            ->where('s.user_id', $cashierId)
            ->sum('si.qty');

        $latestTransactions = $this->cashierSalesQuery()
            ->latest('s.id')
            ->limit(6)
            ->get();

        $lowStock = $this->stockRows()
            ->filter(fn ($row) => (int) $row->stock <= (int) $row->minimum_stock)
            ->take(5)
            ->values();

        return view('cashier.dashboard', compact(
            'todayTransactions',
            'todaySales',
            'todayProducts',
            'latestTransactions',
            'lowStock'
        ));
    }

    public function pos()
    {
        $products = $this->productRows();

        return view('cashier.pos', compact('products'));
    }

    public function history()
    {
        $transactions = $this->cashierSalesQuery()
            ->latest('s.id')
            ->limit(50)
            ->get();

        return view('cashier.history', compact('transactions'));
    }

    public function stock()
    {
        $stocks = $this->stockRows();

        return view('cashier.stock', compact('stocks'));
    }

    private function cashierSalesQuery()
    {
        return DB::table('sales as s')
            ->leftJoin('sale_items as si', 'si.sale_id', '=', 's.id')
            ->select(
                's.id',
                's.transaction_number',
                's.created_at',
                's.grand_total',
                's.payment_method',
                's.payment_status',
                's.sale_status',
                's.note',
                DB::raw('COALESCE(SUM(si.qty), 0) as total_qty')
            )
            ->where('s.note', 'like', '%POS kasir%')
            ->where('s.user_id', $this->cashierId())
            ->groupBy('s.id', 's.transaction_number', 's.created_at', 's.grand_total', 's.payment_method', 's.payment_status', 's.sale_status', 's.note');
    }

    private function cashierId(): int
    {
        return (int) session('cashier_user.id', 0);
    }

    private function productRows()
    {
        return DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('branch_stocks as bs', function ($join) {
                $join->on('bs.product_variant_id', '=', 'pv.id')->where('bs.branch_id', '=', 1);
            })
            ->select(
                'pv.id',
                'p.name',
                'pv.variant_name',
                'pv.sku',
                'pv.barcode',
                'pv.weight_gram',
                'pv.selling_price',
                'pv.minimum_stock',
                DB::raw('COALESCE(bs.stock - bs.reserved_stock, 0) as stock')
            )
            ->where('p.status', 'active')
            ->where('pv.status', 'active')
            ->orderBy('pv.weight_gram')
            ->get();
    }

    private function stockRows()
    {
        return DB::table('branch_stocks as bs')
            ->join('product_variants as pv', 'pv.id', '=', 'bs.product_variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->select('p.name', 'pv.variant_name', 'pv.sku', 'pv.minimum_stock', DB::raw('bs.stock - bs.reserved_stock as stock'))
            ->orderBy('p.name')
            ->orderBy('pv.weight_gram')
            ->get();
    }
}
