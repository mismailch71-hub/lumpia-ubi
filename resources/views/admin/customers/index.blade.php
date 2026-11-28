<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer - Lumbi Uwuu Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI DINAMIS -->
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
            <h2>Data Customer Terdaftar</h2>
            <p>Kelola data akun pembeli, perbarui informasi/password customer, dan pantau riwayat belanja Lumpia Ubi.</p>
        </div>

        @if(session('success'))
            <div class="alert-success" style="margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="section-box">
            <div class="customers-filter-bar">
                <h3 class="customers-filter-title">Daftar Seluruh Customer</h3>

                <form action="{{ route('admin.customers.index') }}" method="GET" class="customers-search-form">
                    <input type="text"
                           name="search"
                           class="customers-search-input"
                           placeholder="Cari nama / email / HP..."
                           value="{{ request('search') }}">
                    <button type="submit" class="btn-submit-small customers-search-btn">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.customers.index') }}" class="btn-secondary customers-reset-btn">Reset</a>
                    @endif
                </form>
            </div>

            <!-- TABEL DATA CUSTOMER LENGKAP -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 72px; text-align: center;">INISIAL</th>
                            <th>NAMA CUSTOMER</th>
                            <th>EMAIL</th>
                            <th style="text-align: center;">NO. HP / WA</th>
                            <th style="text-align: center;">TOTAL ORDER</th>
                            <th style="text-align: center;">TOTAL BELANJA</th>
                            <th style="width: 120px; text-align: center;">PESANAN</th>
                            <th style="width: 155px; text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            @php
                                $initial = strtoupper(substr($customer->name, 0, 1));
                                $phoneNum = $customer->phone ?? $customer->no_hp ?? null;
                            @endphp
                            <tr>
                                <td style="text-align: center;">
                                    <div class="courier-avatar" style="width: 36px; height: 36px; font-size: 13px; font-weight: bold; background: #6b21a8; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        {{ $initial }}
                                    </div>
                                </td>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td>{{ $customer->email }}</td>
                                <td style="text-align: center;">
                                    @if(!empty($phoneNum))
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phoneNum) }}" target="_blank" style="color: #16a34a; text-decoration: none; font-weight: 600;" title="Hubungi via WhatsApp">
                                            {{ $phoneNum }}
                                        </a>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;"><strong>{{ $customer->total_orders }} Transaksi</strong></td>
                                <td style="text-align: center;">
                                    <strong style="color: #16a34a;">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('admin.orders.index', ['search' => $customer->name]) }}"
                                       class="btn-detail-small btn-customer-orders"
                                       title="Lihat Pesanan Customer">
                                        Lihat Pesanan
                                    </a>
                                </td>
                                <td style="text-align: center;">
                                    <div class="customer-action-group">
                                        <button type="button"
                                                class="btn-customer-edit"
                                                onclick="openModal('editCustomerModal-{{ $customer->id }}')"
                                                title="Edit Data Customer">
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.customers.destroy', $customer->id) }}"
                                              method="POST"
                                              class="btn-customer-delete-form"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun customer ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn-customer-delete"
                                                    title="Hapus Customer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-row">Belum ada customer terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TAMPILAN PAGINASI DINAMIS -->
            @if(method_exists($customers, 'links') && $customers->hasPages())
                <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </main>

    <!-- MODAL EDIT DATA & PASSWORD CUSTOMER -->
    @foreach($customers as $customer)
        @php
            $phoneVal = $customer->phone ?? $customer->no_hp ?? '';
        @endphp
        <div id="editCustomerModal-{{ $customer->id }}" class="modal-overlay">
            <div class="modal-box" style="max-width: 460px;">
                <div class="modal-header">
                    <h3>Edit Customer: {{ $customer->name }}</h3>
                    <button type="button" class="btn-close-modal" onclick="closeModal('editCustomerModal-{{ $customer->id }}')" aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Nama Customer</label>
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}" required>
                        </div>

                        <div class="form-group">
                            <label>No. HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="{{ $phoneVal }}" placeholder="Contoh: 081234567890">
                        </div>

                        <div class="form-group">
                            <label>Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak ingin mengganti password">
                            <small style="color: #64748b; font-size: 11.5px;">Minimal 6 karakter bila ingin mereset password customer.</small>
                        </div>

                        <div class="modal-footer-actions" style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                            <button type="button" class="btn-cancel-edit" onclick="closeModal('editCustomerModal-{{ $customer->id }}')">Batal</button>
                            <button type="submit" class="btn-submit-small">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>