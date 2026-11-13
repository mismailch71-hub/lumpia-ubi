<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Form Login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses Autentikasi / Login User
     */
    public function login(Request $request)
    {
        // 1. Validasi Input Form
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Cek Kredensial Login
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 3. Redirect Dinamis Berdasarkan Role Spatie
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('kurir')) {
                return redirect()->route('courier.dashboard'); // Diarahkan ke dashboard kurir
            }

            // Default untuk Role Customer
            return redirect()->route('dashboard');
        }

        // 4. Jika Gagal, Kembalikan Pesan Error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Tampilkan Halaman Form Register Customer
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses Pendaftaran User Baru (Role Customer)
     */
    public function register(Request $request)
    {
        // 1. Validasi Input Register
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Buat User Baru
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Otomatis Assign Role 'customer' Spatie
        $user->assignRole('customer');

        // 4. Otomatis Login Setelah Register Success
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang di Lumpia Ubi.');
    }

    /**
     * Proses Logout User
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}