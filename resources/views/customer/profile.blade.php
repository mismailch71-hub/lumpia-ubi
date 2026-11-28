<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body" data-has-errors="{{ $errors->any() ? '1' : '0' }}">

    @php
        $currentUser = $user ?? auth()->user();
        $avatarPath = $currentUser->avatar ?? ($currentUser->foto ?? null);
        $avatarUrl = null;

        if (!empty($avatarPath)) {
            $cleanPath = ltrim((string) $avatarPath, '/');
            if (\Illuminate\Support\Str::startsWith($cleanPath, ['http://', 'https://'])) {
                $avatarUrl = $cleanPath;
            } elseif (\Illuminate\Support\Str::startsWith($cleanPath, 'uploads/')) {
                $avatarUrl = asset($cleanPath);
            } else {
                $storageClean = \Illuminate\Support\Str::startsWith($cleanPath, 'storage/')
                    ? \Illuminate\Support\Str::after($cleanPath, 'storage/')
                    : $cleanPath;
                $avatarUrl = asset('storage/' . $storageClean);
            }
        }

        $displayPhone = $currentUser->phone ?? $currentUser->no_hp ?? $currentUser->whatsapp ?? null;
        $displayAddress = $currentUser->alamat ?? $currentUser->address ?? null;
    @endphp

    <!-- HEADER ATAS KHUSUS TAMPILAN HP -->
    <div class="mobile-top-header">
        <span>Lumbi Uwuu Store</span>
    </div>

    <!-- SIDEBAR CUSTOMER -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Lumbi Uwuu Store</span>
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
    <main class="main-content" id="profil-section">
        <div class="profile-header-box">
            <div class="header-title courier-profile-title">
                <h2>Profil Saya</h2>
                <p>Informasi detail akun, poin loyalitas, dan alamat pengiriman Anda.</p>
            </div>

            <div class="header-actions-group">
                <span class="points-badge">Poin Kamu: {{ $currentUser->points ?? 0 }} Poin</span>
                <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                    Pindah Menu ke Bawah
                </button>
                <button type="button" class="btn-edit-profile" id="btnToggleEdit" onclick="toggleCustomerEditForm()">
                    Edit Profil
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success-custom courier-profile-alert">
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

        <!-- 1. TAMPILAN PROFIL BERSIH (DEFAULT) -->
        <div class="profile-card-full" id="profileViewCard">
            <div class="courier-profile-top">
                <div class="avatar-circle courier-avatar-box">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}?v={{ time() }}"
                             alt="Foto Profil {{ $currentUser->name ?? 'Customer' }}"
                             class="courier-avatar-img"
                             onerror="this.style.display='none'; if (this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <span class="courier-avatar-fallback" style="display: none;">
                            {{ strtoupper(substr($currentUser->name ?? 'C', 0, 1)) }}
                        </span>
                    @else
                        <span>{{ strtoupper(substr($currentUser->name ?? 'C', 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="profile-name courier-profile-name">{{ $currentUser->name ?? 'Customer' }}</h3>
                    <p class="profile-email courier-profile-email">{{ $currentUser->email ?? '-' }}</p>
                </div>
            </div>

            <hr class="profile-divider courier-profile-divider">

            <div class="profile-info-grid">
                <div class="info-item-box">
                    <label>Peran / Role</label>
                    <p><span class="payment-badge badge-paid">Member Lumpia Ubi</span></p>
                </div>

                <div class="info-item-box">
                    <label>No. Handphone / WhatsApp</label>
                    <p>{{ !empty($displayPhone) ? $displayPhone : 'Belum Diatur' }}</p>
                </div>

                <div class="info-item-box">
                    <label>Total Poin Loyalitas</label>
                    <p><span class="courier-status-active">{{ $currentUser->points ?? 0 }} Poin</span></p>
                </div>

                <div class="info-item-box">
                    <label>Tanggal Terdaftar</label>
                    <p>{{ isset($currentUser->created_at) ? \Carbon\Carbon::parse($currentUser->created_at)->translatedFormat('d F Y') : '-' }}</p>
                </div>

                <div class="info-item-box customer-address-info-box">
                    <label>Alamat Pengiriman Utama</label>
                    <p>{{ !empty($displayAddress) ? $displayAddress : 'Belum Diatur' }}</p>
                </div>
            </div>
        </div>

        <!-- 2. FORM EDIT PROFIL (TERSEMBUNYI SAMPAI TOMBOL EDIT PROFIL DIKLIK) -->
        <div class="section-box courier-edit-card" id="profileEditCard">
            <div class="courier-edit-header">
                <h3>Edit Data &amp; Keamanan Akun</h3>
                <button type="button" class="btn-cancel-edit" onclick="toggleCustomerEditForm()">Batal</button>
            </div>

            <form action="{{ \Illuminate\Support\Facades\Route::has('customer.profile.update') ? route('customer.profile.update') : url('/customer/profile') }}"
                  method="POST" enctype="multipart/form-data" class="form-order-catalog">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="avatar">Foto Profil (Menggantikan Inisial)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="form-control file-input-compact">
                    <small class="help-text-muted">Format gambar (JPG, JPEG, PNG, WEBP).</small>
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $currentUser->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $currentUser->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" id="phone" class="form-control"
                           value="{{ old('phone', $displayPhone ?? '') }}"
                           placeholder="Contoh: 081234567890">
                </div>

                <div class="form-group">
                    <label for="address">Alamat Pengiriman Utama</label>
                    <textarea name="address" id="address" rows="3" class="form-control"
                              placeholder="Masukkan alamat lengkap pengiriman...">{{ old('address', $displayAddress ?? '') }}</textarea>
                </div>

                <hr class="profile-divider courier-edit-divider">

                <h4 class="section-title-sub">Ganti Password (Opsional)</h4>
                <p class="section-desc-sub">Kosongkan jika tidak ingin mengubah password akun Anda.</p>

                <div class="form-group">
                    <label for="current_password">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Masukkan password lama">
                </div>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter">
                </div>

                <div class="form-group form-group-compact">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <div class="courier-edit-actions">
                    <button type="submit" class="btn-submit-small btn-online btn-profile-submit">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>