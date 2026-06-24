@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
<style>
    .dashboard-alert-action{display:flex;align-items:center;justify-content:space-between;gap:14px}
    .dashboard-alert-action .btn{font-size:12px;padding:8px 12px;white-space:nowrap;background:white;border:1px solid rgba(220,38,38,.18);color:#b91c1c}
    .stat-micro{font-size:11px;color:var(--gray-400);margin-top:5px;line-height:1.35}
    .omni-note{font-size:11px;color:var(--gray-400);margin-top:5px;line-height:1.35}
    .chart-wrap{height:286px;position:relative;margin-top:14px;padding:6px 0 0}
    .chart-wrap canvas{width:100%!important;height:100%!important}
    .chart-summary{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
    .chart-pill{display:inline-flex;gap:7px;align-items:center;border:1px solid var(--gray-200);border-radius:999px;padding:7px 10px;font-size:12px;font-weight:800;color:var(--gray-600);background:#fff}
    .chart-dot{width:10px;height:10px;border-radius:50%;background:var(--primary-dark)}
    .chart-dot.dark{background:var(--dark)}
    .best-bars{display:grid;gap:14px;margin-top:14px}
    .best-bar-row{display:grid;gap:7px}
    .best-bar-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-end;font-size:13px}
    .best-bar-name{font-weight:900;color:var(--dark)}
    .best-bar-meta{font-size:12px;color:var(--gray-600)}
    .best-track{height:13px;border-radius:999px;background:#fff3e6;overflow:hidden;border:1px solid #ffe2bf}
    .best-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--primary-dark),var(--primary));min-width:9%}
    .best-empty{border:1px dashed var(--gray-200);border-radius:12px;padding:18px;color:var(--gray-600);background:var(--gray-100)}
    .stock-modal-backdrop{position:fixed;inset:0;z-index:100;background:rgba(15,23,42,.48);display:none;align-items:center;justify-content:center;padding:18px}
    .stock-modal-backdrop.is-open{display:flex}
    .stock-modal{width:min(920px,100%);max-height:88vh;overflow:hidden;background:white;border-radius:18px;box-shadow:0 25px 70px rgba(15,23,42,.28);border:1px solid var(--gray-200);display:flex;flex-direction:column}
    .stock-modal-header{padding:20px 22px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
    .stock-modal-title{font-family:Poppins,sans-serif;color:var(--dark);font-size:22px;font-weight:900;margin:0}
    .stock-modal-subtitle{font-size:13px;color:var(--gray-600);margin:5px 0 0}
    .stock-modal-close{border:0;background:var(--gray-100);color:var(--dark);width:34px;height:34px;border-radius:10px;cursor:pointer;font-weight:900}
    .stock-modal-body{padding:18px 22px;overflow:auto}
    .stock-modal-footer{padding:16px 22px;border-top:1px solid var(--gray-200);display:flex;justify-content:space-between;gap:12px;align-items:center;background:#fff}
    .stock-table{width:100%;border-collapse:collapse}
    .stock-table th{font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-400);text-align:left;background:var(--gray-100);padding:12px}
    .stock-table td{padding:12px;border-bottom:1px solid var(--gray-200);font-size:14px}
    .stock-input{width:120px;border:1px solid var(--gray-200);border-radius:10px;padding:9px 10px;font:inherit}
    .stock-input:focus{outline:2px solid rgba(245,147,64,.25);border-color:var(--primary-dark)}
    .stock-badge{display:inline-flex;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:900}
    .stock-badge.low{background:rgba(239,68,68,.1);color:#dc2626}
    .stock-badge.safe{background:rgba(34,197,94,.12);color:#15803d}
    .stock-modal-message{font-size:13px;color:var(--gray-600)}
    .stock-modal-message.success{color:#15803d;font-weight:800}
    .stock-modal-message.error{color:#dc2626;font-weight:800}
    @media(max-width:900px){.dashboard-alert-action{align-items:flex-start;flex-direction:column}.chart-wrap{height:240px}.stock-table{min-width:680px}.stock-modal-footer{align-items:flex-start;flex-direction:column}}
</style>
<div class="app">
    <aside class="sidebar">
        <div class="brand">Tifanny<span>ERP</span><div style="font-size:11px;color:rgba(255,255,255,.38);font-family:Inter;margin-top:3px">Admin Dashboard</div></div>
        <nav class="menu">
            <div class="menu-label">Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="active">Dashboard</a>
            <a href="{{ route('admin.pos') }}">POS Kasir</a>
            <div class="menu-label">Operasional</div>
            <a href="{{ route('admin.inventory') }}">Inventaris</a>
            <a href="{{ route('admin.website-orders') }}">Pesanan Website</a>
            <a href="{{ route('admin.invoices') }}">Invoice</a>
            <div class="menu-label">Analitik</div>
            <a href="{{ route('admin.reports') }}">Laporan</a>
            <a href="{{ route('admin.ai') }}">AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="{{ route('home') }}">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle ?? 'Dashboard ERP' }}</strong>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin-left:auto">@csrf<button class="btn btn-outline" type="submit">Logout</button></form>
        </div>
        <div class="content">
            @if($lowStock->count())
                <div id="lowStockAlert" class="alert alert-danger">
                    <div class="dashboard-alert-action">
                        <div>
                            <strong><span id="lowStockCount">{{ $lowStock->count() }}</span> produk perlu restock.</strong>
                            <span id="lowStockText">{{ $lowStock->take(4)->map(fn($x) => $x->name.' '.$x->variant_name.' tinggal '.$x->stock.' pack')->join(', ') }}{{ $lowStock->count() > 4 ? ', dan lainnya' : '' }}</span>
                        </div>
                        <a class="btn btn-outline" href="{{ route('admin.inventory') }}">Buat PO / Pesan Ke Supplier</a>
                    </div>
                </div>
            @else
                <div id="lowStockAlert" class="alert alert-danger" style="display:none">
                    <div class="dashboard-alert-action">
                        <div>
                            <strong><span id="lowStockCount">0</span> produk perlu restock.</strong>
                            <span id="lowStockText"></span>
                        </div>
                        <a class="btn btn-outline" href="{{ route('admin.inventory') }}">Buat PO / Pesan Ke Supplier</a>
                    </div>
                </div>
            @endif

            <div class="card" style="display:flex;gap:20px;align-items:center;justify-content:space-between;margin-bottom:20px;background:linear-gradient(135deg,#fff8f0,#ffffff);border-color:#ffd4a3">
                <div>
                    <div style="font-size:13px;font-weight:800;color:var(--primary-dark);text-transform:uppercase;letter-spacing:.8px">Admin / Owner</div>
                    <h1 class="font-poppins" style="margin:6px 0;color:var(--dark);font-size:30px">Amplang Tifanny ERP</h1>
                    <p style="margin:0;color:var(--gray-600)">Pantau penjualan, stok, invoice, laporan, dan AI Analytics dari satu dashboard admin.</p>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end">
                    <a class="btn btn-primary" href="{{ route('admin.pos') }}">Buka POS</a>
                    <button type="button" class="btn btn-outline" id="openStockModal">Cek Stok</button>
                    <a class="btn btn-dark" href="{{ route('admin.ai') }}">AI Analytics</a>
                </div>
            </div>

            <div class="grid grid-4" style="margin-bottom:20px">
                <div class="card stat"><div class="label">Penjualan Hari Ini</div><div class="num">Rp {{ number_format($todaySales,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">{{ number_format($todayTransactions,0,',','.') }} transaksi hari ini</div><div class="omni-note">({{ number_format($todayWebsiteTransactions,0,',','.') }} via Website, {{ number_format($todayPosTransactions,0,',','.') }} via POS Kasir)</div></div>
                <div class="card stat"><div class="label">Revenue Bulan Ini</div><div class="num">Rp {{ number_format($monthRevenue,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">Est. profit Rp {{ number_format($monthProfit,0,',','.') }}</div><div class="stat-micro">(Berdasarkan perhitungan HPP otomatis)</div></div>
                <div class="card stat"><div class="label">Total Transaksi</div><div class="num">{{ number_format($totalTransactions,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">Rata-rata Rp {{ number_format($avgOrderValue,0,',','.') }}</div><div class="omni-note">({{ number_format($websiteTransactions,0,',','.') }} via Website, {{ number_format($posTransactions,0,',','.') }} via POS Kasir)</div></div>
                <div class="card stat"><div class="label">Stok & Invoice</div><div class="num" id="totalStockValue">{{ number_format($totalStock,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">{{ number_format($invoiceCount,0,',','.') }} invoice tersimpan</div></div>
            </div>

            <div class="grid" style="grid-template-columns:minmax(0,2fr) minmax(300px,1fr);margin-bottom:20px">
                <div class="card">
                    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:10px">
                        <div>
                            <h3 class="font-poppins" style="margin:0;color:var(--dark)">Grafik Penjualan 7 Hari</h3>
                            <p style="margin:6px 0 0;color:var(--gray-600);font-size:13px">Revenue dan jumlah transaksi harian.</p>
                        </div>
                        <a class="btn btn-outline" href="{{ route('admin.reports') }}">Laporan</a>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="chart-summary">
                        <span class="chart-pill"><span class="chart-dot"></span>Revenue 7 hari</span>
                        <span class="chart-pill"><span class="chart-dot dark"></span>Jumlah transaksi</span>
                    </div>
                </div>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0;color:var(--dark)">Produk Terlaris</h3>
                    <p style="margin:6px 0 0;color:var(--gray-600);font-size:13px">Porsi penjualan berdasarkan varian produk.</p>
                    @php($maxBestQty = max(1, (int) $bestProducts->max('total_qty')))
                    <div class="best-bars">
                        @forelse($bestProducts as $p)
                            @php($barWidth = max(9, round(((int) $p->total_qty / $maxBestQty) * 100)))
                            <div class="best-bar-row">
                                <div class="best-bar-head">
                                    <div>
                                        <div class="best-bar-name">{{ $p->product_name }}</div>
                                        <div class="best-bar-meta">{{ $p->variant_name }}</div>
                                    </div>
                                    <div style="text-align:right">
                                        <strong style="color:var(--dark)">{{ number_format($p->total_qty,0,',','.') }} pack</strong>
                                        <div class="best-bar-meta">Rp {{ number_format($p->total_sales,0,',','.') }}</div>
                                    </div>
                                </div>
                                <div class="best-track"><div class="best-fill" style="width:{{ $barWidth }}%"></div></div>
                            </div>
                        @empty
                            <div class="best-empty">Belum ada transaksi. Buat transaksi di POS untuk memunculkan data.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:8px">
                    <h3 class="font-poppins" style="margin:0;color:var(--dark)">Transaksi Terbaru</h3>
                    <a class="btn btn-outline" href="{{ route('admin.invoices') }}">Invoice</a>
                </div>
                <table class="table">
                    <thead><tr><th>No Transaksi</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($latestSales as $sale)
                            <tr>
                                <td><strong>{{ $sale->transaction_number }}</strong></td>
                                <td>{{ $sale->customer_name }}</td>
                                <td>Rp {{ number_format($sale->grand_total,0,',','.') }}</td>
                                <td>{{ strtoupper($sale->payment_method) }}</td>
                                <td><span class="badge badge-success">{{ $sale->payment_status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Belum ada transaksi POS. Silakan demo melalui menu POS Kasir.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<div class="stock-modal-backdrop" id="stockModal" aria-hidden="true">
    <div class="stock-modal" role="dialog" aria-modal="true" aria-labelledby="stockModalTitle">
        <div class="stock-modal-header">
            <div>
                <h2 class="stock-modal-title" id="stockModalTitle">Ubah / Update Stok</h2>
                <p class="stock-modal-subtitle">Masukkan stok baru per produk. Perubahan disimpan langsung ke database tanpa refresh halaman.</p>
            </div>
            <button type="button" class="stock-modal-close" id="closeStockModal" aria-label="Tutup modal">X</button>
        </div>
        <div class="stock-modal-body">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Stok Baru</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="stockModalRows">
                    <tr><td colspan="5" style="color:var(--gray-600)">Memuat data stok...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="stock-modal-footer">
            <div class="stock-modal-message" id="stockModalMessage">Pilih produk, isi stok baru, lalu klik Simpan.</div>
            <button type="button" class="btn btn-outline" id="refreshStockRows">Refresh Data</button>
        </div>
    </div>
</div>
<script>
const formatMoney = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
const formatNumber = value => new Intl.NumberFormat('id-ID').format(value || 0);
const csrfToken = @json(csrf_token());

const stockModal = document.getElementById('stockModal');
const stockRowsEl = document.getElementById('stockModalRows');
const stockMessageEl = document.getElementById('stockModalMessage');

document.getElementById('openStockModal').addEventListener('click', () => {
    stockModal.classList.add('is-open');
    stockModal.setAttribute('aria-hidden', 'false');
    loadStockRows();
});

document.getElementById('closeStockModal').addEventListener('click', closeStockModal);
document.getElementById('refreshStockRows').addEventListener('click', loadStockRows);
stockModal.addEventListener('click', event => {
    if (event.target === stockModal) closeStockModal();
});

function closeStockModal() {
    stockModal.classList.remove('is-open');
    stockModal.setAttribute('aria-hidden', 'true');
}

function setStockMessage(text, type = '') {
    stockMessageEl.textContent = text;
    stockMessageEl.className = 'stock-modal-message ' + type;
}

async function loadStockRows() {
    stockRowsEl.innerHTML = '<tr><td colspan="5" style="color:var(--gray-600)">Memuat data stok...</td></tr>';
    setStockMessage('Mengambil data stok terbaru...');

    try {
        const response = await fetch(@json(route('admin.stocks.modal-data')), {
            headers: {'Accept': 'application/json'}
        });
        if (!response.ok) throw new Error('Gagal memuat data stok.');
        const data = await response.json();
        renderStockRows(data.products || []);
        updateDashboardStockSummary(data.summary);
        setStockMessage('Data stok siap diperbarui.');
    } catch (error) {
        stockRowsEl.innerHTML = '<tr><td colspan="5" style="color:#dc2626">Data stok gagal dimuat.</td></tr>';
        setStockMessage(error.message || 'Terjadi kesalahan saat memuat data.', 'error');
    }
}

function renderStockRows(products) {
    if (!products.length) {
        stockRowsEl.innerHTML = '<tr><td colspan="5" style="color:var(--gray-600)">Belum ada produk aktif.</td></tr>';
        return;
    }

    stockRowsEl.innerHTML = products.map(product => {
        const isLow = Number(product.stock) <= Number(product.minimum_stock);
        return `
            <tr data-product-row="${product.product_id}">
                <td><strong style="color:var(--dark)">${escapeHtml(product.name)}</strong><div style="font-size:12px;color:var(--gray-600)">${escapeHtml(product.sku || '-')}</div></td>
                <td><strong data-current-stock="${product.product_id}">${formatNumber(product.stock)}</strong> pack</td>
                <td><input class="stock-input" type="number" min="0" value="${Number(product.stock)}" data-stock-input="${product.product_id}" aria-label="Stok baru ${escapeHtml(product.name)}"></td>
                <td><span class="stock-badge ${isLow ? 'low' : 'safe'}" data-stock-status="${product.product_id}">${isLow ? 'Restock' : 'Aman'}</span></td>
                <td><button type="button" class="btn btn-primary" style="padding:8px 12px;font-size:13px" onclick="saveStock(${product.product_id})">Simpan</button></td>
            </tr>
        `;
    }).join('');
}

async function saveStock(productId) {
    const input = document.querySelector(`[data-stock-input="${productId}"]`);
    const newStock = Number(input.value);

    if (!Number.isInteger(newStock) || newStock < 0) {
        setStockMessage('Stok baru harus berupa angka minimal 0.', 'error');
        input.focus();
        return;
    }

    setStockMessage('Menyimpan perubahan stok...');

    try {
        const response = await fetch(@json(route('admin.stocks.update')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({product_id: productId, new_stock: newStock})
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Stok gagal diperbarui.');

        document.querySelector(`[data-current-stock="${productId}"]`).textContent = formatNumber(data.new_stock);
        const status = document.querySelector(`[data-stock-status="${productId}"]`);
        status.textContent = 'Tersimpan';
        status.className = 'stock-badge safe';
        updateDashboardStockSummary(data.summary);
        setStockMessage(data.message || 'Stok berhasil diperbarui.', 'success');
        await loadStockRows();
    } catch (error) {
        setStockMessage(error.message || 'Terjadi kesalahan saat menyimpan stok.', 'error');
    }
}

function updateDashboardStockSummary(summary) {
    if (!summary) return;
    document.getElementById('totalStockValue').textContent = formatNumber(summary.total_stock);

    const alertEl = document.getElementById('lowStockAlert');
    const countEl = document.getElementById('lowStockCount');
    const textEl = document.getElementById('lowStockText');
    const count = Number(summary.low_stock_count || 0);

    countEl.textContent = formatNumber(count);
    textEl.textContent = summary.low_stock_text || '';
    alertEl.style.display = count > 0 ? '' : 'none';
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {label:'Revenue', data:@json($chartSales), borderColor:'#F59340', backgroundColor:'rgba(245,147,64,.18)', fill:true, tension:.42, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#F59340', pointBorderColor:'#fff', pointBorderWidth:2, borderWidth:3},
            {label:'Transaksi', data:@json($chartTransactions), borderColor:'#393053', backgroundColor:'rgba(57,48,83,.08)', yAxisID:'transactions', tension:.42, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#393053', pointBorderColor:'#fff', pointBorderWidth:2, borderWidth:3}
        ]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        interaction:{mode:'index', intersect:false},
        plugins:{
            legend:{display:false},
            tooltip:{backgroundColor:'#393053', padding:12, titleFont:{weight:'bold'}, callbacks:{label:ctx=>ctx.dataset.label === 'Revenue' ? 'Revenue: '+formatMoney(ctx.parsed.y) : 'Transaksi: '+ctx.parsed.y}}
        },
        scales:{
            x:{grid:{display:false}, ticks:{color:'#666'}},
            y:{beginAtZero:true, grid:{color:'rgba(57,48,83,.08)'}, ticks:{color:'#666', callback:value=>formatMoney(value)}},
            transactions:{beginAtZero:true, position:'right', grid:{drawOnChartArea:false}, ticks:{precision:0, color:'#666'}}
        }
    }
});
</script>
@endsection
