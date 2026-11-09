<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pratinjau mobile — FORTEXIV</title>
  <style>
    * { box-sizing: border-box; }
    body { min-width: 320px; min-height: 100vh; margin: 0; background: #e8ede5; color: #17241a; font: 15px/1.5 system-ui, sans-serif; }
    .preview { display: grid; justify-items: center; gap: 20px; padding: 28px 16px; }
    .preview-header { width: min(100%, 410px); }
    .preview-label { margin: 0 0 4px; color: #55734b; font-size: 12px; font-weight: 700; letter-spacing: .1em; }
    h1 { margin: 0; font-size: 28px; line-height: 1.15; }
    .preview-copy { margin: 8px 0 0; color: #526052; }
    .preview-link { display: inline-block; margin-top: 10px; color: #315b2d; font-weight: 700; }
    .phone-frame { width: 410px; max-width: calc(100vw - 24px); height: min(844px, calc(100dvh - 210px)); min-height: 480px; overflow: hidden; border: 10px solid #172019; border-radius: 30px; background: #fff; box-shadow: 0 18px 50px #17201933; }
    .phone-frame iframe { display: block; width: 100%; height: 100%; border: 0; background: #fff; }
    @media (max-width: 480px) {
      .preview { gap: 14px; padding-top: 20px; }
      .phone-frame { height: min(844px, calc(100dvh - 190px)); min-height: 360px; }
    }
  </style>
</head>
<body>
<main class="preview">
  <header class="preview-header">
    <p class="preview-label">PRATINJAU RESPONSIF</p>
    <h1>Tampilan FORTEXIV di HP</h1>
    <p class="preview-copy">Situs ditampilkan pada lebar layar ponsel. Gunakan navigasi di dalam bingkai untuk melihat halaman lain.</p>
    <a class="preview-link" href="{{ route('home') }}" target="_blank" rel="noopener">Buka situs di tab baru</a>
  </header>
  <div class="phone-frame">
    <iframe src="{{ route('home') }}" title="Pratinjau situs FORTEXIV pada layar ponsel"></iframe>
  </div>
</main>
</body>
</html>
