<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🍠</span> Lumpia Ubi Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}">📊 Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="active">📦 Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}">🍠 Kelola Produk</a></li>
                <li><a href="#">🛵 Daftar Kurir</a></li>
                <li><a href="#">👥 Data Customer</a></li>
                <li><a href="#">📈 Laporan</a></li>
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
            <h2>📦 Kelola Pesanan Customer</h2>
            <p>Atur penugasan kurir (Yoga, Rafi, Mail) dan pantau status transaksi pemesanan.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="section-box">
            <h3>Daftar Seluruh Pesanan Masuk</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer & Alamat</th>
                            <th>Total Bayar</th>
                            <th>Status Saat Ini</th>
                            <th>Penugasan Kurir</th>
                            <th>Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td><strong>#ORD-{{ $order->id }}</strong></td>
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
                                    @else
                                        <span class="badge-order" style="background: #f1f5f9; color: #475569;">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- FORM ASSIGN KURIR -->
                                    <form action="{{ route('admin.orders.assign-kurir', $order->id) }}" method="POST" class="flex-form">
                                        @csrf
                                        <select name="courier_id" class="form-select" required>
                                            <option value="">-- Pilih Kurir --</option>
                                            @foreach($couriers as $courier)
                                                <option value="{{ $courier->id }}" {{ $order->courier_id == $courier->id ? 'selected' : '' }}>
                                                    🛵 {{ $courier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-submit-small">Tugaskan</button>
                                    </form>
                                </td>
                                <td>
                                    <!-- FORM UPDATE STATUS MANUAL -->
                                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="flex-form">
                                        @csrf
                                        <select name="status" class="form-select" onchange="this.form.submit()">
                                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="diproses" {{ $order->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                            <option value="dalam_pengiriman" {{ $order->status == 'dalam_pengiriman' ? 'selected' : '' }}>Dalam Pengiriman</option>
                                            <option value="selesai" {{ $order->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="dibatalkan" {{ $order->status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">
                                    Belum ada pesanan transaksi masuk.
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