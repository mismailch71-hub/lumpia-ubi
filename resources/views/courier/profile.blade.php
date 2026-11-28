<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Kurir Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body" data-has-errors="{{ $errors->any() ? '1' : '0' }}">

    @php
        $avatarPath = $user->avatar ?? ($user->foto ?? null);
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
    @endphp

    <!-- SIDEBAR KURIR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Kurir Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('courier.dashboard') }}">Tugas Pengiriman</a></li>
                <li><a href="{{ route('courier.history') }}">Riwayat Pengiriman</a></li>
                <li><a href="{{ route('courier.profile') }}" class="active">Profil</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="profile-header-box">
            <div class="header-title courier-profile-title">
                <h2>Profil Saya</h2>
                <p>Informasi detail akun kurir Anda.</p>
            </div>

            <!-- TOMBOL EDIT DI POJOK KANAN ATAS -->
            <button type="button" class="btn-edit-profile" id="btnToggleEdit" onclick="toggleEditForm()">
                Edit Profil
            </button>
        </div>

        @if(session('success'))
            <div class="alert-success courier-profile-alert">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-danger-custom">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- 1. TAMPILAN PROFIL BERSIH (DEFAULT) -->
        <div class="profile-card-full" id="profileViewCard">
            <div class="courier-profile-top">
                <div class="avatar-circle courier-avatar-box">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}?v={{ time() }}"
                             alt="Foto Profil {{ $user->name }}"
                             class="courier-avatar-img"
                             onerror="this.style.display='none'; if (this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <span class="courier-avatar-fallback" style="display: none;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="profile-name courier-profile-name">{{ $user->name }}</h3>
                    <p class="profile-email courier-profile-email">{{ $user->email }}</p>
                </div>
            </div>

            <hr class="profile-divider courier-profile-divider">

            <div class="profile-info-grid">
                <div class="info-item-box">
                    <label>Peran / Role</label>
                    <p><span class="payment-badge badge-paid">Kurir Resmi Lumpia Ubi</span></p>
                </div>

                <div class="info-item-box">
                    <label>No. Handphone / WhatsApp</label>
                    <p>{{ $user->no_hp ?? 'Belum Diatur' }}</p>
                </div>

                <div class="info-item-box">
                    <label>Status Akun</label>
                    <p><span class="courier-status-active">Aktif Bertugas</span></p>
                </div>

                <div class="info-item-box">
                    <label>Tanggal Terdaftar</label>
                    <p>{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. FORM EDIT PROFIL (TERSEMBUNYI KECUALI TOMBOL DIKLIK) -->
        <div class="section-box courier-edit-card" id="profileEditCard">
            <div class="courier-edit-header">
                <h3>Edit Data &amp; Keamanan Akun</h3>
                <button type="button" class="btn-cancel-edit" onclick="toggleEditForm()">Batal</button>
            </div>

            <form action="{{ route('courier.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="avatar">Foto Profil (Menggantikan Inisial)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="form-control file-input-compact">
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email (Tidak Dapat Diubah)</label>
                    <input type="email" id="email" class="form-control form-control-disabled" value="{{ $user->email }}" disabled>
                </div>

                <div class="form-group">
                    <label for="no_hp">No. Handphone / WhatsApp</label>
                    <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 081234567890">
                </div>

                <hr class="profile-divider courier-edit-divider">

                <h4 class="section-title-sub">Ganti Password (Opsional)</h4>
                <p class="section-desc-sub">Kosongkan jika tidak ingin mengubah password akun Anda.</p>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <div class="courier-edit-actions">
                    <button type="submit" class="btn-submit-small btn-online btn-profile-submit">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/courier-script.js') }}?v={{ time() }}"></script>
</body>
</html>