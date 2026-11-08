<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumpia Ubi - Toko Online</title>
    
    <!-- MEMANGGIL FILE CSS UNGU TERPISAH -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <!-- NAVBAR UNGU -->
    <header class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand">
                LUMPIA UBI
            </a>

            <div>
                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="btn-nav-auth">Dashboard Admin</a>
                        @elseif(auth()->user()->hasRole('kurir'))
                            <a href="{{ route('kurir.dashboard') }}" class="btn-nav-auth">Dashboard Kurir</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn-nav-auth">Dashboard Saya</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" style="padding: 6px 12px; color: #ffffff; font-weight: bold; font-size: 13px; text-decoration: none;">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-nav-auth">Daftar</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- HERO SECTION BANNER -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-text">
                <h1>LUMPIA UBI RENYAH & MANIS</h1>
                <p>Sensasi kulit renyah di luar dengan isian ubi manis & lelehan topping melimpah di dalam. Dibuat segar setiap hari, siap diantar hangat ke tempatmu!</p>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/lumpia.webp') }}" alt="Lumpia Ubi Utama" onerror="this.src='https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=500&q=80'">
            </div>
        </div>
    </section>

    <!-- KATALOG PRODUK (DINAMIS DARI DATABASE) -->
    <section id="katalog" class="katalog">
        <div class="katalog-header">
            <h2>PILIHAN VARIAN LUMPIA UBI</h2>
            <p>Pilih varian rasa dan porsi sesuai selera kamu.</p>
        </div>

        <div class="product-grid">

            @forelse($products as $product)
                <!-- KOTAK PRODUK DINAMIS -->
                <div class="product-card">
                    <div>
                        <div class="product-img">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            @else
                                <img src="{{ asset('images/lumpia.webp') }}" alt="{{ $product->name }}" onerror="this.src='https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=500&q=80'">
                            @endif
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name }}</h3>
                            <p class="product-desc">{{ $product->description ?? 'Sensasi renyah dan nikmat.' }}</p>
                            
                            <!-- PILIHAN HARGA DINAMIS (SEDANG & BESAR) -->
                            <div class="portion-list">
                                <div class="portion-item">
                                    <span class="portion-name">Sedang (4+1)</span>
                                    <span class="portion-price">Rp {{ number_format($product->price_medium, 0, ',', '.') }}</span>
                                </div>
                                <div class="portion-item">
                                    <span class="portion-name">Besar (6+2)</span>
                                    <span class="portion-price">Rp {{ number_format($product->price_large, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-action">
                        <a href="{{ route('login') }}" class="btn-order">
                            Pesan {{ str_replace(['Lumpia Ubi ', 'Lumpia '], '', $product->name) }}
                        </a>
                    </div>
                </div>
            @empty
                <!-- TAMPILAN JIKA BELUM ADA DATA PRODUK DI DATABASE -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280;">
                    <p style="font-style: italic;">Belum ada varian Lumpia Ubi yang tersedia saat ini.</p>
                </div>
            @endforelse

        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p style="margin: 0;">&copy; {{ date('Y') }} Lumpia Ubi Online. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>