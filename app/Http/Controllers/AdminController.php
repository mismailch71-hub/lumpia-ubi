<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Tampilkan Halaman Utama Dashboard Admin secara Aman & Dinamis
     */
    public function index()
    {
        // 1. Data Statistik Utama
        $totalProducts  = Product::count();
        $totalCouriers  = User::role('kurir')->count();
        $totalCustomers = User::role('customer')->count();
        
        // Inisialisasi default aman jika tabel orders belum terbuat
        $totalOrders              = 0;
        $totalRevenue             = 0;
        $pendingVerificationCount = 0;
        $recentOrders             = [];
        $busyCourierIds           = [];

        if (Schema::hasTable('orders')) {
            $totalOrders  = DB::table('orders')->count();
            $totalRevenue = DB::table('orders')->where('status', 'selesai')->sum('total_price') ?? 0;
            
            // Hitung jumlah pesanan QRIS yang menunggu verifikasi bukti bayar
            $pendingVerificationCount = DB::table('orders')
                ->where('status', 'menunggu_verifikasi')
                ->count();

            // Ambil 10 pesanan terbaru lengkap dengan nama customer & nama kurir pengantar
            $recentOrders = DB::table('orders')
                ->join('users as customers', 'orders.user_id', '=', 'customers.id')
                ->leftJoin('users as couriers', 'orders.courier_id', '=', 'couriers.id')
                ->select(
                    'orders.*',
                    'customers.name as customer_name',
                    'couriers.name as courier_name'
                )
                ->orderBy('orders.created_at', 'desc')
                ->take(10)
                ->get();

            // Ambil ID Kurir yang saat ini sedang dalam pengiriman aktif
            $busyCourierIds = DB::table('orders')
                ->where('status', 'dalam_pengiriman')
                ->whereNotNull('courier_id')
                ->pluck('courier_id')
                ->toArray();
        }

        // 2. Data Kurir Aktif
        $couriers = User::role('kurir')->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCouriers',
            'totalCustomers',
            'totalOrders',
            'totalRevenue',
            'pendingVerificationCount',
            'couriers',
            'busyCourierIds',
            'recentOrders'
        ));
    }

    /**
     * Konfirmasi Bukti Pembayaran QRIS Valid -> Ubah Status ke Diproses
     */
    public function verifyPayment($id)
    {
        DB::table('orders')->where('id', $id)->update([
            'status'     => 'diproses',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran QRIS untuk pesanan #ORD-' . $id . ' dikonfirmasi VALID! Pesanan siap ditugaskan ke kurir.');
    }

    /**
     * Tolak Bukti Pembayaran QRIS Tidak Valid / Palsu
     */
    public function rejectPayment($id)
    {
        DB::table('orders')->where('id', $id)->update([
            'status'     => 'ditolak',
            'updated_at' => now(),
        ]);

        return back()->with('error', 'Bukti pembayaran pesanan #ORD-' . $id . ' ditolak.');
    }
}