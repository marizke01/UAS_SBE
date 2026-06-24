@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
<style>
    .inventory-head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}
    .inventory-tools{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .stock-input{width:112px;border:1px solid var(--gray-200);border-radius:10px;padding:9px 10px;font:inherit}
    .stock-input:focus{outline:2px solid rgba(245,147,64,.25);border-color:var(--primary-dark)}
    .save-stock-btn{font-size:13px;padding:9px 12px}
    .stock-message{font-size:13px;color:var(--gray-600)}
    .stock-message.success{color:#15803d;font-weight:800}
    .stock-message.error{color:#dc2626;font-weight:800}
    .inventory-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .summary-card{background:white;border:1px solid var(--gray-200);border-radius:14px;padding:16px}
    .summary-card strong{font-family:Poppins,sans-serif;color:var(--dark);font-size:24px}
    .summary-card div{font-size:12px;color:var(--gray-600);margin-top:4px}
    @media(max-width:900px){.inventory-head{align-items:flex-start;flex-direction:column}.inventory-summary{grid-template-columns:1fr}.table{min-width:820px}}
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
            <a href="/admin/website-orders" class="{{ request()->is('admin/website-orders') ? 'active' : '' }}">Pesanan Website</a>
            <a href="/admin/invoices" class="{{ request()->is('admin/invoices') ? 'active' : '' }}">Invoice</a>
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="/">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <strong class="font-poppins" style="font-size:20px;color:var(--dark)">Inventaris</strong>
            <div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div>
        </div>
        <div class="content">
            @php
                $totalStock = (int) $stocks->sum(fn($s) => (int) $s->stock);
                $lowStockCount = $stocks->filter(fn($s) => (int) $s->stock <= (int) $s->minimum_stock)->count();
                $safeStockCount = $stocks->count() - $lowStockCount;
            @endphp

            <div class="inventory-summary">
                <div class="summary-card"><strong id="inventoryTotalStock">{{ number_format($totalStock,0,',','.') }}</strong><div>Total stok tersedia</div></div>
                <div class="summary-card"><strong id="inventoryLowStock">{{ number_format($lowStockCount,0,',','.') }}</strong><div>Produk perlu restock</div></div>
                <div class="summary-card"><strong id="inventorySafeStock">{{ number_format($safeStockCount,0,',','.') }}</strong><div>Produk aman</div></div>
            </div>

            <div class="card">
                <div class="inventory-head">
                    <div>
                        <h3 class="font-poppins" style="margin:0;color:var(--dark)">Stok Produk per Cabang</h3>
                        <p style="margin:6px 0 0;color:var(--gray-600);font-size:13px">Ubah stok langsung dari tabel inventaris. Klik Simpan untuk update database tanpa refresh halaman.</p>
                    </div>
                    <div class="inventory-tools">
                        <span class="stock-message" id="inventoryStockMessage">Siap memperbarui stok.</span>
                        <button type="button" class="btn btn-outline" id="refreshInventoryStock">Refresh Data</button>
                    </div>
                </div>

                <div style="overflow:auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cabang</th>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Baru</th>
                                <th>Min. Stok</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryStockRows">
                            @foreach($stocks as $s)
                                <tr data-stock-row="{{ $s->product_variant_id }}">
                                    <td>{{ $s->branch_name }}</td>
                                    <td><strong>{{ $s->product_name }} {{ $s->variant_name }}</strong></td>
                                    <td>{{ $s->sku }}</td>
                                    <td><strong data-current-stock="{{ $s->product_variant_id }}">{{ number_format($s->stock,0,',','.') }}</strong> pack</td>
                                    <td><input class="stock-input" type="number" min="0" value="{{ (int) $s->stock }}" data-stock-input="{{ $s->product_variant_id }}" data-minimum-stock="{{ (int) $s->minimum_stock }}" aria-label="Stok baru {{ $s->product_name }} {{ $s->variant_name }}"></td>
                                    <td>{{ $s->minimum_stock }}</td>
                                    <td>Rp {{ number_format($s->selling_price,0,',','.') }}</td>
                                    <td><span data-stock-status="{{ $s->product_variant_id }}" class="badge {{ $s->stock <= $s->minimum_stock ? 'badge-danger' : 'badge-success' }}">{{ $s->stock <= $s->minimum_stock ? 'Stok Rendah' : 'Aman' }}</span></td>
                                    <td><button type="button" class="btn btn-primary save-stock-btn" onclick="saveInventoryStock({{ $s->product_variant_id }})">Simpan</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin-top:20px">
                <div style="display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
                    <div>
                        <h3 class="font-poppins" style="margin:0;color:var(--dark)">Riwayat Perubahan Stok</h3>
                        <p style="margin:6px 0 0;color:var(--gray-600);font-size:13px">Mencatat stok lama, stok baru, tipe pergerakan, dan catatan perubahan.</p>
                    </div>
                    <a class="btn btn-outline" href="{{ route('admin.exports.stock') }}">Export Stok CSV</a>
                </div>
                <div style="overflow:auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th>Tipe</th>
                                <th>Qty</th>
                                <th>Stok Lama</th>
                                <th>Stok Baru</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockMovements as $movement)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</td>
                                    <td><strong>{{ $movement->product_name }} {{ $movement->variant_name }}</strong></td>
                                    <td>{{ $movement->sku }}</td>
                                    <td><span class="badge {{ $movement->movement_type === 'out' ? 'badge-danger' : ($movement->movement_type === 'adjustment' ? 'badge-warning' : 'badge-success') }}">{{ $movement->movement_type }}</span></td>
                                    <td>{{ number_format($movement->qty,0,',','.') }}</td>
                                    <td>{{ number_format($movement->stock_before,0,',','.') }}</td>
                                    <td>{{ number_format($movement->stock_after,0,',','.') }}</td>
                                    <td>{{ $movement->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8">Belum ada riwayat perubahan stok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const inventoryCsrfToken = @json(csrf_token());
const inventoryMessage = document.getElementById('inventoryStockMessage');
const formatInventoryNumber = value => new Intl.NumberFormat('id-ID').format(value || 0);

document.getElementById('refreshInventoryStock').addEventListener('click', loadInventoryStockRows);

function setInventoryMessage(text, type = '') {
    inventoryMessage.textContent = text;
    inventoryMessage.className = 'stock-message ' + type;
}

async function saveInventoryStock(productId) {
    const input = document.querySelector(`[data-stock-input="${productId}"]`);
    const newStock = Number(input.value);

    if (!Number.isInteger(newStock) || newStock < 0) {
        setInventoryMessage('Stok baru harus angka minimal 0.', 'error');
        input.focus();
        return;
    }

    setInventoryMessage('Menyimpan perubahan stok...');

    try {
        const response = await fetch(@json(route('admin.stocks.update')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': inventoryCsrfToken
            },
            body: JSON.stringify({product_id: productId, new_stock: newStock})
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Stok gagal diperbarui.');

        applyInventoryStockValue(productId, data.new_stock);
        updateInventorySummary(data.summary);
        setInventoryMessage(data.message || 'Stok berhasil diperbarui.', 'success');
    } catch (error) {
        setInventoryMessage(error.message || 'Terjadi kesalahan saat menyimpan stok.', 'error');
    }
}

async function loadInventoryStockRows() {
    setInventoryMessage('Mengambil data stok terbaru...');

    try {
        const response = await fetch(@json(route('admin.stocks.modal-data')), {
            headers: {'Accept': 'application/json'}
        });
        if (!response.ok) throw new Error('Gagal memuat data stok.');
        const data = await response.json();

        (data.products || []).forEach(product => {
            const input = document.querySelector(`[data-stock-input="${product.product_id}"]`);
            if (!input) return;
            input.value = Number(product.stock);
            applyInventoryStockValue(product.product_id, product.stock);
        });

        updateInventorySummary(data.summary);
        setInventoryMessage('Data stok terbaru sudah dimuat.', 'success');
    } catch (error) {
        setInventoryMessage(error.message || 'Data stok gagal dimuat.', 'error');
    }
}

function applyInventoryStockValue(productId, stock) {
    const current = document.querySelector(`[data-current-stock="${productId}"]`);
    const input = document.querySelector(`[data-stock-input="${productId}"]`);
    const status = document.querySelector(`[data-stock-status="${productId}"]`);
    const minimum = Number(input?.dataset.minimumStock || 0);
    const isLow = Number(stock) <= minimum;

    if (current) current.textContent = formatInventoryNumber(stock);
    if (input) input.value = Number(stock);
    if (status) {
        status.textContent = isLow ? 'Stok Rendah' : 'Aman';
        status.className = 'badge ' + (isLow ? 'badge-danger' : 'badge-success');
    }
}

function updateInventorySummary(summary) {
    if (!summary) return;
    const totalRows = document.querySelectorAll('[data-stock-row]').length;
    const lowCount = Number(summary.low_stock_count || 0);
    document.getElementById('inventoryTotalStock').textContent = formatInventoryNumber(summary.total_stock);
    document.getElementById('inventoryLowStock').textContent = formatInventoryNumber(lowCount);
    document.getElementById('inventorySafeStock').textContent = formatInventoryNumber(Math.max(0, totalRows - lowCount));
}
</script>
@endsection
