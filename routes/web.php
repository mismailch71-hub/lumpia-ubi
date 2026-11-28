<?php

use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Courier\CourierDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderChatController;

// 1. Landing Page Publik
Route::get('/', function () {
    $products = Product::where('is_available', true)->get();
    return view('welcome', compact('products'));
});

// Route::get('/', function () {
//     return view('maintenance');
// });

// 2. Route Autentikasi Manual (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Persetujuan Login Admin via Email (di luar middleware guest)
Route::get('/admin/login/waiting', [AuthController::class, 'showAdminApprovalWaiting'])
    ->name('admin.login.waiting');
Route::get('/admin/login/status/{token}', [AuthController::class, 'checkAdminApprovalStatus'])
    ->name('admin.login.status');
Route::get('/admin/login/approve/{token}', [AuthController::class, 'approveAdminLogin'])
    ->middleware('signed')
    ->name('admin.login.approve');
Route::get('/admin/login/reject/{token}', [AuthController::class, 'rejectAdminLogin'])
    ->middleware('signed')
    ->name('admin.login.reject');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 3. Redirect / Dashboard User Berdasarkan Role
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Jika Role Admin -> Ke Dashboard Admin
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    // Jika Role Kurir -> Ke Dashboard Kurir
    if ($user->hasRole('kurir')) {
        return redirect()->route('courier.dashboard');
    }

    // Jika Role Customer -> Ke Halaman Pesanan Customer
    return redirect()->route('customer.orders.index');
})->middleware('auth')->name('dashboard');

