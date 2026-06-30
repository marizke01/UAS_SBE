@extends('layouts.tifanny', ['title' => $pageTitle ?? 'Purchase Orders'])

@section('body')
<style>
    .po-head {display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:24px}
    .po-search-bar {display:flex;gap:12px;margin-bottom:20px}
    .search-input {flex:1;border:1px solid var(--gray-200);border-radius:10px;padding:10px 14px;font:inherit}
    .search-input:focus {outline:2px solid rgba(245,147,64,.25);border-color:var(--primary-dark)}
    .status-badge {display:inline-flex;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:capitalize}
    .status-draft {background:rgba(245,158,11,.14);color:#D97706}
    .status-sent {background:rgba(59,130,246,.14);color:#2563EB}
    .status-completed {background:rgba(34,197,94,.12);color:#15803D}
    .action-btn-group {display:flex;gap:8px}
    .action-btn {padding:6px 12px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;border:none}
    .btn-view {background:rgba(57,48,83,.08);color:var(--dark)}
    .btn-view:hover {background:rgba(57,48,83,.15)}
    .btn-pdf {background:rgba(239,68,68,.08);color:#DC2626;border:1px solid rgba(239,68,68,.18)}
    .btn-pdf:hover {background:rgba(239,68,68,.15)}
    .btn-email {background:rgba(245,147,64,.12);color:var(--primary-dark);border:1px solid rgba(245,147,64,.2)}
    .btn-email:hover {background:rgba(245,147,64,.22)}
    .po-table th {font-weight:800}
    .empty-state {text-align:center;padding:48px 20px;color:var(--gray-600)}
    .empty-state-emoji {font-size:48px;margin-bottom:12px}
</style>

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
            <a href="/admin/purchase-order" class="active">Purchase Order</a>
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
        <div class="topbar">
            <strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle }}</strong>
            <div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div>
        </div>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            <div class="po-head">
                <div>
                    <h2 class="font-poppins" style="margin:0;color:var(--dark)">Manajemen Purchase Order</h2>
                    <p style="margin:4px 0 0;color:var(--gray-600);font-size:14px">Buat dan kelola pesanan barang ke supplier/produksi.</p>
                </div>
                <a href="{{ route('admin.purchase-order.create') }}" class="btn btn-primary" style="padding:12px 20px;box-shadow:0 4px 15px rgba(255,178,107,0.3)">
                    + Buat PO Baru
                </a>
            </div>

            <div class="card">
                <div class="po-search-bar">
                    <input type="text" id="poSearchInput" class="search-input" placeholder="Cari berdasarkan nomor PO atau status...">
                </div>

                <div style="overflow-x:auto">
                    <table class="table po-table" id="poTable">
                        <thead>
                            <tr>
                                <th>No. PO</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th style="text-align:center">Jumlah Produk</th>
                                <th style="text-align:center">Total Qty</th>
                                <th style="text-align:right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $po)
                            <tr>
                                <td style="font-weight:700;color:var(--dark)">{{ $po->po_number }}</td>
                                <td>{{ date('d M Y, H:i', strtotime($po->created_at)) }}</td>
                                <td>
                                    @if($po->status === 'draft')
                                        <span class="status-badge status-draft">Draft</span>
                                    @elseif($po->status === 'sent')
                                        <span class="status-badge status-sent">Terkirim</span>
                                    @else
                                        <span class="status-badge status-completed">Selesai</span>
                                    @endif
                                </td>
                                <td style="text-align:center;font-weight:600">{{ $po->total_items }} item</td>
                                <td style="text-align:center;font-weight:600">{{ number_format($po->total_qty, 0, ',', '.') }} pack</td>
                                <td style="text-align:right">
                                    <div class="action-btn-group" style="justify-content:flex-end">
                                        <a href="{{ route('admin.purchase-order.show', $po->id) }}" class="action-btn btn-view" title="Lihat Detail">
                                            Lihat
                                        </a>
                                        <a href="{{ route('admin.purchase-order.pdf', $po->id) }}" class="action-btn btn-pdf" title="Download PDF">
                                            PDF
                                        </a>
                                        <a href="{{ route('admin.purchase-order.show', $po->id) }}#email-card" class="action-btn btn-email" title="Kirim Email">
                                            Kirim Email
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-emoji">📦</div>
                                        <strong>Belum Ada Purchase Order</strong>
                                        <p style="margin:6px 0 0;font-size:13px">Belum ada pemesanan barang yang terdaftar. Klik button "Buat PO Baru" di atas untuk memesan barang.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.getElementById('poSearchInput').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#poTable tbody tr');
        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>
@endsection
