@extends('layouts.tifanny', ['title' => 'Amplang Tifanny - Camilan Khas Kalimantan'])

@section('body')
@php
    $phone = preg_replace('/[^0-9]/', '', $settings['business_phone'] ?? '6281234567890');
    $shopProducts = $products->map(function ($product) {
        $variantText = strtolower($product->variant_name ?? '');
        preg_match('/\d+/', $variantText, $sizeMatch);
        $size = (int) ($product->weight_gram ?? ($sizeMatch[0] ?? 0));
        $image = $size <= 110
            ? 'amplang-85gr.png'
            : ($size <= 165 ? 'amplang-130gr.png' : 'amplang-200gr.png');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'variant_name' => $product->variant_name,
            'sku' => $product->sku,
            'selling_price' => (float) $product->selling_price,
            'stock' => (int) $product->stock,
            'minimum_stock' => (int) $product->minimum_stock,
            'image' => asset('assets/products/'.$image),
        ];
    })->values();
@endphp

<style>
    :root{--warm:#f59340;--warm-2:#ffb26b;--warm-3:#fff4e6;--ink:#332747;--muted:#6d6675;--line:#f0e6da}
    body{background:#fffaf4}
    .site-nav{height:82px;background:rgba(255,255,255,.94);backdrop-filter:blur(18px);border-bottom:1px solid var(--line);position:sticky;top:0;z-index:50}
    .site-nav .nav-inner{height:82px;display:flex;align-items:center;gap:28px}
    .brand{font-family:Poppins,sans-serif;font-weight:900;font-size:28px;color:var(--ink);letter-spacing:0}
    .brand span{color:var(--warm)}
    .nav-menu{margin-left:auto;display:flex;align-items:center;gap:22px;font-weight:800;color:#5d5566}
    .nav-menu a:not(.icon-btn):hover{color:var(--warm)}
    .icon-btn{position:relative;width:46px;height:46px;border-radius:14px;border:1px solid #efd8bd;background:#fff7ec;display:inline-flex;align-items:center;justify-content:center;color:var(--ink)}
    .icon-btn svg{width:23px;height:23px;stroke:currentColor;stroke-width:2.2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .cart-count{position:absolute;top:-8px;right:-8px;min-width:22px;height:22px;border-radius:999px;background:var(--warm);color:white;font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;padding:0 6px}

    .hero{background:radial-gradient(circle at 78% 35%,#ffd19a 0 18%,transparent 32%),linear-gradient(120deg,#fff8ef 0%,#ffe6c5 100%);padding:76px 0 54px;overflow:hidden}
    .hero-grid{display:grid;grid-template-columns:1.03fr .97fr;gap:48px;align-items:center}
    .eyebrow{display:inline-flex;align-items:center;border-radius:999px;background:rgba(245,147,64,.12);color:#c76817;padding:8px 14px;font-weight:900;font-size:14px}
    .hero h1{font-family:Poppins,sans-serif;font-size:clamp(42px,6.2vw,76px);line-height:1.05;margin:18px 0 18px;color:var(--ink);font-weight:900;letter-spacing:0}
    .hero p{font-size:21px;line-height:1.75;color:#5f5868;max-width:660px;margin:0}
    .hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:30px}
    .cta-primary{background:var(--warm);color:white;border-radius:14px;padding:16px 26px;font-weight:900;box-shadow:0 14px 28px rgba(245,147,64,.28)}
    .cta-secondary{background:white;color:var(--ink);border:1px solid var(--line);border-radius:14px;padding:16px 24px;font-weight:900}
    .hero-product-wrap{min-height:440px;display:flex;align-items:center;justify-content:center}
    .hero-product{width:min(390px,76vw);height:auto;filter:drop-shadow(0 32px 28px rgba(51,39,71,.18));transform:rotate(-3deg)}

    .section-pad{padding:72px 0}
    .section-head{display:flex;justify-content:space-between;gap:22px;align-items:end;flex-wrap:wrap;margin-bottom:28px}
    .section-title{font-family:Poppins,sans-serif;color:var(--ink);font-size:clamp(30px,4vw,44px);line-height:1.16;margin:0;font-weight:900}
    .section-copy{color:var(--muted);font-size:16px;line-height:1.7;margin:8px 0 0;max-width:660px}
    .value-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .value-card{background:white;border:1px solid var(--line);border-radius:8px;padding:22px;box-shadow:0 10px 26px rgba(51,39,71,.06)}
    .value-icon{width:44px;height:44px;border-radius:12px;background:#fff0dc;color:var(--warm);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
    .value-icon svg{width:24px;height:24px;stroke:currentColor;stroke-width:2.1;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .value-card h3{font-family:Poppins,sans-serif;color:var(--ink);font-size:18px;line-height:1.35;margin:0 0 8px}
    .value-card p{color:var(--muted);line-height:1.55;margin:0;font-size:14px}

    .products-section{background:#fff7ed}
    .shop-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}
    .product-card{background:white;border:1px solid var(--line);border-radius:8px;padding:26px;box-shadow:0 10px 26px rgba(51,39,71,.07);display:flex;flex-direction:column;min-height:100%;transition:.2s}
    .product-card:hover{transform:translateY(-4px);box-shadow:0 18px 40px rgba(51,39,71,.12)}
    .product-photo{height:250px;display:flex;align-items:center;justify-content:center;background:#fffaf4;border-radius:8px;margin-bottom:22px;overflow:hidden}
    .product-photo img{width:100%;height:100%;object-fit:contain;filter:drop-shadow(0 16px 14px rgba(51,39,71,.13))}
    .variant{font-style:italic;color:#8a4d19;font-size:15px;margin-bottom:8px}
    .product-card h3{font-family:Poppins,sans-serif;color:var(--ink);font-size:23px;line-height:1.35;margin:0 0 14px}
    .price{font-family:Poppins,sans-serif;color:var(--warm);font-weight:900;font-size:28px;margin-bottom:8px}
    .stock{color:var(--muted);font-size:13px;margin-bottom:22px}
    .product-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:auto}
    .wa-btn,.cart-btn{border-radius:999px;padding:13px 14px;font-weight:900;text-align:center;cursor:pointer}
    .wa-btn{background:#25d366;color:white}
    .cart-btn{border:2px solid #efd8bd;background:white;color:var(--ink)}
    .cart-btn:hover{border-color:var(--warm);background:#fff4e6}
    .success-banner{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#15803d;display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}

    .story-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:34px;align-items:center}
    .story-media{background:#fff2df;border-radius:8px;min-height:360px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid var(--line)}
    .story-media img{width:min(320px,72vw);filter:drop-shadow(0 20px 20px rgba(51,39,71,.14))}
    .story-copy{background:white;border:1px solid var(--line);border-radius:8px;padding:34px;box-shadow:0 10px 30px rgba(51,39,71,.06)}
    .story-copy p{color:var(--muted);line-height:1.85;font-size:17px;margin:0 0 16px}

    .footer{background:var(--ink);color:white;padding:46px 0}
    .footer-grid{display:grid;grid-template-columns:1.2fr .9fr .9fr .8fr;gap:26px}
    .footer h3{font-family:Poppins,sans-serif;margin:0 0 12px;color:white}
    .footer p,.footer a{color:rgba(255,255,255,.72);line-height:1.7;margin:0}
    .map-box{height:122px;border-radius:8px;border:1px dashed rgba(255,255,255,.25);background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.62);font-weight:800;text-align:center;padding:12px}
    .socials{display:flex;gap:10px;margin-top:12px}
    .socials a{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:white;font-weight:900}
    .floating-wa{position:fixed;right:24px;bottom:24px;z-index:80;display:inline-flex;align-items:center;gap:10px;background:#25d366;color:white;border-radius:999px;padding:14px 18px;font-weight:900;box-shadow:0 16px 34px rgba(37,211,102,.32)}
    .floating-wa svg{width:24px;height:24px;fill:currentColor}
    .floating-wa:hover{transform:translateY(-2px);box-shadow:0 20px 42px rgba(37,211,102,.38)}

    @media(max-width:1050px){.hero-grid,.story-grid{grid-template-columns:1fr}.value-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.shop-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.footer-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hero-product-wrap{min-height:320px}}
    @media(max-width:760px){.site-nav{height:auto}.site-nav .nav-inner{height:auto;padding-top:14px;padding-bottom:14px;align-items:flex-start}.brand{font-size:24px}.nav-menu{gap:10px;flex-wrap:wrap;justify-content:flex-end}.nav-menu a:not(.icon-btn){display:none}.hero{padding:46px 0}.value-grid,.shop-grid,.footer-grid{grid-template-columns:1fr}.product-actions{grid-template-columns:1fr}.section-pad{padding:52px 0}.story-copy{padding:24px}.floating-wa{right:16px;bottom:16px;padding:14px}.floating-wa span{display:none}}
</style>

<nav class="site-nav">
    <div class="container nav-inner">
        <a href="/" class="brand">Amplang <span>Tifanny</span></a>
        <div class="nav-menu">
            <a href="#home">Home</a>
            <a href="#products">Produk</a>
            <a href="#about">Tentang Kami</a>
            <a href="#contact">Hubungi Kami</a>
            <a href="{{ route('public.checkout.page') }}" class="icon-btn" aria-label="Keranjang belanja">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.6"></circle><circle cx="18" cy="20" r="1.6"></circle><path d="M3 4h2l2.3 10.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H7"></path></svg>
                <span class="cart-count" id="navCartCount">0</span>
            </a>
            <a href="/admin" class="icon-btn" aria-label="Login admin">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </a>
        </div>
    </div>
</nav>

<main id="home">
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="eyebrow">Camilan khas Kalimantan</div>
                <h1>Kerenyahan Otentik Khas Kalimantan</h1>
                <p>Amplang Tifanny dibuat dari ikan tenggiri pilihan dengan proses higienis, rasa gurih, dan tekstur renyah yang cocok untuk oleh-oleh atau stok camilan keluarga.</p>
                <div class="hero-actions">
                    <a href="#products" class="cta-primary">Pesan Sekarang</a>
                    <a href="https://wa.me/{{ $phone }}" class="cta-secondary">Konsultasi WhatsApp</a>
                </div>
            </div>
            <div class="hero-product-wrap">
                <img class="hero-product" src="{{ asset('assets/products/hero-amplang-transparent.png') }}" alt="Produk Amplang Tifanny">
            </div>
        </div>
    </section>

    <section class="section-pad">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Kenapa Amplang Tifanny?</h2>
                    <p class="section-copy">Setiap kemasan dibuat untuk menjaga rasa khas Pontianak tetap gurih, bersih, dan aman dinikmati kapan saja.</p>
                </div>
            </div>
            <div class="value-grid">
                <div class="value-card">
                    <div class="value-icon"><svg viewBox="0 0 24 24"><path d="M4 12c3-5 8-6 16-3-2 5-7 8-16 3Z"></path><path d="M16 9l4-4"></path></svg></div>
                    <h3>100% Ikan Tenggiri Segar</h3>
                    <p>Bahan utama dipilih dari ikan berkualitas untuk menghasilkan rasa gurih alami.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.4 8.7-8 9-4.6-.3-8-4-8-9V7l8-4Z"></path><path d="M9 12l2 2 4-5"></path></svg></div>
                    <h3>Tanpa Pengawet</h3>
                    <p>Diproses dengan kontrol produksi yang rapi agar tetap renyah tanpa bahan pengawet.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><svg viewBox="0 0 24 24"><path d="M7 3h10v18l-5-3-5 3V3Z"></path><path d="M9 8h6"></path><path d="M9 12h4"></path></svg></div>
                    <h3>Sertifikasi Halal & P-IRT</h3>
                    <p>Produk memiliki legalitas usaha pangan rumahan untuk menambah rasa percaya pembeli.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><svg viewBox="0 0 24 24"><path d="M5 12h14"></path><path d="M8 8l-3 4 3 4"></path><path d="M16 8l3 4-3 4"></path></svg></div>
                    <h3>Tekstur Renyah & Tidak Keras</h3>
                    <p>Renyahnya pas, ringan saat digigit, dan nyaman untuk semua anggota keluarga.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-pad products-section" id="products">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Katalog Produk</h2>
                    <p class="section-copy">Pilih ukuran kemasan sesuai kebutuhan, lalu pesan melalui WhatsApp atau masukkan ke keranjang.</p>
                </div>
                <a href="{{ route('public.checkout.page') }}" class="cta-secondary">Lihat Keranjang <span id="cartBadge">0</span></a>
            </div>

            @if(session('success'))
                <div class="success-banner">
                    <strong>{{ session('success') }}</strong>
                    @if(session('invoice_url'))
                        <a class="cta-primary" href="{{ session('invoice_url') }}">Download Invoice</a>
                    @endif
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="shop-grid">
                @foreach($shopProducts as $product)
                    <article class="product-card">
                        <div class="product-photo">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }} {{ $product['variant_name'] }}">
                        </div>
                        <div class="variant">{{ $product['variant_name'] }}</div>
                        <h3>{{ $product['name'] }} {{ $product['variant_name'] }}</h3>
                        <div class="price">Rp {{ number_format($product['selling_price'],0,',','.') }}</div>
                        <div class="stock" style="color:{{ $product['stock'] <= $product['minimum_stock'] ? 'var(--danger)' : 'var(--muted)' }}">Stok: {{ $product['stock'] }} pack</div>
                        <div class="product-actions">
                            <a class="wa-btn" href="https://wa.me/{{ $phone }}?text={{ urlencode('Halo, saya mau pesan '.$product['name'].' '.$product['variant_name']) }}">WhatsApp</a>
                            <button type="button" class="cart-btn" onclick="addPublicCartById({{ $product['id'] }})">Keranjang</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-pad" id="about">
        <div class="container story-grid">
            <div class="story-media">
                <img src="{{ asset('assets/products/amplang-200gr.png') }}" alt="Kemasan Amplang Tifanny">
            </div>
            <div class="story-copy">
                <div class="eyebrow">Tentang Kami</div>
                <h2 class="section-title" style="margin-top:16px">Dari dapur produksi higienis untuk rasa khas yang konsisten</h2>
                <p>Amplang Tifanny lahir dari kecintaan pada camilan khas Kalimantan yang gurih dan renyah. Setiap batch diproses dengan standar kebersihan yang terjaga, mulai dari pemilihan bahan baku sampai pengemasan.</p>
                <p>Kami menggunakan ikan tenggiri berkualitas dan bumbu pilihan agar cita rasa amplang tetap otentik. Produk dikemas praktis sehingga cocok untuk oleh-oleh, hampers, maupun camilan harian di rumah.</p>
            </div>
        </div>
    </section>
</main>

<footer class="footer" id="contact">
    <div class="container footer-grid">
        <div>
            <h3>Amplang Tifanny</h3>
            <p>Camilan khas Kalimantan dengan rasa gurih, renyah, dan cocok untuk semua momen.</p>
            <div class="socials">
                <a href="#" aria-label="Instagram">IG</a>
                <a href="#" aria-label="Facebook">FB</a>
                <a href="#" aria-label="TikTok">TT</a>
            </div>
        </div>
        <div>
            <h3>Alamat Produksi</h3>
            <p>Pontianak, Kalimantan Barat</p>
            <div class="map-box">Google Maps Placeholder</div>
        </div>
        <div>
            <h3>Hubungi Kami</h3>
            <p>WhatsApp: <a href="https://wa.me/{{ $phone }}">{{ $settings['business_phone'] ?? '+62 812-3456-7890' }}</a></p>
            <p>Email: {{ $settings['business_email'] ?? 'info@amplangtifanny.test' }}</p>
        </div>
        <div>
            <h3>Jam Operasional</h3>
            <p>Senin - Sabtu</p>
            <p>08.00 - 17.00 WIB</p>
        </div>
    </div>
</footer>

<a class="floating-wa" href="https://wa.me/{{ $phone }}" aria-label="Chat WhatsApp Amplang Tifanny">
    <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16.02 3.2A12.72 12.72 0 0 0 5.05 22.36L3.2 29l6.82-1.78A12.72 12.72 0 1 0 16.02 3.2Zm0 2.3a10.42 10.42 0 0 1 8.86 15.9 10.38 10.38 0 0 1-13.98 3.7l-.48-.28-4.05 1.06 1.08-3.94-.31-.5A10.42 10.42 0 0 1 16.02 5.5Zm-4.1 5.6c-.23 0-.6.08-.92.44-.32.35-1.22 1.2-1.22 2.92 0 1.72 1.25 3.38 1.43 3.62.18.24 2.42 3.88 5.97 5.28 2.95 1.16 3.55.93 4.19.87.64-.06 2.06-.84 2.35-1.65.29-.82.29-1.52.2-1.66-.09-.15-.32-.24-.67-.42-.35-.17-2.06-1.02-2.38-1.13-.32-.12-.56-.18-.8.17-.23.35-.91 1.13-1.12 1.36-.2.23-.41.26-.76.09-.35-.18-1.48-.55-2.82-1.74-1.04-.93-1.75-2.08-1.95-2.43-.2-.35-.02-.54.15-.71.16-.16.35-.41.53-.61.18-.2.23-.35.35-.59.12-.23.06-.44-.03-.61-.09-.18-.8-1.92-1.1-2.63-.29-.7-.58-.6-.8-.61h-.6Z"/></svg>
    <span>WhatsApp</span>
</a>

<script>
const shopProducts = @json($shopProducts);
let publicCart = loadPublicCart();

function loadPublicCart() {
    try {
        return JSON.parse(localStorage.getItem('tifanny_public_cart') || '[]');
    } catch (error) {
        return [];
    }
}

function savePublicCart() {
    localStorage.setItem('tifanny_public_cart', JSON.stringify(publicCart));
}

function addPublicCartById(id) {
    const product = shopProducts.find(item => Number(item.id) === Number(id));
    if (!product) return;

    const existing = publicCart.find(item => Number(item.id) === Number(id));
    if (existing) {
        if (existing.qty < Number(product.stock)) existing.qty++;
    } else {
        publicCart.push({
            id: product.id,
            name: product.name + ' ' + product.variant_name,
            price: Number(product.selling_price),
            qty: 1,
            stock: Number(product.stock),
            image: product.image
        });
    }
    renderPublicCart();
}

function renderPublicCart() {
    const count = publicCart.reduce((sum, item) => sum + item.qty, 0);
    savePublicCart();
    document.querySelectorAll('#navCartCount, #cartBadge').forEach(el => el.textContent = count);
}

renderPublicCart();
</script>
@endsection
