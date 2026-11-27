<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Lumpia Ubi Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <!-- SIDEBAR NAVIGASI TUNGGAL -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                Lumpia Ubi Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Kelola Produk</a></li>
                <li><a href="{{ route('admin.couriers.index') }}" class="{{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}">Daftar Kurir</a></li>
                <li><a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">Data Customer</a></li>
                <li><a href="#" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Keluar / Logout</button>
        </form>
    </aside>

    <!-- KONTEN UTAMA TUNGGAL -->
    <main class="main-content">
        <div class="header-title">
            <h2>Kelola Pesanan Customer</h2>
            <p>Verifikasi bukti pembayaran QRIS, atur penugasan kurir, dan pantau status transaksi pemesanan.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #fecaca;">
                {{ session('error') }}
            </div>
        @endif

        <div class="section-box">
            <!-- BARIS FILTER & CARI -->
            <div class="filter-bar">
                <h3 style="margin: 0;">Daftar Seluruh Pesanan Masuk</h3>
                
                <form action="{{ route('admin.orders.index') }}" method="GET" class="filter-group">
                    <input type="text" name="search" class="input-search" placeholder="Cari customer / ID..." value="{{ request('search') }}">
                    
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi QRIS</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="dalam_pengiriman" {{ request('status') == 'dalam_pengiriman' ? 'selected' : '' }}>Dalam Pengiriman</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>

                    <button type="submit" class="btn-submit-small">Cari</button>

                    @if(request('status') || request('search'))
                        <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="padding: 6px 12px; font-size: 11px;">Reset</a>
                    @endif
                </form>
            </div>

            <!-- TABEL PESANAN RINGKAS -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">ID Order</th>
                            <th style="width: 26%;">Customer &amp; Alamat</th>
                            <th style="width: 20%;">Total &amp; Bukti Bayar</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 25%;">Verifikasi / Penugasan Kurir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <strong>#ORD-{{ $order->id }}</strong><br>
                                    <button type="button" class="btn-detail-small" onclick="openModal('modal-{{ $order->id }}')">Detail</button>
                                </td>
                                <td>
                                    <strong>{{ $order->customer_name }}</strong><br>
                                    <span class="address-text">{{ $order->shipping_address }}</span>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong><br>
                                    @if(strtolower($order->payment_method) == 'qris')
                                        <span style="display: inline-block; margin-top: 4px; background: #f3e8ff; color: #6b21a8; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: bold;">
                                            QRIS
                                        </span>
                                        @if(!empty($order->payment_proof))
                                            <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" title="Klik untuk perbesar bukti QRIS">
                                                    <img src="{{ asset('storage/' . $order->payment_proof) }}" 
                                                         alt="Bukti QRIS" 
                                                         style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                                                </a>
                                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" style="font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: underline;">
                                                    Lihat Bukti
                                                </a>
                                            </div>
                                        @else
                                            <div style="font-size: 11px; color: #dc2626; margin-top: 4px;">Tanpa bukti</div>
                                        @endif
                                    @else
                                        <span style="display: inline-block; margin-top: 4px; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: bold;">
                                            COD
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'menunggu_verifikasi')
                                        <span class="badge-order" style="background: #fef3c7; color: #b45309;">Cek Bukti QRIS</span>
                                    @elseif($order->status == 'pending')
                                        <span class="badge-order badge-pending">Menunggu Konfirmasi</span>
                                    @elseif($order->status == 'diproses')
                                        <span class="badge-order" style="background: #e0f2fe; color: #0369a1;">Diproses</span>
                                    @elseif($order->status == 'dalam_pengiriman')
                                        <span class="badge-order badge-process">Dalam Pengiriman</span>
                                    @elseif($order->status == 'selesai')
                                        <span class="badge-order badge-success">Selesai</span>
                                    @elseif($order->status == 'ditolak')
                                        <span class="badge-order badge-canceled">Bukti Ditolak</span>
                                    @elseif($order->status == 'dibatalkan')
                                        <span class="badge-order badge-canceled">Dibatalkan</span>
                                    @else
                                        <span class="badge-order" style="background: #f1f5f9; color: #475569;">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'menunggu_verifikasi')
                                        <!-- TOMBOL KONFIRMASI / TOLAK BUKTI QRIS -->
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                            <form action="{{ route('admin.orders.verify', $order->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Yakin saldo QRIS Rp {{ number_format($order->total_price, 0, ',', '.') }} sudah masuk dan bukti valid?')">
                                                @csrf
                                                <button type="submit" style="background: #16a34a; color: #ffffff; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; width: 100%;">
                                                    Valid / Lunas
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.orders.reject', $order->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Tolak pesanan ini karena bukti pembayaran tidak valid/palsu?')">
                                                @csrf
                                                <button type="submit" style="background: #dc2626; color: #ffffff; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; width: 100%;">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @elseif(in_array($order->status, ['ditolak', 'dibatalkan']))
                                        <span style="font-size: 12px; color: #94a3b8; font-style: italic;">Pesanan dihentikan</span>
                                    @else
                                        <!-- FORM TUNGGAL: ASSIGN KURIR -->
                                        <form action="{{ route('admin.orders.assign-kurir', $order->id) }}" method="POST" class="flex-form">
                                            @csrf
                                            <select name="courier_id" class="form-select" style="width: 100%;" required>
                                                <option value="">-- Pilih Kurir --</option>
                                                @foreach($couriers as $courier)
                                                    @php
                                                        $cleanCourierName = trim(str_replace(['Kurir ', '(Kurir 1)', '(Kurir 2)', '(Kurir 3)'], '', $courier->name));
                                                    @endphp
                                                    <option value="{{ $courier->id }}" {{ $order->courier_id == $courier->id ? 'selected' : '' }}>
                                                        Kurir {{ $cleanCourierName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn-submit-small">
                                                {{ $order->courier_id ? 'Ubah' : 'Tugaskan' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-row">Belum ada pesanan transaksi masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL POPUP RINCIAN PESANAN & BUKTI TRANSFER -->
    @foreach($orders as $order)
        <div id="modal-{{ $order->id }}" class="modal-overlay">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Rincian Pesanan #ORD-{{ $order->id }}</h3>
                    <button type="button" class="btn-close-modal" onclick="closeModal('modal-{{ $order->id }}')" aria-label="Tutup">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 6px;"><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_email }})</p>
                    <p style="margin-bottom: 6px;"><strong>Alamat Pengiriman:</strong> {{ $order->shipping_address }}</p>
                    <p style="margin-bottom: 6px;"><strong>Metode Bayar:</strong> {{ strtoupper($order->payment_method ?? 'COD') }}</p>
                    <p style="margin-bottom: 12px;"><strong>Kurir Pengantar:</strong> {{ $order->courier_name ?? 'Belum Ditugaskan' }}</p>

                    <!-- PREVIEW BUKTI PEMBAYARAN DI DALAM MODAL -->
                    @if(strtolower($order->payment_method) == 'qris' && !empty($order->payment_proof))
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 14px; text-align: center;">
                            <div style="font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Bukti Pembayaran QRIS Customer:</div>
                            <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti Pembayaran" style="max-width: 100%; max-height: 240px; border-radius: 6px; border: 1px solid #cbd5e1;">
                            </a>
                            <div style="margin-top: 8px;">
                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" style="font-size: 12px; color: #2563eb; font-weight: 600; text-decoration: underline;">
                                    Buka Ukuran Penuh di Tab Baru
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    <h4 style="margin-top: 14px; margin-bottom: 8px; color: #1e293b; font-size: 14px;">Produk yang Dipesan:</h4>
                    <table class="item-list-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Jumlah</th>
                                <th>Harga Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }} Porsi</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#94a3b8;">Tidak ada rincian item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div style="text-align: right; font-size: 14px; margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <strong>Total Pembayaran: <span style="color: #7e22ce;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></strong>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/admin-orders.js') }}?v={{ time() }}"></script>
</body>
</html>