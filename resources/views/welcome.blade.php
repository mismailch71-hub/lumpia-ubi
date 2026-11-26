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
        <img src="{{ asset('images/logo-lumbi.jpeg') }}"
             onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
             alt="Logo Lumbi Uwuu"
             style="height: 26px; width: auto; object-fit: contain; vertical-align: middle; margin-right: 8px;">
        <span>Lumbi Uwuu Store</span>
    </div>

    <!-- SIDEBAR / NAVIGASI UTAMA -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand" style="display: flex; align-items: center; gap: 8px;">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     style="height: 30px; width: auto; object-fit: contain;">
                <span>Lumbi Uwuu Store</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ url('/') }}" class="active">Beranda</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
                @if (Route::has('register'))
                    <li><a href="{{ route('register') }}">Daftar Akun</a></li>
                @endif
            </ul>
        </div>

        @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        @else
            <div style="padding: 10px 0;">
                <a href="{{ route('login') }}"
                   class="btn-submit-small btn-online btn-mulai-pesan"
                   style="display: block; text-align: center; text-decoration: none; background-color: #f59e0b; color: #2e0743; padding: 10px 14px; border-radius: 10px; font-weight: 800;">
                    Mulai Pesan
                </a>
            </div>
        @endauth
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <!-- TOMBOL GANTI POSISI SIDEBAR -->
        <div class="header-flex" style="justify-content: flex-end;">
            <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                Pindah Menu ke Bawah
            </button>
        </div>

        <!-- HERO BANNER PROMO (GABUNGAN TEKS & GAMBAR DI ATAS) -->
        <div class="customer-order-card" style="background: linear-gradient(135deg, #2e073f 0%, #6b21a8 100%); color: #ffffff; display: flex; flex-direction: column; align-items: flex-start; gap: 14px; margin-top: 12px;">
            <div style="width: 100%; max-width: 520px; margin: 0 auto;">
                <img src="{{ asset('images/lumpia-banner.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/lumpia-default.jpeg') }}'; } else { this.src='{{ asset('images/lumpia-default.jpg') }}'; }"
                     alt="Lumpia Ubi Utama"
                     style="width: 100%; aspect-ratio: 16 / 9; max-height: 280px; object-fit: cover; border-radius: 12px; border: 2px solid rgba(255,255,255,0.25); display: block;">
            </div>
            <div>
                <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block;">
                    Freshly Made Every Day
                </span>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #ffffff; line-height: 1.6; font-weight: 500;">
                    Waktunya ngemil enak bareng <strong>Lumpia Ubi Lumer</strong>! Bebas pilih ukuran sesuai selera, mulai dari <strong>Medium (4+1)</strong> sampai <strong>Large (6+2)</strong> yang lebih puas. Pesan sekarang juga, kamu tinggal duduk manis sambil pantau perjalanan pesananmu hingga tiba!
                </p>
            </div>
        </div>

        <!-- KATALOG PRODUK 2 KOLOM -->
        <div class="header-title" style="margin-top: 24px;">
            <h3 style="margin: 0; color: #1e293b;">Pilihan Varian Lumpia Ubi</h3>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b;">Pilih varian rasa dan ukuran porsi sesuai selera kamu.</p>
        </div>

        <div class="product-grid">
            @forelse($products as $product)
                @php
                    $imgUrl = (!empty($product->image) && file_exists(public_path('storage/' . $product->image)))
                        ? asset('storage/' . $product->image)
                        : asset('images/lumpia-default.jpeg');
                @endphp
                <div class="product-card">
                    <!-- GAMBAR PRODUK -->
                    <div class="product-image-box">
                        <img src="{{ $imgUrl }}" alt="{{ $product->name }}" onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/lumpia-default.jpg') }}'; }">
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
                            Pesan Sekarang
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="btn-submit-small btn-online btn-order-trigger"
                           style="display: block; text-align: center; text-decoration: none; background-color: #6b21a8; color: #ffffff; padding: 10px 14px; border-radius: 10px; font-weight: 700; margin-top: 10px;">
                            Masuk untuk Memesan
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
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 13px;">
            <p style="margin: 0 0 8px 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                <span>Ikuti &amp; pantau info di Instagram:</span>
                <a href="https://www.instagram.com/lumbi_uwuu/" target="_blank" rel="noopener noreferrer"
                    style="display: inline-flex; align-items: center; gap: 5px; color: #6b21a8; font-weight: 700; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
                    </svg>
                    <span>@lumbi_uwuu</span>
                </a>
            </p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Lumbi Uwuu Store. Hak Cipta Dilindungi.</p>
        </div>
    </main>

    <!-- MODAL POPUP CHECKOUT (AKTIF JIKA USER SUDAH LOGIN) -->
    @auth
        <div id="orderModal" class="modal-backdrop" style="display: none;">
            <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
                <div class="modal-header">
                    <h3>Konfirmasi Checkout</h3>
                    <button type="button" class="btn-close-modal" onclick="closeOrderModal()">x</button>
                </div>

                <div class="modal-product-summary">
                    <img id="modalProductImg" src="{{ asset('images/lumpia-default.jpeg') }}" onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/lumpia-default.jpg') }}'; }" alt="Produk" class="modal-product-img">
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
                            <option value="cod">COD (Bayar Cash di Tempat)</option>
                            <option value="qris">Scan QRIS (DANA, GoPay, OVO, ShopeePay, M-Banking)</option>
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
                            Unduh Gambar QRIS (Jika scan dari galeri HP)
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