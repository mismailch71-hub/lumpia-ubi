<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Lumbi Uwuu Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h2>Laporan Penjualan &amp; Keuangan</h2>
            <p>Ringkasan transaksi dan omset toko Lumpia Ubi.</p>
        </div>

        <div class="stats-grid reports-stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Pendapatan (Omset)</h3>
                    <p class="reports-revenue-text">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Transaksi Aktif / Selesai</h3>
                    <p>{{ $completedOrdersCount }} Pesanan</p>
                </div>
            </div>
        </div>

        <div class="section-box reports-chart-box">
            <h3 class="reports-chart-title">Grafik Tren Pendapatan Bulanan ({{ date('Y') }})</h3>
            <div class="reports-chart-canvas-wrap">
                <canvas id="salesChart" data-monthly="{{ json_encode(array_values($monthlyRevenue)) }}"></canvas>
            </div>
        </div>

        @php
            $totalReportRows = count($reports);
        @endphp

        <div class="section-box">
            <div class="reports-toolbar">
                <div class="reports-toolbar-top">
                    <h3 class="reports-history-title">Riwayat Seluruh Transaksi</h3>
                    <div class="reports-summary-badge">
                        Menampilkan <strong id="reportVisibleCount">{{ $totalReportRows }}</strong> transaksi
                    </div>
                </div>

                <div class="reports-toolbar-controls">
                    <form action="{{ route('admin.reports.index') }}" method="GET" class="reports-filter-form">
                        @if($totalReportRows > 10)
                            <input type="text"
                                   id="reportQuickSearch"
                                   class="reports-search-input"
                                   placeholder="Cari ID / customer / kurir...">
                        @endif
                        <input type="date" name="start_date" class="reports-date-input" value="{{ $startDate }}">
                        <span class="reports-date-sep">s/d</span>
                        <input type="date" name="end_date" class="reports-date-input" value="{{ $endDate }}">
                        <button type="submit" class="btn-submit-small reports-toolbar-btn">Filter</button>
                        @if($startDate || $endDate)
                            <a href="{{ route('admin.reports.index') }}" class="btn-secondary reports-reset-btn">Reset</a>
                        @endif
                    </form>

                    <button type="button" id="btnPrintReport" class="btn-submit-small btn-print reports-print-btn">Cetak Laporan</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table" id="reportsTable">
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
                                $cleanCourierName = !empty($item->courier_name)
                                    ? trim(preg_replace('/\(.*?\)/', '', str_replace('Kurir', '', $item->courier_name)))
                                    : null;
                                $searchText = strtolower('#ord-' . $item->id . ' ' . ($item->customer_name ?? 'Customer') . ' ' . ($cleanCourierName ?? '') . ' ' . ($item->status ?? ''));
                            @endphp
                            <tr class="report-data-row" data-search="{{ $searchText }}">
                                <td><strong>#ORD-{{ $item->id }}</strong></td>
                                <td>{{ $item->customer_name ?? 'Customer' }}</td>
                                <td>
                                    @if($cleanCourierName)
                                        {{ $cleanCourierName }}
                                    @else
                                        <span class="reports-courier-empty">-</span>
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
                                    <strong class="reports-revenue-text">Rp {{ number_format($item->total_price, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row reports-empty-cell">
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
