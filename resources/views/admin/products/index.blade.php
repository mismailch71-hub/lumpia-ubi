<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Lumbi Uwuu Admin</title>
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

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="header-title">
            <h2>Kelola Produk Lumpia Ubi</h2>
            <p>Tambah, edit, upload gambar, atau hapus menu varian Lumpia Ubi yang dijual.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-alert-error">
                <ul class="admin-error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="section-box">
            <div class="products-filter-bar">
                <h3 class="products-filter-title">Daftar Menu Produk</h3>
                <a href="{{ route('admin.products.create') }}" class="btn-add-product-clean">Tambah Produk Baru</a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="col-product-img">Gambar</th>
                            <th class="col-product-name">Nama Produk</th>
                            <th class="col-product-price">Harga</th>
                            <th class="col-product-stock">Stok</th>
                            <th class="col-product-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $hasStorageImage = !empty($product->image) && file_exists(public_path('storage/' . $product->image));
                                $imgSrc = $hasStorageImage
                                    ? asset('storage/' . $product->image)
                                    : asset('images/lumpia-default.jpg');
                                $displayPrice = $product->price_medium ?? $product->price ?? 0;
                                $displayStock = $product->stock ?? 25;
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ $imgSrc }}"
                                         alt="{{ $product->name }}"
                                         onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'"
                                         class="product-thumb-img">
                                    <button type="button"
                                            class="btn-detail-small btn-change-image"
                                            onclick="openImageModal('imgModal-{{ $product->id }}')">
                                        Ganti Gambar
                                    </button>
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if(!empty($product->price_large))
                                        <div class="product-variant-subtext">
                                            Medium: Rp {{ number_format($product->price_medium ?? 0, 0, ',', '.') }} | Large: Rp {{ number_format($product->price_large, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td><strong>Rp {{ number_format($displayPrice, 0, ',', '.') }}</strong></td>
                                <td>{{ $displayStock }} Porsi</td>
                                <td>
                                    <div class="product-action-group">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-edit-menu">
                                            Edit Menu
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}"
                                              method="POST"
                                              class="btn-delete-menu-form"
                                              onsubmit="return confirm('Hapus menu {{ addslashes($product->name) }} secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete-menu">
                                                Hapus Produk
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-row">Belum ada data produk menu Lumpia Ubi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL UPLOAD / GANTI GAMBAR PRODUK -->
    @foreach($products as $product)
        @php
            $hasStorageImage = !empty($product->image) && file_exists(public_path('storage/' . $product->image));
            $currentImg = $hasStorageImage
                ? asset('storage/' . $product->image)
                : asset('images/lumpia-default.jpg');
        @endphp
        <div id="imgModal-{{ $product->id }}" class="modal-overlay">
            <div class="modal-box product-image-modal-box">
                <div class="modal-header">
                    <h3>Gambar Produk: {{ $product->name }}</h3>
                    <button type="button" class="btn-close-modal" onclick="closeImageModal('imgModal-{{ $product->id }}')" aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="product-image-preview-wrap">
                        <img src="{{ $currentImg }}"
                             alt="{{ $product->name }}"
                             onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'"
                             class="product-image-preview">
                    </div>

                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $product->name }}">
                        @if(isset($product->price_medium))
                            <input type="hidden" name="price_medium" value="{{ $product->price_medium }}">
                        @endif
                        @if(isset($product->price_large))
                            <input type="hidden" name="price_large" value="{{ $product->price_large }}">
                        @endif
                        @if(isset($product->price))
                            <input type="hidden" name="price" value="{{ $product->price }}">
                        @endif
                        @if(isset($product->stock))
                            <input type="hidden" name="stock" value="{{ $product->stock }}">
                        @endif
                        @if(isset($product->description))
                            <input type="hidden" name="description" value="{{ $product->description }}">
                        @endif

                        <div class="form-group">
                            <label>Pilih File Gambar Baru</label>
                            <input type="file" name="image" accept="image/*" class="form-control product-file-input" required>
                            <small class="product-file-hint">Format JPG, PNG, WEBP, atau JFIF.</small>
                        </div>

                        <div class="product-modal-actions">
                            <button type="button" class="btn-cancel-edit" onclick="closeImageModal('imgModal-{{ $product->id }}')">Batal</button>
                            <button type="submit" class="btn-submit-small">Simpan Gambar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>