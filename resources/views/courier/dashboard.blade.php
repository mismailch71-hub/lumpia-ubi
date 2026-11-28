<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Kurir - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR KURIR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand admin-brand-box">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Kurir Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('courier.dashboard') }}" class="{{ request()->routeIs('courier.dashboard') ? 'active' : '' }}">
                        Tugas Pengiriman
                    </a>
                </li>
                <li>
                    <a href="{{ route('courier.history') }}" class="{{ request()->routeIs('courier.history') ? 'active' : '' }}">
                        Riwayat Pengiriman
                    </a>
                </li>
                <li>
                    <a href="{{ route('courier.profile') }}" class="{{ request()->routeIs('courier.profile') ? 'active' : '' }}">
                        Profil
                    </a>
                </li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA KURIR -->
    <main class="main-content">
        <div class="header-title">
            <h2>Halo, {{ Auth::user()->name }}</h2>
            <p>Kelola dan antarkan pesanan Lumpia Ubi ke customer.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- STATISTIK KURIR -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Sedang Diantar</h3>
                    <p class="text-active-count">{{ $activeCount }} Pesanan</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="info">
                    <h3>Selesai Diantar</h3>
                    <p class="text-completed-count">{{ $completedCount }} Pesanan</p>
                </div>
            </div>
        </div>

        <!-- KHUSUS DAFTAR TUGAS AKTIF SAJA -->
        <div class="section-box">
            <h3 class="courier-section-title">Daftar Tugas Pengiriman Aktif</h3>

            <div class="courier-chat-board">
                @forelse($myDeliveries as $order)
                    @php
                        $address = $order->shipping_address ?? 'Jl. Mawar No. 12, Mataram';
                        $timeLabel = !empty($order->created_at) ? date('H:i', strtotime($order->created_at)) : date('H:i');
                    @endphp
                    <div class="courier-chat-card" data-order-chat="{{ $order->id }}">
                        <div class="courier-chat-header">
                            <h4 class="courier-chat-order-id">#ORD-{{ $order->id }} &bull; {{ $order->customer_name }}</h4>
                            <div class="courier-chat-badges">
                                @if(isset($order->payment_method) && strtolower($order->payment_method) == 'cod')
                                    <span class="payment-badge badge-cod">TAGIH COD</span>
                                @else
                                    <span class="payment-badge badge-paid">LUNAS</span>
                                @endif

                                @if($order->status == 'dalam_pengiriman')
                                    <span class="badge-status badge-busy">Dalam Pengiriman</span>
                                @else
                                    <span class="badge-status badge-default">{{ ucfirst($order->status) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="courier-chat-thread" id="chatThread-{{ $order->id }}">
                            <div class="chat-bubble chat-bubble-incoming">
                                <div class="chat-bubble-label">Alamat Tujuan</div>
                                <div>{{ $address }}</div>
                                @if(!empty($order->customer_phone))
                                    <div class="chat-bubble-contact">
                                        <strong>Kontak:</strong> {{ $order->customer_phone }}
                                    </div>
                                @endif
                                <div class="chat-bubble-footer">
                                    <span>{{ $timeLabel }}</span>
                                </div>
                            </div>

                            @if(!empty($order->notes))
                                <div class="chat-bubble chat-bubble-incoming">
                                    <div class="chat-bubble-label">Catatan Pesanan</div>
                                    <div>{{ $order->notes }}</div>
                                    <div class="chat-bubble-footer">
                                        <span>{{ $timeLabel }}</span>
                                    </div>
                                </div>
                            @endif

                            @if(isset($order->items) && count($order->items) > 0)
                                <div class="chat-bubble chat-bubble-incoming">
                                    <div class="chat-bubble-label">Rincian Item</div>
                                    <ul class="chat-bubble-items">
                                        @foreach($order->items as $item)
                                            <li><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (@Rp {{ number_format($item->price, 0, ',', '.') }})</li>
                                        @endforeach
                                    </ul>
                                    <div class="chat-bubble-footer">
                                        <span>{{ $timeLabel }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="chat-bubble chat-bubble-outgoing">
                                <div class="chat-bubble-label">Ringkasan Tagihan</div>
                                <div>
                                    Total Tagihan: <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                                </div>
                                <div class="chat-bubble-footer">
                                    <span>{{ $order->status == 'dalam_pengiriman' ? 'Sedang Diantar' : 'Siap Antar' }}</span>
                                </div>
                            </div>
                        </div>

                        <form class="chat-reply-bar courier-quick-note-form" data-target-thread="chatThread-{{ $order->id }}">
                            <input type="text"
                                   class="chat-reply-input"
                                   placeholder="Tulis catatan posisi atau info pengantaran untuk order ini...">
                            <button type="submit" class="btn-submit-small chat-reply-btn">Kirim Catatan</button>
                        </form>

                        <div class="courier-chat-actions">
                            <div class="courier-left-actions">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}"
                                   target="_blank"
                                   class="btn-maps courier-action-btn">
                                    Buka Petunjuk Maps
                                </a>
                            </div>

                            <form action="{{ route('courier.orders.update-status', $order->id) }}" method="POST" class="flex-form">
                                @csrf
                                @if($order->status != 'dalam_pengiriman')
                                    <input type="hidden" name="status" value="dalam_pengiriman">
                                    <button type="submit" class="btn-submit-small btn-busy courier-action-btn">Mulai Antar</button>
                                @else
                                    <input type="hidden" name="status" value="selesai">
                                    <button type="submit"
                                            class="btn-submit-small btn-online courier-action-btn"
                                            onclick="return confirm('Apakah pesanan ini sudah berhasil diterima oleh customer?')">
                                        Selesaikan Order
                                    </button>
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
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/courier-script.js') }}?v={{ time() }}"></script>
</body>
</html>