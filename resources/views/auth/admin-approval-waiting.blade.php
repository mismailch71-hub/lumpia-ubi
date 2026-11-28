<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Persetujuan Email - Lumbi Uwuu</title>
    <link rel="stylesheet" href="{{ asset('css/admin-style.css') }}?v={{ time() }}">
</head>
<body class="approval-waiting-body">
    <div id="adminApprovalWaiting"
         class="approval-waiting-card"
         data-status-url="{{ route('admin.login.status', ['token' => $token]) }}">
        <h2 class="approval-waiting-title">Menunggu Persetujuan Email</h2>
        <p id="statusMessage" class="approval-waiting-message">
            Tautan persetujuan login telah dikirim ke email Anda. Silakan buka email dan klik <strong>Setujui Akses Login</strong>.
        </p>
        <p class="approval-waiting-expiry">
            Berlaku sampai: {{ \Carbon\Carbon::parse($expiresAt)->format('d M Y H:i:s') }}
        </p>
        <div class="approval-waiting-footer">
            <a href="{{ route('login') }}" class="approval-waiting-back-link">Kembali ke Halaman Login</a>
        </div>
    </div>

    <script src="{{ asset('js/admin-script.js') }}?v={{ time() }}"></script>
</body>
</html>