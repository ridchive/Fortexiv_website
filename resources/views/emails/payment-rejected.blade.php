<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran Perlu Diperbaiki - FORTEXIV</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        .card { max-width: 600px; margin: 0 auto; background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 32px; }
        .header { border-bottom: 1px solid #334155; padding-bottom: 20px; margin-bottom: 24px; text-align: center; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 2px; color: #f43f5e; text-transform: uppercase; margin: 0; }
        h1 { font-size: 20px; color: #f8fafc; margin-top: 0; }
        p { line-height: 1.6; color: #94a3b8; font-size: 15px; }
        .reason-box { background-color: rgba(244, 63, 94, 0.1); border-left: 4px solid #f43f5e; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .reason-title { font-size: 13px; text-transform: uppercase; font-weight: 700; color: #fb7185; margin: 0 0 6px 0; letter-spacing: 0.5px; }
        .reason-text { color: #fecdd3; font-size: 15px; margin: 0; }
        .info-box { background-color: #0f172a; border-radius: 8px; padding: 16px; margin: 20px 0; border: 1px solid #334155; }
        .btn-container { text-align: center; margin-top: 28px; }
        .btn { display: inline-block; background-color: #e11d48; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .warning-text { font-size: 13px; color: #f59e0b; background-color: rgba(245, 158, 11, 0.1); border-radius: 6px; padding: 12px; margin-top: 20px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #334155; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 class="logo">FORTEXIV</h2>
        </div>
        <h1>Bukti Pembayaran Belum Dapat Diterima</h1>
        <p>Halo <strong>{{ $order->buyer?->name ?? 'Pelanggan' }}</strong>,</p>
        <p>Mohon maaf, bukti pembayaran yang Anda unggah untuk pesanan <strong>#{{ $order->order_code }}</strong> belum dapat kami verifikasi.</p>
        
        <div class="reason-box">
            <div class="reason-title">Catatan / Alasan Penolakan dari Admin:</div>
            <p class="reason-text">"{{ $reason }}"</p>
        </div>

        <div class="info-box">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Nomor Pesanan:</td>
                    <td style="padding: 6px 0; color: #f1f5f9; font-weight: 600; text-align: right; font-size: 14px;">{{ $order->order_code }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Total Tagihan:</td>
                    <td style="padding: 6px 0; color: #f43f5e; font-weight: 700; text-align: right; font-size: 14px;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                @if($order->expires_at)
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Batas Waktu Bayar:</td>
                    <td style="padding: 6px 0; color: #fbbf24; font-weight: 600; text-align: right; font-size: 14px;">{{ $order->expires_at->translatedFormat('d F Y H:i WIB') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>Silakan periksa kembali mutasi/rekening Anda dan unggah bukti transfer yang jelas serta valid sebelum batas waktu pembayaran berakhir.</p>

        <div class="btn-container">
            <a href="{{ route('orders.show', $order) }}" class="btn">Unggah Ulang Bukti Pembayaran</a>
        </div>

        <div class="warning-text">
            ⚠️ <strong>Perhatian:</strong> Jika bukti pembayaran baru tidak diunggah hingga batas waktu berakhir, pesanan akan otomatis dibatalkan dan stok produk akan dikembalikan ke sistem.
        </div>

        <div class="footer">
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem FORTEXIV Marketplace.</p>
        </div>
    </div>
</body>
</html>
