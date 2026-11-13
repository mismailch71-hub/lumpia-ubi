<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .sidebar, .filter-bar, .btn-print, .btn-logout { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
            .admin-body { background: white !important; }
        }
    </style>
</head>
<body class="admin-body">

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

    <main class="main-content">
        <div class="header-title">
            <h2>📈 Laporan Penjualan & Keuangan</h2>
            <p>Ringkasan transaksi dan omset toko Lumpia Ubi.</p>
        </div>

        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 24px;">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Pendapatan (Omset)</h3>
                    <p style="color: #10b981;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="icon">💰</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Transaksi Aktif / Selesai</h3>
                    <p>{{ $completedOrdersCount }} Pesanan</p>
                </div>
                <div class="icon">✅</div>
            </div>
        </div>

        <div class="section-box" style="margin-bottom: 24px;">
            <h3 style="margin-bottom: 16px;">📊 Grafik Tren Pendapatan Bulanan ({{ date('Y') }})</h3>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="salesChart" data-monthly="{{ json_encode(array_values($monthlyRevenue)) }}"></canvas>
            </div>
        </div>

        <div class="section-box">
            <div class="filter-bar" style="margin-bottom: 16px;">
                <h3 style="margin: 0;">Riwayat Seluruh Transaksi</h3>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <form action="{{ route('admin.reports.index') }}" method="GET" class="flex-form" style="gap: 8px;">
                        <input type="date" name="start_date" class="input-search" value="{{ $startDate }}">
                        <span style="font-size: 12px; color: #64748b;">s/d</span>
                        <input type="date" name="end_date" class="input-search" value="{{ $endDate }}">
                        <button type="submit" class="btn-submit-small">Filter</button>
                        @if($startDate || $endDate)
                            <a href="{{ route('admin.reports.index') }}" class="btn-secondary" style="padding: 6px 10px; text-decoration: none; font-size: 12px;">Reset</a>
                        @endif
                    </form>

                    <button onclick="window.print()" class="btn-submit-small btn-print" style="background: #0284c7; white-space: nowrap;">🖨️ Cetak Laporan</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID ORDER</th>
                            <th>NAMA CUSTOMER</th>
                            <th>KURIR PENGANTAR</th>
                            <th style="text-align: center;">STATUS</th>
                            <th>TANGGAL TRANSAKSI</th>
                            <th style="text-align: right;">TOTAL BAYAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $item)
                            @php
                                // MEMBERSIHKAN TEKS NAMA KURIR SECARA PRESISI
                                $cleanCourierName = !empty($item->courier_name) 
                                    ? trim(preg_replace('/\(.*?\)/', '', str_replace('Kurir', '', $item->courier_name))) 
                                    : null;
                            @endphp
                            <tr>
                                <td><strong>#ORD-{{ $item->id }}</strong></td>
                                <td>{{ $item->customer_name ?? 'Customer' }}</td>
                                <td>
                                    @if($cleanCourierName)
                                        🛵 {{ $cleanCourierName }}
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($item->status == 'selesai')
                                        <span class="badge-order badge-success">Selesai</span>
                                    @elseif($item->status == 'dalam_pengiriman' || $item->status == 'diproses')
                                        <span class="badge-order badge-process">Sedang Diproses</span>
                                    @else
                                        <span class="badge-order badge-pending">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ date('d M Y, H:i', strtotime($item->created_at)) }}</td>
                                <td style="text-align: right;">
                                    <strong style="color: #10b981;">Rp {{ number_format($item->total_price, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row" style="text-align: center; padding: 20px; color: #64748b;">
                                    Belum ada data transaksi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/report-chart.js') }}?v={{ time() }}"></script>
</body>
</html>