<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Lumpia Ubi</title>
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="auth-header">
            <div class="logo">🍠</div>
            <h2>Selamat Datang!</h2>
            <p>Masuk ke akun Lumpia Ubi kamu</p>
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
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                
                <!-- CHECKBOX SHOW PASSWORD -->
                <div class="show-password-group">
                    <input type="checkbox" id="show-password" onclick="togglePassword()">
                    <label for="show-password">Lihat Password</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">MASUK SEKARANG</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Akun Baru</a>
        </div>
    </div>

    <!-- SCRIPT TOGGLE PASSWORD -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>