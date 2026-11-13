<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- HEADER ATAS KHUSUS TAMPILAN HP -->
    <div class="mobile-top-header">
        <span>🥟</span> Lumpia Ubi Store
    </div>

    <!-- SIDEBAR CUSTOMER -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🥟</span> Lumpia Ubi Store
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('customer.catalog') }}">🛍️ Katalog Produk</a></li>
                <li><a href="{{ route('customer.orders.index') }}" class="active">📦 Pesanan Saya</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <!-- HEADER DENGAN TOMBOL GANTI POSISI MENU -->
        <div class="header-flex">
            <div class="header-title" style="margin-bottom: 0;">
                <h2>📦 Status & Riwayat Pesanan Saya</h2>
                <p>Pantau status antar kurir dan rincian pesanan Lumpia Ubi Anda.</p>
            </div>

            <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                ⬇️ Pindah Menu ke Bawah
            </button>
        </div>

        @if(session('success'))
            <div class="alert-success-custom" style="margin-bottom: 20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger-custom" style="margin-bottom: 20px;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div class="section-box">
            @forelse($orders as $order)
                <div class="customer-order-card">
                    <div class="delivery-card-header">
                        <div>
                            <h4>#ORD-{{ $order->id }}</h4>
                            <small style="color: #64748b; font-size: 12px;">
                                📅 Tanggal: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WITA
                            </small>
                        </div>
                        <div>
                            @if(isset($order->payment_method) && strtolower($order->payment_method) == 'cod')
                                <span class="payment-badge badge-cod">💵 COD</span>
                            @else
                                <span class="payment-badge badge-paid">💳 LUNAS</span>
                            @endif

                            @if($order->status == 'selesai')
                                <span class="badge-status badge-status-selesai">🟢 Selesai</span>
                            @elseif($order->status == 'dalam_pengiriman')
                                <span class="badge-status badge-status-pengiriman">🛵 Dalam Pengiriman</span>
                            @else
                                <span class="badge-status badge-status-diproses">⏳ {{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="delivery-card-body">
                        <p><strong>Alamat Tujuan:</strong> {{ $order->shipping_address }}</p>

                        <!-- ITEM PEMBELIAN -->
                        @if(isset($order->items) && count($order->items) > 0)
                            <div class="order-items-box">
                                <div class="order-items-title">🛍️ Rincian Menu:</div>
                                <ul class="order-item-list">
                                    @foreach($order->items as $item)
                                        <li><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (@Rp {{ number_format($item->price, 0, ',', '.') }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p style="margin-top: 8px;"><strong>Total Pembayaran:</strong> <strong class="text-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></p>

                        <!-- INFORMASI KURIR YANG MENGANTAR -->
                        @if(!empty($order->courier_name))
                            <div class="courier-info-box">
                                <div class="courier-info-text">
                                    <strong>🛵 Kurir Pengantar:</strong> {{ $order->courier_name }}
                                </div>
                                @if(!empty($order->courier_phone))
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->courier_phone) }}" target="_blank" class="btn-whatsapp" style="padding: 6px 12px; font-size: 12px;">
                                        💬 Hubungi Kurir
                                    </a>
                                @endif
                            </div>
                        @else
                            <div style="background: #f8fafc; padding: 10px; border-radius: 6px; margin-top: 10px; font-size: 12px; color: #64748b;">
                                ℹ️ Pesanan Anda sedang disiapkan. Admin akan segera menugaskan kurir.
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state-box">
                    <p>Anda belum memiliki riwayat pemesanan Lumpia Ubi.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- SCRIPT EKSTERNAL UNTUK LAYOUT TOGGLE -->
    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>