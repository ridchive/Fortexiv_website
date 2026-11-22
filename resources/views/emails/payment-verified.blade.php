<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Terverifikasi - FORTEXIV</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        .card { max-width: 600px; margin: 0 auto; background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 32px; }
        .header { border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 24px; text-align: center; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #10b981; text-transform: uppercase; margin: 0; }
        h1 { font-size: 20px; color: #f8fafc; margin-top: 0; }
        p { line-height: 1.6; color: #94a3b8; font-size: 15px; }
        .info-box { background-color: #0f172a; border-radius: 8px; padding: 16px; margin: 20px 0; border: 1px solid #334155; }
        .btn-container { text-align: center; margin-top: 28px; }
        .btn { display: inline-block; background-color: #059669; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .badge { display: inline-block; background-color: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 600; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #334155; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 class="logo">FORTEXIV</h2>
        </div>
        <h1>Pembayaran Berhasil Diverifikasi!</h1>
        <p>Halo <strong>{{ $order->buyer?->name ?? 'Pelanggan' }}</strong>,</p>
        <p>Kabar baik! Pembayaran untuk pesanan Anda telah dikonfirmasi dan diverifikasi oleh Admin. Tiket pengambilan (pickup QR) kini telah aktif.</p>
        
        <div class="info-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Nomor Pesanan:</td>
                    <td style="padding: 6px 0; color: #f1f5f9; font-weight: 600; text-align: right; font-size: 14px;">{{ $order->order_code }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Total Tagihan:</td>
                    <td style="padding: 6px 0; color: #34d399; font-weight: 700; text-align: right; font-size: 14px;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Status Pesanan:</td>
                    <td style="padding: 6px 0; text-align: right; font-size: 14px;"><span class="badge">Lunas (Paid)</span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Tanggal Pengambilan:</td>
                    <td style="padding: 6px 0; color: #f1f5f9; font-weight: 600; text-align: right; font-size: 14px;">{{ $order->pickup_date ? $order->pickup_date->translatedFormat('l, d F Y') : '-' }}</td>
                </tr>
            </table>
        </div>

        <p>Simpan dan tunjukkan QR Code pada tiket saat mengambil produk di lokasi acara.</p>

        <div class="btn-container">
            <a href="{{ route('tickets.show', $order) }}" class="btn">Buka Tiket Pickup QR</a>
        </div>

        <div class="footer">
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem FORTEXIV Marketplace.</p>
        </div>
    </div>
</body>
</html>
