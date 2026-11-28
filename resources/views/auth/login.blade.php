<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Lumbi Uwuu Store</title>
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}?v={{ time() }}">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                 onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                 alt="Logo Lumbi Uwuu"
                 class="auth-brand-logo">
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun Lumbi Uwuu kamu</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="login_email">Email</label>
                <input type="email" id="login_email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <div class="show-password-group">
                    <input type="checkbox" id="show-password">
                    <label for="show-password">Lihat Password</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">MASUK SEKARANG</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Akun Baru</a>
        </div>
    </div>

    <script src="{{ asset('js/auth-script.js') }}?v={{ time() }}"></script>
</body>
</html>