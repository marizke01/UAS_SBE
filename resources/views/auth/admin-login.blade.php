@extends('layouts.tifanny', ['title' => 'Login Admin - Tifanny ERP'])

@section('body')
<nav class="nav">
    <div class="container nav-inner">
        <a href="{{ route('home') }}" class="logo">Tifanny<span>.</span></a>
        <div class="nav-links">
            <a href="{{ route('home') }}">Produk</a>
        </div>
    </div>
</nav>

<section style="min-height:calc(100vh - 66px);display:flex;align-items:center;background:var(--gray-100);padding:46px 20px">
    <form method="POST" action="{{ route('admin.login.submit') }}" style="width:100%;max-width:430px;margin:auto;background:white;border:1px solid var(--gray-200);border-radius:18px;padding:34px;box-shadow:0 18px 50px rgba(57,48,83,.10)">
        @csrf
        <div class="logo" style="font-size:32px;margin-bottom:26px">Tifanny<span>.</span></div>

        <input class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="Email admin" style="height:54px;margin-bottom:14px;font-size:16px" required autofocus>
        <input class="form-control" type="password" name="password" placeholder="Password" style="height:54px;margin-bottom:18px;font-size:16px" required>

        @if($errors->any())
            <div style="color:var(--danger);font-size:14px;margin-bottom:14px">{{ $errors->first() }}</div>
        @endif

        <button class="btn btn-dark" style="width:100%;height:56px;font-size:16px">Login</button>
    </form>
</section>
@endsection
