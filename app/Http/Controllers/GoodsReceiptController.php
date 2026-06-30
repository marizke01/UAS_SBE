<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function receive(Request $request, $poId)
    {
        DB::transaction(function () use ($request, $poId) {

            $grnId = DB::table('goods_receipts')->insertGetId([
                'po_id' => $poId,
                'created_at' => now()
            ]);

            foreach ($request->items as $item) {

                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $grnId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty']
                ]);

                // INI KUNCI STOK
                DB::table('branch_stocks')
                    ->where('product_id', $item['product_id'])
                    ->increment('quantity', $item['qty']);

                DB::table('stock_movements')->insert([
                    'product_id' => $item['product_id'],
                    'type' => 'in',
                    'quantity' => $item['qty'],
                    'reference' => 'GRN-'.$grnId
                ]);
            }

            DB::table('purchase_orders')
                ->where('id', $poId)
                ->update(['status' => 'completed']);
        });

        return back()->with('success', 'Barang diterima & stok update');
    }
}