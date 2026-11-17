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
            <div class="sidebar-brand" style="display: flex; align-items: center; gap: 10px; padding: 12px 18px;">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}" 
                     onerror="if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.jpeg') }}'; } else if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu" 
                     style="height: 40px; width: auto; object-fit: contain; flex-shrink: 0;">
                <span style="font-weight: 800; font-size: 17px; color: #ffffff; letter-spacing: 0.3px;">Lumbi Uwuu</span>
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

        @if(session('success'))
            <div class="alert-success" style="margin-bottom: 16px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #fecaca;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- BANNER PERINGATAN JIKA ADA PEMBAYARAN QRIS MENUNGGU VERIFIKASI -->
        @if(($pendingVerificationCount ?? 0) > 0)
            <div style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong>⏳ Perhatian:</strong> Ada <strong>{{ $pendingVerificationCount }} pesanan QRIS</strong> yang menunggu verifikasi bukti pembayaran!
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'menunggu_verifikasi']) }}" style="background: #d97706; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none;">
                    🔍 Cek Sekarang
                </a>
            </div>
        @endif

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
            <h3>📦 Pesanan Terbaru (Verifikasi QRIS & Penugasan Kurir)</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total & Bukti Bayar</th>
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
                                    @if($order->status == 'menunggu_verifikasi')
                                        <span class="badge-order" style="background: #fef3c7; color: #b45309;">⏳ Cek Bukti QRIS</span>
                                    @elseif($order->status == 'pending')
                                        <span class="badge-order badge-pending">Menunggu Konfirmasi</span>
                                    @elseif($order->status == 'diproses')
                                        <span class="badge-order" style="background: #e0f2fe; color: #0369a1;">📦 Diproses</span>
                                    @elseif($order->status == 'dalam_pengiriman')
                                        <span class="badge-order badge-process">🛵 Dalam Pengiriman</span>
                                    @elseif($order->status == 'selesai')
                                        <span class="badge-order badge-success">✅ Selesai</span>
                                    @elseif($order->status == 'ditolak')
                                        <span class="badge-order badge-canceled">❌ Bukti Ditolak</span>
                                    @else
                                        <span class="badge-order" style="background: #f1f5f9; color: #475569;">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong><br>
                                    @if(strtolower($order->payment_method ?? '') == 'qris')
                                        <span style="display: inline-block; margin-top: 4px; background: #f3e8ff; color: #6b21a8; padding: 2px 6px; border-radius: 5px; font-size: 10px; font-weight: bold;">
                                            📱 QRIS
                                        </span>
                                        @if(!empty($order->payment_proof))
                                            <div style="margin-top: 5px; display: flex; align-items: center; gap: 6px;">
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" title="Klik untuk perbesar bukti QRIS">
                                                    <img src="{{ asset('storage/' . $order->payment_proof) }}" 
                                                         alt="Bukti QRIS" 
                                                         style="width: 38px; height: 38px; object-fit: cover; border-radius: 5px; border: 1px solid #cbd5e1;">
                                                </a>
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" style="font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: underline;">
                                                    🔍 Bukti
                                                </a>
                                            </div>
                                        @else
                                            <div style="font-size: 10px; color: #dc2626; margin-top: 3px;">⚠️ Tanpa bukti</div>
                                        @endif
                                    @else
                                        <span style="display: inline-block; margin-top: 4px; background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 5px; font-size: 10px; font-weight: bold;">
                                            💵 COD
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($order->courier_name))
                                        <strong>🛵 {{ $order->courier_name }}</strong>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'menunggu_verifikasi')
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <form action="{{ route('admin.orders.verify', $order->id) }}" method="POST" onsubmit="return confirm('Yakin saldo QRIS Rp {{ number_format($order->total_price, 0, ',', '.') }} sudah masuk dan bukti valid?')">
                                                @csrf
                                                <button type="submit" style="background: #16a34a; color: #ffffff; border: none; padding: 5px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                                    ✅ Valid
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" onsubmit="return confirm('Tolak pesanan ini karena bukti tidak valid?')">
                                                @csrf
                                                <button type="submit" style="background: #dc2626; color: #ffffff; border: none; padding: 5px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                                    ❌ Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @elseif(in_array($order->status, ['ditolak', 'dibatalkan']))
                                        <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Ditolak</span>
                                    @else
                                        <a href="{{ route('admin.orders.index') }}" class="btn-action-small">Assign Kurir</a>
                                    @endif
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