@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny Kasir'])

@section('body')
<style>
    .cashier-app{display:flex;min-height:100vh;background:#f7f3ee}
    .cashier-sidebar{width:240px;background:#2f2544;color:white;padding:18px 12px;position:fixed;inset:0 auto 0 0}
    .cashier-brand{font-family:Poppins,sans-serif;font-size:21px;font-weight:900;padding:8px 10px 18px}
    .cashier-brand span{color:var(--primary)}
    .cashier-menu-label{font-size:10px;color:rgba(255,255,255,.36);font-weight:900;letter-spacing:1.4px;text-transform:uppercase;margin:18px 10px 8px}
    .cashier-menu a,.cashier-menu button{width:100%;border:0;display:flex;gap:10px;align-items:center;padding:12px;border-radius:10px;background:transparent;color:rgba(255,255,255,.76);font-weight:800;font:inherit;text-align:left;cursor:pointer}
    .cashier-menu a:hover,.cashier-menu a.active,.cashier-menu button:hover{background:var(--primary);color:var(--dark)}
    .cashier-main{margin-left:240px;flex:1}
    .cashier-topbar{height:66px;background:white;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;padding:0 26px;position:sticky;top:0;z-index:20}
    .cashier-content{padding:28px}
    .cashier-page-title{font-family:Poppins,sans-serif;font-size:26px;color:var(--dark);font-weight:900}
    .cashier-user{margin-left:auto;color:var(--gray-600);font-size:14px}
    .cashier-card{background:white;border:1px solid var(--gray-200);border-radius:12px;padding:22px;box-shadow:0 8px 26px rgba(57,48,83,.06)}
    .cashier-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}
    .cashier-stat-number{font-family:Poppins,sans-serif;color:var(--dark);font-size:30px;font-weight:900;margin-top:8px}
    .cashier-muted{color:var(--gray-600);line-height:1.6}
    @media(max-width:900px){.cashier-app{display:block}.cashier-sidebar{position:static;width:auto}.cashier-main{margin-left:0}.cashier-stats{grid-template-columns:1fr}}
</style>

<div class="cashier-app">
    <aside class="cashier-sidebar">
        <div class="cashier-brand">Tifanny<span>Kasir</span><div style="font-size:11px;color:rgba(255,255,255,.42);font-family:Inter;margin-top:4px">Point of Sales</div></div>
        <nav class="cashier-menu">
            <div class="cashier-menu-label">Kasir</div>
            <a href="{{ route('cashier.dashboard') }}" class="{{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}">Dashboard Kasir</a>
            <a href="{{ route('cashier.pos') }}" class="{{ request()->routeIs('cashier.pos') ? 'active' : '' }}">Point of Sales</a>
            <a href="{{ route('cashier.history') }}" class="{{ request()->routeIs('cashier.history') ? 'active' : '' }}">Riwayat Transaksi</a>
            <a href="{{ route('cashier.stock') }}" class="{{ request()->routeIs('cashier.stock') ? 'active' : '' }}">Monitoring Stok</a>
            <div class="cashier-menu-label">Publik</div>
            <a href="{{ route('home') }}">Website Publik</a>
            <form method="POST" action="{{ route('cashier.logout') }}" style="margin-top:12px">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    </aside>
    <main class="cashier-main">
        <div class="cashier-topbar">
            <div class="cashier-page-title">{{ $pageTitle ?? 'Dashboard Kasir' }}</div>
            <div class="cashier-user">{{ session('cashier_user.name', 'Kasir') }}</div>
        </div>
        <div class="cashier-content">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @yield('cashier_content')
        </div>
    </main>
</div>
@endsection
