<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan &amp; Rating - Admin Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ time() }}">
</head>
<body class="admin-body">

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand admin-brand-box">
                <img src="{{ asset('images/logo-lumbi.jpeg') }}" 
                     onerror="if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.jpeg') }}'; } else if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                     alt="Logo Lumbi Uwuu" 
                     class="admin-brand-logo">
                <span class="admin-brand-text">Lumbi Uwuu</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('admin.orders.index') }}">Kelola Pesanan</a></li>
                <li><a href="{{ route('admin.products.index') }}">Kelola Produk</a></li>
                <li><a href="{{ route('admin.promos.index') }}">Promo</a></li>
                <li><a href="{{ route('admin.reviews.index') }}" class="active">Ulasan &amp; Rating</a></li>
                <li><a href="{{ route('admin.couriers.index') }}">Daftar Kurir</a></li>
                <li><a href="{{ route('admin.customers.index') }}">Data Customer</a></li>
                <li><a href="{{ \Illuminate\Support\Facades\Route::has('admin.reports.index') ? route('admin.reports.index') : route('admin.orders.index') }}">Laporan</a></li>
            </ul>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </aside>

    <main class="main-content">
        <div class="header-title">
            <h2>Ulasan &amp; Rating Customer</h2>
            <p>Daftar penilaian bintang dan ulasan pembeli terhadap produk serta pengantaran.</p>
        </div>

        <div class="section-box">
            <h3>Daftar Ulasan Masuk</h3>
            <div class="table-responsive recent-orders-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Customer</th>
                            <th>Kurir</th>
                            <th>Rating</th>
                            <th>Ulasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td><strong>#ORD-{{ $order->id }}</strong></td>
                                <td>{{ $order->customer_name ?? ($order->user->name ?? 'Customer') }}</td>
                                <td>{{ $order->courier_name ?? '-' }}</td>
                                <td>
                                    @if(!empty($order->rating))
                                        <span class="admin-rating-text">{{ $order->rating }} / 5</span>
                                    @else
                                        <span class="admin-unassigned-text">Belum ada rating</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($order->review))
                                        "{{ $order->review }}"
                                    @else
                                        <span class="admin-unassigned-text">Belum ada ulasan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-table-empty">
                                    Belum ada ulasan maupun rating dari customer.
                                </td>
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