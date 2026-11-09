<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CourierController;
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

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('kurir')) {
        return view('kurir.dashboard');
    }

    return view('dashboard');
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

});