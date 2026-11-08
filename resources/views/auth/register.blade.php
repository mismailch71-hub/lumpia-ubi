<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <div class="logo">🍠</div>
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
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap" required autofocus>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" id="register_password" name="password" placeholder="Minimal 8 karakter" required>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" id="register_password_confirm" name="password_confirmation" placeholder="Ulangi password" required>
                
                <!-- CHECKBOX SHOW PASSWORD -->
                <div class="show-password-group">
                    <input type="checkbox" id="show-register-password" onclick="toggleRegisterPassword()">
                    <label for="show-register-password">Lihat Password</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">DAFTAR SEKARANG</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>

    <!-- SCRIPT TOGGLE PASSWORD REGISTER -->
    <script>
        function toggleRegisterPassword() {
            const pass = document.getElementById('register_password');
            const passConfirm = document.getElementById('register_password_confirm');
            const isText = pass.type === 'text';
            
            pass.type = isText ? 'password' : 'text';
            passConfirm.type = isText ? 'password' : 'text';
        }
    </script>
</body>
</html>