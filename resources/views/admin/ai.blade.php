@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
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
            <div class="menu-label">Analitik</div>
            <a href="/admin/reports" class="{{ request()->is('admin/reports') ? 'active' : '' }}">Laporan</a>
            <a href="/admin/ai-analytics" class="{{ request()->is('admin/ai-analytics') ? 'active' : '' }}">AI Analytics</a>
            <div class="menu-label">Publik</div>
            <a href="/">Website Publik</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar"><strong class="font-poppins" style="font-size:20px;color:var(--dark)">{{ $pageTitle ?? 'AI Analytics Dashboard' }}</strong><div style="margin-left:auto;color:var(--gray-600);font-size:14px">Tifanny Admin</div></div>
        <div class="content">
            <div class="grid grid-4" style="margin-bottom:20px">
                <div class="card stat"><div class="num">Rp {{ number_format($projection,0,',','.') }}</div><div class="label">Prediksi Revenue 7 Hari</div></div>
                <div class="card stat"><div class="num">Rp {{ number_format($totalRevenue,0,',','.') }}</div><div class="label">Total Pendapatan</div></div>
                <div class="card stat"><div class="num">{{ number_format($totalTransactions,0,',','.') }}</div><div class="label">Total Transaksi</div></div>
                <div class="card stat"><div class="num">Rp {{ number_format($estimatedProfit,0,',','.') }}</div><div class="label">Estimasi Profit</div></div>
            </div>

            <div class="grid" style="grid-template-columns:2fr 1fr;margin-bottom:20px">
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">Tren Penjualan 14 Hari</h3>
                    <canvas id="aiSalesTrend" height="120"></canvas>
                </div>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">Top 5 Produk</h3>
                    <canvas id="aiTopProducts" height="200"></canvas>
                </div>
            </div>

            <div class="grid grid-3" style="margin-bottom:20px">
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">Produk Terlaris</h3>
                    @forelse($topProducts as $product)
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--gray-200);padding:10px 0">
                            <span>{{ $product->product_name }} {{ $product->variant_name }}</span>
                            <strong>{{ (int) $product->total_qty }} pack</strong>
                        </div>
                    @empty
                        <p style="color:var(--gray-600)">Belum ada transaksi dalam 30 hari terakhir.</p>
                    @endforelse
                </div>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">Produk Kurang Laku</h3>
                    @foreach($slowProducts as $product)
                        <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--gray-200);padding:10px 0">
                            <span>{{ $product->name }} {{ $product->variant_name }}</span>
                            <strong>{{ (int) $product->sold_30_days }} pack</strong>
                        </div>
                    @endforeach
                </div>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0">AI Insight</h3>
                    @foreach($insights as $insight)
                        <div style="border-left:4px solid var(--primary);background:var(--gray-100);border-radius:12px;padding:12px;margin-bottom:10px">{{ $insight }}</div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <h3 class="font-poppins" style="margin-top:0">Prediksi Stok & Rekomendasi Restock</h3>
                <table class="table">
                    <thead><tr><th>Produk</th><th>Stok</th><th>Minimum</th><th>Rata-rata Terjual/Hari</th><th>Estimasi Habis</th><th>Rekomendasi Produksi</th></tr></thead>
                    <tbody>
                        @foreach($forecastProducts as $product)
                            <tr>
                                <td><strong>{{ $product->name }} {{ $product->variant_name }}</strong><br><span style="color:var(--gray-400);font-size:12px">{{ $product->sku }}</span></td>
                                <td>{{ (int) $product->stock }} pack</td>
                                <td>{{ (int) $product->minimum_stock }} pack</td>
                                <td>{{ number_format($product->avg_daily_sales, 1, ',', '.') }} pack</td>
                                <td>
                                    @if($product->days_until_stockout === null)
                                        Belum ada pola
                                    @elseif($product->days_until_stockout <= 7)
                                        <span class="badge badge-danger">{{ (int) $product->days_until_stockout }} hari</span>
                                    @else
                                        <span class="badge badge-success">{{ (int) $product->days_until_stockout }} hari</span>
                                    @endif
                                </td>
                                <td>{{ (int) $product->recommended_restock }} pack</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<script>
const money = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
new Chart(document.getElementById('aiSalesTrend'), {
    type: 'line',
    data: {
        labels: @json($salesTrend->pluck('label')),
        datasets: [
            {label: 'Revenue', data: @json($salesTrend->pluck('revenue')), borderColor: '#FFB26B', backgroundColor: 'rgba(255,178,107,.18)', fill: true, tension: .35},
            {label: 'Transaksi', data: @json($salesTrend->pluck('transactions')), borderColor: '#3B82F6', yAxisID: 'transactions', tension: .35}
        ]
    },
    options: {
        interaction: {mode: 'index', intersect: false},
        scales: {
            y: {ticks: {callback: value => money(value)}},
            transactions: {position: 'right', grid: {drawOnChartArea: false}, ticks: {precision: 0}}
        }
    }
});
new Chart(document.getElementById('aiTopProducts'), {
    type: 'bar',
    data: {
        labels: @json($topProducts->map(fn ($p) => $p->product_name . ' ' . $p->variant_name)),
        datasets: [{label: 'Pack terjual', data: @json($topProducts->pluck('total_qty')), backgroundColor: '#F59340'}]
    },
    options: {indexAxis: 'y', plugins: {legend: {display: false}}, scales: {x: {ticks: {precision: 0}}}}
});
</script>
@endsection
