<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}">
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

    <!-- KONTEN UTAMA DASHBOARD -->
    <main class="main-content">
        <div class="header-title">
            <h2>Selamat Datang, Admin! 👋</h2>
            <p>Berikut ringkasan statistik dan aktivitas sistem toko Lumpia Ubi hari ini.</p>
        </div>

        <!-- 5 CARDS STATISTIK LENGKAP -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Produk</h3>
                    <p>{{ $totalProducts }}</p>
                </div>
                <div class="icon">🍠</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Kurir</h3>
                    <p>{{ $totalCouriers }}</p>
                </div>
                <div class="icon">🛵</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Customer</h3>
                    <p>{{ $totalCustomers }}</p>
                </div>
                <div class="icon">👥</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Pesanan</h3>
                    <p>{{ $totalOrders }}</p>
                </div>
                <div class="icon">📦</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Pendapatan</h3>
                    <p>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="icon">💰</div>
            </div>
        </div>

        <!-- DAFTAR KURIR AKTIF WITH STATUS & DYNAMIC AVATAR INITIAL -->
        <div class="section-box">
            <h3>🛵 Tim Kurir Pengantar Aktif</h3>
            <div class="courier-list">
                @forelse($couriers as $courier)
                    @php
                        // Logika Inisial Nama Depan Kurir (Yoga -> Y, Rafi -> R, Mail -> M)
                        $cleanName = trim(str_replace(['Kurir ', '(Kurir 1)', '(Kurir 2)', '(Kurir 3)'], '', $courier->name));
                        $initial = strtoupper(substr($cleanName, 0, 1));
                        
                        // Status Keaktifan Dinamis
                        $isBusy = in_array($courier->id, $busyCourierIds ?? []);
                    @endphp
                    <div class="courier-card">
                        <div class="courier-avatar">
                            {{ $initial }}
                        </div>
                        <div class="courier-info">
                            <h4>{{ $courier->name }}</h4>
                            <p>{{ $courier->email }}</p>
                            @if($isBusy)
                                <span class="badge-status badge-busy">🟡 Sedang Mengantar</span>
                            @else
                                <span class="badge-status badge-online">🟢 Ready / Siap Antar</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="color: #64748b; font-size: 13px;">Belum ada kurir terdaftar.</p>
                @endforelse
            </div>
        </div>

        <!-- TABEL PESANAN TERBARU (RECENT ORDERS) -->
        <div class="section-box">
            <h3>📦 Pesanan Terbaru (Perlu Penugasan Kurir)</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total Pembayaran</th>
                            <th>Kurir Pengantar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td><strong>#ORD-{{ $order->id }}</strong></td>
                                <td>{{ $order->customer_name }}</td>
                                <td>
                                    @if($order->status == 'pending')
                                        <span class="badge-order badge-pending">Menunggu Konfirmasi</span>
                                    @elseif($order->status == 'dalam_pengiriman' || $order->status == 'diproses')
                                        <span class="badge-order badge-process">Sedang Diproses</span>
                                    @elseif($order->status == 'selesai')
                                        <span class="badge-order badge-success">Selesai</span>
                                    @else
                                        <span class="badge-order" style="background: #f1f5f9; color: #475569;">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td>
                                    @if(!empty($order->courier_name))
                                        <strong>🛵 {{ $order->courier_name }}</strong>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.index') }}" class="btn-action-small">Assign Kurir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">
                                    Belum ada pesanan masuk saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>