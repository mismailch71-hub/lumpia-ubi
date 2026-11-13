<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengiriman - Lumpia Ubi</title>
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
                <li><a href="{{ route('courier.dashboard') }}">📦 Tugas Pengiriman</a></li>
                <li><a href="{{ route('courier.history') }}" class="active">📜 Riwayat Pengiriman</a></li>
                <li><a href="{{ route('courier.profile') }}">👤 Profil Saya</a></li>
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
            <h2>📜 Riwayat Pengiriman Selesai</h2>
            <p>Daftar pesanan yang telah berhasil Anda antarkan.</p>
        </div>

        <!-- REKAPITULASI RINGKASAN -->
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Terantar</h3>
                    <p class="text-completed-count">{{ $completedDeliveries->count() }} Pesanan</p>
                </div>
                <div class="icon">✅</div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Total Nilai Antaran</h3>
                    <p class="text-active-count" style="color: #059669;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
                <div class="icon">💰</div>
            </div>
        </div>

        <div class="section-box">
            @forelse($completedDeliveries as $completedOrder)
                <div class="delivery-card completed">
                    <div class="delivery-card-header">
                        <div>
                            <h4>#ORD-{{ $completedOrder->id }}</h4>
                            <small style="color: #64748b; font-size: 12px;">
                                🕒 Selesai: {{ \Carbon\Carbon::parse($completedOrder->updated_at)->translatedFormat('d M Y, H:i') }} WITA
                            </small>
                        </div>
                        <div>
                            <!-- BADGE METODE PEMBAYARAN -->
                            @if(isset($completedOrder->payment_method) && strtolower($completedOrder->payment_method) == 'cod')
                                <span class="payment-badge badge-cod">💵 COD</span>
                            @else
                                <span class="payment-badge badge-paid">💳 LUNAS</span>
                            @endif

                            <span class="badge-status badge-online">🟢 Selesai</span>
                        </div>
                    </div>

                    <div class="delivery-card-body">
                        <p><strong>Customer:</strong> {{ $completedOrder->customer_name }}</p>
                        <p><strong>Alamat Tujuan:</strong> {{ $completedOrder->shipping_address ?? 'Jl. Mawar No. 12, Mataram' }}</p>

                        <!-- ITEM TERANTAR -->
                        @if(isset($completedOrder->items) && count($completedOrder->items) > 0)
                            <div class="order-items-box">
                                <div class="order-items-title">🛍️ Item Terantar:</div>
                                <ul class="order-item-list">
                                    @foreach($completedOrder->items as $item)
                                        <li><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p style="margin-top: 8px;"><strong>Total Tagihan:</strong> <strong class="text-price">Rp {{ number_format($completedOrder->total_price, 0, ',', '.') }}</strong></p>
                    </div>

                    <!-- FOOTER KARTU KONSISTEN -->
                    <div class="delivery-card-footer">
                        @if(!empty($completedOrder->customer_phone))
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $completedOrder->customer_phone) }}" target="_blank" class="btn-whatsapp">
                                💬 Hubungi WhatsApp Customer
                            </a>
                        @else
                            <span class="empty-row" style="font-size: 12px;">💬 No HP Customer Tidak Tersedia</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state-box">
                    <p>Belum ada riwayat pesanan yang Anda selesaikan.</p>
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>