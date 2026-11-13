<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kurir - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR KURIR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🛵</span> Kurir Lumpia Ubi
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('courier.dashboard') }}" class="active">
                        📦 Tugas Pengiriman
                    </a>
                </li>
                <li>
                    <a href="{{ route('courier.history') }}">
                        📜 Riwayat Pengiriman
                    </a>
                </li>
                <li>
                    <a href="{{ route('courier.profile') }}">
                        👤 Profil Saya
                    </a>
                </li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA KURIR -->
    <main class="main-content">
        <div class="header-title">
            <h2>🛵 Halo, {{ Auth::user()->name }}!</h2>
            <p>Kelola dan antarkan pesanan Lumpia Ubi ke customer.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- STATISTIK KURIR -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Sedang Diantar</h3>
                    <p class="text-active-count">{{ $activeCount }} Pesanan</p>
                </div>
                <div class="icon">🟡</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Selesai Diantar</h3>
                    <p class="text-completed-count">{{ $completedCount }} Pesanan</p>
                </div>
                <div class="icon">🟢</div>
            </div>
        </div>

        <!-- KHUSUS DAFTAR TUGAS AKTIF SAJA -->
        <div class="section-box">
            <h3>Daftar Tugas Pengiriman Aktif</h3>

            @forelse($myDeliveries as $order)
                <div class="delivery-card">
                    <div class="delivery-card-header">
                        <h4>#ORD-{{ $order->id }}</h4>
                        <div>
                            @if(isset($order->payment_method) && strtolower($order->payment_method) == 'cod')
                                <span class="payment-badge badge-cod">💵 TAGIH COD</span>
                            @else
                                <span class="payment-badge badge-paid">💳 LUNAS</span>
                            @endif

                            @if($order->status == 'dalam_pengiriman')
                                <span class="badge-status badge-busy">🟡 Dalam Pengiriman</span>
                            @else
                                <span class="badge-status badge-default">{{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="delivery-card-body">
                        <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                        <p><strong>Alamat Tujuan:</strong> {{ $order->shipping_address ?? 'Jl. Mawar No. 12, Mataram' }}</p>
                        
                        @if(!empty($order->notes))
                            <div class="customer-note">
                                📝 <strong>Catatan:</strong> {{ $order->notes }}
                            </div>
                        @endif

                        @if(isset($order->items) && count($order->items) > 0)
                            <div class="order-items-box">
                                <div class="order-items-title">🛍️ Rincian Pesanan:</div>
                                <ul class="order-item-list">
                                    @foreach($order->items as $item)
                                        <li><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (@Rp {{ number_format($item->price, 0, ',', '.') }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p style="margin-top: 8px;"><strong>Total Tagihan:</strong> <strong class="text-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></p>
                    </div>

                    <div class="delivery-card-footer">
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->shipping_address ?? 'Mataram') }}" target="_blank" class="btn-maps">
                                📍 Buka Petunjuk Maps
                            </a>

                            @if(!empty($order->customer_phone))
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank" class="btn-whatsapp">
                                    💬 Hubungi WhatsApp
                                </a>
                            @else
                                <span class="empty-row" style="align-self: center;">No HP Tidak Ada</span>
                            @endif
                        </div>

                        <form action="{{ route('courier.orders.update-status', $order->id) }}" method="POST" class="flex-form">
                            @csrf
                            @if($order->status != 'dalam_pengiriman')
                                <input type="hidden" name="status" value="dalam_pengiriman">
                                <button type="submit" class="btn-submit-small btn-busy">🛵 Mulai Antar</button>
                            @else
                                <input type="hidden" name="status" value="selesai">
                                <button type="submit" class="btn-submit-small btn-online" onclick="return confirm('Apakah pesanan ini sudah berhasil diterima oleh customer?')">✅ Selesaikan Order</button>
                            @endif
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state-box">
                    <p>Tidak ada tugas pengiriman aktif yang perlu diantar saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>