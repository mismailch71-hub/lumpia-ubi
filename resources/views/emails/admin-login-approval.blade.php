<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Login Admin</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 24px; color: #1e293b;">
    <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
        <h2 style="margin-top: 0; color: #581c87;">Permintaan Akses Login Admin</h2>
        <p>Ada upaya login ke akun admin <strong>Lumbi Uwuu</strong>:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13.5px;">
            <tr>
                <td style="padding: 6px 0; color: #64748b; width: 110px;">Akun</td>
                <td style="padding: 6px 0;"><strong>{{ $adminUser->name }}</strong> ({{ $adminUser->email }})</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #64748b;">IP Address</td>
                <td style="padding: 6px 0;">{{ $ipAddress }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #64748b;">Perangkat</td>
                <td style="padding: 6px 0;">{{ $userAgent }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #64748b;">Berlaku hingga</td>
                <td style="padding: 6px 0;">{{ $expiresAtText }}</td>
            </tr>
        </table>

        <div style="margin: 22px 0; display: flex; gap: 10px;">
            <a href="{{ $approveUrl }}"
               style="display: inline-block; background: #16a34a; color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 7px; font-weight: bold; font-size: 14px;">
                Setujui Akses Login
            </a>
            <a href="{{ $rejectUrl }}"
               style="display: inline-block; background: #dc2626; color: #ffffff; text-decoration: none; padding: 10px 18px; border-radius: 7px; font-weight: bold; font-size: 14px; margin-left: 8px;">
                Tolak Login
            </a>
        </div>

        <p style="font-size: 12px; color: #64748b; margin-bottom: 0;">
            Jika Anda tidak melakukan permintaan ini, klik <strong>Tolak Login</strong> dan segera ganti password admin.
        </p>
    </div>
</body>
</html>