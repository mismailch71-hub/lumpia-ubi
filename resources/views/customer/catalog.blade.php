<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body" data-default-img="{{ asset('images/lumpia-default.jpg') }}">

    @php
        $currentUser = $user ?? auth()->user();
        $userPoints = $currentUser->points ?? 0;
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
                <li><a href="{{ route('customer.orders.index') }}">Pesanan</a></li>
                <li><a href="{{ route('customer.catalog') }}" class="active">Katalog</a></li>
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
        <div class="promo-banner-box">
            <div>
                <span class="promo-pill">Pemberitahuan Promo</span>
                <p>
                    Ongkir <strong>Rp 0</strong> untuk area <strong>Kampus Unram</strong>! Di luar Unram hanya <strong>Rp 1.000 / km</strong> dan dapat <strong>+1 Poin</strong> setiap beli <strong>1 produk</strong>.
                </p>
            </div>
            <button type="button" class="cart-floating-btn" onclick="openCartModal()">
                Keranjang (<span id="cartCountBadge">0</span>)
            </button>
        </div>

        <div class="header-flex">
            <div class="header-title">
                <h2>Katalog Lumpia Ubi</h2>
                <p>Bisa pilih Coklat, Keju, dan Mix sekaligus dalam satu keranjang.</p>
            </div>

            <div class="customer-header-actions">
                <span class="points-badge">Poin Kamu: <span id="userPointsDisplay">{{ $userPoints }}</span> Poin</span>
                <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                    Pindah Menu ke Bawah
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success-custom customer-alert-gap">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="customer-error-box">
                <strong>Pesanan belum tersimpan:</strong>
                <ul class="customer-error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="product-grid">
            @forelse($products as $product)
                @php
                    $imgUrl = (!empty($product->image) && file_exists(public_path('storage/' . $product->image)))
                        ? asset('storage/' . $product->image)
                        : asset('images/lumpia-default.jpg');
                    $stockQty = $product->stock ?? 25;
                @endphp
                <div class="product-card">
                    <div class="product-image-box">
                        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'">
                    </div>

                    <div class="product-info">
                        <span class="stock-tag">Stok: <span class="js-stock-val" data-product-id="{{ $product->id }}">{{ $stockQty }}</span></span>
                        <h3 class="product-card-title">{{ $product->name }}</h3>
                        <p class="product-card-desc">{{ $product->description ?? 'Lumpia Ubi renyah dengan isian manis/gurih yang melumer di mulut.' }}</p>

                        <div class="product-price-box">
                            <span class="price-tag">Medium: Rp {{ number_format($product->price_medium, 0, ',', '.') }}</span>
                            <span class="price-tag">Large: Rp {{ number_format($product->price_large, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="customer-product-actions">
                        <button type="button" class="btn-submit-small btn-online"
                                onclick="quickAddToCart('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->price_medium }}', '{{ $product->price_large }}', '{{ $imgUrl }}')">
                            + Keranjang
                        </button>
                        <button type="button" class="btn-submit-small customer-btn-checkout"
                                onclick="openCartModalWithItem('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->price_medium }}', '{{ $product->price_large }}', '{{ $imgUrl }}')">
                            Checkout
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state-box product-grid-empty">
                    <p>Belum ada produk yang tersedia di katalog saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <div id="orderModal" class="modal-backdrop modal-hidden">
        <div class="modal-content modal-scrollable">
            <div class="modal-header">
                <h3>Keranjang &amp; Checkout</h3>
                <button type="button" class="btn-close-modal" onclick="closeOrderModal()">x</button>
            </div>

            <div class="cart-picker-box">
                <div class="cart-picker-title">
                    Tambah Varian ke Keranjang (Bisa Coklat, Keju, Mix Sekaligus)
                </div>
                <div class="cart-picker-grid">
                    <div class="form-group form-group-compact">
                        <label class="label-sm">Varian Rasa</label>
                        <select id="pickerProduct" class="form-control">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                        data-name="{{ $p->name }}"
                                        data-medium="{{ $p->price_medium }}"
                                        data-large="{{ $p->price_large }}"
                                        data-stock="{{ $p->stock ?? 25 }}">
                                    {{ $p->name }} (Stok: {{ $p->stock ?? 25 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-group-compact">
                        <label class="label-sm">Ukuran</label>
                        <select id="pickerSize" class="form-control">
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    <div class="form-group form-group-compact">
                        <label class="label-sm">Qty</label>
                        <input type="number" id="pickerQty" class="form-control" value="1" min="1" max="25">
                    </div>
                    <button type="button" class="btn-submit-small btn-online btn-add-cart-inline" onclick="addPickerItemToCart()">
                        + Tambah
                    </button>
                </div>
            </div>

            <div id="cartItemsContainer" class="cart-items-container"></div>

            <form action="{{ route('customer.orders.store') }}" method="POST" enctype="multipart/form-data" class="form-order-catalog" onsubmit="return prepareCartSubmission()">
                @csrf
                <input type="hidden" name="cart_items" id="cartItemsInput">
                <input type="hidden" name="shipping_cost" id="shippingCostInput" value="0">
                <input type="hidden" name="distance_km" id="distanceKmInput" value="0">
                <input type="hidden" name="latitude" id="latInput">
                <input type="hidden" name="longitude" id="lngInput">
                <input type="hidden" name="earned_points" id="earnedPointsInput" value="0">

                <div class="form-group">
                    <label>Nomor HP WhatsApp yang Aktif <span class="text-danger-star">*</span></label>
                    <input type="tel" name="whatsapp_phone" id="customerWaPhone" class="form-control"
                           value="{{ $currentUser->phone ?? $currentUser->no_hp ?? $currentUser->whatsapp ?? '' }}"
                           placeholder="Contoh: 081234567890" required>
                    <small class="help-text-muted">Dipakai untuk konfirmasi &amp; verifikasi status pembayaran pesanan Anda.</small>
                </div>

                <div class="form-group">
                    <div class="location-header-row">
                        <label class="label-no-margin">Lokasi Saat Ini / Tujuan (Sumber: Kampus Unram)</label>
                        <button type="button" class="btn-layout-toggle btn-location-detect" onclick="detectCurrentLocation()">
                            Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                    <textarea name="shipping_address" id="shippingAddressInput" class="form-control" rows="2"
                              placeholder="Masukkan alamat lengkap atau klik Gunakan Lokasi Saat Ini..." required>{{ $currentUser->alamat ?? $currentUser->address ?? '' }}</textarea>
                    <small id="locationStatusText" class="location-status-text">
                        Titik acuan: Kampus Universitas Mataram (Unram). Di area Unram = Rp 0, di luar Unram = Rp 1.000 / km.
                    </small>
                </div>

                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" id="modalPaymentMethod" class="form-control" onchange="handlePaymentMethodChange()" required>
                        <option value="cod">COD (Bayar Cash di Tempat)</option>
                        <option value="qris">Scan QRIS (DANA, GoPay, OVO, ShopeePay, M-Banking)</option>
                    </select>
                </div>

                <div id="qrisPaymentBox" class="payment-instruction-box modal-hidden">
                    <div class="qris-title">
                        Scan QRIS: <span class="qris-brand">LUMBI UWUUU</span>
                    </div>
                    <p class="qris-desc">
                        Setelah transfer/scan QRIS, unggah bukti bayar di bawah &amp; pastikan nomor WA sudah benar untuk verifikasi.
                    </p>
                    <img src="{{ asset('images/qris-lumbi.jpeg') }}" alt="QRIS LUMBI UWUUU" class="qris-image-preview">
                </div>

                <div id="paymentProofBox" class="form-group payment-proof-box modal-hidden">
                    <label>Upload Bukti Pembayaran (Screenshot) <span class="text-danger-star">*</span></label>
                    <input type="file" name="payment_proof" id="modalPaymentProof" accept="image/*" class="form-control file-input-compact">
                </div>

                <div class="form-group">
                    <label>Catatan Pesanan (Opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Misal: Pagar warna hitam / Titip satpam">
                </div>

                <div class="order-summary-box">
                    <div class="summary-row">
                        <span>Subtotal Produk:</span>
                        <strong id="textSubtotal">Rp 0</strong>
                    </div>
                    <div class="summary-row">
                        <span>Jarak dari Unram:</span>
                        <strong id="textDistance">0,0 km</strong>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim (1 km = Rp 1.000):</span>
                        <strong id="textShipping">Rp 0</strong>
                    </div>
                    <div class="summary-row">
                        <span>Estimasi Poin Didapat (+1 / produk):</span>
                        <strong id="textPointsEarned">+0 Poin</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Pembayaran:</span>
                        <strong id="textGrandTotal">Rp 0</strong>
                    </div>
                </div>

                <div class="customer-modal-footer">
                    <button type="button" class="btn-cancel-edit" onclick="closeOrderModal()">Batal</button>
                    <button type="submit" class="btn-submit-small btn-online">Pesan Sekarang</button>
                </div>
            </form>
        </div>
    </div>

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
                <div class="customer-modal-footer">
                    <button type="button" class="btn-cancel-edit" onclick="closeProfileModal()">Tutup</button>
                    <button type="submit" class="btn-submit-small btn-online">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>