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

@php($pageTitle = 'AI Analytics')
<div class="grid grid-3" style="margin-bottom:20px">
    <div class="card" style="background:linear-gradient(135deg,var(--dark),var(--dark2));color:white"><h3>📈 Prediksi 7 Hari</h3><div class="font-poppins" style="font-size:30px;font-weight:900;color:var(--primary)">Rp {{ number_format($projection,0,',','.') }}</div><p style="color:rgba(255,255,255,.65)">Estimasi sederhana dari rata-rata revenue 7 hari terakhir.</p></div>
    <div class="card" style="background:linear-gradient(135deg,var(--dark),var(--dark2));color:white"><h3>📦 Rekomendasi Restock</h3><div class="font-poppins" style="font-size:30px;font-weight:900;color:var(--primary)">{{ $lowStock->count() }} Produk</div><p style="color:rgba(255,255,255,.65)">Produk dengan stok <= minimum stock.</p></div>
    <div class="card" style="background:linear-gradient(135deg,var(--dark),var(--dark2));color:white"><h3>🔥 Fokus Produk</h3><div class="font-poppins" style="font-size:24px;font-weight:900;color:var(--primary)">{{ $products->sortBy('stock')->first()->name ?? '-' }}</div><p style="color:rgba(255,255,255,.65)">Prioritas dari stok paling rendah.</p></div>
</div>
<div class="card">
    <h3 class="font-poppins" style="margin-top:0">💡 Rekomendasi AI untuk Presentasi</h3>
    @forelse($lowStock as $p)
        <div style="border-left:4px solid var(--primary);background:var(--gray-100);border-radius:12px;padding:14px;margin-bottom:10px"><strong>Restock {{ $p->name }} {{ $p->variant_name }}</strong><br>Stok saat ini {{ $p->stock }} pack, minimum {{ $p->minimum_stock }} pack. Rekomendasi: tambah minimal {{ max(50, $p->minimum_stock * 5) }} pack.</div>
    @empty
        <div style="border-left:4px solid var(--success);background:var(--gray-100);border-radius:12px;padding:14px">Semua stok masih aman. Fokus presentasi pada POS, dashboard, invoice, dan laporan.</div>
    @endforelse
</div>
        </div>
    </main>
</div>
@endsection
