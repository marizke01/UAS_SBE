@extends('layouts.tifanny', ['title' => 'Tifanny Amplang — Website Publik'])

@section('body')
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="logo">Tifanny<span>.</span></a>
        <div class="nav-links">
            <a href="#products">Produk</a>
            <a href="#reseller">Reseller</a>
            <a href="#contact">Kontak</a>
            <a href="/admin" class="btn btn-dark">🔐 ERP Dashboard</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="badge badge-warning">🦐 Amplang Seafood Premium #1 Indonesia</div>
            <h1>Nikmati Cita Rasa <span>Amplang</span> Terbaik</h1>
            <p>Website publik dan ERP Dashboard Tifanny sudah terhubung ke database existing. Katalog, POS, stok, invoice, dan laporan siap ditunjukkan untuk presentasi.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:26px">
                <a href="#products" class="btn btn-primary">🛒 Lihat Produk</a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['business_phone'] ?? '6281234567890') }}" class="btn btn-outline">💬 WhatsApp</a>
            </div>
        </div>
        <div class="hero-emoji">🦐</div>
    </div>
</section>

<section class="section" id="products">
    <div class="container">
        <h2 class="section-title">Produk dari Database</h2>
        <div class="grid grid-3">
            @foreach($products as $product)
                <div class="card product">
                    <div class="product-img">🦐</div>
                    <h3 class="font-poppins" style="margin:0;color:var(--dark)">{{ $product->name }}</h3>
                    <div style="color:var(--gray-600);font-size:14px;margin:6px 0 12px">{{ $product->variant_name }} · SKU {{ $product->sku }}</div>
                    <div class="price">Rp {{ number_format($product->selling_price,0,',','.') }}</div>
                    <div style="font-size:13px;color:{{ $product->stock <= $product->minimum_stock ? 'var(--danger)' : 'var(--gray-600)' }};margin:10px 0">Stok: {{ $product->stock }} pack</div>
                    <a href="/admin/pos" class="btn btn-primary" style="width:100%;margin-top:10px">Beli via POS</a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" id="reseller" style="background:white">
    <div class="container grid grid-3">
        <div class="card"><h3>💵 Margin Reseller</h3><p>Harga reseller sudah tersedia pada tabel varian produk.</p></div>
        <div class="card"><h3>📦 Stok Real-Time</h3><p>Stok cabang dibaca dari tabel branch_stocks.</p></div>
        <div class="card"><h3>🧾 Invoice Otomatis</h3><p>Setiap transaksi POS membuat invoice dan payment otomatis.</p></div>
    </div>
</section>

<footer id="contact" style="background:var(--dark2);color:white;padding:34px 0">
    <div class="container" style="display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap">
        <div><strong class="logo" style="color:white">Tifanny<span>.</span></strong><br><span style="color:rgba(255,255,255,.6)">ERP Commerce Demo</span></div>
        <div style="color:rgba(255,255,255,.7)">Kontak: {{ $settings['business_phone'] ?? '-' }} · {{ $settings['business_email'] ?? '-' }}</div>
    </div>
</footer>
@endsection
