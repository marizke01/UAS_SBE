@extends('layouts.tifanny', ['title' => 'Checkout - Tifanny Amplang'])

@section('body')
<style>
    .public-nav{height:80px;background:white;border-bottom:1px solid var(--gray-200)}
    .public-nav .nav-inner{height:80px}
    .public-nav .logo{font-size:32px}
    .public-nav .nav-links{font-size:16px;gap:26px}
    .cart-link{position:relative;width:48px;height:48px;border-radius:14px;background:#fff8f0;border:1px solid #f1dcc3;display:inline-flex;align-items:center;justify-content:center;color:var(--dark)}
    .cart-link svg{width:24px;height:24px;stroke:currentColor;stroke-width:2.2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .cart-count{position:absolute;top:-8px;right:-8px;min-width:22px;height:22px;border-radius:999px;background:var(--primary-dark);color:white;font-size:12px;font-weight:900;display:flex;align-items:center;justify-content:center;padding:0 6px}
    .checkout-page{background:var(--gray-100);min-height:calc(100vh - 80px);padding:48px 0}
    .checkout-layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:24px;align-items:start}
    .checkout-card{background:white;border:1px solid var(--gray-200);border-radius:8px;padding:24px;box-shadow:0 8px 26px rgba(57,48,83,.08)}
    .checkout-title{font-family:Poppins,sans-serif;color:var(--dark);font-size:34px;margin:0 0 8px}
    .checkout-subtitle{margin:0 0 24px;color:var(--gray-600)}
    .checkout-row{display:grid;grid-template-columns:82px 1fr auto;gap:16px;align-items:center;border-bottom:1px solid var(--gray-200);padding:16px 0}
    .checkout-row:first-child{padding-top:0}
    .checkout-thumb{width:82px;height:82px;background:#fff8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .checkout-thumb img{max-width:100%;max-height:76px;object-fit:contain;filter:drop-shadow(0 8px 8px rgba(57,48,83,.12))}
    .qty-control{display:flex;align-items:center;gap:9px;margin-top:10px}
    .qty-control button{width:30px;height:30px;border-radius:50%;border:0;background:var(--primary);color:var(--dark);font-weight:900;cursor:pointer}
    .line-price{font-family:Poppins,sans-serif;color:var(--dark);font-weight:900;text-align:right}
    .empty-cart{background:#fff8f0;border:1px dashed #efc391;color:var(--gray-600);border-radius:8px;padding:22px;line-height:1.6}
    .summary-line{display:flex;justify-content:space-between;gap:12px;margin:12px 0;color:var(--gray-600)}
    .summary-total{display:flex;justify-content:space-between;gap:12px;border-top:1px solid var(--gray-200);padding-top:16px;margin-top:16px;color:var(--dark);font-size:18px}
    .summary-total strong{font-family:Poppins,sans-serif;font-size:26px}
    .checkout-form label{display:block;font-weight:800;color:var(--dark);margin:14px 0 7px}
    .checkout-form .form-control{padding:12px 13px}
    .success-banner{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:16px 18px;margin-bottom:18px;color:#15803d;display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
    @media(max-width:900px){.checkout-layout{grid-template-columns:1fr}.public-nav .nav-links{gap:12px;font-size:14px}.checkout-row{grid-template-columns:70px 1fr}.line-price{grid-column:2;text-align:left}.checkout-thumb{width:70px;height:70px}}
</style>

<nav class="public-nav">
    <div class="container nav-inner">
        <a href="/" class="logo">Tifanny<span>.</span></a>
        <div class="nav-links">
            <a href="/#products">Produk</a>
            <a href="/#reseller">Reseller</a>
            <a href="/#contact">Kontak</a>
            <a href="{{ route('public.checkout.page') }}" class="cart-link" aria-label="Keranjang belanja">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.6"></circle><circle cx="18" cy="20" r="1.6"></circle><path d="M3 4h2l2.3 10.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H7"></path></svg>
                <span class="cart-count" id="navCartCount">0</span>
            </a>
            <a href="/admin" class="btn btn-dark" style="padding:14px 24px;border-radius:12px">ERP Dashboard</a>
        </div>
    </div>
</nav>

<main class="checkout-page">
    <div class="container">
        <h1 class="checkout-title">Checkout</h1>
        <p class="checkout-subtitle">Periksa pesanan, isi data pembeli, lalu proses transaksi.</p>

        @if(session('success'))
            <div class="success-banner" id="checkoutSuccess">
                <strong>{{ session('success') }}</strong>
                @if(session('invoice_url'))
                    <a class="btn btn-primary" href="{{ session('invoice_url') }}">Download Invoice</a>
                @endif
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="checkout-layout">
            <section class="checkout-card">
                <h2 class="font-poppins" style="margin:0 0 18px;color:var(--dark)">Keranjang Belanja</h2>
                <div id="checkoutCartList"></div>
            </section>

            <form method="POST" action="{{ route('public.checkout') }}" class="checkout-card checkout-form" onsubmit="return beforeCheckoutSubmit()">
                @csrf
                <h2 class="font-poppins" style="margin:0 0 18px;color:var(--dark)">Ringkasan</h2>
                <div class="summary-line"><span>Jumlah item</span><strong id="summaryQty">0 pack</strong></div>
                <div class="summary-line"><span>Subtotal</span><strong id="summarySubtotal">Rp 0</strong></div>
                <div class="summary-total"><span>Total</span><strong id="summaryTotal">Rp 0</strong></div>

                <label>Nama Pembeli</label>
                <input class="form-control" name="customer_name" placeholder="Nama pembeli" autocomplete="name">
                <label>No. WhatsApp</label>
                <input class="form-control" name="customer_phone" placeholder="08xxxxxxxxxx" autocomplete="tel">
                <label>Metode Pembayaran</label>
                <select class="form-control" name="payment_method">
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                    <option value="cash">Tunai di tempat</option>
                </select>
                <input type="hidden" name="discount" value="0">
                <input type="hidden" name="tax" value="0">
                <input type="hidden" name="cart_payload" id="checkoutCartPayload">
                <button class="btn btn-primary" style="width:100%;padding:15px;margin-top:18px;font-size:16px">Checkout Sekarang</button>
                <a class="btn btn-outline" href="/#products" style="width:100%;padding:13px;margin-top:10px">Tambah Produk Lagi</a>
            </form>
        </div>
    </div>
</main>

<script>
const storageKey = 'tifanny_public_cart';
let publicCart = loadPublicCart();
const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n || 0);

if (document.getElementById('checkoutSuccess')) {
    publicCart = [];
    savePublicCart();
}

function loadPublicCart() {
    try {
        return JSON.parse(localStorage.getItem(storageKey) || '[]');
    } catch (error) {
        return [];
    }
}

function savePublicCart() {
    localStorage.setItem(storageKey, JSON.stringify(publicCart));
}

function incCart(id) {
    const item = publicCart.find(row => Number(row.id) === Number(id));
    if (item && item.qty < item.stock) item.qty++;
    renderCheckoutCart();
}

function decCart(id) {
    const item = publicCart.find(row => Number(row.id) === Number(id));
    if (item) item.qty--;
    publicCart = publicCart.filter(row => row.qty > 0);
    renderCheckoutCart();
}

function renderCheckoutCart() {
    const list = document.getElementById('checkoutCartList');
    const count = publicCart.reduce((sum, item) => sum + item.qty, 0);
    const subtotal = publicCart.reduce((sum, item) => sum + item.price * item.qty, 0);

    savePublicCart();
    document.getElementById('navCartCount').textContent = count;
    document.getElementById('summaryQty').textContent = count + ' pack';
    document.getElementById('summarySubtotal').textContent = rupiah(subtotal);
    document.getElementById('summaryTotal').textContent = rupiah(subtotal);

    if (!publicCart.length) {
        list.innerHTML = `<div class="empty-cart">Keranjang masih kosong. <a href="/#products" style="font-weight:900;color:var(--dark)">Pilih produk dulu</a> untuk mulai checkout.</div>`;
        return;
    }

    list.innerHTML = publicCart.map(item => `
        <div class="checkout-row">
            <div class="checkout-thumb"><img src="${item.image || ''}" alt="${item.name}"></div>
            <div>
                <strong style="color:var(--dark)">${item.name}</strong><br>
                <small>${rupiah(item.price)} / pack</small>
                <div class="qty-control">
                    <button type="button" onclick="decCart(${item.id})">-</button>
                    <strong>${item.qty}</strong>
                    <button type="button" onclick="incCart(${item.id})">+</button>
                </div>
            </div>
            <div class="line-price">${rupiah(item.price * item.qty)}</div>
        </div>
    `).join('');
}

function beforeCheckoutSubmit() {
    if (!publicCart.length) {
        alert('Keranjang masih kosong.');
        return false;
    }
    document.getElementById('checkoutCartPayload').value = JSON.stringify(publicCart.map(item => ({id: item.id, qty: item.qty})));
    return true;
}

renderCheckoutCart();
</script>
@endsection
