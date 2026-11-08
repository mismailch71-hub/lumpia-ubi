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
        
        // Cek aman apakah tabel orders sudah ada di database
        $totalOrders  = 0;
        $totalRevenue = 0;
        $recentOrders = [];

        if (Schema::hasTable('orders')) {
            $totalOrders  = DB::table('orders')->count();
            $totalRevenue = DB::table('orders')->where('status', 'selesai')->sum('total_price') ?? 0;
            
            $recentOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select('orders.*', 'users.name as customer_name')
                ->orderBy('orders.created_at', 'desc')
                ->take(5)
                ->get();
        }

        // 2. Data Kurir Aktif
        $couriers = User::role('kurir')->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCouriers',
            'totalCustomers',
            'totalOrders',
            'totalRevenue',
            'couriers',
            'recentOrders'
        ));
    }
}