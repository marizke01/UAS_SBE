@extends('layouts.tifanny', ['title' => $pageTitle ?? 'Akun Saya'])

@section('body')
<style>
    .account-grid {display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:24px}
    .form-group {margin-bottom:18px}
    .form-group label {display:block;font-weight:700;margin-bottom:8px;color:var(--dark);font-size:14px}
    .form-group input {width:100%;padding:11px 14px;border:1px solid var(--gray-200);border-radius:10px;font:inherit}
    .form-group input:focus {outline:2px solid rgba(245,147,64,.22);border-color:var(--primary-dark)}
    .form-group input:disabled {background:var(--gray-100);color:var(--gray-400);cursor:not-allowed}
    .text-danger {color:var(--danger);font-size:12px;font-weight:700;margin-top:6px;display:block}
    @media(max-width:900px) {
        .account-grid {grid-template-columns:1fr}
    }
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
            <a href="/admin/account" class="active">Akun Saya</a>
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

            <h2 class="font-poppins" style="margin:0 0 4px;color:var(--dark)">Pengaturan Akun</h2>
            <p style="margin:0;color:var(--gray-600);font-size:14px">Kelola informasi profil dan keamanan kata sandi akun admin Anda.</p>

            <div class="account-grid">
                <!-- Profile Card -->
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0;margin-bottom:20px;color:var(--dark);border-bottom:1px solid var(--gray-200);padding-bottom:12px">👤 Profil Saya</h3>
                    
                    <form action="{{ route('admin.account.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Alamat Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Hak Akses / Role</label>
                            <input type="text" value="{{ ucfirst($user->role_name) }}" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px;font-size:15px;box-shadow:0 4px 12px rgba(255,178,107,0.3)">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Password Card -->
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0;margin-bottom:20px;color:var(--dark);border-bottom:1px solid var(--gray-200);padding-bottom:12px">🔒 Ubah Password</h3>
                    
                    <form action="{{ route('admin.account.password') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Password Saat Ini</label>
                            <input type="password" name="current_password" required placeholder="Masukkan password Anda saat ini...">
                            @error('current_password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" required placeholder="Minimal 8 karakter...">
                            @error('new_password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" required placeholder="Ulangi password baru Anda...">
                        </div>

                        <button type="submit" class="btn btn-dark" style="width:100%;margin-top:10px;padding:12px;font-size:15px;box-shadow:0 4px 12px rgba(57,48,83,0.2)">
                            Perbarui Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
