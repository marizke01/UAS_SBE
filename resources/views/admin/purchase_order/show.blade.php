@extends('layouts.tifanny', ['title' => $pageTitle ?? 'Detail Purchase Order'])

@section('body')
<style>
    .po-detail-head {display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:24px}
    .po-meta-grid {display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:20px;margin-bottom:24px}
    .po-meta-card {background:var(--gray-100);border:1px solid var(--gray-200);border-radius:12px;padding:16px}
    .po-meta-label {font-size:12px;color:var(--gray-400);text-transform:uppercase;font-weight:800;letter-spacing:.8px}
    .po-meta-value {font-size:18px;font-weight:700;color:var(--dark);margin-top:6px}
    .status-badge {display:inline-flex;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:800;text-transform:capitalize}
    .status-draft {background:rgba(245,158,11,.14);color:#D97706}
    .status-sent {background:rgba(59,130,246,.14);color:#2563EB}
    .status-completed {background:rgba(34,197,94,.12);color:#15803D}
    .email-card {border:1px solid #ffd4a3;background:linear-gradient(135deg, #fffcf9, #ffffff);border-radius:var(--radius);padding:24px;margin-top:24px}
    .btn-group {display:flex;gap:12px;margin-top:20px}
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
                <div class="alert alert-success" style="margin-bottom:20px">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if(session('success_email'))
                <div class="alert alert-success" style="background:rgba(34,197,94,.10);border:1px solid rgba(34,197,94,.25);color:#15803d;padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:14px">
                    <span style="font-size:24px">✉️</span>
                    <div>
                        <strong style="font-size:16px">Email Terkirim!</strong><br>
                        <span style="font-size:14px">{{ session('success_email') }}</span>
                    </div>
                </div>
            @endif

            <div class="po-detail-head">
                <div>
                    <a href="{{ route('admin.purchase-order.index') }}" style="color:var(--primary-dark);font-weight:700;font-size:14px;display:inline-flex;align-items:center;gap:6px;margin-bottom:10px">
                        ← Kembali ke Daftar PO
                    </a>
                    <h2 class="font-poppins" style="margin:0;color:var(--dark)">Rincian Purchase Order</h2>
                </div>
                <div class="btn-group" style="margin:0">
                    <a href="{{ route('admin.purchase-order.pdf', $po->id) }}" class="btn btn-outline" style="border-color:#EF4444;color:#EF4444;background:#fff5f5">
                        📥 Download PDF
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="po-meta-grid">
                    <div class="po-meta-card">
                        <div class="po-meta-label">Nomor Purchase Order</div>
                        <div class="po-meta-value">{{ $po->po_number }}</div>
                    </div>
                    <div class="po-meta-card">
                        <div class="po-meta-label">Tanggal PO dibuat</div>
                        <div class="po-meta-value">{{ date('d M Y, H:i', strtotime($po->created_at)) }}</div>
                    </div>
                    <div class="po-meta-card">
                        <div class="po-meta-label">Status Pesanan</div>
                        <div style="margin-top:8px">
                            @if($po->status === 'draft')
                                <span class="status-badge status-draft" style="font-size:14px;padding:6px 14px">Draft</span>
                            @elseif($po->status === 'sent')
                                <span class="status-badge status-sent" style="font-size:14px;padding:6px 14px">Terkirim</span>
                            @else
                                <span class="status-badge status-completed" style="font-size:14px;padding:6px 14px">Selesai</span>
                            @endif
                        </div>
                    </div>
                </div>

                <h3 class="font-poppins" style="color:var(--dark);margin:24px 0 12px;font-size:18px">Daftar Item Barang</h3>
                <div style="overflow-x:auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Produk / Barang</th>
                                <th style="text-align:center;width:150px">Jumlah Pesan (Qty)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td style="font-weight:700;color:var(--dark)">{{ $item->name }}</td>
                                <td style="text-align:center;font-weight:700;color:var(--primary-dark)">{{ number_format($item->qty, 0, ',', '.') }} pack</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="email-card" id="email-card">
                <h3 class="font-poppins" style="margin:0 0 8px;color:var(--dark);font-size:18px">✉️ Kirim Purchase Order Ke Supplier</h3>
                <p style="margin:0 0 16px;color:var(--gray-600);font-size:14px">Masukkan alamat email supplier untuk mengirimkan dokumen Purchase Order ini.</p>
                
                <form action="{{ route('admin.purchase-order.send', $po->id) }}" method="POST">
                    @csrf
                    <div style="display:flex;gap:12px;align-items:center;max-width:600px">
                        <input type="email" name="email" class="form-control" placeholder="supplier@example.com atau alamat email lainnya..." value="{{ env('PRODUCTION_EMAIL', 'produksi@tifanny.com') }}" required style="flex:1">
                        <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-weight:700;white-space:nowrap;box-shadow:0 4px 12px rgba(255,178,107,0.3)">
                            Kirim Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
