<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ time() }}">
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
            <h2>Dashboard Admin Operasional</h2>
            <p>Ringkasan pesanan keranjang multi-varian, verifikasi WA/QRIS, stok, poin, promo, serta rating &amp; ulasan Lumbi Uwuu.</p>
        </div>

        @if(session('success'))
            <div class="alert-success admin-alert-spacing">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- BANNER OPERASIONAL (TOMBOL TETAP KE KELOLA VARIAN & PRODUK) -->
        <div class="admin-promo-card">
            <div>
                <h3>Ringkasan Operasional Lumbi Uwuu</h3>
                <p>Ongkir Rp 0 area Kampus Unram (-8.58819, 116.09288), luar Unram Rp 1.000/km, +1 Poin tiap 1 produk, dan keranjang Coklat, Keju &amp; Mix.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="admin-promo-action">
                Kelola Varian &amp; Produk
            </a>
        </div>

        @if(($pendingVerificationCount ?? 0) > 0)
            <div class="admin-qris-alert">
                <div>
                    <strong>Perhatian:</strong> Ada <strong>{{ $pendingVerificationCount }} pesanan QRIS</strong> yang menunggu verifikasi bukti pembayaran!
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'menunggu_verifikasi']) }}" class="admin-qris-alert-btn">
                    Cek Sekarang
                </a>
            </div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Produk</h3>
                    <p>{{ $totalProducts }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Kurir</h3>
                    <p>{{ $totalCouriers }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Customer</h3>
                    <p>{{ $totalCustomers }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Pesanan</h3>
                    <p>{{ $totalOrders }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Pendapatan</h3>
                    <p>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="admin-ops-grid">
            <div class="admin-ops-card">
                <div class="admin-ops-title">Stok Varian</div>
                <div class="admin-ops-value">Default 25 / Produk</div>
                <div class="admin-ops-note">Berlaku untuk varian Coklat, Keju, dan Mix sesuai pemesanan keranjang.</div>
            </div>
            <div class="admin-ops-card">
                <div class="admin-ops-title">Ongkir Dari Unram</div>
                <div class="admin-ops-value">Rp 0 di Unram | Rp 1.000/km</div>
                <div class="admin-ops-note">Sumber koordinat Kampus Pusat Unram (-8.58819, 116.09288).</div>
            </div>
            <div class="admin-ops-card">
                <div class="admin-ops-title">Poin &amp; Promo</div>
                <div class="admin-ops-value">+1 Poin / 1 Produk</div>
                <div class="admin-ops-note">Atur pemberitahuan promo melalui menu Promo di sidebar.</div>
            </div>
            <div class="admin-ops-card">
                <div class="admin-ops-title">Ulasan &amp; Rating</div>
                <div class="admin-ops-value">Chat Web, Rating &amp; Ulasan</div>
                <div class="admin-ops-note">Pantau semua ulasan pembeli pada menu Ulasan &amp; Rating.</div>
            </div>
        </div>

        <div class="section-box">
            <h3>Pesanan Terbaru (Verifikasi WA/QRIS, Rating &amp; Ulasan)</h3>
            <div class="table-responsive recent-orders-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer &amp; WA</th>
                            <th>Status</th>
                            <th>Total &amp; Bukti Bayar</th>
                            <th>Rating &amp; Ulasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php
                                $reviewText = $order->review ?? ($order->ulasan ?? null);
                            @endphp
                            <tr>
                                <td><strong>#ORD-{{ $order->id }}</strong></td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong>
                                    @if(!empty($order->whatsapp_phone))
                                        <div class="admin-wa-subtext">
                                            WA: {{ $order->whatsapp_phone }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'menunggu_verifikasi')
                                        <span class="badge-order badge-verify-qris">Cek Bukti QRIS</span>
                                    @elseif($order->status == 'pending')
                                        <span class="badge-order badge-pending">Menunggu Konfirmasi</span>
                                    @elseif($order->status == 'diproses')
                                        <span class="badge-order badge-diproses-info">Diproses</span>
                                    @elseif($order->status == 'dalam_pengiriman')
                                        <span class="badge-order badge-process">Dalam Pengiriman</span>
                                    @elseif($order->status == 'selesai')
                                        <span class="badge-order badge-success">Selesai</span>
                                    @elseif($order->status == 'ditolak')
                                        <span class="badge-order badge-canceled">Bukti Ditolak</span>
                                    @else
                                        <span class="badge-order badge-default-status">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong><br>
                                    @if(strtolower($order->payment_method ?? '') == 'qris')
                                        <span class="admin-pay-badge admin-pay-qris">
                                            QRIS
                                        </span>
                                        @if(!empty($order->payment_proof))
                                            <div class="admin-proof-row">
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" title="Klik untuk perbesar bukti QRIS">
                                                    <img src="{{ asset('storage/' . $order->payment_proof) }}" 
                                                         alt="Bukti QRIS" 
                                                         class="admin-proof-thumb">
                                                </a>
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="admin-proof-link">
                                                    Bukti
                                                </a>
                                            </div>
                                        @else
                                            <div class="admin-proof-missing">Tanpa bukti</div>
                                        @endif
                                    @else
                                        <span class="admin-pay-badge admin-pay-cod">
                                            COD
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($order->rating))
                                        <div class="admin-rating-text">
                                            Rating: {{ $order->rating }}/5
                                        </div>
                                    @else
                                        <span class="admin-unassigned-text">Belum ada rating</span>
                                    @endif

                                    @if(!empty($reviewText))
                                        <div class="admin-wa-subtext">
                                            Ulasan: "{{ $reviewText }}"
                                        </div>
                                    @else
                                        <div class="admin-unassigned-text">Belum ada ulasan</div>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'menunggu_verifikasi')
                                        <div class="admin-action-group">
                                            <form action="{{ route('admin.orders.verify', $order->id) }}" method="POST"
                                                  data-confirm="Yakin saldo QRIS Rp {{ number_format($order->total_price, 0, ',', '.') }} sudah masuk dan bukti valid?">
                                                @csrf
                                                <button type="submit" class="btn-verify-valid">
                                                    Valid
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST"
                                                  data-confirm="Tolak pesanan ini karena bukti tidak valid?">
                                                @csrf
                                                <button type="submit" class="btn-verify-reject">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @elseif(in_array($order->status, ['ditolak', 'dibatalkan']))
                                        <span class="admin-rejected-label">Ditolak</span>
                                    @else
                                        <a href="{{ route('admin.orders.index') }}" class="btn-action-small">Assign Kurir</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-table-empty">
                                    Belum ada pesanan masuk saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>