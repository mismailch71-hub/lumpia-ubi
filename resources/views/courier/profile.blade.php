<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Kurir Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR KURIR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🛵</span> Kurir Lumpia Ubi
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('courier.dashboard') }}">📦 Tugas Pengiriman</a></li>
                <li><a href="{{ route('courier.history') }}">📜 Riwayat Pengiriman</a></li>
                <li><a href="{{ route('courier.profile') }}" class="active">👤 Profil Saya</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="profile-header-box">
            <div class="header-title" style="margin: 0;">
                <h2>👤 Profil Saya</h2>
                <p>Informasi detail akun kurir Anda.</p>
            </div>
            
            <!-- TOMBOL EDIT DI POJOK KANAN ATAS -->
            <button type="button" class="btn-edit-profile" id="btnToggleEdit" onclick="toggleEditForm()">
                ✏️ Edit Profil
            </button>
        </div>

        @if(session('success'))
            <div class="alert-success" style="margin-bottom: 20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-danger-custom">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        <!-- 1. TAMPILAN PROFIL BERSIH (DEFAULT) -->
        <div class="profile-card-full" id="profileViewCard">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="avatar-circle" style="margin: 0;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="profile-name" style="font-size: 22px;">{{ $user->name }}</h3>
                    <p class="profile-email" style="font-size: 15px;">{{ $user->email }}</p>
                </div>
            </div>

            <hr class="profile-divider" style="margin: 24px 0;">

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
                    <p><span style="color: #059669; font-weight: bold;">🟢 Aktif Bertugas</span></p>
                </div>

                <div class="info-item-box">
                    <label>Tanggal Terdaftar</label>
                    <p>{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. FORM EDIT PROFIL (TERSEMBUNYI KECUALI TOMBOL DIKLIK) -->
        <div class="section-box" id="profileEditCard" style="display: none; margin-top: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>✏️ Edit Data & Keamanan Akun</h3>
                <button type="button" class="btn-cancel-edit" onclick="toggleEditForm()">❌ Batal</button>
            </div>
            
            <form action="{{ route('courier.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

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

                <hr class="profile-divider" style="margin: 20px 0;">

                <h4 class="section-title-sub">🔒 Ganti Password (Opsional)</h4>
                <p class="section-desc-sub">Kosongkan jika tidak ingin mengubah password akun Anda.</p>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn-submit-small btn-online btn-profile-submit">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- SCRIPT TOGGLE EDIT FORM -->
    <script>
        function toggleEditForm() {
            var viewCard = document.getElementById('profileViewCard');
            var editCard = document.getElementById('profileEditCard');
            var btnToggle = document.getElementById('btnToggleEdit');

            if (editCard.style.display === 'none') {
                editCard.style.display = 'block';
                viewCard.style.display = 'none';
                btnToggle.style.display = 'none';
            } else {
                editCard.style.display = 'none';
                viewCard.style.display = 'block';
                btnToggle.style.display = 'inline-flex';
            }
        }

        // Auto buka form edit jika ada error validasi saat submit
        @if($errors->any())
            toggleEditForm();
        @endif
    </script>
</body>
</html>