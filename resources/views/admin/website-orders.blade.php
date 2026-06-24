@extends('layouts.tifanny', ['title' => $title ?? 'Pesanan Website'])

@section('body')
<style>
    .orders-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .summary-box{background:white;border:1px solid var(--gray-200);border-radius:14px;padding:16px}
    .summary-box strong{font-family:Poppins,sans-serif;color:var(--dark);font-size:25px}
    .summary-box div{font-size:12px;color:var(--gray-600);margin-top:4px}
    .status-select{border:1px solid var(--gray-200);border-radius:10px;padding:9px 10px;background:white;font:inherit;min-width:178px}
    .status-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .order-note{max-width:280px;color:var(--gray-600);font-size:12px;line-height:1.45}
    @media(max-width:900px){.orders-summary{grid-template-columns:1fr}.table{min-width:980px}}
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
            <a href="/admin/website-orders" class="{{ request()->is('admin/website-orders') ? 'active' : '' }}">Pesanan Website</a>
            <a href="/admin/invoices" class="{{ request()->is('admin/invoices') ? 'active' : '' }}">Invoice</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="/">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <strong class="font-poppins" style="font-size:20px;color:var(--dark)">Pesanan Website</strong>
            <div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div>
        </div>
        <div class="content">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

            <div class="orders-summary">
                <div class="summary-box"><strong>{{ number_format($summary['total'],0,',','.') }}</strong><div>Total pesanan website</div></div>
                <div class="summary-box"><strong>{{ number_format($summary['pending'],0,',','.') }}</strong><div>Menunggu konfirmasi</div></div>
                <div class="summary-box"><strong>{{ number_format($summary['processing'],0,',','.') }}</strong><div>Diproses / dikirim</div></div>
                <div class="summary-box"><strong>{{ number_format($summary['done'],0,',','.') }}</strong><div>Selesai</div></div>
            </div>

            <div class="card">
                <div style="display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
                    <div>
                        <h3 class="font-poppins" style="margin:0;color:var(--dark)">Manajemen Pesanan Website</h3>
                        <p style="margin:6px 0 0;color:var(--gray-600);font-size:13px">Kelola status pesanan dari checkout pembeli di website publik.</p>
                    </div>
                    <a class="btn btn-outline" href="{{ route('admin.exports.sales') }}">Export Pesanan / Penjualan CSV</a>
                </div>
                <div style="overflow:auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No Transaksi</th>
                                <th>Pembeli</th>
                                <th>WhatsApp</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Ubah Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td><strong>{{ $order->transaction_number }}</strong><div class="order-note">{{ $order->note }}</div></td>
                                    <td>{{ $order->customer_name_text }}</td>
                                    <td>{{ $order->customer_phone_text }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($order->total_qty,0,',','.') }} pack</td>
                                    <td>Rp {{ number_format($order->grand_total,0,',','.') }}</td>
                                    <td>{{ strtoupper($order->payment_method) }}<br><span class="badge {{ $order->payment_status === 'cancelled' ? 'badge-danger' : 'badge-success' }}">{{ $order->payment_status }}</span></td>
                                    <td><span class="badge {{ $order->order_status === 'Dibatalkan' ? 'badge-danger' : ($order->order_status === 'Selesai' ? 'badge-success' : 'badge-warning') }}">{{ $order->order_status }}</span></td>
                                    <td>
                                        <form class="status-form" method="POST" action="{{ route('admin.website-orders.status', $order->id) }}">
                                            @csrf
                                            <select class="status-select" name="status">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-primary">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9">Belum ada pesanan dari website publik.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
