<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Promo - Admin Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand admin-brand-box">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}" 
                     onerror="if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.jpeg') }}'; } else if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu" 
                     class="admin-brand-logo">
                <span class="admin-brand-text">Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}">Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}">Kelola Produk</a></li>
                <li><a href="{{ route('admin.promos.index') }}" class="active">Promo</a></li>
                <li><a href="{{ route('admin.reviews.index') }}">Ulasan &amp; Rating</a></li>
                <li><a href="{{ route('admin.couriers.index') }}">Daftar Kurir</a></li>
                <li><a href="{{ route('admin.customers.index') }}">Data Customer</a></li>
                <li><a href="{{ \Illuminate\Support\Facades\Route::has('admin.reports.index') ? route('admin.reports.index') : route('admin.orders.index') }}">Laporan</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <main class="main-content">
        <div class="header-title">
            <h2>Pemberitahuan &amp; Pengaturan Promo</h2>
            <p>Kelola informasi promo yang tampil di halaman customer.</p>
        </div>

        @if(session('success'))
            <div class="alert-success admin-alert-spacing">
                {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <h3>Form Promo Aktif</h3>
            <form action="{{ route('admin.promos.store') }}" method="POST" id="adminPromoForm">
                @csrf
                <div class="form-group">
                    <label>Judul Promo</label>
                    <input type="text" id="adminPromoTitle" name="title" class="form-control"
                           value="{{ old('title', $promo->title ?? 'Ongkir Rp 0 Area Kampus Unram & +1 Poin Tiap 1 Produk') }}" required>
                </div>

                <div class="form-group">
                    <label>Keterangan Promo</label>
                    <textarea id="adminPromoDesc" name="description" rows="3" class="form-control" required>{{ old('description', $promo->description ?? 'Pesan Coklat, Keju, dan Mix dalam satu keranjang. Area Kampus Unram ongkir Rp 0, luar Unram Rp 1.000/km, dan tiap 1 produk dapat +1 poin.') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Status Promo</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ ($promo->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ ($promo->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="modal-footer-actions">
                    <button type="submit" class="btn-submit-small">Simpan Promo</button>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>