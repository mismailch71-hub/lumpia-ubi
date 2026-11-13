<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI TUNGGAL -->
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

    <!-- KONTEN UTAMA TUNGGAL -->
    <main class="main-content">
        <div class="header-title">
            <h2>📦 Kelola Pesanan Customer</h2>
            <p>Atur penugasan kurir (Yoga, Rafi, Mail) dan pantau status transaksi pemesanan.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <!-- BARIS FILTER & CARI -->
            <div class="filter-bar">
                <h3 style="margin: 0;">Daftar Seluruh Pesanan Masuk</h3>
                
                <form action="{{ route('admin.orders.index') }}" method="GET" class="filter-group">
                    <input type="text" name="search" class="input-search" placeholder="🔍 Cari customer / ID..." value="{{ request('search') }}">
                    
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="dalam_pengiriman" {{ request('status') == 'dalam_pengiriman' ? 'selected' : '' }}>Dalam Pengiriman</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>

                    <button type="submit" class="btn-submit-small">Cari</button>

                    @if(request('status') || request('search'))
                        <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding: 6px 12px; font-size: 11px;">Reset</a>
                    @endif
                </form>
            </div>

            <!-- TABEL PESANAN RINGKAS -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">ID Order</th>
                            <th style="width: 30%;">Customer & Alamat</th>
                            <th style="width: 15%;">Total Bayar</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 25%;">Penugasan Kurir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <strong>#ORD-{{ $order->id }}</strong><br>
                                    <button type="button" class="btn-detail-small" onclick="openModal('modal-{{ $order->id }}')">👁️ Detail</button>
                                </td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong><br>
                                    <span class="address-text">📍 {{ $order->shipping_address }}</span>
                                </td>
                                <td><strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></td>
                                <td>
                                    @if($order->status == 'pending')
                                        <span class="badge-order badge-pending">Menunggu Konfirmasi</span>
                                    @elseif($order->status == 'dalam_pengiriman')
                                        <span class="badge-order badge-process">Dalam Pengiriman</span>
                                    @elseif($order->status == 'selesai')
                                        <span class="badge-order badge-success">Selesai</span>
                                    @elseif($order->status == 'dibatalkan')
                                        <span class="badge-order badge-canceled">Dibatalkan</span>
                                    @else
                                        <span class="badge-order" style="background: #f1f5f9; color: #475569;">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- FORM TUNGGAL: DENGAN ASSIGN KURIR OTOMATIS MENGUBAH STATUS -->
                                    <form action="{{ route('admin.orders.assign-kurir', $order->id) }}" method="POST" class="flex-form">
                                        @csrf
                                        <select name="courier_id" class="form-select" style="width: 100%;" required>
                                            <option value="">-- Pilih Kurir --</option>
                                            @foreach($couriers as $courier)
                                                @php
                                                    $cleanCourierName = trim(str_replace(['Kurir ', '(Kurir 1)', '(Kurir 2)', '(Kurir 3)'], '', $courier->name));
                                                @endphp
                                                <option value="{{ $courier->id }}" {{ $order->courier_id == $courier->id ? 'selected' : '' }}>
                                                    🛵 Kurir {{ $cleanCourierName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-submit-small">
                                            {{ $order->courier_id ? 'Ubah' : 'Tugaskan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-row">Belum ada pesanan transaksi masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL POPUP (SEMUA DISIMPANKAN DI LUAR TABEL & DIBERI DISPLAY NONE) -->
    @foreach($orders as $order)
        <div id="modal-{{ $order->id }}" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>📋 Rincian Pesanan #ORD-{{ $order->id }}</h3>
                    <button class="btn-close-modal" onclick="closeModal('modal-{{ $order->id }}')">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 6px;"><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_email }})</p>
                    <p style="margin-bottom: 6px;"><strong>Alamat Pengiriman:</strong> {{ $order->shipping_address }}</p>
                    <p style="margin-bottom: 12px;"><strong>Kurir Pengantar:</strong> {{ $order->courier_name ?? 'Belum Ditugaskan' }}</p>
                    
                    <h4 style="margin-top: 15px; margin-bottom: 8px; color: #1e293b; font-size: 14px;">Produk yang Dipesan:</h4>
                    <table class="item-list-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Jumlah</th>
                                <th>Harga Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td>🍠 {{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }} Porsi</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#94a3b8;">Tidak ada rincian item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div style="text-align: right; font-size: 14px; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                        <strong>Total Pembayaran: <span style="color: #7e22ce;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></strong>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>