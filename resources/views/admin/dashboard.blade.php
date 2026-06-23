@extends('layouts.tifanny', ['title' => $title ?? 'Tifanny ERP'])

@section('body')
<div class="app">
    <aside class="sidebar">
        <div class="brand">Tifanny<span>ERP</span><div style="font-size:11px;color:rgba(255,255,255,.38);font-family:Inter;margin-top:3px">Admin Dashboard</div></div>
        <nav class="menu">
            <div class="menu-label">Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="active">Dashboard</a>
            <a href="{{ route('admin.pos') }}">POS Kasir</a>
            <div class="menu-label">Operasional</div>
            <a href="{{ route('admin.inventory') }}">Inventaris</a>
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
                <div class="alert alert-danger">
                    <strong>{{ $lowStock->count() }} produk perlu restock.</strong>
                    {{ $lowStock->take(4)->map(fn($x) => $x->name.' '.$x->variant_name.' tinggal '.$x->stock.' pack')->join(', ') }}{{ $lowStock->count() > 4 ? ', dan lainnya' : '' }}
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
                    <a class="btn btn-outline" href="{{ route('admin.inventory') }}">Cek Stok</a>
                    <a class="btn btn-dark" href="{{ route('admin.ai') }}">AI Analytics</a>
                </div>
            </div>

            <div class="grid grid-4" style="margin-bottom:20px">
                <div class="card stat"><div class="label">Penjualan Hari Ini</div><div class="num">Rp {{ number_format($todaySales,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">{{ number_format($todayTransactions,0,',','.') }} transaksi hari ini</div></div>
                <div class="card stat"><div class="label">Revenue Bulan Ini</div><div class="num">Rp {{ number_format($monthRevenue,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">Est. profit Rp {{ number_format($monthProfit,0,',','.') }}</div></div>
                <div class="card stat"><div class="label">Total Transaksi</div><div class="num">{{ number_format($totalTransactions,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">Rata-rata Rp {{ number_format($avgOrderValue,0,',','.') }}</div></div>
                <div class="card stat"><div class="label">Stok & Invoice</div><div class="num">{{ number_format($totalStock,0,',','.') }}</div><div style="font-size:12px;color:var(--gray-600);margin-top:8px">{{ number_format($invoiceCount,0,',','.') }} invoice tersimpan</div></div>
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
                    <canvas id="salesChart" height="112"></canvas>
                </div>
                <div class="card">
                    <h3 class="font-poppins" style="margin-top:0;color:var(--dark)">Produk Terlaris</h3>
                    @forelse($bestProducts as $p)
                        <div style="display:grid;grid-template-columns:1fr auto;gap:10px;border-bottom:1px solid var(--gray-200);padding:11px 0">
                            <div><strong>{{ $p->product_name }}</strong><div style="font-size:12px;color:var(--gray-600)">{{ $p->variant_name }}</div></div>
                            <div style="text-align:right"><strong>{{ number_format($p->total_qty,0,',','.') }} pack</strong><div style="font-size:12px;color:var(--gray-600)">Rp {{ number_format($p->total_sales,0,',','.') }}</div></div>
                        </div>
                    @empty
                        <p style="color:var(--gray-600)">Belum ada transaksi. Buat transaksi di POS untuk memunculkan data.</p>
                    @endforelse
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
<script>
const formatMoney = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {label:'Revenue', data:@json($chartSales), borderColor:'#F59340', backgroundColor:'rgba(245,147,64,.14)', fill:true, tension:.35, pointRadius:3},
            {label:'Transaksi', data:@json($chartTransactions), borderColor:'#3B82F6', backgroundColor:'rgba(59,130,246,.12)', yAxisID:'transactions', tension:.35, pointRadius:3}
        ]
    },
    options: {
        interaction:{mode:'index', intersect:false},
        plugins:{legend:{position:'bottom'}},
        scales:{
            y:{ticks:{callback:value=>formatMoney(value)}},
            transactions:{position:'right', grid:{drawOnChartArea:false}, ticks:{precision:0}}
        }
    }
});
</script>
@endsection
