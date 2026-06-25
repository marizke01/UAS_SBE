@extends('layouts.tifanny', ['title' => 'Login Kasir - Tifanny ERP'])

@section('body')
<nav class="nav">
    <div class="container nav-inner">
        <a href="{{ route('home') }}" class="logo">Tifanny<span>.</span></a>
        <div class="nav-links">
            <a href="{{ route('home') }}">Website Publik</a>
            <a href="{{ route('admin.login') }}">Login Admin</a>
        </div>
    </div>
</nav>

<section style="min-height:calc(100vh - 66px);display:flex;align-items:center;background:linear-gradient(135deg,#fff8f0,#ffe8cc);padding:46px 20px">
    <form method="POST" action="{{ route('cashier.login.submit') }}" style="width:100%;max-width:430px;margin:auto;background:white;border:1px solid var(--gray-200);border-radius:18px;padding:34px;box-shadow:0 18px 50px rgba(57,48,83,.10)">
        @csrf
        <div class="logo" style="font-size:32px;margin-bottom:10px">Tifanny<span>Kasir</span></div>
        <p style="margin:0 0 24px;color:var(--gray-600);line-height:1.6">Masuk untuk membuka POS, riwayat transaksi, dan monitoring stok kasir.</p>

        <input class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="Email kasir" style="height:54px;margin-bottom:14px;font-size:16px" required autofocus>
        <input class="form-control" type="password" name="password" placeholder="Password" style="height:54px;margin-bottom:18px;font-size:16px" required>

        @if($errors->any())
            <div style="color:var(--danger);font-size:14px;margin-bottom:14px">{{ $errors->first() }}</div>
        @endif
        @if(session('error'))
            <div style="color:var(--danger);font-size:14px;margin-bottom:14px">{{ session('error') }}</div>
        @endif

        <button class="btn btn-dark" style="width:100%;height:56px;font-size:16px">Login Kasir</button>
    </form>
</section>
@endsection
