<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    @php
        $currentUser = $user ?? auth()->user();
        $avatarUrl = (!empty($currentUser->avatar) && file_exists(public_path('storage/' . $currentUser->avatar)))
            ? asset('storage/' . $currentUser->avatar)
            : asset('images/logo-lumbi.jpeg');
    @endphp

    <!-- HEADER ATAS KHUSUS TAMPILAN HP -->
    <div class="mobile-top-header">
        <span>Lumpia Ubi Store</span>
    </div>

    <!-- SIDEBAR CUSTOMER -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>Lumpia Ubi Store</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('customer.orders.index') }}">Pesanan</a></li>
                <li><a href="{{ route('customer.catalog') }}">Katalog</a></li>
                <li><a href="{{ \Illuminate\Support\Facades\Route::has('customer.profile') ? route('customer.profile') : '#profil-section' }}" class="active">Profil</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="header-flex">
            <div class="header-title">
                <h2>Edit Profil Saya</h2>
                <p>Perbarui foto profil, nomor WhatsApp verifikasi, alamat pengiriman, email, dan password.</p>
            </div>

            <div class="header-actions-group">
                <span class="points-badge">Poin Kamu: {{ $currentUser->points ?? 0 }} Poin</span>
                <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                    Pindah Menu ke Bawah
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success-custom">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger-custom">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-danger-custom">
                <ul class="order-item-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="customer-order-card" id="profil-section">
            <div class="modal-product-summary">
                <img src="{{ $avatarUrl }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; }"
                     alt="Foto Profil {{ $currentUser->name ?? 'Customer' }}"
                     class="modal-product-img">
                <div class="modal-product-detail">
                    <h4>{{ $currentUser->name ?? 'Customer' }}</h4>
                    <p>{{ $currentUser->email ?? '-' }}</p>
                </div>
            </div>

            <form action="{{ \Illuminate\Support\Facades\Route::has('customer.profile.update') ? route('customer.profile.update') : url('/customer/profile') }}"
                  method="POST" enctype="multipart/form-data" class="form-order-catalog">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Upload Foto Profil</label>
                    <input type="file" name="avatar" accept="image/*" class="form-control file-input-compact">
                    <small class="help-text-muted">Format gambar (JPG, JPEG, PNG, WEBP).</small>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $currentUser->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $currentUser->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" class="form-control"
                           value="{{ old('phone', $currentUser->phone ?? $currentUser->no_hp ?? $currentUser->whatsapp ?? '') }}"
                           placeholder="Contoh: 081234567890">
                </div>

                <div class="form-group">
                    <label>Alamat Pengiriman Utama</label>
                    <textarea name="address" rows="3" class="form-control"
                              placeholder="Masukkan alamat lengkap pengiriman...">{{ old('address', $currentUser->alamat ?? $currentUser->address ?? '') }}</textarea>
                </div>

                <div class="order-summary-box">
                    <div class="cart-picker-title">Ganti Password (Opsional — isi jika ingin mengubah password)</div>

                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Masukkan password lama">
                    </div>

                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
                    </div>

                    <div class="form-group form-group-compact">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>

                <div class="modal-footer-actions">
                    <a href="{{ route('customer.catalog') }}" class="btn-cancel-edit">Kembali ke Katalog</a>
                    <button type="submit" class="btn-submit-small btn-online">Simpan Perubahan Profil</button>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>