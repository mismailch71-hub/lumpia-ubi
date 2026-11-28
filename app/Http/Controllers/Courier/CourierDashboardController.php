<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CourierDashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard kurir (Tugas Pengiriman Aktif)
     */
    public function index()
    {
        $courierId = Auth::id();

        $myDeliveries = DB::table('orders')
            ->leftJoin('users as customers', 'orders.user_id', '=', 'customers.id')
            ->select(
                'orders.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.no_hp as customer_phone'
            )
            ->where('orders.courier_id', $courierId)
            ->where('orders.status', '!=', 'selesai')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $orderIds = $myDeliveries->pluck('id')->toArray();
        $orderItems = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('order_items.order_id', 'order_items.quantity', 'order_items.price', 'products.name as product_name')
            ->whereIn('order_items.order_id', $orderIds)
            ->get()
            ->groupBy('order_id');

        foreach ($myDeliveries as $order) {
            $order->items = $orderItems->get($order->id, collect());
        }

        $activeCount = $myDeliveries->count();
        $completedCount = DB::table('orders')
            ->where('courier_id', $courierId)
            ->where('status', 'selesai')
            ->count();

        return view('courier.dashboard', compact('myDeliveries', 'activeCount', 'completedCount'));
    }

    /**
     * Tampilkan Riwayat Pengiriman Selesai
     */
    public function history()
    {
        $courierId = Auth::id();

        $completedDeliveries = DB::table('orders')
            ->leftJoin('users as customers', 'orders.user_id', '=', 'customers.id')
            ->select(
                'orders.*',
                'customers.name as customer_name',
                'customers.no_hp as customer_phone'
            )
            ->where('orders.courier_id', $courierId)
            ->where('orders.status', 'selesai')
            ->orderBy('orders.updated_at', 'desc')
            ->get();

        $orderIds = $completedDeliveries->pluck('id')->toArray();
        $orderItems = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('order_items.order_id', 'order_items.quantity', 'order_items.price', 'products.name as product_name')
            ->whereIn('order_items.order_id', $orderIds)
            ->get()
            ->groupBy('order_id');

        foreach ($completedDeliveries as $order) {
            $order->items = $orderItems->get($order->id, collect());
        }

        $totalIncome = $completedDeliveries->sum('total_price');

        return view('courier.history', compact('completedDeliveries', 'totalIncome'));
    }

    /**
     * Tampilkan Halaman Profil Kurir
     */
    public function profile()
    {
        $user = Auth::user();
        return view('courier.profile', compact('user'));
    }

    /**
     * Update Data Profil & Password Kurir
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'no_hp'    => 'nullable|string|max:20',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $updateData = [
            'name'       => $request->name,
            'no_hp'      => $request->no_hp,
            'updated_at' => now(),
        ];

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid() && Schema::hasColumn('users', 'avatar')) {
            if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update($updateData);

        return redirect()->back()->with('success', 'Profil dan informasi akun berhasil diperbarui!');
    }

    /**
     * Update status pengiriman oleh kurir
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dalam_pengiriman,selesai',
        ]);

        $affected = DB::table('orders')
            ->where('id', $id)
            ->where('courier_id', Auth::id())
            ->update([
                'status'     => $request->status,
                'updated_at' => now(),
            ]);

        if (!$affected) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan atau Anda tidak berhak mengedit tugas ini.');
        }

        return redirect()->back()->with('success', 'Status pengiriman berhasil diperbarui!');
    }

    /**
     * Hapus riwayat pengiriman yang sudah berstatus selesai
     */
    public function destroyHistory($id)
    {
        $courierId = Auth::id();

        $order = DB::table('orders')
            ->where('id', $id)
            ->where('courier_id', $courierId)
            ->where('status', 'selesai')
            ->first();

        if (!$order) {
            return redirect()->route('courier.history')
                ->with('error', 'Riwayat pesanan tidak ditemukan atau belum berstatus selesai.');
        }

        DB::transaction(function () use ($id) {
            if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'order_id')) {
                DB::table('order_items')->where('order_id', $id)->delete();
            }

            if (Schema::hasTable('order_messages')) {
                if (Schema::hasColumn('order_messages', 'order_id')) {
                    DB::table('order_messages')->where('order_id', $id)->delete();
                } elseif (Schema::hasColumn('order_messages', 'id_order')) {
                    DB::table('order_messages')->where('id_order', $id)->delete();
                }
            }

            DB::table('orders')->where('id', $id)->delete();
        });

        return redirect()->route('courier.history')
            ->with('success', 'Riwayat pesanan selesai #ORD-' . $id . ' berhasil dihapus.');
    }
}