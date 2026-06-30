@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
<div class="app">
    <aside class="sidebar">
        <div class="brand">Tifanny<span>ERP</span><div style="font-size:11px;color:rgba(255,255,255,.38);font-family:Inter;margin-top:3px">Queens Amplang</div></div>
        <nav class="menu">
            <div class="menu-label">Utama</div>
            <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">Dashboard</a>
            <a href="/admin/pos" class="{{ request()->is('admin/pos') ? 'active' : '' }}">POS Kasir</a>
            <div class="menu-label">Operasional</div>
            <a href="/admin/inventory" class="{{ request()->is('admin/inventory') ? 'active' : '' }}">Inventaris</a>
            <a href="/admin/cashiers" class="{{ request()->is('admin/cashiers') ? 'active' : '' }}">Manajemen Kasir</a>
            <a href="/admin/website-orders" class="{{ request()->is('admin/website-orders') ? 'active' : '' }}">Pesanan Website</a>
            <a href="/admin/invoices" class="{{ request()->is('admin/invoices') ? 'active' : '' }}">Invoice</a>
            <a href="/admin/purchase-order" class="{{ request()->is('admin/purchase-order*') ? 'active' : '' }}">Purchase Order</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Pengaturan</div>
            <a href="/admin/account" class="{{ request()->is('admin/account') ? 'active' : '' }}">Akun Saya</a>
            <div class="menu-label">Publik</div>
            <a href="/">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar"><strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle ?? 'Laporan Bisnis' }}</strong><div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div></div>
        <div class="content">
            <div class="card" style="margin-bottom:20px">
                <h3 class="font-poppins" style="margin-top:0">Export Laporan Seller</h3>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <a class="btn btn-primary" href="{{ route('admin.seller-invoices.daily', ['date' => now()->toDateString()]) }}">Download Harian Hari Ini</a>
                    <a class="btn btn-dark" href="{{ route('admin.seller-invoices.full', ['month' => now()->format('Y-m')]) }}">Download Bulan Ini</a>
                    <a class="btn btn-outline" href="{{ route('admin.seller-invoices.full', ['year' => now()->format('Y')]) }}">Download Tahun Ini</a>
                </div>
            </div>

            <div class="card" style="margin-bottom:20px">
                <h3 class="font-poppins" style="margin-top:0">Export Excel / CSV</h3>
                <p style="margin:0 0 14px;color:var(--gray-600);font-size:13px">File CSV bisa langsung dibuka di Microsoft Excel untuk kebutuhan laporan owner.</p>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <a class="btn btn-primary" href="{{ route('admin.exports.sales') }}">Export Penjualan CSV</a>
                    <a class="btn btn-outline" href="{{ route('admin.exports.stock') }}">Export Stok CSV</a>
                    <a class="btn btn-dark" href="{{ route('admin.exports.best-products') }}">Export Produk Terlaris CSV</a>
                </div>
            </div>

            <div class="card">
                <h3 class="font-poppins" style="margin-top:0">Laporan Penjualan Harian</h3>
                <table class="table"><thead><tr><th>Tanggal</th><th>Transaksi</th><th>Revenue</th><th>Estimasi Profit</th><th>PDF</th></tr></thead><tbody>
                    @forelse($dailySales as $r)
                        <tr>
                            <td>{{ $r->date }}</td>
                            <td>{{ $r->transactions }}</td>
                            <td>Rp {{ number_format($r->revenue,0,',','.') }}</td>
                            <td>Rp {{ number_format($r->estimated_profit,0,',','.') }}</td>
                            <td><a class="btn btn-outline" href="{{ route('admin.seller-invoices.daily', ['date' => $r->date]) }}">Download</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada transaksi penjualan.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
        </div>
    </main>
</div>
@endsection
