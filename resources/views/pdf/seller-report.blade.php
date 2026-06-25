<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @include('pdf.partials.style')
</head>
<body>
    <div class="header">
        <div class="brand">Amplang Tifanny ERP Invoice</div>
        <div class="muted">{{ $title }} - {{ $period }}</div>
    </div>

    <div class="summary">
        Jumlah transaksi: <strong>{{ $summary['transactions'] }}</strong><br>
        Total penjualan: <strong>Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</strong><br>
        Produk terjual: <strong>{{ $summary['items_sold'] }} pack</strong><br>
        Estimasi profit: <strong>Rp {{ number_format($summary['profit'], 0, ',', '.') }}</strong><br>
        Margin estimasi: <strong>{{ number_format($summary['margin'], 1, ',', '.') }}%</strong>
    </div>

    <h3>Daftar Transaksi</h3>
    <table class="data">
        <thead>
            <tr><th>Tanggal</th><th>No Transaksi</th><th>Customer</th><th class="right">Total</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->transaction_number }}</td>
                    <td>{{ $sale->customer_name }}</td>
                    <td class="right">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                    <td>{{ strtoupper($sale->payment_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Produk Terlaris</h3>
    <table class="data">
        <thead>
            <tr><th>Produk</th><th class="right">Qty</th><th class="right">Revenue</th></tr>
        </thead>
        <tbody>
            @forelse($topProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="right">{{ $product->qty }}</td>
                    <td class="right">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada produk terjual.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Amplang Tifanny ERP - Summary, total penjualan, produk terlaris, dan performa bisnis.</div>
</body>
</html>
