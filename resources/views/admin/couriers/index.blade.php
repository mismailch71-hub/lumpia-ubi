<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kurir - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI DINAMIS -->
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
                <li><a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">👥 Data Customer</a></li>
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
            <h2>🛵 Tim Kurir Pengantar</h2>
            <p>Pantau status keaktifan real-time dan kelola akun kurir Lumpia Ubi.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <div class="filter-bar">
                <h3 style="margin: 0;">Daftar Seluruh Kurir</h3>

                <div class="filter-group" style="display: flex; gap: 8px; align-items: center;">
                    <form action="{{ route('admin.couriers.index') }}" method="GET" class="flex-form">
                        <input type="text" name="search" class="input-search" placeholder="🔍 Cari nama / email..." value="{{ request('search') }}">
                        <button type="submit" class="btn-submit-small">Cari</button>
                    </form>

                    <button type="button" class="btn-submit-small" onclick="openModal('modal-tambah-kurir')" style="padding: 8px 14px; white-space: nowrap;">+ Tambah Kurir</button>
                </div>
            </div>

            <!-- TABEL KURIR PAS DI LAYAR -->
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="admin-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Inisial</th>
                            <th style="width: 22%;">Nama Kurir</th>
                            <th style="width: 25%;">Email</th>
                            <th style="width: 20%;">Status Saat Ini</th>
                            <th style="width: 15%;">Total Selesai</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($couriers as $courier)
                            @php
                                $cleanName = trim(str_replace(['Kurir ', '(Kurir 1)', '(Kurir 2)', '(Kurir 3)'], '', $courier->name));
                                $initial = strtoupper(substr($cleanName, 0, 1));
                                $isBusy = in_array($courier->id, $busyCourierIds ?? []);
                            @endphp
                            <tr>
                                <td>
                                    <div class="courier-avatar" style="width: 36px; height: 36px; font-size: 13px; font-weight: bold; background: #7e22ce; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        {{ $initial }}
                                    </div>
                                </td>
                                <td><strong>Kurir {{ $cleanName }}</strong></td>
                                <td>{{ $courier->email }}</td>
                                <td>
                                    @if($isBusy)
                                        <span class="badge-status badge-busy" style="white-space: nowrap;">🟡 Sedang Mengantar</span>
                                    @else
                                        <span class="badge-status badge-online" style="white-space: nowrap;">🟢 Ready / Siap Antar</span>
                                    @endif
                                </td>
                                <td><strong>{{ $courier->total_deliveries ?? 0 }} Pesanan</strong></td>
                                <td>
                                    <form action="{{ route('admin.couriers.destroy', $courier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun kurir ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-submit-small" style="background: #ef4444; padding: 6px 10px;">🗑️ Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">Belum ada kurir terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL TAMBAH KURIR BARU -->
    <div id="modal-tambah-kurir" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-header">
                <h3>🛵 Tambah Akun Kurir Baru</h3>
                <button class="btn-close-modal" onclick="closeModal('modal-tambah-kurir')">&times;</button>
            </div>
            <form action="{{ route('admin.couriers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Nama Kurir</label>
                        <input type="text" name="name" class="input-search" style="width: 100%;" placeholder="Contoh: Yoga" required>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Email</label>
                        <input type="email" name="email" class="input-search" style="width: 100%;" placeholder="yoga@lumpiaubi.com" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Password</label>
                        <input type="password" name="password" class="input-search" style="width: 100%;" placeholder="******" required>
                    </div>

                    <div style="text-align: right;">
                        <button type="button" class="btn-secondary" onclick="closeModal('modal-tambah-kurir')" style="padding: 8px 14px; margin-right: 6px;">Batal</button>
                        <button type="submit" class="btn-submit-small">Simpan Kurir</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>