<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <span>Lumbi Uwuu Store</span>
    </div>

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Lumbi Uwuu Store</span>
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
                <p>Pantau pesanan, hapus riwayat pesanan lama, dan beri rating serta ulasan.</p>
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
                @php
                    $isPastOrder = in_array(strtolower($order->status ?? ''), ['selesai', 'dibatalkan', 'batal', 'ditolak']);
                    $hasReview = !empty($order->review ?? $order->ulasan ?? '');
                @endphp
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

                        <div class="order-summary-action-row">
                            <p class="order-total-row">
                                <strong>Total Pembayaran:</strong> <strong class="text-price">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                            </p>

                            @if($isPastOrder)
                                <form action="{{ \Illuminate\Support\Facades\Route::has('customer.orders.destroy') ? route('customer.orders.destroy', $order->id) : url('/customer/orders/' . $order->id) }}"
                                      method="POST"
                                      class="delete-order-form"
                                      onsubmit="return confirm('Yakin ingin menghapus riwayat pesanan #ORD-{{ $order->id }} yang sudah lalu?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-order">
                                        Hapus Pesanan
                                    </button>
                                </form>
                            @endif
                        </div>

                        <form action="{{ \Illuminate\Support\Facades\Route::has('customer.orders.rating') ? route('customer.orders.rating', $order->id) : url('/customer/orders/' . $order->id . '/rating') }}"
                              method="POST"
                              class="rating-review-form">
                            @csrf
                            <div class="rating-box-row">
                                <span class="rating-label">Rating Pesanan:</span>
                                <select name="rating" id="ratingSelect-{{ $order->id }}" class="form-control rating-select">
                                    <option value="5" {{ ($order->rating ?? 5) == 5 ? 'selected' : '' }}>5 - Mantap</option>
                                    <option value="4" {{ ($order->rating ?? 0) == 4 ? 'selected' : '' }}>4 - Enak</option>
                                    <option value="3" {{ ($order->rating ?? 0) == 3 ? 'selected' : '' }}>3 - Cukup</option>
                                    <option value="2" {{ ($order->rating ?? 0) == 2 ? 'selected' : '' }}>2 - Kurang</option>
                                    <option value="1" {{ ($order->rating ?? 0) == 1 ? 'selected' : '' }}>1 - Buruk</option>
                                </select>

                                <label class="review-checkbox-label">
                                    <input type="checkbox"
                                           id="reviewCheckbox-{{ $order->id }}"
                                           name="with_review"
                                           value="1"
                                           class="review-checkbox-input"
                                           {{ $hasReview ? 'checked' : '' }}
                                           onchange="toggleReviewBox('{{ $order->id }}')">
                                    <span>Tambah Ulasan</span>
                                </label>

                                <button type="submit" class="btn-layout-toggle rating-submit-btn">
                                    Kirim Rating
                                </button>

                                @if(!empty($order->rating))
                                    <span id="ratingStatus-{{ $order->id }}" class="rating-status-text">
                                        Tersimpan: {{ $order->rating }}/5
                                    </span>
                                @endif
                            </div>

                            <div id="reviewBox-{{ $order->id }}"
                                 class="review-box-wrapper {{ $hasReview ? 'is-visible' : '' }}">
                                <textarea name="review"
                                          id="reviewTextarea-{{ $order->id }}"
                                          rows="2"
                                          class="form-control review-textarea"
                                          placeholder="Tulis ulasan Anda tentang rasa, kemasan, atau pengiriman...">{{ $order->review ?? $order->ulasan ?? '' }}</textarea>
                            </div>
                        </form>
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