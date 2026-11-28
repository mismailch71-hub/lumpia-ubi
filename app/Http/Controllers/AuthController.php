<?php

namespace App\Http\Controllers;

use App\Mail\AdminLoginApprovalMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Alamat email tujuan persetujuan login admin
     */
    private function ownerApprovalEmail(User $adminUser): string
    {
        return config('mail.admin_approval_to')
            ?: env('ADMIN_APPROVAL_EMAIL', $adminUser->email);
    }

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
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $normalizedEmail = strtolower(trim($credentials['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        // Khusus role admin: kirim link persetujuan ke email terlebih dahulu
        if ($user->hasRole('admin')) {
            $token = Str::random(64);
            $expiresAt = now()->addMinutes(10);

            DB::table('admin_login_approvals')->insert([
                'token'      => $token,
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'remember'   => $request->boolean('remember'),
                'status'     => 'pending',
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $approveUrl = URL::temporarySignedRoute(
                'admin.login.approve',
                $expiresAt,
                ['token' => $token]
            );

            $rejectUrl = URL::temporarySignedRoute(
                'admin.login.reject',
                $expiresAt,
                ['token' => $token]
            );

            Mail::to($this->ownerApprovalEmail($user))->send(
                new AdminLoginApprovalMail(
                    adminUser: $user,
                    approveUrl: $approveUrl,
                    rejectUrl: $rejectUrl,
                    ipAddress: (string) $request->ip(),
                    userAgent: (string) $request->userAgent(),
                    expiresAtText: $expiresAt->format('d M Y H:i:s')
                )
            );

            $request->session()->put('pending_admin_approval_token', $token);

            return redirect()->route('admin.login.waiting');
        }

        // Non-admin langsung login seperti biasa
        if (Auth::attempt([
            'email'    => $user->email,
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $loggedInUser = Auth::user();
            if ($loggedInUser->hasRole('kurir')) {
                return redirect()->route('courier.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Halaman Menunggu Persetujuan di Email
     */
    public function showAdminApprovalWaiting(Request $request)
    {
        $token = $request->session()->get('pending_admin_approval_token');
        if (!$token) {
            return redirect()->route('login');
        }

        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();
        if (!$approval) {
            return redirect()->route('login');
        }

        return view('auth.admin-approval-waiting', [
            'token'     => $token,
            'expiresAt' => $approval->expires_at,
        ]);
    }

    /**
     * Endpoint Polling Status Persetujuan & Login Otomatis
     */
    public function checkAdminApprovalStatus(Request $request, string $token)
    {
        $sessionToken = $request->session()->get('pending_admin_approval_token');
        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            return response()->json(['status' => 'invalid'], 403);
        }

        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();
        if (!$approval) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($approval->status === 'pending' && now()->greaterThan($approval->expires_at)) {
            DB::table('admin_login_approvals')
                ->where('id', $approval->id)
                ->update(['status' => 'expired', 'updated_at' => now()]);
            $approval->status = 'expired';
        }

        if ($approval->status === 'approved') {
            $adminUser = User::find($approval->user_id);
            if ($adminUser) {
                Auth::login($adminUser, (bool) $approval->remember);
                $request->session()->regenerate();
                $request->session()->forget('pending_admin_approval_token');

                // Hapus / tandai token sudah dipakai
                DB::table('admin_login_approvals')->where('id', $approval->id)->delete();

                return response()->json([
                    'status'       => 'approved',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }
        }

        return response()->json([
            'status' => $approval->status,
        ]);
    }

    /**
     * Klik Tombol Setujui di Email
     */
    public function approveAdminLogin(Request $request, string $token)
    {
        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();

        if (!$approval || now()->greaterThan($approval->expires_at)) {
            return response('Tautan persetujuan sudah tidak berlaku atau kedaluwarsa.', 410);
        }

        if ($approval->status !== 'pending') {
            return response('Permintaan login ini sudah diproses sebelumnya.', 200);
        }

        DB::table('admin_login_approvals')
            ->where('id', $approval->id)
            ->update([
                'status'     => 'approved',
                'updated_at' => now(),
            ]);

        return response(
            '<h2>Akses Login Admin Disetujui</h2><p>Anda dapat kembali ke halaman login di browser; sesi admin akan terbuka otomatis.</p>',
            200
        );
    }

    /**
     * Klik Tombol Tolak di Email
     */
    public function rejectAdminLogin(Request $request, string $token)
    {
        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();

        if (!$approval) {
            return response('Permintaan login tidak ditemukan.', 404);
        }

        DB::table('admin_login_approvals')
            ->where('id', $approval->id)
            ->update([
                'status'     => 'rejected',
                'updated_at' => now(),
            ]);

        return response(
            '<h2>Akses Login Admin Ditolak</h2><p>Upaya login telah diblokir.</p>',
            200
        );
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
        $request->merge([
            'name'  => trim((string) $request->input('name')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email tersebut sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('customer');

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
}<?php

namespace App\Http\Controllers;

use App\Mail\AdminLoginApprovalMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Alamat email tujuan persetujuan login admin
     */
    private function ownerApprovalEmail(User $adminUser): string
    {
        return config('mail.admin_approval_to')
            ?: env('ADMIN_APPROVAL_EMAIL', $adminUser->email);
    }

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
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $normalizedEmail = strtolower(trim($credentials['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        // Khusus role admin: kirim link persetujuan ke email terlebih dahulu
        if ($user->hasRole('admin')) {
            $token = Str::random(64);
            $expiresAt = now()->addMinutes(10);

            DB::table('admin_login_approvals')->insert([
                'token'      => $token,
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'remember'   => $request->boolean('remember'),
                'status'     => 'pending',
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $approveUrl = URL::temporarySignedRoute(
                'admin.login.approve',
                $expiresAt,
                ['token' => $token]
            );

            $rejectUrl = URL::temporarySignedRoute(
                'admin.login.reject',
                $expiresAt,
                ['token' => $token]
            );

            Mail::to($this->ownerApprovalEmail($user))->send(
                new AdminLoginApprovalMail(
                    adminUser: $user,
                    approveUrl: $approveUrl,
                    rejectUrl: $rejectUrl,
                    ipAddress: (string) $request->ip(),
                    userAgent: (string) $request->userAgent(),
                    expiresAtText: $expiresAt->format('d M Y H:i:s')
                )
            );

            $request->session()->put('pending_admin_approval_token', $token);

            return redirect()->route('admin.login.waiting');
        }

        // Non-admin langsung login seperti biasa
        if (Auth::attempt([
            'email'    => $user->email,
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $loggedInUser = Auth::user();
            if ($loggedInUser->hasRole('kurir')) {
                return redirect()->route('courier.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Halaman Menunggu Persetujuan di Email
     */
    public function showAdminApprovalWaiting(Request $request)
    {
        $token = $request->session()->get('pending_admin_approval_token');
        if (!$token) {
            return redirect()->route('login');
        }

        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();
        if (!$approval) {
            return redirect()->route('login');
        }

        return view('auth.admin-approval-waiting', [
            'token'     => $token,
            'expiresAt' => $approval->expires_at,
        ]);
    }

    /**
     * Endpoint Polling Status Persetujuan & Login Otomatis
     */
    public function checkAdminApprovalStatus(Request $request, string $token)
    {
        $sessionToken = $request->session()->get('pending_admin_approval_token');
        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            return response()->json(['status' => 'invalid'], 403);
        }

        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();
        if (!$approval) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($approval->status === 'pending' && now()->greaterThan($approval->expires_at)) {
            DB::table('admin_login_approvals')
                ->where('id', $approval->id)
                ->update(['status' => 'expired', 'updated_at' => now()]);
            $approval->status = 'expired';
        }

        if ($approval->status === 'approved') {
            $adminUser = User::find($approval->user_id);
            if ($adminUser) {
                Auth::login($adminUser, (bool) $approval->remember);
                $request->session()->regenerate();
                $request->session()->forget('pending_admin_approval_token');

                // Hapus / tandai token sudah dipakai
                DB::table('admin_login_approvals')->where('id', $approval->id)->delete();

                return response()->json([
                    'status'       => 'approved',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }
        }

        return response()->json([
            'status' => $approval->status,
        ]);
    }

    /**
     * Klik Tombol Setujui di Email
     */
    public function approveAdminLogin(Request $request, string $token)
    {
        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();

        if (!$approval || now()->greaterThan($approval->expires_at)) {
            return response('Tautan persetujuan sudah tidak berlaku atau kedaluwarsa.', 410);
        }

        if ($approval->status !== 'pending') {
            return response('Permintaan login ini sudah diproses sebelumnya.', 200);
        }

        DB::table('admin_login_approvals')
            ->where('id', $approval->id)
            ->update([
                'status'     => 'approved',
                'updated_at' => now(),
            ]);

        return response(
            '<h2>Akses Login Admin Disetujui</h2><p>Anda dapat kembali ke halaman login di browser; sesi admin akan terbuka otomatis.</p>',
            200
        );
    }

    /**
     * Klik Tombol Tolak di Email
     */
    public function rejectAdminLogin(Request $request, string $token)
    {
        $approval = DB::table('admin_login_approvals')->where('token', $token)->first();

        if (!$approval) {
            return response('Permintaan login tidak ditemukan.', 404);
        }

        DB::table('admin_login_approvals')
            ->where('id', $approval->id)
            ->update([
                'status'     => 'rejected',
                'updated_at' => now(),
            ]);

        return response(
            '<h2>Akses Login Admin Ditolak</h2><p>Upaya login telah diblokir.</p>',
            200
        );
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
        $request->merge([
            'name'  => trim((string) $request->input('name')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email tersebut sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('customer');

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