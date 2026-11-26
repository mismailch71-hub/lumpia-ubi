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

    @php
        $currentUser = $user ?? auth()->user();
    @endphp

    <div class="mobile-top-header">
        <span>Lumpia Ubi Store</span>
    </div>

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>Lumpia Ubi Store</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('customer.orders.index') }}" class="active">Pesanan</a></li>
                <li><a href="{{ route('customer.catalog') }}">Katalog</a></li>
                <li>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('customer.profile') ? route('customer.profile') : '#profil-section' }}" onclick="openProfileModalIfNeeded(event)">
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

    <main class="main-content">
        <div class="header-flex">
            <div class="header-title">
                <h2>Status &amp; Riwayat Pesanan Saya</h2>
                <p>Pantau pesanan, chat kurir langsung di sini, dan beri rating pesanan.</p>
            </div>

            <div class="header-actions-group">
                <span class="badge-status badge-status-selesai">Poin: {{ $currentUser->points ?? 0 }}</span>
                <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                    Pindah Menu ke Bawah
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success-custom">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger-custom">
                {{ session('error') }}
            </div>
        @endif

        <div class="section-box">
            @forelse($orders as $order)
                <div class="customer-order-card">
                    <div class="delivery-card-header">
                        <div>
                            <h4>#ORD-{{ $order->id }}</h4>
                            <small class="order-date-text">
                                Tanggal: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WITA
                            </small>
                            @if(!empty($order->whatsapp_phone))
                                <div class="order-wa-text">
                                    WA Verifikasi: <strong>{{ $order->whatsapp_phone }}</strong>
                                </div>
                            @endif
                        </div>
                        <div>
                            @if(isset($order->payment_method) && strtolower($order->payment_method) == 'cod')
                                <span class="payment-badge badge-cod">COD</span>
                            @else
                                <span class="payment-badge badge-paid">LUNAS / QRIS</span>
                            @endif

                            @if($order->status == 'selesai')
                                <span class="badge-status badge-status-selesai">Selesai</span>
                            @elseif($order->status == 'dalam_pengiriman')
                                <span class="badge-status badge-status-pengiriman">Dalam Pengiriman</span>
                            @else
                                <span class="badge-status badge-status-diproses">{{ ucfirst($order->status) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="delivery-card-body">
                        <p><strong>Alamat Tujuan:</strong> {{ $order->shipping_address }}</p>

                        @if(isset($order->items) && count($order->items) > 0)
                            <div class="order-items-box">
                                <div class="order-items-title">Rincian Menu:</div>
                                <ul class="order-item-list">
                                    @foreach($order->items as $item)
                                        <li><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (@Rp {{ number_format($item->price, 0, ',', '.') }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="order-total-row">
                            <strong>Total Pembayaran:</strong> <strong class="text-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                        </p>

                        <div class="chat-box-panel">
                            <div class="chat-box-title">
                                Chat Kurir ({{ $order->courier_name ?? 'Kurir Lumbi Uwuu' }}) — Langsung di Web
                            </div>
                            <div id="chatMessages-{{ $order->id }}" class="chat-messages-area">
                                <div class="chat-msg-item">
                                    <strong>Kurir:</strong> Halo kak, pesanan #ORD-{{ $order->id }} siap diantar. Silakan chat di sini jika ada patokan lokasi.
                                </div>
                            </div>
                            <div class="chat-input-row">
                                <input type="text" id="chatInput-{{ $order->id }}" class="form-control chat-input-field"
                                       placeholder="Tulis pesan untuk kurir...">
                                <button type="button" class="btn-submit-small btn-online chat-send-btn"
                                        onclick="sendCourierChat('{{ $order->id }}')">
                                    Kirim
                                </button>
                            </div>
                        </div>

                        <div class="rating-box-row">
                            <span class="rating-label">Rating Pesanan:</span>
                            <select id="ratingSelect-{{ $order->id }}" class="form-control rating-select">
                                <option value="5" {{ ($order->rating ?? 5) == 5 ? 'selected' : '' }}>5 - Mantap</option>
                                <option value="4" {{ ($order->rating ?? 0) == 4 ? 'selected' : '' }}>4 - Enak</option>
                                <option value="3" {{ ($order->rating ?? 0) == 3 ? 'selected' : '' }}>3 - Cukup</option>
                                <option value="2" {{ ($order->rating ?? 0) == 2 ? 'selected' : '' }}>2 - Kurang</option>
                                <option value="1" {{ ($order->rating ?? 0) == 1 ? 'selected' : '' }}>1 - Buruk</option>
                            </select>
                            <button type="button" class="btn-layout-toggle rating-submit-btn"
                                    onclick="submitRating('{{ $order->id }}')">
                                Kirim Rating
                            </button>
                            <span id="ratingStatus-{{ $order->id }}" class="rating-status-text">
                                @if(!empty($order->rating)) Tersimpan: {{ $order->rating }}/5 @endif
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state-box">
                    <p>Anda belum memiliki riwayat pemesanan Lumpia Ubi.</p>
                </div>
            @endforelse
        </div>
    </main>

    <div id="profileModal" class="modal-backdrop modal-hidden">
        <div class="modal-content modal-scrollable">
            <div class="modal-header">
                <h3>Edit Profil Saya</h3>
                <button type="button" class="btn-close-modal" onclick="closeProfileModal()">x</button>
            </div>
            <form action="{{ \Illuminate\Support\Facades\Route::has('customer.profile.update') ? route('customer.profile.update') : url('/customer/profile') }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="avatar" accept="image/*" class="form-control file-input-compact">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ $currentUser->name ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $currentUser->email ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" class="form-control" value="{{ $currentUser->phone ?? $currentUser->no_hp ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman Utama</label>
                    <textarea name="address" rows="2" class="form-control">{{ $currentUser->alamat ?? $currentUser->address ?? '' }}</textarea>
                </div>
                <div class="modal-footer-actions">
                    <button type="button" class="btn-cancel-edit" onclick="closeProfileModal()">Tutup</button>
                    <button type="submit" class="btn-submit-small btn-online">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>