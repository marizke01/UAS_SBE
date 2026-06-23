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
            <a href="/admin/invoices" class="{{ request()->is('admin/invoices') ? 'active' : '' }}">Invoice</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="/">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar"><strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle ?? 'Invoice' }}</strong><div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div></div>
        <div class="content">
            <div class="grid grid-3" style="margin-bottom:20px">
                <form class="card" method="GET" action="{{ route('admin.seller-invoices.daily') }}">
                    <h3 class="font-poppins" style="margin-top:0">Laporan Harian</h3>
                    <label>Tanggal transaksi</label>
                    <input class="form-control" type="date" name="date" value="{{ $today }}" style="margin:8px 0 12px">
                    <button class="btn btn-primary" style="width:100%">Download PDF Harian</button>
                </form>
                <form class="card" method="GET" action="{{ route('admin.seller-invoices.full') }}">
                    <h3 class="font-poppins" style="margin-top:0">Full Report Range</h3>
                    <label>Dari tanggal</label>
                    <input class="form-control" type="date" name="start_date" value="{{ $monthStart }}" style="margin:8px 0">
                    <label>Sampai tanggal</label>
                    <input class="form-control" type="date" name="end_date" value="{{ $monthEnd }}" style="margin:8px 0 12px">
                    <button class="btn btn-dark" style="width:100%">Download PDF Range</button>
                </form>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">Bulanan / Tahunan</h3>
                    <form method="GET" action="{{ route('admin.seller-invoices.full') }}" style="margin-bottom:12px">
                        <label>Bulan</label>
                        <input class="form-control" type="month" name="month" value="{{ now()->format('Y-m') }}" style="margin:8px 0">
                        <button class="btn btn-outline" style="width:100%">Download PDF Bulanan</button>
                    </form>
                    <form method="GET" action="{{ route('admin.seller-invoices.full') }}">
                        <label>Tahun</label>
                        <input class="form-control" type="number" name="year" value="{{ now()->format('Y') }}" min="2020" max="2100" style="margin:8px 0 12px">
                        <button class="btn btn-outline" style="width:100%">Download PDF Tahunan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3 class="font-poppins" style="margin-top:0">Invoice Otomatis Buyer & Seller</h3>
                <table class="table">
                    <thead><tr><th>No Invoice</th><th>Transaksi</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Download</th></tr></thead>
                    <tbody>
                        @forelse($invoices as $i)
                            <tr>
                                <td><strong>{{ $i->invoice_number }}</strong></td>
                                <td>{{ $i->transaction_number ?? '-' }}</td>
                                <td>{{ $i->customer_name }}</td>
                                <td>{{ $i->issue_date }}</td>
                                <td>Rp {{ number_format($i->grand_total,0,',','.') }}</td>
                                <td><span class="badge badge-success">{{ $i->status }}</span></td>
                                <td style="display:flex;gap:8px;flex-wrap:wrap">
                                    <a class="btn btn-primary" href="{{ route('admin.invoices.buyer.download', $i->id) }}">Buyer PDF</a>
                                    @if($i->sale_id)
                                        <a class="btn btn-outline" href="{{ route('admin.seller-invoices.transaction', $i->sale_id) }}">Seller PDF</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">Belum ada invoice. Buat transaksi di POS untuk membuat invoice otomatis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
@endsection
