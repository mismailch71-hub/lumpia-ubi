<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🍠</span> Lumpia Ubi Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">📦 Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">🍠 Kelola Produk</a></li>
                <li><a href="{{ route('admin.couriers.index') }}" class="{{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}">🛵 Daftar Kurir</a></li>
                <li><a href="#" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">👥 Data Customer</a></li>
                <li><a href="#" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">📈 Laporan</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="header-title">
            <h2>🍠 Kelola Produk Lumpia Ubi</h2>
            <p>Tambah, edit, atau hapus menu varian Lumpia Ubi yang dijual.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <div class="filter-bar">
                <h3 style="margin: 0;">Daftar Menu Produk</h3>
                <a href="{{ route('admin.products.create') }}" class="btn-submit-small" style="text-decoration: none; padding: 8px 14px;">+ Tambah Produk Baru</a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Gambar</th>
                            <th style="width: 33%;">Nama Produk</th>
                            <th style="width: 20%;">Harga</th>
                            <th style="width: 15%;">Stok</th>
                            <th style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    @if($product->image && file_exists(public_path('storage/' . $product->image)))
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" width="45" height="45" style="object-fit: cover; border-radius: 8px;">
                                    @else
                                        <div style="width: 45px; height: 45px; background: #f3e8ff; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 20px;">
                                            🍠
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td><strong>Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</strong></td>
                                <td>{{ $product->stock ?? 0 }} Porsi</td>
                                <td>
                                    <div class="flex-form">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-detail-small" style="text-decoration: none;">✏️ Edit</a>
                                        
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-submit-small" style="background: #ef4444;">🗑️ Hapus</button>
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

</body>
</html>