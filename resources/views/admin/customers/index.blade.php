<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer - Lumpia Ubi Admin</title>
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
                <li><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">📈 Laporan</a></li>
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
            <h2>👥 Data Customer Terdaftar</h2>
            <p>Kelola dan pantau riwayat belanja pembeli Lumpia Ubi.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <div class="filter-bar">
                <h3 style="margin: 0;">Daftar Seluruh Customer</h3>

                <div class="filter-group">
                    <form action="{{ route('admin.customers.index') }}" method="GET" class="flex-form">
                        <input type="text" name="search" class="input-search" placeholder="🔍 Cari nama / email / HP..." value="{{ request('search') }}">
                        <button type="submit" class="btn-submit-small">Cari</button>
                    </form>
                </div>
            </div>

            <!-- TABEL DATA CUSTOMER LENGKAP -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">INISIAL</th>
                            <th>NAMA CUSTOMER</th>
                            <th>EMAIL</th>
                            <th style="text-align: center;">NO. HP / WA</th>
                            <th style="text-align: center;">TOTAL ORDER</th>
                            <th style="text-align: center;">TOTAL BELANJA</th>
                            <th style="width: 140px; text-align: center;">AKSI</th>
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
                                    <div class="courier-avatar" style="width: 36px; height: 36px; font-size: 13px; font-weight: bold; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        {{ $initial }}
                                    </div>
                                </td>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td>{{ $customer->email }}</td>
                                <td style="text-align: center;">
                                    @if(!empty($phoneNum))
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phoneNum) }}" target="_blank" style="color: #10b981; text-decoration: none; font-weight: 600;" title="Hubungi via WhatsApp">
                                            💬 {{ $phoneNum }}
                                        </a>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;"><strong>{{ $customer->total_orders }} Transaksi</strong></td>
                                <td style="text-align: center;">
                                    <strong style="color: #10b981;">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    <div class="flex-form" style="justify-content: center; gap: 4px;">
                                        <a href="{{ route('admin.orders.index', ['search' => $customer->name]) }}" class="btn-detail-small" style="text-decoration: none; padding: 6px 8px;" title="Lihat Pesanan">👁️ Pesanan</a>
                                        
                                        <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun customer ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-submit-small" style="background: #ef4444; padding: 6px 10px;" title="Hapus Customer">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-row">Belum ada customer terdaftar.</td>
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

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>