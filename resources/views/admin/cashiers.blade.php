@extends('layouts.tifanny', ['title' => $pageTitle ?? 'Manajemen Kasir'])

@section('body')
<style>
    .cashier-admin-grid{display:grid;grid-template-columns:380px 1fr;gap:20px;align-items:start}
    .cashier-form{display:grid;gap:12px}
    .cashier-form label{font-weight:800;color:var(--dark);font-size:13px}
    .cashier-row-form{display:grid;grid-template-columns:1fr 1fr 140px 130px auto;gap:8px;align-items:center}
    .cashier-row-form .form-control{min-width:0}
    @media(max-width:1100px){.cashier-admin-grid{grid-template-columns:1fr}.cashier-row-form{grid-template-columns:1fr}.table{min-width:900px}}
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
        <div class="topbar">
            <strong class="font-poppins" style="font-size:20px;color:var(--dark)">Manajemen Kasir</strong>
            <div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div>
        </div>
        <div class="content">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <div class="cashier-admin-grid">
                <section class="card">
                    <h2 class="font-poppins" style="margin:0 0 6px;color:var(--dark)">Tambah Akun Kasir</h2>
                    <p style="margin:0 0 18px;color:var(--gray-600);line-height:1.6">Akun ini hanya dapat login ke dashboard kasir dan tidak bisa membuka dashboard admin.</p>
                    <form method="POST" action="{{ route('admin.cashiers.store') }}" class="cashier-form">
                        @csrf
                        <label>Nama Kasir</label>
                        <input class="form-control" name="name" placeholder="Nama kasir" required>
                        <label>Email</label>
                        <input class="form-control" type="email" name="email" placeholder="kasir@email.com" required>
                        <label>No. HP</label>
                        <input class="form-control" name="phone" placeholder="08xxxxxxxxxx">
                        <label>Password</label>
                        <input class="form-control" type="password" name="password" placeholder="Minimal 6 karakter" required>
                        <button class="btn btn-primary" style="margin-top:6px">Tambah Kasir</button>
                    </form>
                </section>

                <section class="card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:16px">
                        <div>
                            <h2 class="font-poppins" style="margin:0;color:var(--dark)">Daftar Kasir</h2>
                            <p style="margin:6px 0 0;color:var(--gray-600)">Kelola status dan password kasir.</p>
                        </div>
                        <a class="btn btn-dark" href="{{ route('cashier.login') }}">Buka Login Kasir</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kasir</th>
                                <th>Kontak</th>
                                <th>Transaksi</th>
                                <th>Revenue</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashiers as $cashier)
                                <tr>
                                    <td>
                                        <strong>{{ $cashier->name }}</strong><br>
                                        <small>{{ $cashier->email }}</small>
                                    </td>
                                    <td>{{ $cashier->phone ?: '-' }}</td>
                                    <td>{{ (int) $cashier->transaction_count }}</td>
                                    <td>Rp {{ number_format($cashier->revenue, 0, ',', '.') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.cashiers.update', $cashier->id) }}" class="cashier-row-form">
                                            @csrf
                                            @method('PUT')
                                            <input class="form-control" name="name" value="{{ $cashier->name }}" required>
                                            <input class="form-control" type="email" name="email" value="{{ $cashier->email }}" required>
                                            <input class="form-control" name="phone" value="{{ $cashier->phone }}">
                                            <select class="form-control" name="status">
                                                <option value="active" @selected($cashier->status === 'active')>Aktif</option>
                                                <option value="inactive" @selected($cashier->status === 'inactive')>Nonaktif</option>
                                                <option value="suspended" @selected($cashier->status === 'suspended')>Suspend</option>
                                            </select>
                                            <button class="btn btn-primary">Simpan</button>
                                            <input class="form-control" type="password" name="password" placeholder="Password baru opsional" style="grid-column:1 / -2">
                                        </form>
                                        <form method="POST" action="{{ route('admin.cashiers.destroy', $cashier->id) }}" onsubmit="return confirm('Nonaktifkan akun kasir ini?')" style="margin-top:8px">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline" type="submit">Nonaktifkan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">Belum ada akun kasir.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection
