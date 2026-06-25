@extends('cashier.layout', ['pageTitle' => 'Monitoring Stok', 'title' => 'Monitoring Stok Kasir - Tifanny ERP'])

@section('cashier_content')
<section class="cashier-card">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px">
        <div>
            <h2 class="font-poppins" style="margin:0;color:var(--dark)">Stok Produk</h2>
            <p class="cashier-muted" style="margin:6px 0 0">Kasir hanya dapat melihat stok. Perubahan stok tetap dilakukan oleh admin/owner.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('cashier.pos') }}">Buka POS</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>SKU</th>
                <th>Stok</th>
                <th>Minimum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                <tr>
                    <td>{{ $stock->name }} {{ $stock->variant_name }}</td>
                    <td>{{ $stock->sku }}</td>
                    <td>{{ (int) $stock->stock }} pack</td>
                    <td>{{ (int) $stock->minimum_stock }} pack</td>
                    <td>
                        @if((int) $stock->stock <= (int) $stock->minimum_stock)
                            <span class="badge badge-danger">Stok Menipis</span>
                        @else
                            <span class="badge badge-success">Aman</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Data stok belum tersedia.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
