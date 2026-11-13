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
                <li><a href="{{ route('customer.catalog') }}" class="active">🛍️ Katalog Produk</a></li>
                <li><a href="{{ route('customer.orders.index') }}">📦 Pesanan Saya</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">🚪 Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <!-- HEADER DENGAN TOMBOL GANTI POSISI SIDEBAR -->
        <div class="header-flex">
            <div class="header-title" style="margin-bottom: 0;">
                <h2>🛍️ Katalog Lumpia Ubi</h2>
                <p>Pilih varian Lumpia Ubi favorit Anda dan pesan dengan cepat.</p>
            </div>

            <button type="button" id="btnToggleLayout" class="btn-layout-toggle" onclick="toggleSidebarPosition()">
                ⬇️ Pindah Menu ke Bawah
            </button>
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
                        <p class="product-card-desc">{{ $product->description ?? 'Lumpia Ubi renyah dengan isian manis/gurih yang melumer di mulut.' }}</p>
                        
                        <div class="product-price-box">
                            <span class="price-tag">Medium: Rp {{ number_format($product->price_medium, 0, ',', '.') }}</span>
                            <span class="price-tag">Large: Rp {{ number_format($product->price_large, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- TOMBOL BUKA MODAL PESAN -->
                    <button type="button" class="btn-submit-small btn-online btn-order-trigger" 
                            onclick="openOrderModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ addslashes($product->description ?? '') }}', '{{ $product->price_medium }}', '{{ $product->price_large }}', '{{ $imgUrl }}')">
                        🛒 Pesan Sekarang
                    </button>
                </div>
            @empty
                <div class="empty-state-box" style="grid-column: 1 / -1;">
                    <p>Belum ada produk yang tersedia di katalog saat ini.</p>
                </div>
            @endforelse
        </div>
    </main>

    <!-- MODAL POPUP CHECKOUT INTERAKSI GOFOOD STYLE -->
    <div id="orderModal" class="modal-backdrop" style="display: none;">
        <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3>🛒 Konfirmasi Checkout</h3>
                <button type="button" class="btn-close-modal" onclick="closeOrderModal()">✖</button>
            </div>
            
            <!-- RINGKASAN PRODUK & FOTO -->
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
                    <!-- PILIH VARIAN UKURAN -->
                    <div class="form-group">
                        <label>Varian Ukuran</label>
                        <select name="variant" id="modalVariant" class="form-control" onchange="calculateTotal()" required>
                            <!-- Dynamic via JS -->
                        </select>
                    </div>

                    <!-- JUMLAH PESAN -->
                    <div class="form-group">
                        <label>Jumlah Pesan</label>
                        <input type="number" name="quantity" id="modalQuantity" value="1" min="1" class="form-control" oninput="calculateTotal()" onchange="calculateTotal()" required>
                    </div>
                </div>

                <!-- ALAMAT PENGIRIMAN (AUTO-FILL DARI PROFILE) -->
                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea name="shipping_address" class="form-control" rows="2" placeholder="Masukkan alamat lengkap pengiriman..." required>{{ $user->alamat ?? $user->address ?? '' }}</textarea>
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
                    <input type="text" name="notes" class="form-control" placeholder="Misal: Pagar warna hitam / Tanpa cabe">
                </div>

                <!-- RINGKASAN BIAYA TRANSAKSI REAL-TIME -->
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

    <script src="{{ asset('js/customer-script.js') }}?v={{ time() }}"></script>
</body>
</html>