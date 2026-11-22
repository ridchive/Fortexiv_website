<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran Diterima - FORTEXIV</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        .card { max-width: 600px; margin: 0 auto; background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 32px; }
        .header { border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 24px; text-align: center; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #38bdf8; text-transform: uppercase; margin: 0; }
        h1 { font-size: 20px; color: #f8fafc; margin-top: 0; }
        p { line-height: 1.6; color: #94a3b8; font-size: 15px; }
        .info-box { background-color: #0f172a; border-radius: 8px; padding: 16px; margin: 20px 0; border: 1px solid #334155; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .info-label { color: #64748b; }
        .info-value { color: #f1f5f9; font-weight: 600; text-align: right; }
        .btn-container { text-align: center; margin-top: 28px; }
        .btn { display: inline-block; background-color: #0284c7; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #334155; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 class="logo">FORTEXIV</h2>
        </div>
        <h1>Bukti Pembayaran Diterima</h1>
        <p>Halo <strong>{{ $order->buyer?->name ?? 'Pelanggan' }}</strong>,</p>
        <p>Terima kasih! Bukti transfer pembayaran untuk pesanan Anda telah berhasil diunggah dan sedang dalam antrean verifikasi manual oleh Admin FORTEXIV.</p>
        
        <div class="info-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Nomor Pesanan:</td>
                    <td style="padding: 6px 0; color: #f1f5f9; font-weight: 600; text-align: right; font-size: 14px;">{{ $order->order_code }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Total Tagihan:</td>
                    <td style="padding: 6px 0; color: #38bdf8; font-weight: 700; text-align: right; font-size: 14px;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Status:</td>
                    <td style="padding: 6px 0; color: #fbbf24; font-weight: 600; text-align: right; font-size: 14px;">Menunggu Verifikasi</td>
                </tr>
            </table>
        </div>

        <p>Tiket pickup QR dan rincian pengambilan akan aktif secara otomatis segera setelah Admin memverifikasi pembayaran Anda.</p>

        <div class="btn-container">
            <a href="{{ route('orders.show', $order) }}" class="btn">Lihat Status Pesanan</a>
        </div>

        <div class="footer">
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem FORTEXIV Marketplace.</p>
        </div>
    </div>
</body>
</html>
