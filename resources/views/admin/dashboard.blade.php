@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
<div class="app">
    <aside class="sidebar">
        <div class="brand">Tifanny<span>ERP</span><div style="font-size:11px;color:rgba(255,255,255,.38);font-family:Inter;margin-top:3px">Demo Presentasi Hari Ini</div></div>
        <nav class="menu">
            <div class="menu-label">Utama</div>
            <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">📊 Dashboard</a>
            <a href="/admin/pos" class="{{ request()->is('admin/pos') ? 'active' : '' }}">🛒 POS Kasir</a>
            <div class="menu-label">Operasional</div>
            <a href="/admin/inventory" class="{{ request()->is('admin/inventory') ? 'active' : '' }}">📦 Inventaris</a>
            <a href="/admin/invoices" class="{{ request()->is('admin/invoices') ? 'active' : '' }}">🧾 Invoice</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">📈 Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">🤖 AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="/">🌐 Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar"><strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle ?? 'Dashboard' }}</strong><div style="margin-left:auto;color:var(--gray-600);font-size:14px">👤 Tifanny Admin</div></div>
        <div class="content">

@php($pageTitle = 'Dashboard ERP')
@if($lowStock->count())
    <div class="alert alert-danger">⚠️ Stok rendah: {{ $lowStock->map(fn($x) => $x->name.' '.$x->variant_name.' tinggal '.$x->stock)->join(', ') }}</div>
@endif
<div class="grid grid-4" style="margin-bottom:20px">
    <div class="card stat"><div class="num">Rp {{ number_format($todaySales,0,',','.') }}</div><div class="label">Penjualan Hari Ini</div></div>
    <div class="card stat"><div class="num">Rp {{ number_format($monthRevenue,0,',','.') }}</div><div class="label">Revenue Bulan Ini</div></div>
    <div class="card stat"><div class="num">{{ number_format($totalTransactions,0,',','.') }}</div><div class="label">Total Transaksi</div></div>
    <div class="card stat"><div class="num">{{ number_format($totalStock,0,',','.') }}</div><div class="label">Total Stok Pack</div></div>
</div>
<div class="grid" style="grid-template-columns:2fr 1fr;margin-bottom:20px">
    <div class="card"><h3 class="font-poppins" style="margin-top:0">📈 Grafik Penjualan 7 Hari</h3><canvas id="salesChart" height="110"></canvas></div>
    <div class="card"><h3 class="font-poppins" style="margin-top:0">🔥 Produk Terlaris</h3>
        @forelse($bestProducts as $p)
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--gray-200);padding:10px 0"><span>{{ $p->product_name }} {{ $p->variant_name }}</span><strong>{{ $p->total_qty }} pack</strong></div>
        @empty
            <p style="color:var(--gray-600)">Belum ada transaksi. Buat transaksi di POS untuk memunculkan data.</p>
        @endforelse
    </div>
</div>
<div class="card">
    <h3 class="font-poppins" style="margin-top:0">🧾 Transaksi Terbaru</h3>
    <table class="table"><thead><tr><th>No Transaksi</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead><tbody>
        @forelse($latestSales as $sale)
            <tr><td><strong>{{ $sale->transaction_number }}</strong></td><td>{{ $sale->customer_name }}</td><td>Rp {{ number_format($sale->grand_total,0,',','.') }}</td><td>{{ strtoupper($sale->payment_method) }}</td><td><span class="badge badge-success">{{ $sale->payment_status }}</span></td></tr>
        @empty
            <tr><td colspan="5">Belum ada transaksi POS. Silakan demo melalui menu POS Kasir.</td></tr>
        @endforelse
    </tbody></table>
</div>
<script>
new Chart(document.getElementById('salesChart'), {type:'line',data:{labels:@json($chartLabels),datasets:[{label:'Revenue',data:@json($chartSales),borderColor:'#FFB26B',backgroundColor:'rgba(255,178,107,.18)',fill:true,tension:.35}]},options:{plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>'Rp '+new Intl.NumberFormat('id-ID').format(v)}}}}});
</script>
        </div>
    </main>
</div>
@endsection
