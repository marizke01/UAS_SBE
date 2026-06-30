<h2>PURCHASE ORDER</h2>

<p>No: {{ $po->po_number }}</p>
<p>Tanggal: {{ date('Y-m-d') }}</p>

<hr>

<table width="100%" border="1" cellpadding="5">
    <tr>
        <th>Produk</th>
        <th>Qty</th>
    </tr>

    @foreach($items as $item)
    <tr>
        <td>{{ $item->name }}</td>
        <td>{{ $item->qty }}</td>
    </tr>
    @endforeach
</table>

<br><br>
<p>Ditandatangani oleh: Admin Tifanny ERP</p>