// Group Route Khusus Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Kelola Pesanan Admin
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{id}/assign-kurir', [OrderController::class, 'assignKurir'])->name('orders.assign-kurir');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Verifikasi & Tolak Bukti Pembayaran QRIS
    Route::post('/orders/{id}/verify', [OrderController::class, 'verifyPayment'])->name('orders.verify');
    Route::post('/orders/{id}/reject', [OrderController::class, 'rejectPayment'])->name('orders.reject');

    // CRUD Produk Lumpia Ubi
    Route::resource('products', ProductController::class);

    // Menu Promo
    // Menu Promo
    Route::get('/promos', function () {
        $promo = DB::table('promos')->orderByDesc('id')->first();

        return view('admin.promos.index', compact('promo'));
    })->name('promos.index');

    Route::post('/promos', function (Request $request) {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'is_active'   => ['required', 'boolean'],
        ]);

        $existing = DB::table('promos')->orderByDesc('id')->first();

        if ($existing) {
            DB::table('promos')
                ->where('id', $existing->id)
                ->update([
                    'title'       => $data['title'],
                    'description' => $data['description'],
                    'is_active'   => (bool) $data['is_active'],
                    'updated_at'  => now(),
                ]);
        } else {
            DB::table('promos')->insert([
                'title'       => $data['title'],
                'description' => $data['description'],
                'is_active'   => (bool) $data['is_active'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->route('admin.promos.index')->with('success', 'Pengaturan promo berhasil disimpan ke database.');
    })->name('promos.store');

    // Menu Ulasan & Rating
    Route::get('/reviews', function () {
        $orders = DB::table('orders')
            ->orderByDesc('id')
            ->get();

        return view('admin.reviews.index', compact('orders'));
    })->name('reviews.index');

    // Kelola Daftar Kurir (Dinamis)
    Route::get('/couriers', [CourierController::class, 'index'])->name('couriers.index');
    Route::post('/couriers', [CourierController::class, 'store'])->name('couriers.store');
    Route::put('/couriers/{id}', function (Request $request, $id) {
        $courier = DB::table('users')->where('id', $id)->first();

        if (! $courier) {
            return redirect()->route('admin.couriers.index')->with('error', 'Data kurir tidak ditemukan.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
        ]);

        $updateData = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'updated_at' => now(),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return redirect()->route('admin.couriers.index')->with('success', 'Data kurir berhasil diperbarui.');
    })->name('couriers.update');
    Route::delete('/couriers/{id}', [CourierController::class, 'destroy'])->name('couriers.destroy');

    // Kelola Data Customer (Dinamis)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::put('/customers/{id}', function (Request $request, $id) {
        $customer = DB::table('users')->where('id', $id)->first();

        if (! $customer) {
            return redirect()->route('admin.customers.index')->with('error', 'Data customer tidak ditemukan.');
        }

        $hasPhoneCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'phone');
        $hasNoHpCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'no_hp');

        $rules = [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
        ];

        if ($hasPhoneCol || $hasNoHpCol) {
            $rules['phone'] = ['nullable', 'string', 'max:30'];
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'updated_at' => now(),
        ];

        if ($hasPhoneCol) {
            $updateData['phone'] = $validated['phone'] ?? null;
        }
        if ($hasNoHpCol) {
            $updateData['no_hp'] = $validated['phone'] ?? null;
        }
        if (! empty($validated['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return redirect()->route('admin.customers.index')->with('success', 'Data customer berhasil diperbarui.');
    })->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Laporan Penjualan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Menu Pengeluaran (Bahan Baku & Biaya Produksi)
    Route::get('/expenses', function (\Illuminate\Http\Request $request) {
        $query = \Illuminate\Support\Facades\DB::table('expenses');

        if ($request->filled('category') && in_array($request->category, ['bahan_baku', 'biaya_produksi'])) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = (clone $query)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $filteredTotal = (clone $query)->sum('amount');

        $totalBahanBaku = \Illuminate\Support\Facades\DB::table('expenses')->where('category', 'bahan_baku')->sum('amount');
        $totalBiayaProduksi = \Illuminate\Support\Facades\DB::table('expenses')->where('category', 'biaya_produksi')->sum('amount');
        $totalExpenses = $totalBahanBaku + $totalBiayaProduksi;

        return view('admin.expenses.index', compact(
            'expenses',
            'filteredTotal',
            'totalBahanBaku',
            'totalBiayaProduksi',
            'totalExpenses'
        ));
    })->name('expenses.index');

    Route::post('/expenses', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'category'     => 'required|in:bahan_baku,biaya_produksi',
            'item_name'    => 'required|string|max:150',
            'amount'       => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        \Illuminate\Support\Facades\DB::table('expenses')->insert([
            'category'     => $data['category'],
            'item_name'    => $data['item_name'],
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'notes'        => $data['notes'] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Data pengeluaran berhasil dicatat.');
    })->name('expenses.store');

    Route::delete('/expenses/{id}', function ($id) {
        \Illuminate\Support\Facades\DB::table('expenses')->where('id', $id)->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Data pengeluaran dihapus.');
    })->name('expenses.destroy');
});

// Group Route Khusus Kurir
Route::middleware(['auth', 'role:kurir'])->prefix('courier')->name('courier.')->group(function () {
    Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [CourierDashboardController::class, 'history'])->name('history');
    Route::delete('/history/{id}', [CourierDashboardController::class, 'destroyHistory'])->name('orders.destroy');
    Route::get('/profile', [CourierDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [CourierDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/orders/{id}/update-status', [CourierDashboardController::class, 'updateStatus'])->name('orders.update-status');
});

// RUTE KHUSUS CUSTOMER
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/catalog', [CustomerOrderController::class, 'catalog'])->name('catalog');
    Route::post('/orders/store', [CustomerOrderController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');

    Route::get('/profile', function () {
        return view('customer.profile', ['user' => auth()->user()]);
    })->name('profile');

    Route::put('/profile', [CustomerOrderController::class, 'updateProfile'])->name('profile.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/orders/{orderId}/chat', [OrderChatController::class, 'index'])->name('orders.chat.index');
    Route::post('/orders/{orderId}/chat', [OrderChatController::class, 'store'])->name('orders.chat.store');
});