@extends('cashier.layout', ['pageTitle' => 'Riwayat Transaksi', 'title' => 'Riwayat Kasir - Tifanny ERP'])

@section('cashier_content')
<section class="cashier-card">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px">
        <div>
            <h2 class="font-poppins" style="margin:0;color:var(--dark)">Riwayat Transaksi POS</h2>
            <p class="cashier-muted" style="margin:6px 0 0">Menampilkan transaksi yang dibuat melalui POS kasir/admin.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('cashier.pos') }}">Transaksi Baru</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Qty</th>
                <th>Pembayaran</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trx)
                <tr>
                    <td>{{ $trx->transaction_number }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ (int) $trx->total_qty }} pack</td>
                    <td>{{ strtoupper($trx->payment_method) }}</td>
                    <td><span class="badge badge-success">{{ $trx->sale_status }}</span></td>
                    <td>Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada transaksi POS.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
