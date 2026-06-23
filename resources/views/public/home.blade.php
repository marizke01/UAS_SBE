@extends('layouts.tifanny', ['title' => 'Tifanny Amplang - Website Publik'])

@section('body')
<style>
    .public-nav{height:80px;background:white;border-bottom:1px solid var(--gray-200)}
    .public-nav .nav-inner{height:80px}
    .public-nav .logo{font-size:32px}
    .public-nav .nav-links{font-size:16px;gap:26px}
    .public-hero{background:linear-gradient(110deg,#fff8f0 0%,#ffe4c4 100%);padding:88px 0 72px}
    .public-hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:46px;align-items:center}
    .public-hero h1{font-family:Poppins,sans-serif;font-size:clamp(52px,6.4vw,86px);line-height:1.02;margin:16px 0;color:var(--dark);font-weight:900;letter-spacing:0}
    .public-hero h1 span{color:var(--primary-dark)}
    .public-hero p{font-size:21px;line-height:1.75;color:var(--gray-600);max-width:680px;margin:0}
    .hero-visual{width:min(420px,78vw);aspect-ratio:1;border-radius:50%;background:linear-gradient(145deg,#ffd49d,#ffb26b);display:flex;align-items:center;justify-content:center;margin-left:auto;box-shadow:0 22px 70px rgba(245,147,64,.25)}
    .shrimp-mark{position:relative;width:230px;height:150px}
    .shrimp-body{position:absolute;right:10px;top:28px;width:170px;height:92px;border-radius:92px 92px 26px 26px;background:linear-gradient(135deg,#ff453f,#ff9b32 56%,#ef3d7a);transform:rotate(8deg)}
    .shrimp-head{position:absolute;left:0;top:30px;width:118px;height:78px;border-radius:60px 72px 24px 58px;background:linear-gradient(135deg,#ef3b3b,#ff6b52);clip-path:polygon(0 48%,34% 12%,100% 0,88% 78%,32% 78%)}
    .shrimp-tail{position:absolute;right:8px;bottom:2px;width:90px;height:62px;border-radius:60px 0 60px 18px;background:linear-gradient(135deg,#ef3b3b,#ff7b37);transform:rotate(-8deg)}
    .shrimp-eye{position:absolute;left:52px;top:62px;width:12px;height:24px;border-radius:12px;background:#3b304f}
    .shrimp-antenna{position:absolute;right:8px;top:12px;width:120px;height:42px;border-top:8px solid #e93472;border-radius:60% 80% 0 0;transform:rotate(-2deg)}
    .shrimp-leg{position:absolute;top:104px;width:8px;height:48px;background:#ef4c48;border-radius:10px;transform:rotate(28deg)}
    .leg1{left:50px}.leg2{left:78px}.leg3{left:105px}.leg4{left:132px}
    .product-card{min-height:100%;display:flex;flex-direction:column}
    .product-card .btn{margin-top:auto}
    .product-photo{height:260px;border-radius:16px;background:#fff8f0;display:flex;align-items:center;justify-content:center;margin-bottom:18px;overflow:hidden}
    .product-photo img{width:100%;height:100%;object-fit:contain;padding:10px;filter:drop-shadow(0 18px 16px rgba(57,48,83,.16))}
    @media(max-width:900px){.public-hero-grid{grid-template-columns:1fr}.hero-visual{margin:auto}.public-nav .nav-links{display:none}}
</style>

<nav class="public-nav">
    <div class="container nav-inner">
        <a href="/" class="logo">Tifanny<span>.</span></a>
        <div class="nav-links">
            <a href="#products">Produk</a>
            <a href="#reseller">Reseller</a>
            <a href="#contact">Kontak</a>
            <a href="/admin" class="btn btn-dark" style="padding:14px 24px;border-radius:12px">ERP Dashboard</a>
        </div>
    </div>
</nav>

<section class="public-hero">
    <div class="container public-hero-grid">
        <div>
            <div class="badge badge-warning" style="font-size:14px;padding:8px 15px;background:rgba(255,178,107,.32);color:var(--primary-dark)">Amplang Seafood Premium #1 Indonesia</div>
            <h1>Nikmati Cita Rasa <span>Amplang</span> Terbaik</h1>
            <p>Website publik dan ERP Dashboard Tifanny sudah terhubung ke database existing. Katalog, POS, stok, invoice, dan laporan siap ditunjukkan untuk presentasi.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:30px">
                <a href="#products" class="btn btn-primary" style="font-size:16px;padding:16px 26px;border-radius:12px">Lihat Produk</a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['business_phone'] ?? '6281234567890') }}" class="btn btn-outline" style="font-size:16px;padding:16px 26px;border-radius:12px">WhatsApp</a>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="shrimp-mark">
                <div class="shrimp-antenna"></div>
                <div class="shrimp-tail"></div>
                <div class="shrimp-body"></div>
                <div class="shrimp-head"></div>
                <div class="shrimp-eye"></div>
                <div class="shrimp-leg leg1"></div>
                <div class="shrimp-leg leg2"></div>
                <div class="shrimp-leg leg3"></div>
                <div class="shrimp-leg leg4"></div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="products" style="background:var(--gray-100)">
    <div class="container">
        <h2 class="section-title">Produk Amplang Tifanny</h2>
        <div class="grid grid-3">
            @foreach($products as $product)
                @php
                    $variantText = strtolower($product->variant_name ?? '');
                    preg_match('/\d+/', $variantText, $sizeMatch);
                    $size = (int) ($product->weight_gram ?? ($sizeMatch[0] ?? 0));
                    $image = $size <= 110
                        ? 'amplang-85gr.png'
                        : ($size <= 165 ? 'amplang-130gr.png' : 'amplang-200gr.png');
                @endphp
                <div class="card product product-card">
                    <div class="product-photo">
                        <img src="{{ asset('assets/products/'.$image) }}" alt="{{ $product->name }} {{ $product->variant_name }}">
                    </div>
                    <h3 class="font-poppins" style="margin:0;color:var(--dark)">{{ $product->name }}</h3>
                    <div style="color:var(--gray-600);font-size:14px;margin:6px 0 12px">{{ $product->variant_name }} | SKU {{ $product->sku }}</div>
                    <div class="price">Rp {{ number_format($product->selling_price,0,',','.') }}</div>
                    <div style="font-size:13px;color:{{ $product->stock <= $product->minimum_stock ? 'var(--danger)' : 'var(--gray-600)' }};margin:10px 0 18px">Stok: {{ $product->stock }} pack</div>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['business_phone'] ?? '6281234567890') }}" class="btn btn-primary" style="width:100%;padding:14px">Pesan Produk</a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" id="reseller" style="background:white">
    <div class="container grid grid-3">
        <div class="card"><h3>Margin Reseller</h3><p>Harga reseller sudah tersedia pada tabel varian produk.</p></div>
        <div class="card"><h3>Stok Real-Time</h3><p>Stok produk dibaca dari data inventory yang sama dengan ERP.</p></div>
        <div class="card"><h3>Invoice Otomatis</h3><p>Transaksi internal melalui POS admin akan membuat invoice dan payment otomatis.</p></div>
    </div>
</section>

<footer id="contact" style="background:var(--dark2);color:white;padding:34px 0">
    <div class="container" style="display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap">
        <div><strong class="logo" style="color:white">Tifanny<span>.</span></strong><br><span style="color:rgba(255,255,255,.6)">Amplang Premium</span></div>
        <div style="color:rgba(255,255,255,.7)">Kontak: {{ $settings['business_phone'] ?? '-' }} | {{ $settings['business_email'] ?? '-' }}</div>
    </div>
</footer>
@endsection
