<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Courier\CourierDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

// 1. Landing Page Publik
Route::get('/', function () {
    $products = Product::where('is_available', true)->get();
    return view('welcome', compact('products'));
});

// 2. Route Autentikasi Manual (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

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
    
    // CRUD Produk Lumpia Ubi
    Route::resource('products', ProductController::class);

    // Kelola Daftar Kurir (Dinamis)
    Route::get('/couriers', [CourierController::class, 'index'])->name('couriers.index');
    Route::post('/couriers', [CourierController::class, 'store'])->name('couriers.store');
    Route::delete('/couriers/{id}', [CourierController::class, 'destroy'])->name('couriers.destroy');

    // Kelola Data Customer (Dinamis)
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // 📈 Laporan Penjualan (BARU)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Route untuk Verifikasi & Tolak Bukti Pembayaran QRIS
    Route::post('/admin/orders/{id}/verify', [AdminOrderController::class, 'verifyPayment'])->name('admin.orders.verify');
    Route::post('/admin/orders/{id}/reject', [AdminOrderController::class, 'rejectPayment'])->name('admin.orders.reject');
});

Route::middleware(['auth', 'role:kurir'])->prefix('courier')->name('courier.')->group(function () {
    Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history', [CourierDashboardController::class, 'history'])->name('history');
    Route::get('/profile', [CourierDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [CourierDashboardController::class, 'updateProfile'])->name('profile.update'); // 👈 Rute Baru
    Route::post('/orders/{id}/update-status', [CourierDashboardController::class, 'updateStatus'])->name('orders.update-status');
});

// RUTE KHUSUS CUSTOMER
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/catalog', [CustomerOrderController::class, 'catalog'])->name('catalog');
    Route::post('/orders/store', [CustomerOrderController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
});