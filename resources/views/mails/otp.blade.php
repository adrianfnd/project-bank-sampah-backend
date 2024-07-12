<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Bank Sampah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 4px;
            margin: 20px 0;
        }

        .warning {
            color: #e74c3c;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Bank Sampah - Kode OTP</h1>
        <p>Halo,</p>
        <p>Berikut adalah kode OTP Anda untuk aplikasi Bank Sampah:</p>
        <div class="otp-code">{{ $otp }}</div>
        <p class="warning">Perhatian: Jangan berikan kode ini kepada siapapun. Ini adalah rahasia Anda.</p>
        <p>Jika Anda tidak meminta kode ini, mohon abaikan email ini.</p>
        <div class="footer">
            <p>Terima kasih telah menggunakan aplikasi Bank Sampah.</p>
            <p>&copy; 2024 Bank Sampah. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>
