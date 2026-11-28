<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kurir - Lumbi Uwuu Store</title>
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
            <h2>Tim Kurir Pengantar</h2>
            <p>Pantau status keaktifan real-time dan kelola akun kurir Lumbi Uwuu.</p>
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
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="section-box">
            <div class="courier-filter-bar">
                <h3 class="courier-filter-title">Daftar Seluruh Kurir</h3>

                <div class="courier-toolbar-actions">
                    <form action="{{ route('admin.couriers.index') }}" method="GET" class="courier-search-form">
                        <input type="text"
                                name="search"
                                class="courier-search-input"
                                placeholder="Cari nama atau email kurir..."
                                value="{{ request('search') }}">
                        <button type="submit" class="btn-submit-small courier-search-btn">Cari</button>
                        @if(request('search'))
                            <a href="{{ route('admin.couriers.index') }}" class="btn-secondary courier-reset-btn">Reset</a>
                        @endif
                    </form>

                    <button type="button"
                            class="btn-submit-small courier-add-btn"
                            onclick="openModal('modal-tambah-kurir')">
                        + Tambah Kurir
                    </button>
                </div>
            </div>

            <!-- TABEL KURIR PAS DI LAYAR -->
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="admin-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Inisial</th>
                            <th style="width: 22%;">Nama Kurir</th>
                            <th style="width: 24%;">Email</th>
                            <th style="width: 18%;">Status Saat Ini</th>
                            <th style="width: 12%;">Total Selesai</th>
                            <th style="width: 16%; text-align: center;">Aksi</th>
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
                                        <span class="badge-status badge-busy" style="white-space: nowrap;">Sedang Mengantar</span>
                                    @else
                                        <span class="badge-status badge-online" style="white-space: nowrap;">Ready / Siap Antar</span>
                                    @endif
                                </td>
                                <td><strong>{{ $courier->total_deliveries ?? 0 }} Pesanan</strong></td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: nowrap;">
                                        <button type="button"
                                                onclick="openModal('editCourierModal-{{ $courier->id }}')"
                                                style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1; cursor: pointer; white-space: nowrap;">
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.couriers.destroy', $courier->id) }}"
                                              method="POST"
                                              style="margin: 0;"
                                              onsubmit="return confirm('Hapus akun Kurir {{ addslashes($cleanName) }} secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #dc2626; color: #ffffff; border: 1px solid #b91c1c; cursor: pointer; white-space: nowrap;">
                                                Hapus Kurir
                                            </button>
                                        </form>
                                    </div>
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
        <div class="modal-box" style="max-width: 440px;">
            <div class="modal-header">
                <h3>Tambah Akun Kurir Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('modal-tambah-kurir')" aria-label="Tutup">&times;</button>
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
                        <input type="password" name="password" class="input-search" style="width: 100%;" placeholder="Minimal 6 karakter" required>
                    </div>

                    <div style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="btn-cancel-edit" onclick="closeModal('modal-tambah-kurir')">Batal</button>
                        <button type="submit" class="btn-submit-small">Simpan Kurir</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT DATA & PASSWORD KURIR -->
    @foreach($couriers as $courier)
        <div id="editCourierModal-{{ $courier->id }}" class="modal-overlay" style="display: none;">
            <div class="modal-box" style="max-width: 440px;">
                <div class="modal-header">
                    <h3>Edit Kurir: {{ $courier->name }}</h3>
                    <button type="button" class="btn-close-modal" onclick="closeModal('editCourierModal-{{ $courier->id }}')" aria-label="Tutup">&times;</button>
                </div>
                <form action="{{ route('admin.couriers.update', $courier->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Nama Kurir</label>
                            <input type="text" name="name" class="input-search" style="width: 100%;" value="{{ $courier->name }}" required>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Email</label>
                            <input type="email" name="email" class="input-search" style="width: 100%;" value="{{ $courier->email }}" required>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="input-search" style="width: 100%;" minlength="6" placeholder="Kosongkan jika tidak ingin mengganti password">
                            <small style="color: #64748b; font-size: 11px;">Minimal 6 karakter bila ingin mereset password kurir.</small>
                        </div>

                        <div style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                            <button type="button" class="btn-cancel-edit" onclick="closeModal('editCourierModal-{{ $courier->id }}')">Batal</button>
                            <button type="submit" class="btn-submit-small">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>