<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Maintenance — Lumbi Uwuu</title>
    <link rel="stylesheet" href="{{ asset('css/maintenance.css') }}?v={{ time() }}">
</head>
<body class="maintenance-body">
    <main class="maintenance-card">
        <div class="brand-logo-box">
            <img id="maintenanceLogo"
                 src="{{ asset('images/logo-lumbi.jpg') }}"
                 data-fallbacks='@json([asset("images/logo-lumbi.jpeg"), asset("images/logo-lumbi.png")])'
                 alt="Logo Lumbi Uwuu">
            <span class="brand-title">Lumbi Uwuu</span>
        </div>

        <div class="status-badge">🛠️ Sedang Dalam Proses Maintenance</div>

        <h1>Website Sedang Diperbarui</h1>
        <p class="desc-text">
            Halo! Kami sedang melakukan pemeliharaan sistem dan pembaruan layanan agar pengalaman pesan <strong>Lumbi Uwuu</strong> makin lancar. Silakan kembali beberapa saat lagi.
        </p>

        <div class="ig-info-box">
            ℹ️ Mohon maaf atas ketidaknyamanan Anda. Saat ini sistem kami sedang dalam tahap pemeliharaan untuk meningkatkan kualitas layanan.
            Informasi terkini mengenai proses pemeliharaan dan waktu operasional kembali dapat Anda pantau secara berkala melalui akun Instagram resmi kami di <strong>@lumbi_uwuu</strong>. Terima kasih atas pengertian dan kesabaran Anda.
        </div>

       <a href="https://www.instagram.com/lumbi_uwuu/" target="_blank" rel="noopener noreferrer"
                style="display: inline-flex; align-items: center; gap: 5px; color: #6b21a8; font-weight: 700; text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
            </svg>
            <span>@lumbi_uwuu</span>
        </a>

        <footer class="maintenance-footer">
            &copy; {{ date('Y') }} Lumbi Uwuu. Hak Cipta Dilindungi.
        </footer>
    </main>

    <script src="{{ asset('js/maintenance.js') }}?v={{ time() }}"></script>
</body>
</html>