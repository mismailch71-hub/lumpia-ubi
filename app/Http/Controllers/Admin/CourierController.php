<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CourierController extends Controller
{
    /**
     * Menampilkan daftar seluruh kurir beserta status keaktifan & statistik pengiriman
     */
    public function index(Request $request)
    {
        // Ambil semua user dengan role kurir
        $query = User::role('kurir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $couriers = $query->orderBy('created_at', 'desc')->get();

        // Cari tahu ID kurir yang sedang sibuk mengantar pesanan (status: dalam_pengiriman)
        $busyCourierIds = DB::table('orders')
            ->where('status', 'dalam_pengiriman')
            ->whereNotNull('courier_id')
            ->pluck('courier_id')
            ->toArray();

        // Hitung statistik pengiriman untuk setiap kurir
        foreach ($couriers as $courier) {
            $courier->total_deliveries = DB::table('orders')
                ->where('courier_id', $courier->id)
                ->where('status', 'selesai')
                ->count();

            $courier->active_deliveries = DB::table('orders')
                ->where('courier_id', $courier->id)
                ->where('status', 'dalam_pengiriman')
                ->count();
        }

        return view('admin.couriers.index', compact('couriers', 'busyCourierIds'));
    }

    /**
     * Menyimpan data kurir baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('kurir');

        return redirect()->back()->with('success', 'Akun kurir baru berhasil ditambahkan!');
    }

    /**
     * Menghapus akun kurir
     */
    public function destroy($id)
    {
        $courier = User::findOrFail($id);
        $courier->delete();

        return redirect()->back()->with('success', 'Akun kurir berhasil dihapus!');
    }
}