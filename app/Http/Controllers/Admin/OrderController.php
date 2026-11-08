<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan Seluruh Daftar Pesanan
     */
    public function index()
    {
        $orders = DB::table('orders')
            ->join('users as customers', 'orders.user_id', '=', 'customers.id')
            ->leftJoin('users as couriers', 'orders.courier_id', '=', 'couriers.id')
            ->select(
                'orders.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'couriers.name as courier_name'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        // Mengambil 3 Kurir Spesifik untuk Penugasan (Yoga, Rafi, Mail)
        $couriers = User::role('kurir')->get();

        return view('admin.orders.index', compact('orders', 'couriers'));
    }

    /**
     * Fitur Assign Kurir ke Pesanan
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