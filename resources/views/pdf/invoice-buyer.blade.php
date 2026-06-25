<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @include('pdf.partials.style')
</head>
<body>
    <div class="header">
        <div class="brand">Amplang Tifanny ERP Invoice</div>
        <div class="muted">Invoice customer otomatis dari transaksi POS</div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <strong>Customer</strong><br>
                {{ $invoice->customer_name }}<br>
                Status pembayaran: {{ strtoupper($invoice->status) }}
            </td>
            <td class="right">
                <strong>{{ $invoice->invoice_number }}</strong><br>
                Transaksi: {{ $invoice->transaction_number ?? '-' }}<br>
                Tanggal: {{ $invoice->issue_date }}<br>
                Metode: {{ strtoupper($invoice->payment_method ?? '-') }}
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr><th>Produk</th><th class="right">Qty</th><th class="right">Harga</th><th class="right">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="width:100%">
            <tr><td>Subtotal</td><td class="right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon</td><td class="right">Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td></tr>
            <tr><td>Pajak</td><td class="right">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td></tr>
            <tr><td><strong>Total Pembayaran</strong></td><td class="right"><strong>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</strong></td></tr>
        </table>
    </div>

    <div class="footer">Amplang Tifanny ERP - Invoice tersimpan otomatis di riwayat transaksi customer dan admin.</div>
</body>
</html>
