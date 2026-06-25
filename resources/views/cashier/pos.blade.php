@extends('cashier.layout', ['pageTitle' => 'Point of Sales', 'title' => 'POS Kasir - Tifanny ERP'])

@section('cashier_content')
<style>
    .cashier-pos-layout{display:grid;grid-template-columns:1fr 380px;gap:20px}
    .cashier-products{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
    .cashier-pos-card{text-align:center;cursor:pointer}
    .cashier-pos-card:hover{border-color:var(--primary);box-shadow:var(--shadow)}
    @media(max-width:900px){.cashier-pos-layout{grid-template-columns:1fr}}
</style>

<div class="cashier-pos-layout">
    <div>
        <div class="cashier-card" style="margin-bottom:14px">
            <input id="search" class="form-control" placeholder="Cari produk / SKU / barcode..." oninput="filterProducts(this.value)">
        </div>
        <div class="cashier-products" id="productGrid">
            @foreach($products as $product)
                @php
                    $variantText = strtolower($product->variant_name ?? '');
                    preg_match('/\d+/', $variantText, $sizeMatch);
                    $size = (int) ($product->weight_gram ?? ($sizeMatch[0] ?? 0));
                    $image = $size <= 110
                        ? 'amplang-85gr.png'
                        : ($size <= 165 ? 'amplang-130gr.png' : 'amplang-200gr.png');
                @endphp
                <div class="cashier-card cashier-pos-card" data-search="{{ strtolower($product->name.' '.$product->variant_name.' '.$product->sku.' '.$product->barcode) }}" onclick='addToCart(@json($product))'>
                    <div style="height:118px;background:#fff8f0;border-radius:12px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;overflow:hidden">
                        <img src="{{ asset('assets/products/'.$image) }}" alt="{{ $product->name }} {{ $product->variant_name }}" style="max-width:100%;max-height:112px;object-fit:contain;filter:drop-shadow(0 10px 10px rgba(57,48,83,.14))">
                    </div>
                    <strong>{{ $product->name }}</strong><br>
                    <span style="font-weight:800;color:var(--primary-dark)">{{ $product->variant_name }}</span>
                    <div style="font-size:12px;color:var(--gray-400);margin:5px 0">{{ $product->sku }}</div>
                    <div class="price" style="font-size:18px">Rp {{ number_format($product->selling_price,0,',','.') }}</div>
                    <div style="font-size:12px;color:{{ $product->stock <= $product->minimum_stock ? 'var(--danger)' : 'var(--gray-600)' }}">Stok: {{ $product->stock }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('cashier.pos.checkout') }}" class="cashier-card" onsubmit="return beforeSubmit()">
        @csrf
        <h3 class="font-poppins" style="margin-top:0;color:var(--dark)">Keranjang Kasir</h3>
        <div id="cartList" style="min-height:220px;color:var(--gray-600)">Keranjang kosong.</div>
        <hr style="border:0;border-top:1px solid var(--gray-200);margin:18px 0">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Subtotal</span><strong id="subtotalText">Rp 0</strong></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:12px"><span>Total</span><strong id="totalText" style="font-size:22px;color:var(--dark)">Rp 0</strong></div>
        <label>Metode Pembayaran</label>
        <select class="form-control" name="payment_method" style="margin:8px 0 12px">
            <option value="cash">Tunai</option>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer</option>
        </select>
        <input type="number" class="form-control" name="paid_amount" id="paidAmount" placeholder="Jumlah bayar tunai / opsional" style="margin-bottom:12px">
        <input type="hidden" name="discount" value="0">
        <input type="hidden" name="tax" value="0">
        <input type="hidden" name="cart_payload" id="cartPayload">
        <button class="btn btn-primary" style="width:100%;font-size:16px;padding:14px">Proses Pembayaran</button>
    </form>
</div>

<script>
let cart = [];
const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n || 0);

function filterProducts(q) {
    q = (q || '').toLowerCase();
    document.querySelectorAll('.cashier-pos-card').forEach(el => {
        el.style.display = el.dataset.search.includes(q) ? 'block' : 'none';
    });
}

function addToCart(p) {
    let item = cart.find(x => Number(x.id) === Number(p.id));
    if (item) {
        if (item.qty < Number(p.stock)) item.qty++;
    } else {
        cart.push({
            id: p.id,
            name: p.name + ' ' + p.variant_name,
            price: Number(p.selling_price),
            qty: 1,
            stock: Number(p.stock)
        });
    }
    renderCart();
}

function inc(id) {
    let item = cart.find(x => Number(x.id) === Number(id));
    if (item && item.qty < item.stock) item.qty++;
    renderCart();
}

function dec(id) {
    let item = cart.find(x => Number(x.id) === Number(id));
    if (item) item.qty--;
    cart = cart.filter(x => x.qty > 0);
    renderCart();
}

function renderCart() {
    let el = document.getElementById('cartList');
    if (!cart.length) {
        el.innerHTML = 'Keranjang kosong.';
    } else {
        el.innerHTML = cart.map(i => `
            <div class="cart-item">
                <div style="flex:1">
                    <strong>${i.name}</strong><br>
                    <small>${rupiah(i.price)} / pack</small>
                </div>
                <button type="button" onclick="dec(${i.id})">-</button>
                <strong>${i.qty}</strong>
                <button type="button" onclick="inc(${i.id})">+</button>
                <strong>${rupiah(i.price * i.qty)}</strong>
            </div>
        `).join('');
    }

    let total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    subtotalText.textContent = rupiah(total);
    totalText.textContent = rupiah(total);
    paidAmount.placeholder = 'Total: ' + rupiah(total);
}

function beforeSubmit() {
    if (!cart.length) {
        alert('Keranjang masih kosong');
        return false;
    }
    cartPayload.value = JSON.stringify(cart.map(i => ({id: i.id, qty: i.qty})));
    return true;
}
</script>
@endsection
