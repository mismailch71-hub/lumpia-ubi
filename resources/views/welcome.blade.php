<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumpia Ubi Store - Toko Online</title>
    
    <!-- MENGGUNAKAN ARSITEKTUR CSS SERAGAM DENGAN MODUL CUSTOMER -->
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/courier-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/customer-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- HEADER ATAS KHUSUS TAMPILAN HP -->
    <div class="mobile-top-header">
        <span>🥟</span> Lumpia Ubi Store
    </div>

    <!-- SIDEBAR / NAVIGASI UTAMA -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <span>🥟</span> Lumpia Ubi Store
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ url('/') }}" class="active">🏠 Beranda & Katalog</a></li>
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <li><a href="{{ route('admin.dashboard') }}">⚙️ Dashboard Admin</a></li>
                    @elseif(auth()->user()->hasRole('kurir'))
                        <li><a href="{{ route('kurir.dashboard') }}">🛵 Dashboard Kurir</a></li>
                    @else
                        <li><a href="{{ route('customer.catalog') }}">🛍️ Katalog Member</a></li>
                        <li><a href="{{ route('customer.orders.index') }}">📦 Pesanan Saya</a></li>
                    @endif
                @else
                    <li><a href="{{ route('login') }}">🔑 Masuk / Login</a></li>
                    @if (Route::has('register'))
                        <li><a href="{{ route('register') }}">📝 Daftar Akun</a></li>
                    @endif
                @endauth
            </ul>
        </div>

        @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
            </form>
        @else
            <div style="padding: 10px 0;">
                <a href="{{ route('login') }}" class="btn-submit-small btn-online" style="display: block; text-align: center; text-decoration: none;">
                    🛒 Mulai Pesan
                </a>
            </div>
        @endauth
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <!-- HEADER DENGAN TOMBOL GANTI POSISI SIDEBAR -->
        <div class="header-flex">
            <div class="header-title" style="margin-bottom: 0;">
                <h2>🥟 Lumpia Ubi Renyah & Manis</h2>
                <p>Dibuat segar setiap hari dengan lelehan topping melimpah, siap diantar hangat ke tempatmu!</p>
            </div>

            <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                ⬇️ Pindah Menu ke Bawah
            </button>
        </div>

        <!-- HERO BANNER PROMO -->
        <div class="customer-order-card" style="background: linear-gradient(135deg, #2e073f 0%, #6b21a8 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-top: 16px;">
            <div style="flex: 1; min-width: 240px;">
                <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    🔥 Freshly Made Every Day
                </span>
                <h3 style="margin: 10px 0 6px 0; font-size: 20px;">Nikmati Kelezatan Lumpia Ubi Lumer!</h3>
                <p style="margin: 0; font-size: 13px; color: #f3e8ff; line-height: 1.5;">
                    Tersedia pilihan porsi <strong>Medium (4+1)</strong> dan <strong>Large (6+2)</strong>. Pesan sekarang dan pantau pengantaran kurir secara langsung.
                </p>
            </div>
            <div>
                <img src="{{ asset('images/lumpia-default.jpg') }}" 
                     onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'" 
                     alt="Lumpia Ubi Utama" 
                     style="width: 130px; height: 95px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255,255,255,0.3);">
            </div>
        </div>

        <!-- KATALOG PRODUK 2 KOLOM -->
        <div class="header-title" style="margin-top: 24px;">
            <h3 style="margin: 0; color: #1e293b;">🛍️ Pilihan Varian Lumpia Ubi</h3>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Pilih varian rasa dan ukuran porsi sesuai selera kamu.</p>
        </div>

        <div class="product-grid">
            @forelse($products as $product)
                @php
                    $imgUrl = (!empty($product->image) && file_exists(public_path('storage/' . $product->image))) 
                        ? asset('storage/' . $product->image) 
                        : asset('images/lumpia-default.jpg');
                @endphp
                <div class="product-card">
                    <!-- GAMBAR PRODUK -->
                    <div class="product-image-box">
                        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'">
                    </div>

                    <div class="product-info">
                        <h3 class="product-card-title">{{ $product->name }}</h3>
                        <p class="product-card-desc">{{ $product->description ?? 'Sensasi kulit renyah di luar dengan isian ubi lembut dan lumer di dalam.' }}</p>
                        
                        <div class="product-price-box">
                            <span class="price-tag">Medium (4+1): Rp {{ number_format($product->price_medium, 0, ',', '.') }}</span>
                            <span class="price-tag">Large (6+2): Rp {{ number_format($product->price_large, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI: JIKA SUDAH LOGIN BUKA MODAL, JIKA BELUM ARAHKAN KE LOGIN -->
                    @auth
                        <button type="button" class="btn-submit-small btn-online btn-order-trigger" 
                                onclick="openOrderModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ addslashes($product->description ?? '') }}', '{{ $product->price_medium }}', '{{ $product->price_large }}', '{{ $imgUrl }}')">
                            🛒 Pesan Sekarang
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn-submit-small btn-online btn-order-trigger" style="display: block; text-align: center; text-decoration: none;">
                            🔑 Masuk untuk Memesan
                        </a>
                    @endauth
                </div>
            @empty
                <div class="empty-state-box" style="grid-column: 1 / -1;">
                    <p>Belum ada varian Lumpia Ubi yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>

        <!-- FOOTER -->
        <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 13px;">
            <p style="margin: 0;">&copy; {{ date('Y') }} Lumpia Ubi Store. Hak Cipta Dilindungi.</p>
        </footer>
    </main>

    <!-- MODAL POPUP CHECKOUT (AKTIF JIKA USER SUDAH LOGIN) -->
    @auth
        <div id="orderModal" class="modal-backdrop" style="display: none;">
            <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
                <div class="modal-header">
                    <h3>🛒 Konfirmasi Checkout</h3>
                    <button type="button" class="btn-close-modal" onclick="closeOrderModal()">✖</button>
                </div>
                
                <div class="modal-product-summary">
                    <img id="modalProductImg" src="{{ asset('images/lumpia-default.jpg') }}" onerror="this.src='{{ asset('images/lumpia-default.jpeg') }}'" alt="Produk" class="modal-product-img">
                    <div class="modal-product-detail">
                        <h4 id="modalProductName">Nama Produk</h4>
                        <p id="modalProductDesc">Deskripsi singkat produk...</p>
                    </div>
                </div>

                <form action="{{ route('customer.orders.store') }}" method="POST" enctype="multipart/form-data" class="form-order-catalog">
                    @csrf
                    <input type="hidden" name="product_id" id="modalProductId">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label>Varian Ukuran</label>
                            <select name="variant" id="modalVariant" class="form-control" onchange="calculateTotal()" required>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jumlah Pesan</label>
                            <input type="number" name="quantity" id="modalQuantity" value="1" min="1" class="form-control" oninput="calculateTotal()" onchange="calculateTotal()" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Pengiriman</label>
                        <textarea name="shipping_address" class="form-control" rows="2" placeholder="Masukkan alamat lengkap pengiriman..." required>{{ auth()->user()->alamat ?? auth()->user()->address ?? '' }}</textarea>
                    </div>

                    <!-- METODE PEMBAYARAN (COD & QRIS SAJA) -->
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="payment_method" id="modalPaymentMethod" class="form-control" onchange="handlePaymentMethodChange()" required>
                            <option value="cod">💵 COD (Bayar Cash di Tempat)</option>
                            <option value="qris">📱 Scan QRIS (DANA, GoPay, OVO, ShopeePay, M-Banking)</option>
                        </select>
                    </div>

                    <!-- KOTAK SCAN QRIS (MUNCUL OTOMATIS JIKA PILIH QRIS) -->
                    <div id="qrisPaymentBox" class="payment-instruction-box" style="display: none;">
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                            Scan QRIS: <span style="color: #6b21a8;">LUMBI UWUUU</span>
                        </div>
                        <p style="font-size: 11px; color: #64748b; margin: 4px 0;">
                            Buka aplikasi DANA / GoPay / OVO / ShopeePay / M-Banking lalu scan kode di bawah ini:
                        </p>
                        <img src="{{ asset('images/qris-lumbi.jpeg') }}" alt="QRIS LUMBI UWUUU" class="qris-image-preview">
                        <a href="{{ asset('images/qris-lumbi.jpeg') }}" download="QRIS-LUMBI-UWUUU.jpg" style="font-size: 11px; color: #6b21a8; font-weight: 600; text-decoration: underline;">
                            ⬇️ Unduh Gambar QRIS (Jika scan dari galeri HP)
                        </a>
                    </div>

                    <!-- UPLOAD BUKTI PEMBAYARAN (WAJIB UNTUK QRIS) -->
                    <div id="paymentProofBox" class="form-group" style="display: none; margin-top: 10px;">
                        <label>Upload Bukti Pembayaran (Screenshot) <span style="color: #dc2626;">*</span></label>
                        <input type="file" name="payment_proof" id="modalPaymentProof" accept="image/*" class="form-control" style="padding: 6px;">
                        <small style="color: #64748b; font-size: 11px;">Wajib unggah screenshot bukti pembayaran QRIS agar pesanan segera diproses.</small>
                    </div>

                    <div class="form-group">
                        <label>Catatan Pesanan (Opsional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="Misal: Pagar warna hitam">
                    </div>

                    <div class="order-summary-box">
                        <div class="summary-row">
                            <span>Subtotal Produk:</span>
                            <strong id="textSubtotal">Rp 0</strong>
                        </div>
                        <div class="summary-row">
                            <span>Ongkos Kirim (Flat):</span>
                            <strong id="textShipping">Rp 5.000</strong>
                        </div>
                        <div class="summary-row total">
                            <span>Total Pembayaran:</span>
                            <strong id="textGrandTotal">Rp 0</strong>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 15px;">
                        <button type="button" class="btn-cancel-edit" onclick="closeOrderModal()">Batal</button>
                        <button type="submit" class="btn-submit-small btn-online">Buat Pesanan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>