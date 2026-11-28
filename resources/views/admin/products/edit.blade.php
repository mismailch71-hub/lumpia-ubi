<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Lumbi Uwuu Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
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
            <h2>Edit Produk: {{ $product->name }}</h2>
            <p>Perbarui informasi nama, deskripsi, harga, stok, atau foto produk.</p>
        </div>

        @if($errors->any())
            <div class="admin-alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $hasStorageImage = !empty($product->image) && file_exists(public_path('storage/' . $product->image));
            $currentImg = $hasStorageImage
                ? asset('storage/' . $product->image)
                : asset('images/lumpia-default.jpg');
        @endphp

        <div class="section-box" style="max-width: 680px;">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi Produk (Opsional)</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                    <div class="form-group">
                        <label>Harga Medium</label>
                        <input type="number" name="price_medium" class="form-control" min="0" step="1" value="{{ old('price_medium', $product->price_medium ?? $product->price ?? 10000) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Large</label>
                        <input type="number" name="price_large" class="form-control" min="0" step="1" value="{{ old('price_large', $product->price_large ?? 15000) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; align-items: start;">
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stock" class="form-control" min="0" step="1" value="{{ old('stock', $product->stock ?? 25) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Ganti Foto Produk (Opsional)</label>
                        <input type="file" name="image" accept="image/*" class="form-control" style="padding: 6px;">
                        <div style="margin-top: 8px;">
                            <img src="{{ $currentImg }}"
                                 alt="{{ $product->name }}"
                                 onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'"
                                 style="width: 84px; height: 84px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 16px; display: flex; gap: 10px; align-items: center;">
                    <button type="submit" class="btn-submit-small" style="padding: 9px 18px;">Simpan Perubahan</button>
                    <a href="{{ route('admin.products.index') }}" class="btn-secondary" style="padding: 9px 16px; text-decoration: none; font-size: 13px;">Kembali</a>
                </div>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>