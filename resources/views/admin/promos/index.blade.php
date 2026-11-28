<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Promo Lumbi Uwuu Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand admin-brand-box">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Admin Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Kelola Produk</a></li>
                <li><a href="{{ route('admin.expenses.index') }}" class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">Pengeluaran</a></li>
                <li><a href="{{ route('admin.promos.index') }}" class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">Promo</a></li>
                <li><a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Ulasan &amp; Rating</a></li>
                <li><a href="{{ route('admin.couriers.index') }}" class="{{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}">Daftar Kurir</a></li>
                <li><a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">Data Customer</a></li>
                <li><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan</a></li>
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
            <p>Kelola informasi promo yang tampil di halaman customer serta poin loyalitas (1 poin = Rp 1 setelah pembayaran lunas).</p>
        </div>

        @if(session('success'))
            <div class="alert-success admin-alert-spacing">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-alert-error admin-alert-spacing">
                <ul class="admin-error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="section-box">
            <h3>Form Promo Aktif</h3>
            <form action="{{ route('admin.promos.store') }}" method="POST" id="adminPromoForm">
                @csrf
                <div class="form-group">
                    <label for="adminPromoTitle">Judul Promo</label>
                    <input type="text"
                           id="adminPromoTitle"
                           name="title"
                           class="form-control"
                           value="{{ old('title', $promo->title ?? 'Ongkir Rp 0 Area Kampus Unram & Poin Loyalitas (1 Poin = Rp 1)') }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="adminPromoDesc">Keterangan Promo</label>
                    <textarea id="adminPromoDesc"
                              name="description"
                              rows="3"
                              class="form-control"
                              required>{{ old('description', $promo->description ?? 'Pesan Coklat, Keju, dan Mix dalam satu keranjang dengan ongkir area Kampus Unram Rp 0 (luar Unram Rp 1.000/km). Poin didapat setelah pembayaran lunas dengan 1 produk sama dengan 1 poin (1 poin = Rp 1) dan dapat digunakan sebagai potongan saat checkout.') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="adminPromoStatus">Status Promo</label>
                    <select id="adminPromoStatus" name="is_active" class="form-control">
                        <option value="1" {{ (int) old('is_active', $promo->is_active ?? 1) === 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ (int) old('is_active', $promo->is_active ?? 1) === 0 ? 'selected' : '' }}>Nonaktif</option>
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