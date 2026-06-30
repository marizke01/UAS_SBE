@extends('layouts.tifanny', ['title' => $pageTitle ?? 'Buat Purchase Order'])

@section('body')
<style>
    .po-create-head {display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
    .product-row.is-selected {background:rgba(255,178,107,0.06)}
    .qty-input {width:100px;border:1px solid var(--gray-200);border-radius:8px;padding:8px 10px;text-align:center;font-weight:700}
    .qty-input:disabled {background:var(--gray-100);color:var(--gray-400);cursor:not-allowed}
    .search-box {margin-bottom:18px}
    .checkbox-cell {width:50px;text-align:center}
    .select-all-btn {padding:5px 10px;background:none;border:1px solid var(--gray-200);border-radius:6px;font-size:12px;font-weight:600;cursor:pointer}
    .select-all-btn:hover {background:var(--gray-100)}
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
            <a href="/admin/purchase-order" class="active">Purchase Order</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Pengaturan</div>
            <a href="/admin/account" class="{{ request()->is('admin/account') ? 'active' : '' }}">Akun Saya</a>
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
            <div class="po-create-head">
                <div>
                    <h2 class="font-poppins" style="margin:0;color:var(--dark)">Buat Purchase Order Baru</h2>
                    <p style="margin:4px 0 0;color:var(--gray-600);font-size:14px">Pilih produk dan tentukan jumlah barang yang ingin dipesan.</p>
                </div>
                <a href="{{ route('admin.purchase-order.index') }}" class="btn btn-outline">Batal</a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <strong>Gagal!</strong> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.purchase-order.store') }}" method="POST" id="poForm">
                @csrf

                <div class="card">
                    <div class="search-box">
                        <input type="text" id="productSearchInput" class="form-control" placeholder="Cari nama produk...">
                    </div>

                    <div style="overflow-x:auto">
                        <table class="table" id="productsTable">
                            <thead>
                                <tr>
                                    <th class="checkbox-cell">
                                        Pilih
                                    </th>
                                    <th>Nama Produk</th>
                                    <th style="width:180px;text-align:center">Jumlah Pesan (Qty)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr class="product-row" id="row_{{ $product->id }}">
                                    <td class="checkbox-cell">
                                        <input type="checkbox" name="items[{{ $product->id }}][checked]" value="1" id="check_{{ $product->id }}" onchange="toggleProduct({{ $product->id }})" style="width:18px;height:18px;cursor:pointer">
                                        <input type="hidden" name="items[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                    </td>
                                    <td>
                                        <label for="check_{{ $product->id }}" style="font-weight:700;color:var(--dark);cursor:pointer;display:block">
                                            {{ $product->name }}
                                        </label>
                                    </td>
                                    <td style="text-align:center">
                                        <input type="number" name="items[{{ $product->id }}][qty]" id="qty_{{ $product->id }}" class="qty-input" value="10" min="1" disabled>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:24px;color:var(--gray-600)">Tidak ada produk aktif.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top:20px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary" style="padding:12px 30px;font-size:16px;box-shadow:0 4px 15px rgba(255,178,107,0.4)">
                        Kirim Purchase Order (Draft)
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    function toggleProduct(id) {
        const checkbox = document.getElementById('check_' + id);
        const qtyInput = document.getElementById('qty_' + id);
        const row = document.getElementById('row_' + id);

        if (checkbox.checked) {
            qtyInput.removeAttribute('disabled');
            row.classList.add('is-selected');
        } else {
            qtyInput.setAttribute('disabled', 'true');
            row.classList.remove('is-selected');
        }
    }

    document.getElementById('productSearchInput').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#productsTable tbody tr');
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            const text = row.querySelector('label').textContent.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>
@endsection
