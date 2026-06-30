@extends('cashier.layout', ['title' => $pageTitle ?? 'Akun Saya'])

@section('cashier_content')
<style>
    .account-grid {display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:14px}
    .form-group {margin-bottom:18px}
    .form-group label {display:block;font-weight:700;margin-bottom:8px;color:var(--dark);font-size:14px}
    .form-group input {width:100%;padding:11px 14px;border:1px solid var(--gray-200);border-radius:10px;font:inherit}
    .form-group input:focus {outline:2px solid rgba(255,178,107,.22);border-color:var(--primary-dark)}
    .form-group input:disabled {background:var(--gray-100);color:var(--gray-400);cursor:not-allowed}
    .text-danger {color:var(--danger);font-size:12px;font-weight:700;margin-top:6px;display:block}
    @media(max-width:900px) {
        .account-grid {grid-template-columns:1fr}
    }
</style>

<div style="margin-bottom:20px">
    <h2 class="font-poppins" style="margin:0 0 4px;color:var(--dark)">Pengaturan Akun Kasir</h2>
    <p style="margin:0;color:var(--gray-600);font-size:14px">Kelola informasi profil diri dan keamanan password akun kasir Anda.</p>
</div>

<div class="account-grid">
    <!-- Profile Card -->
    <div class="cashier-card">
        <h3 class="font-poppins" style="margin-top:0;margin-bottom:20px;color:var(--dark);border-bottom:1px solid var(--gray-200);padding-bottom:12px">👤 Profil Kasir</h3>
        
        <form action="{{ route('cashier.account.update') }}" method="POST">
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

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px;font-weight:700;box-shadow:0 4px 12px rgba(255,178,107,0.3)">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <!-- Password Card -->
    <div class="cashier-card">
        <h3 class="font-poppins" style="margin-top:0;margin-bottom:20px;color:var(--dark);border-bottom:1px solid var(--gray-200);padding-bottom:12px">🔒 Ubah Password</h3>
        
        <form action="{{ route('cashier.account.password') }}" method="POST">
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

            <button type="submit" class="btn btn-dark" style="width:100%;margin-top:10px;padding:12px;font-weight:700;background:#2f2544;color:white;box-shadow:0 4px 12px rgba(47,37,68,0.2)">
                Perbarui Password
            </button>
        </form>
    </div>
</div>
@endsection
