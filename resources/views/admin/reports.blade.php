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

@php($pageTitle = 'Laporan Bisnis')
<div class="card">
    <h3 class="font-poppins" style="margin-top:0">📈 Laporan Penjualan Harian</h3>
    <table class="table"><thead><tr><th>Tanggal</th><th>Transaksi</th><th>Revenue</th><th>Estimasi Profit</th></tr></thead><tbody>
        @forelse($dailySales as $r)
            <tr><td>{{ $r->date }}</td><td>{{ $r->transactions }}</td><td>Rp {{ number_format($r->revenue,0,',','.') }}</td><td>Rp {{ number_format($r->estimated_profit,0,',','.') }}</td></tr>
        @empty
            <tr><td colspan="4">Belum ada transaksi penjualan.</td></tr>
        @endforelse
    </tbody></table>
</div>
        </div>
    </main>
</div>
@endsection
