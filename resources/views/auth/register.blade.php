<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Lumbi Uwuu Store</title>
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}?v={{ time() }}">
</head>
<body class="auth-page">

    <div class="auth-card">
       <div class="auth-header">
            <img src="{{ asset('images/logo-lumbi.jpeg') }}"
                 onerror="if (this.src.endsWith('.jpeg')) { this.src='{{ asset('images/logo-lumbi.jpg') }}'; } else if (this.src.endsWith('.jpg')) { this.src='{{ asset('images/logo-lumbi.png') }}'; }"
                 alt="Logo Lumbi Uwuu"
                 class="auth-brand-logo">
            <h2>Daftar Akun</h2>
            <p>Buat akun untuk memesan Lumpia Ubi</p>
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

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="register_name">Nama Lengkap</label>
                <input type="text" id="register_name" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap" required autofocus>
            </div>

            <div class="form-group">
                <label for="register_email">Email</label>
                <input type="email" id="register_email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
            </div>

            <div class="form-group">
                <label for="register_password">Password</label>
                <input type="password" id="register_password" name="password" placeholder="Minimal 8 karakter" required>
            </div>

            <div class="form-group">
                <label for="register_password_confirm">Konfirmasi Password</label>
                <input type="password" id="register_password_confirm" name="password_confirmation" placeholder="Ulangi password" required>

                <div class="show-password-group">
                    <input type="checkbox" id="show-register-password">
                    <label for="show-register-password">Lihat Password</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">DAFTAR SEKARANG</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>

    <script src="{{ asset('js/auth-script.js') }}?v={{ time() }}"></script>
</body>
</html>