<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @include('pdf.partials.style')
</head>
<body>
    <div class="header">
        <div class="brand">Amplang Tifanny ERP Invoice</div>
        <div class="muted">Seller internal report per transaksi</div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <strong>Transaksi</strong><br>
                {{ $sale->transaction_number }}<br>
                Customer: {{ $sale->customer_name }}
            </td>
            <td class="right">
                Tanggal: {{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}<br>
                Metode: {{ strtoupper($sale->payment_method) }}<br>
                Status: {{ strtoupper($sale->payment_status) }}
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr><th>Produk</th><th>SKU</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Subtotal</th><th class="right">Est. Profit</th></tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->product_name }} {{ $item->variant_name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format(($item->price - $item->purchase_price) * $item->qty, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total pendapatan: <strong>Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</strong><br>
        Total produk terjual: <strong>{{ $summary['items_sold'] }} pack</strong><br>
        Estimasi profit: <strong>Rp {{ number_format($summary['profit'], 0, ',', '.') }}</strong>
    </div>

    <div class="footer">Amplang Tifanny ERP - Laporan internal owner.</div>
</body>
</html>
