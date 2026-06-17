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

@php($pageTitle = 'Invoice')
<div class="card">
    <h3 class="font-poppins" style="margin-top:0">🧾 Invoice Otomatis</h3>
    <table class="table"><thead><tr><th>No Invoice</th><th>Transaksi</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead><tbody>
        @forelse($invoices as $i)
            <tr><td><strong>{{ $i->invoice_number }}</strong></td><td>{{ $i->transaction_number ?? '-' }}</td><td>{{ $i->customer_name }}</td><td>{{ $i->issue_date }}</td><td>Rp {{ number_format($i->grand_total,0,',','.') }}</td><td><span class="badge badge-success">{{ $i->status }}</span></td></tr>
        @empty
            <tr><td colspan="6">Belum ada invoice. Buat transaksi di POS untuk membuat invoice otomatis.</td></tr>
        @endforelse
    </tbody></table>
</div>
        </div>
    </main>
</div>
@endsection
