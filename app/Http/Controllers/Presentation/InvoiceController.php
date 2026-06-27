<?php

namespace App\Http\Controllers\Presentation;

use App\Http\Controllers\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function buyer(int $invoice)
    {
        $invoiceRow = $this->invoiceQuery()->where('i.id', $invoice)->firstOrFail();
        $items = DB::table('invoice_items')->where('invoice_id', $invoiceRow->id)->get();

        return $this->pdf('pdf.invoice-buyer', [
            'invoice' => $invoiceRow,
            'items' => $items,
        ], $invoiceRow->invoice_number . '.pdf');
    }

    public function sellerTransaction(int $sale)
    {
        $saleRow = $this->saleQuery()->where('s.id', $sale)->firstOrFail();
        $items = $this->saleItemsQuery()->where('si.sale_id', $saleRow->id)->get();

        return $this->pdf('pdf.seller-transaction', [
            'sale' => $saleRow,
            'items' => $items,
            'summary' => $this->summaryForSales(collect([$saleRow]), $items),
        ], 'seller-transaction-' . $saleRow->transaction_number . '.pdf');
    }

    public function sellerDaily(Request $request)
    {
        $date = Carbon::parse($request->query('date', today()->toDateString()))->toDateString();
        $sales = $this->saleQuery()
            ->whereDate('s.created_at', $date)
            ->orderBy('s.created_at')
            ->get();
        $items = $this->saleItemsFor($sales->pluck('id'));

        return $this->pdf('pdf.seller-report', [
            'title' => 'Daily Seller Report',
            'period' => Carbon::parse($date)->translatedFormat('d F Y'),
            'sales' => $sales,
            'items' => $items,
            'summary' => $this->summaryForSales($sales, $items),
            'topProducts' => $this->topProducts($items),
        ], 'seller-daily-' . $date . '.pdf');
    }

    public function sellerFull(Request $request)
    {
        [$start, $end, $period] = $this->resolvePeriod($request);
        $sales = $this->saleQuery()
            ->whereBetween('s.created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('s.created_at')
            ->get();
        $items = $this->saleItemsFor($sales->pluck('id'));

        return $this->pdf('pdf.seller-report', [
            'title' => 'Full Seller Report',
            'period' => $period,
            'sales' => $sales,
            'items' => $items,
            'summary' => $this->summaryForSales($sales, $items),
            'topProducts' => $this->topProducts($items),
        ], 'seller-full-' . $start->toDateString() . '-' . $end->toDateString() . '.pdf');
    }

    private function invoiceQuery()
    {
        return DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('sales as s', 's.id', '=', 'i.sale_id')
            ->select('i.*', DB::raw('COALESCE(c.name, "Walk-in Customer") as customer_name'), 's.transaction_number', 's.payment_method');
    }

    private function saleQuery()
    {
        return DB::table('sales as s')
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->select('s.*', DB::raw('COALESCE(c.name, "Walk-in Customer") as customer_name'));
    }

    private function saleItemsQuery()
    {
        return DB::table('sale_items as si')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'si.product_variant_id')
            ->select('si.*', DB::raw('COALESCE(pv.production_cost, si.price * 0.6) as production_cost'));
    }

    private function saleItemsFor($saleIds)
    {
        if ($saleIds->isEmpty()) {
            return collect();
        }

        return $this->saleItemsQuery()->whereIn('si.sale_id', $saleIds)->get();
    }

    private function summaryForSales($sales, $items): array
    {
        $revenue = (float) $sales->sum('grand_total');
        $profit = (float) $items->sum(fn ($item) => ((float) $item->price - (float) $item->production_cost) * (int) $item->qty);

        return [
            'transactions' => $sales->count(),
            'revenue' => $revenue,
            'items_sold' => (int) $items->sum('qty'),
            'profit' => $profit,
            'margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
    }

    private function topProducts($items)
    {
        return $items
            ->groupBy(fn ($item) => $item->product_name . ' ' . $item->variant_name)
            ->map(fn ($rows, $name) => (object) [
                'name' => $name,
                'qty' => (int) $rows->sum('qty'),
                'revenue' => (float) $rows->sum('subtotal'),
            ])
            ->sortByDesc('qty')
            ->take(5)
            ->values();
    }

    private function resolvePeriod(Request $request): array
    {
        if ($request->filled('month')) {
            $start = Carbon::parse($request->query('month') . '-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return [$start, $end, 'Bulan ' . $start->translatedFormat('F Y')];
        }

        if ($request->filled('year')) {
            $start = Carbon::createFromDate((int) $request->query('year'), 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();

            return [$start, $end, 'Tahun ' . $start->format('Y')];
        }

        $start = Carbon::parse($request->query('start_date', today()->startOfMonth()->toDateString()));
        $end = Carbon::parse($request->query('end_date', today()->toDateString()));

        return [$start, $end, $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y')];
    }

    private function pdf(string $view, array $data, string $filename)
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
