@extends('cashier.layout', ['pageTitle' => 'Dashboard Kasir', 'title' => 'Dashboard Kasir - Tifanny ERP'])

@section('cashier_content')
<div class="cashier-stats">
    <div class="cashier-card">
        <div class="cashier-muted">Transaksi Hari Ini</div>
        <div class="cashier-stat-number">{{ $todayTransactions }}</div>
    </div>
    <div class="cashier-card">
        <div class="cashier-muted">Penjualan Hari Ini</div>
        <div class="cashier-stat-number">Rp {{ number_format($todaySales, 0, ',', '.') }}</div>
    </div>
    <div class="cashier-card">
        <div class="cashier-muted">Produk Terjual</div>
        <div class="cashier-stat-number">{{ $todayProducts }} pack</div>
    </div>
</div>

<div class="grid grid-3" style="grid-template-columns:1.3fr .7fr;align-items:start">
    <section class="cashier-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px">
            <h2 class="font-poppins" style="margin:0;color:var(--dark)">Transaksi POS Terbaru</h2>
            <a class="btn btn-primary" href="{{ route('cashier.pos') }}">Buka POS</a>
        </div>
        <table class="table">
            <thead><tr><th>No Transaksi</th><th>Tanggal</th><th>Qty</th><th>Total</th></tr></thead>
            <tbody>
                @forelse($latestTransactions as $trx)
                    <tr>
                        <td>{{ $trx->transaction_number }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ (int) $trx->total_qty }} pack</td>
                        <td>Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Belum ada transaksi POS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="cashier-card">
        <h2 class="font-poppins" style="margin:0 0 14px;color:var(--dark)">Stok Menipis</h2>
        @forelse($lowStock as $stock)
            <div style="display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid var(--gray-200);padding:11px 0">
                <div>
                    <strong>{{ $stock->name }}</strong><br>
                    <small class="cashier-muted">{{ $stock->variant_name }} - {{ $stock->sku }}</small>
                </div>
                <strong style="color:var(--danger)">{{ (int) $stock->stock }}</strong>
            </div>
        @empty
            <p class="cashier-muted">Semua stok masih aman.</p>
        @endforelse
    </section>
</div>
@endsection
