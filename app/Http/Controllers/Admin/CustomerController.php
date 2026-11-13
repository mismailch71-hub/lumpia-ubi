<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Menampilkan daftar customer dengan paginasi, pencarian No HP, dan kalkulasi total belanja.
     */
    public function index(Request $request)
    {
        $query = User::role('customer');

        // Pencarian Nama, Email, atau No HP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Paginasi 10 customer per halaman
        $customers = $query->orderBy('created_at', 'desc')->paginate(10);

        // Menghitung total order & pengeluaran belanja customer (selain status dibatalkan)
        foreach ($customers as $customer) {
            $customer->total_orders = DB::table('orders')
                ->where('user_id', $customer->id)
                ->where('status', '!=', 'dibatalkan')
                ->count();

            $customer->total_spent = DB::table('orders')
                ->where('user_id', $customer->id)
                ->where('status', '!=', 'dibatalkan')
                ->sum('total_price') ?? 0;
        }

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Menghapus akun customer
     */
    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Data customer berhasil dihapus!');
    }
}