<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan Seluruh Daftar Pesanan dengan Fitur Search Pintar & Filter
     */
    public function index(Request $request)
    {
        $query = DB::table('orders')
            ->join('users as customers', 'orders.user_id', '=', 'customers.id')
            ->leftJoin('users as couriers', 'orders.courier_id', '=', 'couriers.id')
            ->select(
                'orders.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'couriers.name as courier_name'
            );

        // Filter berdasarkan status jika dipilih oleh admin
        if ($request->filled('status')) {
            $query->where('orders.status', $request->status);
        }

        // Fitur Search Pintar (Nama Customer / ID Angka / Formatted #ORD-X)
        if ($request->filled('search')) {
            $search = trim($request->search);
            
            // Ekstrak angka murni dari input (misal: #ORD-3 / ORD-3 -> 3)
            $cleanId = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $cleanId) {
                // 1. Cari berdasarkan Nama Customer
                $q->where('customers.name', 'like', "%{$search}%");

                // 2. Jika input mengandung angka, cari berdasarkan ID Order
                if (!empty($cleanId)) {
                    $q->orWhere('orders.id', $cleanId);
                }
            });
        }

        $orders = $query->orderBy('orders.created_at', 'desc')->get();

        // Ambil Rincian Item untuk Setiap Pesanan
        foreach ($orders as $order) {
            $order->items = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('order_items.order_id', $order->id)
                ->select('order_items.*', 'products.name as product_name', 'products.image')
                ->get();
        }

        // Ambil Daftar Kurir Aktif
        $couriers = User::role('kurir')->get();

        return view('admin.orders.index', compact('orders', 'couriers'));
    }

    /**
     * Fitur Assign Kurir ke Pesanan (Otomatis mengubah status ke 'dalam_pengiriman')
     */
    public function assignKurir(Request $request, $id)
    {
        $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'courier_id' => $request->courier_id,
                'status'     => 'dalam_pengiriman',
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Kurir berhasil ditugaskan untuk mengantar pesanan!');
    }

    /**
     * Update Status Pesanan Manual oleh Admin
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,dalam_pengiriman,selesai,dibatalkan',
        ]);

        DB::table('orders')
            ->where('id', $id)
            ->update([
                'status'     => $request->status,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}