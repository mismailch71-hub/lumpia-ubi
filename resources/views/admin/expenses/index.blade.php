<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - Lumbi Uwuu Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand admin-brand-box">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                     onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu"
                     class="admin-brand-logo">
                <span class="admin-brand-text">Admin Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Kelola Produk</a></li>
                <li><a href="{{ route('admin.expenses.index') }}" class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">Pengeluaran</a></li>
                <li><a href="{{ route('admin.promos.index') }}" class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}">Promo</a></li>
                <li><a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Ulasan &amp; Rating</a></li>
                <li><a href="{{ route('admin.couriers.index') }}" class="{{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}">Daftar Kurir</a></li>
                <li><a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">Data Customer</a></li>
                <li><a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <main class="main-content">
        <div class="header-title">
            <h2>Pengeluaran Operasional (Bahan Baku &amp; Biaya Produksi)</h2>
            <p>Catat pembelian bahan baku (ubi, kulit lumpia, coklat, keju) serta biaya produksi (minyak goreng, gas, kemasan).</p>
        </div>

        @if(session('success'))
            <div class="alert-success" style="margin-bottom: 18px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 13px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="expenses-stats-grid">
            <div class="stat-card">
                <div class="info">
                    <h3>Total Bahan Baku</h3>
                    <p>Rp {{ number_format($totalBahanBaku ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="info">
                    <h3>Total Biaya Produksi</h3>
                    <p>Rp {{ number_format($totalBiayaProduksi ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="info">
                    <h3>Total Pengeluaran</h3>
                    <p>Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="section-box expenses-form-box">
            <h3 class="expenses-form-title">Catat Pengeluaran Baru</h3>
            <form action="{{ route('admin.expenses.store') }}" method="POST" class="expenses-form-grid">
                @csrf
                <div class="form-group expenses-field-group">
                    <label>Jenis Pengeluaran</label>
                    <select name="category" class="form-select" required>
                        <option value="bahan_baku">Bahan Baku</option>
                        <option value="biaya_produksi">Biaya Produksi</option>
                    </select>
                </div>
                <div class="form-group expenses-field-group">
                    <label>Nama Item / Keperluan</label>
                    <input type="text" name="item_name" class="form-control" placeholder="Misal: Ubi jalar 10 kg / Minyak goreng" required>
                </div>
                <div class="form-group expenses-field-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" placeholder="50000" required>
                </div>
                <div class="form-group expenses-field-group">
                    <label>Tanggal</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group expenses-field-group">
                    <label>Catatan (Opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Keterangan tambahan">
                </div>
                <div class="expenses-submit-wrap">
                    <button type="submit" class="btn-submit-small expenses-submit-btn">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>

        <div class="section-box">
            <div class="expenses-history-bar">
                <h3 class="expenses-history-title">Riwayat Pengeluaran</h3>
                <form action="{{ route('admin.expenses.index') }}" method="GET" class="expenses-filter-controls">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Jenis --</option>
                        <option value="bahan_baku" {{ request('category') == 'bahan_baku' ? 'selected' : '' }}>Bahan Baku</option>
                        <option value="biaya_produksi" {{ request('category') == 'biaya_produksi' ? 'selected' : '' }}>Biaya Produksi</option>
                    </select>
                    @if(request('category'))
                        <a href="{{ route('admin.expenses.index') }}" class="btn-secondary" style="padding: 6px 12px; font-size: 11px;">Reset</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Nama Item</th>
                            <th>Catatan</th>
                            <th style="text-align: right;">Nominal</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                            <tr>
                                <td>{{ date('d M Y', strtotime($exp->expense_date)) }}</td>
                                <td>
                                    @if($exp->category === 'bahan_baku')
                                        <span class="badge-order" style="background: #fef3c7; color: #92400e;">Bahan Baku</span>
                                    @else
                                        <span class="badge-order" style="background: #e0f2fe; color: #075985;">Biaya Produksi</span>
                                    @endif
                                </td>
                                <td><strong>{{ $exp->item_name }}</strong></td>
                                <td>{{ $exp->notes ?: '-' }}</td>
                                <td style="text-align: right;"><strong>Rp {{ number_format($exp->amount, 0, ',', '.') }}</strong></td>
                                <td style="text-align: center;">
                                    <form action="{{ route('admin.expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-submit-small" style="background: #dc2626; color: #fff;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">Belum ada catatan pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>