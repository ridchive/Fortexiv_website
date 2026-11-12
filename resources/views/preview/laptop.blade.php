<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pratinjau laptop — FORTEXIV</title>
  <style>
    * { box-sizing: border-box; }
    body { min-width: 320px; min-height: 100vh; margin: 0; background: #e8ede5; color: #17241a; font: 15px/1.5 system-ui, sans-serif; }
    .preview { display: grid; justify-items: center; gap: 20px; padding: 28px 32px; }
    .preview-header { width: min(100%, 1280px); }
    .preview-label { margin: 0 0 4px; color: #55734b; font-size: 12px; font-weight: 700; letter-spacing: .1em; }
    h1 { margin: 0; font-size: 28px; line-height: 1.15; }
    .preview-copy { margin: 8px 0 0; color: #526052; }
    .preview-link { display: inline-block; margin-top: 10px; color: #315b2d; font-weight: 700; }
    .laptop-frame { width: min(1280px, 100%); height: min(780px, calc(100dvh - 190px)); min-height: 400px; overflow: hidden; border: 1px solid #aeb8aa; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px #17201933; }
    .browser-bar { display: flex; height: 38px; align-items: center; gap: 7px; padding: 0 12px; border-bottom: 1px solid #d5ddce; background: #f7f9f5; }
    .browser-dot { width: 9px; height: 9px; border-radius: 50%; background: #d98679; }
    .browser-dot:nth-child(2) { background: #e4c46c; }
    .browser-dot:nth-child(3) { background: #86b878; }
    .browser-address { width: min(520px, 70%); margin: 0 auto; padding: 3px 12px; border-radius: 999px; background: #e8ede5; color: #526052; font-size: 12px; text-align: center; }
    .laptop-frame iframe { display: block; width: 100%; height: calc(100% - 38px); border: 0; background: #fff; }
    @media (max-width: 600px) {
      .preview { gap: 14px; padding: 20px 12px; }
      .laptop-frame { height: min(780px, calc(100dvh - 210px)); min-height: 360px; }
    }
  </style>
</head>
<body>
<main class="preview">
  <header class="preview-header">
    <p class="preview-label">PRATINJAU RESPONSIF</p>
    <h1>Tampilan FORTEXIV di Laptop</h1>
    <p class="preview-copy">Situs ditampilkan pada layar lebar. Gunakan navigasi di dalam bingkai untuk membuka halaman lain.</p>
    <a class="preview-link" href="{{ route('home') }}" target="_blank" rel="noopener">Buka situs di tab baru</a>
  </header>
  <div class="laptop-frame">
    <div class="browser-bar" aria-hidden="true">
      <span class="browser-dot"></span>
      <span class="browser-dot"></span>
      <span class="browser-dot"></span>
      <span class="browser-address">FORTEXIV</span>
    </div>
    <iframe src="{{ route('home') }}" title="Pratinjau situs FORTEXIV pada layar laptop"></iframe>
  </div>
</main>
</body>
</html>
