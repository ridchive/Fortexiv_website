<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'FORTEXIV')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: light;
      --bg: #FAFBF6;
      --surface: #FFFFFF;
      --soft: #EEF2E8;
      --ink: #14291A;
      --muted: #587050;
      --line: #D5DDCE;
      --band: #C9D5C5;
      --lime: #A9CB5B;
      --lime-ink: #16301A;
      --dark: #0F140F;
      --dark-ink: #FFFFFF;
      --good: #2C6E31;
      --good-bg: #E0EFDA;
      --warn: #85570A;
      --warn-bg: #FBEFCF;
      --bad: #A8261F;
      --bad-bg: #FADCD8;
      --info: #235A78;
      --info-bg: #DBEAF2;
      --fd: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
      --fb: 'Figtree', 'Segoe UI', system-ui, sans-serif;
      --radius: 8px;
      font-family: var(--fb);
      color: var(--ink);
      background: var(--bg);
    }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--bg); color: var(--ink); font: 16px/1.6 var(--fb); }
    h1, h2, h3, h4 { margin: 0; font-family: var(--fd); line-height: 1.12; }
    h1 { font-size: clamp(2rem, 5vw, 3.4rem); letter-spacing: -.035em; }
    h2 { font-size: clamp(1.5rem, 3.5vw, 2.25rem); letter-spacing: -.025em; }
    h3 { font-size: 1.25rem; }
    p { margin: .7rem 0; }
    a { color: inherit; }
    button, input, select, textarea { font: inherit; }
    .prototype-note { padding: 5px 16px; background: #E9EDE5; color: #3E4A3E; font-size: 12px; text-align: center; }
    .nav { position: sticky; top: 0; z-index: 10; background: #101810; color: #F7FAF4; }
    .nav-inner { max-width: 1240px; margin: 0 auto; padding: 14px 24px; display: grid; grid-template-columns: 1fr auto; gap: 12px 24px; align-items: center; }
    .logo { display: inline-flex; align-items: center; gap: 8px; color: #FFF; font: 800 24px/1 var(--fd); text-decoration: none; letter-spacing: -.04em; }
    .logo::before { content: ""; width: 12px; height: 12px; border-radius: 50%; background: var(--lime); }
    .nav-actions, .nav-links, .nav-account { display: flex; align-items: center; flex-wrap: wrap; gap: 12px 22px; }
    .nav-actions { justify-content: flex-end; }
    .nav-links { grid-column: 1 / -1; justify-content: center; }
    .nav a, .link-button { color: #F7FAF4; text-decoration: none; font-size: 14px; font-weight: 700; }
    .nav a:hover, .link-button:hover { color: var(--lime); }
    .nav-links a.active { color: var(--lime); }
    .nav-search { display: flex; width: min(100%, 600px); margin: 0 auto; grid-column: 1 / -1; border: 2px solid #D9E1D5; border-radius: 999px; overflow: hidden; }
    .nav-search input { width: 100%; min-width: 0; padding: 10px 18px; color: #FFF; background: transparent; border: 0; outline: 0; }
    .nav-search input::placeholder { color: #AEB9AB; }
    .nav-search button { width: 52px; border: 0; color: #FFF; background: transparent; cursor: pointer; }
    .nav-account { gap: 14px; }
    .nav-account .user-name { color: #CAD4C7; font-size: 12px; }
    .link-button { padding: 0; border: 0; background: transparent; cursor: pointer; }
    .inline { display: inline; }
    .app { width: min(100% - 40px, 1200px); min-height: 65vh; margin: 0 auto; padding: 30px 0 64px; }
    .wrap { width: min(100%, 1120px); margin: 0 auto; }
    .hero { position: relative; display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: center; min-height: 350px; margin: 0 0 40px; padding: clamp(24px, 5vw, 56px); overflow: hidden; border-radius: 16px; background: #DDE9C9; }
    .hero-copy { position: relative; z-index: 1; max-width: 560px; }
    .hero-copy h1 { margin: 12px 0 18px; }
    .hero-copy p { max-width: 58ch; color: #40593E; }
    .hero-art { display: grid; grid-template-columns: 1fr 1.25fr; align-items: center; gap: 12px; min-height: 240px; }
    .hero-art-card { display: grid; min-height: 170px; place-items: center; padding: 20px; border: 4px solid #86A84A; border-radius: 14px; background: #F1DCC0; color: #795333; font: 800 18px var(--fd); text-align: center; }
    .hero-art-card:last-child { min-height: 230px; background: #B8D897; color: #355D2D; }
    .eyebrow, .muted, .seller, .project-meta { color: var(--muted); }
    .eyebrow { font-size: 12px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; }
    .section-head { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin: 34px 0 16px; }
    .section-head p { margin: 4px 0 0; color: var(--muted); }
    .section-head a { font-weight: 700; }
    .project-strip { display: grid; grid-auto-columns: minmax(245px, 1fr); grid-auto-flow: column; gap: 14px; padding: 2px 2px 15px; overflow-x: auto; }
    .project-card, .card, .product-card, .stat, .auth-card, .detail-card, .notice, .panel, .box { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); }
    .project-card { display: block; min-height: 175px; padding: 20px; color: var(--ink); text-decoration: none; transition: transform .18s ease, border-color .18s ease; }
    .project-card:hover, .product-card:hover { transform: translateY(-3px); border-color: #91AD72; }
    .project-card .class, .category-name { color: #527A36; font-size: 12px; font-weight: 700; }
    .project-card h3 { margin: 8px 0; }
    .project-card p, .project-meta { margin: 0; font-size: 13px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .product-card { display: flex; flex-direction: column; overflow: hidden; color: var(--ink); text-decoration: none; transition: transform .18s ease, border-color .18s ease; }
    .product-image { display: grid; min-height: 178px; place-items: center; padding: 18px; background: linear-gradient(135deg, #DDE9C9, #F1DCC0); color: #395633; font: 800 19px var(--fd); text-align: center; }
    .product-image img { width: 100%; height: 100%; min-height: 178px; object-fit: cover; }
    .product-body { display: flex; flex: 1; flex-direction: column; padding: 16px; }
    .product-body h3 { margin: 5px 0; font-size: 18px; }
    .seller { margin-top: 2px; font-size: 13px; }
    .product-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: auto; padding-top: 14px; }
    .price { color: #315B2D; font-weight: 800; }
    .btn { display: inline-flex; min-height: 36px; justify-content: center; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--dark); border-radius: 999px; background: var(--dark); color: var(--dark-ink); text-decoration: none; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn:hover { background: #2C3A2C; }
    .btn:disabled, .btn.off { opacity: .48; cursor: not-allowed; }
    .btn.sec { border-color: var(--line); background: var(--soft); color: var(--ink); }
    .btn.line { background: transparent; color: var(--ink); }
    .btn.bad, .btn-danger { border-color: var(--bad); background: var(--bad-bg); color: var(--bad); }
    .btn.lime { border-color: var(--lime); background: var(--lime); color: var(--lime-ink); }
    .btn.sm, .btn-small { min-height: 34px; padding: 6px 12px; font-size: 12px; }
    .btns, .stack, .row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .row.sp { justify-content: space-between; }
    .section-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
    .status-note, .flash, .note { margin: 14px 0; padding: 14px 16px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--soft); color: var(--ink); }
    .status-note.open, .note.good, .flash { border-color: #BDD8B3; background: var(--good-bg); color: var(--good); }
    .status-note.upcoming { border-color: #E8D39D; background: var(--warn-bg); color: var(--warn); }
    .status-note.upcoming, .note.warn { border-color: #E8D59F; background: var(--warn-bg); color: var(--warn); }
    .status-note.closed, .status-note.not_configured, .note.bad { border-color: #E6B6B0; background: var(--bad-bg); color: var(--bad); }
    .status-note.info, .note.info { border-color: #BED7E2; background: var(--info-bg); color: var(--info); }
    .crumb { margin-bottom: 12px; color: var(--muted); font-size: 13px; }
    .crumb a { color: inherit; }
    .pgh { margin: 12px 0 24px; }
    .pgh > p { max-width: 66ch; color: var(--muted); }
    .filters, .searchbar { display: grid; grid-template-columns: minmax(220px, 2fr) repeat(2, minmax(150px, 1fr)) auto; gap: 12px; margin: 18px 0 22px; padding: 16px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); }
    .fld, .field { min-width: 0; margin: 0 0 14px; }
    .fld label, .field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
    .input, .fld input, .fld select, .field input, .field select, .field textarea, textarea, select { width: 100%; min-height: 42px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; background: var(--surface); color: var(--ink); }
    textarea { min-height: 110px; }
    .input:focus, input:focus, select:focus, textarea:focus { outline: 2px solid #A9CB5B; outline-offset: 1px; }
    .pills, .chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .pill, .chip { display: inline-flex; align-items: center; padding: 6px 12px; border: 1px solid var(--line); border-radius: 999px; background: var(--soft); color: var(--ink); font-size: 12px; text-decoration: none; }
    .pill.on { border-color: #8CA66E; background: #DCE9C9; color: #264528; }
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; background: var(--info-bg); color: var(--info); font-size: 11px; font-weight: 700; }
    .badge.paid, .badge.verified, .badge.picked_up, .badge.open, .badge.handed_over { background: var(--good-bg); color: var(--good); }
    .badge.pending_payment, .badge.waiting_verification, .badge.pending, .badge.upcoming { background: var(--warn-bg); color: var(--warn); }
    .badge.cancelled, .badge.rejected, .badge.closed { background: var(--bad-bg); color: var(--bad); }
    .detail-card, .panel, .box { margin: 16px 0; padding: clamp(18px, 3vw, 28px); }
    .detail-card { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 28px; }
    .detail-image { display: grid; min-height: 320px; place-items: center; padding: 20px; border-radius: 8px; background: linear-gradient(135deg, #DDE9C9, #F1DCC0); color: #395633; font: 800 22px var(--fd); text-align: center; }
    .detail-image img { width: 100%; height: 100%; max-height: 500px; object-fit: cover; }
    .detail-copy { align-self: center; }
    .detail-copy h1 { margin: 8px 0 14px; }
    .detail-copy p { color: var(--muted); }
    .two { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr); gap: 20px; align-items: start; }
    .box h2, .panel h2 { margin-bottom: 12px; }
    .kv { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line); }
    .kv:last-child { border-bottom: 0; }
    .cart-i { display: grid; grid-template-columns: 82px minmax(0, 1fr) auto; gap: 14px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--line); }
    .cart-i:last-child { border-bottom: 0; }
    .cart-i img { width: 82px; height: 74px; border-radius: 6px; object-fit: cover; }
    .qty { display: flex; align-items: center; gap: 8px; }
    .qty button { width: 34px; height: 34px; border: 1px solid var(--line); border-radius: 50%; background: var(--soft); cursor: pointer; }
    .qty input { width: 72px; }
    .chk { display: flex; align-items: flex-start; gap: 10px; margin: 18px 0; }
    .chk input { width: 18px; height: 18px; margin-top: 3px; accent-color: #547B37; }
    .empty { padding: 32px 20px; border: 1px dashed #B7C5AE; border-radius: var(--radius); background: var(--surface); color: var(--muted); text-align: center; }
    .empty h3 { margin-bottom: 8px; color: var(--ink); }
    .auth-card, .form-card { width: min(100%, 560px); margin: 24px auto; padding: 26px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); }
    .auth-card { width: min(100%, 440px); }
    .errors { color: var(--bad); font-size: 13px; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .flash { padding: 12px 15px; }
    .table-wrap { margin: 14px 0 22px; overflow: auto; border: 1px solid var(--line); border-radius: var(--radius); background: #FFF; }
    table { width: 100%; border-collapse: collapse; min-width: 620px; }
    th, td { padding: 12px 14px; border-bottom: 1px solid var(--line); font-size: 13px; text-align: left; }
    th { color: var(--muted); font-size: 11px; letter-spacing: .04em; text-transform: uppercase; }
    tr:last-child td { border-bottom: 0; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 12px; margin: 18px 0 24px; }
    .stat { padding: 17px; }
    .stat strong { display: block; color: #315B2D; font: 800 24px var(--fd); }
    .stat span { color: var(--muted); font-size: 12px; }
    .pagination { margin-top: 20px; }
    .footer { padding: 26px 20px; background: #101810; color: #D8E0D5; text-align: center; font-size: 13px; }
    .footer a { color: #EAF1E6; }
    .footer-links { display: flex; justify-content: center; flex-wrap: wrap; gap: 16px; margin-bottom: 10px; }
    .ticket { width: min(100%, 720px); margin: 22px auto; padding: clamp(18px, 4vw, 32px); border: 2px dashed #7F9C69; border-radius: 12px; background: #FFF; text-align: center; }
    .ticket-code { margin: 12px 0; color: #315B2D; font: 800 clamp(22px, 6vw, 34px) var(--fd); letter-spacing: .08em; }
    .qrbox { display: flex; align-items: center; justify-content: center; gap: 20px; padding: 16px; border: 1px solid var(--line); border-radius: 8px; background: #FFF; }
    .qr-demo { width: 150px; height: 150px; flex: 0 0 150px; padding: 8px; border: 1px solid #DDD; background: #FFF; }
    .qr-demo { position: relative; display: grid; place-items: center; }
    .qr-demo svg { display: block; width: 100%; height: 100%; }
    .qr-demo span { position: absolute; padding: 2px 5px; background: #FFF; color: var(--bad); font-size: 11px; font-weight: 800; transform: rotate(-18deg); }
    .receipt { white-space: pre-wrap; word-break: break-word; }
    .inline-form { display: inline-flex; align-items: center; gap: 7px; }
    .inline-form .input { width: auto; }
    .steps { display: grid; grid-template-columns: 1fr; gap: 12px; }
    .step { padding: 18px; border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); }
    .step-number { display: grid; width: 32px; height: 32px; place-items: center; margin-bottom: 10px; border-radius: 50%; background: var(--lime); color: var(--lime-ink); font-weight: 800; }
    .group-status { display: flex; flex-direction: column; gap: 2px; padding: 4px 0; }
    .group-status strong { font-size: 11px; }
    .group-status span { color: var(--muted); font-size: 11px; }
    .project-story { max-width: 760px; }
    .project-story ol { padding-left: 24px; }
    .project-story li { margin: 9px 0; padding-left: 4px; }
    .help-steps { margin-top: 18px; counter-reset: help-step; }
    .help-steps li { padding-top: 12px; border-top: 2px solid var(--ink); list-style: none; counter-increment: help-step; }
    .help-steps li::before { display: block; margin-bottom: 8px; color: var(--good); content: counter(help-step); font: 800 1.6rem var(--fd); }
    .help-steps h3 { margin-bottom: 6px; }
    @media (min-width: 768px) {
      .nav-inner { grid-template-columns: auto minmax(200px, 1fr) auto; }
      .nav-search { grid-column: 2; grid-row: 1; }
      .nav-actions { grid-column: 3; grid-row: 1; }
      .hero { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
      .hero .pic { order: 0; }
      .grid3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .split { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); }
      .steps, .help-steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .ft .cols { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .btn { padding: 10px 16px; font-size: .95rem; }
    }
    @media (min-width: 1024px) {
      .grid3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
      .steps, .help-steps { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      .ft .cols { grid-template-columns: 1.6fr repeat(3, minmax(0, 1fr)); }
      .hero { min-height: 430px; }
    }
      .nav-links { grid-column: 1 / -1; }
    }
    @media (max-width: 700px) {
      .nav-inner { padding: 13px 16px; }
      .app { width: min(100% - 28px, 1200px); padding-top: 20px; }
      .hero { grid-template-columns: 1fr; min-height: 0; padding: 24px; }
      .hero-art { min-height: 165px; }
      .hero-art-card { min-height: 130px; }
      .hero-art-card:last-child { min-height: 155px; }
      .nav-links { gap: 10px 18px; }
      .nav-account { justify-content: center; }
      .detail-card, .two { grid-template-columns: 1fr; }
      .detail-image { min-height: 230px; }
      .section-head { align-items: flex-start; flex-direction: column; }
      .filters, .searchbar { grid-template-columns: 1fr; }
      .steps { grid-template-columns: repeat(2, 1fr); }
      .cart-i { grid-template-columns: 64px minmax(0, 1fr); }
      .cart-i img { width: 64px; height: 64px; }
      .cart-actions { grid-column: 1 / -1; justify-content: space-between; }
      .qrbox { align-items: flex-start; flex-direction: column; }
    }
    @media (prefers-color-scheme: dark) {
      :root:not([data-theme="light"]) {
        color-scheme: dark;
        --bg: #0E160F;
        --surface: #152017;
        --soft: #1B2A1D;
        --ink: #E7F0E1;
        --muted: #A3BB99;
        --line: #2A3B2C;
        --band: #25362A;
        --lime: #8DB04A;
        --lime-ink: #0D1A0F;
        --dark: #E7F0E1;
        --dark-ink: #0E160F;
        --good: #8CD08F;
        --good-bg: #1D3320;
        --warn: #E8B95A;
        --warn-bg: #3A2E12;
        --bad: #F2A19A;
        --bad-bg: #42201D;
        --info: #8CC4E2;
        --info-bg: #17303F;
      }
    }
    :root[data-theme="dark"] {
      color-scheme: dark;
      --bg: #0E160F;
      --surface: #152017;
      --soft: #1B2A1D;
      --ink: #E7F0E1;
      --muted: #A3BB99;
      --line: #2A3B2C;
      --band: #25362A;
      --lime: #8DB04A;
      --lime-ink: #0D1A0F;
      --dark: #E7F0E1;
      --dark-ink: #0E160F;
      --good: #8CD08F;
      --good-bg: #1D3320;
      --warn: #E8B95A;
      --warn-bg: #3A2E12;
      --bad: #F2A19A;
      --bad-bg: #42201D;
      --info: #8CC4E2;
      --info-bg: #17303F;
    }
    body { font-size: 15px; }
    .prototype-note { background: var(--ink); color: var(--bg); font-size: 12px; }
    .app { width: 100%; padding: 0 0 56px; }
    .wrap { width: 100%; max-width: 1180px; padding-right: max(16px, env(safe-area-inset-right, 0px)); padding-left: max(16px, env(safe-area-inset-left, 0px)); }
    .site-header { background: var(--bg); color: var(--ink); }
    .hd { display: grid; grid-template-columns: auto minmax(180px, 1fr) auto; gap: 20px; align-items: center; padding-block: 18px; }
    .brand { display: inline-flex; align-items: center; gap: 8px; color: var(--ink); font: 800 22px/1 var(--fd); text-decoration: none; }
    .brand-mark { width: 12px; height: 12px; flex: 0 0 12px; border-radius: 50% 0 50% 50%; background: var(--lime); transform: rotate(-20deg); }
    .hsearch { display: flex; align-items: center; width: min(100%, 560px); justify-self: center; padding: 0 6px 0 16px; border: 2px solid var(--ink); border-radius: 999px; background: var(--surface); }
    .hsearch input { flex: 1; min-width: 0; padding: 9px 0; border: 0; outline: 0; background: transparent; color: var(--ink); }
    .hsearch button { display: grid; width: 36px; height: 36px; place-items: center; border: 0; background: transparent; color: var(--ink); cursor: pointer; }
    .hicons { display: flex; align-items: center; gap: 6px; }
    .ib { position: relative; display: grid; width: 42px; height: 42px; place-items: center; border: 0; border-radius: 50%; background: transparent; color: var(--ink); text-decoration: none; cursor: pointer; }
    .ib:hover { background: var(--soft); }
    .ib .n { position: absolute; top: 2px; right: 0; display: grid; min-width: 18px; height: 18px; place-items: center; padding: 0 4px; border-radius: 9px; background: var(--dark); color: var(--dark-ink); font-size: 11px; font-weight: 700; }
    .main-nav { display: flex; flex-wrap: wrap; justify-content: center; gap: clamp(18px, 5vw, 56px); padding-bottom: 14px; }
    .main-nav a { padding: 4px 2px 5px; border-bottom: 2px solid transparent; color: var(--ink); font-weight: 600; text-decoration: none; }
    .main-nav a:hover { border-color: var(--line); }
    .main-nav a.active { border-color: var(--good); color: var(--good); }
    .umenu { position: relative; }
    .umenu summary { list-style: none; cursor: pointer; }
    .umenu summary::-webkit-details-marker { display: none; }
    .umenu .pop { position: absolute; top: 46px; right: 0; z-index: 20; min-width: 220px; padding: 8px; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); box-shadow: 0 10px 30px rgba(0,0,0,.14); }
    .umenu .pop a, .umenu .pop button { display: flex; width: 100%; align-items: center; gap: 8px; padding: 9px 10px; border: 0; border-radius: 6px; background: transparent; color: var(--ink); text-align: left; text-decoration: none; cursor: pointer; }
    .umenu .pop a:hover, .umenu .pop button:hover { background: var(--soft); }
    .umenu .who { margin-bottom: 6px; padding: 8px 10px; border-bottom: 1px solid var(--line); }
    .hero { position: relative; display: grid; min-height: 340px; grid-template-columns: 1.05fr 1fr; gap: 0; margin: 0; padding: 0; overflow: hidden; border-radius: 12px; background: var(--lime); color: var(--lime-ink); }
    .hero-carousel { position: relative; display: grid; grid-template-areas: "slide"; height: 460px; }
    .hero { grid-area: slide; height: 100%; min-height: 0; }
    .hero[hidden] { display: none; }
    .hero.is-entering-forward { animation: hero-enter-forward .65s ease-out both; }
    .hero.is-entering-backward { animation: hero-enter-backward .65s ease-out both; }
    @keyframes hero-enter-forward {
      from { opacity: 0; transform: translateX(72px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes hero-enter-backward {
      from { opacity: 0; transform: translateX(-72px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @media (prefers-reduced-motion: reduce) {
      .hero.is-entering-forward, .hero.is-entering-backward { animation: none; }
    }
    .hero .tx { display: flex; flex-direction: column; justify-content: center; gap: 16px; padding: clamp(24px, 4vw, 48px); }
    .hero .tx p { max-width: 46ch; color: inherit; font-size: 1.05rem; }
    .hero .kick { font-size: .9rem; font-weight: 600; }
    .hero .pic { position: relative; min-height: 260px; overflow: hidden; background: var(--soft); }
    .hero .main-scene { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hero .feature-art { position: absolute; z-index: 1; width: 34%; aspect-ratio: 1; right: 6%; bottom: 9%; overflow: hidden; border: 4px solid var(--lime); border-radius: 10px; background: var(--surface); box-shadow: 0 8px 24px rgba(10,40,10,.25); }
    .hero .feature-art.secondary { width: 26%; top: 10%; right: auto; bottom: auto; left: 8%; }
    .hero .feature-art svg { width: 100%; height: 100%; }
    .hero .dots { position: absolute; bottom: 16px; left: clamp(24px, 4vw, 48px); z-index: 2; display: flex; gap: 8px; }
    .hero .dots button { width: 26px; height: 6px; padding: 0; border: 0; border-radius: 3px; background: rgba(22,48,26,.3); cursor: pointer; }
    .hero .dots button[aria-pressed="true"] { background: var(--lime-ink); }
    .hero-dots { position: absolute; bottom: 16px; left: clamp(24px, 4vw, 48px); z-index: 2; display: flex; gap: 8px; }
    .hero-dots button { width: 26px; height: 6px; padding: 0; border: 0; border-radius: 3px; background: rgba(22,48,26,.3); cursor: pointer; }
    .hero-dots button[aria-pressed="true"] { background: var(--lime-ink); }
    .band { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 10px 18px; margin-top: 16px; padding: 12px 18px; border-radius: 6px; background: var(--band); font-weight: 600; text-align: center; }
    .sec-b { padding-block: 44px; border-top: 1px solid var(--line); }
    .sec-h { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 22px; }
    .grid3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .story { display: flex; flex-direction: column; gap: 10px; color: var(--ink); text-decoration: none; }
    .story .im { aspect-ratio: 1; overflow: hidden; border-radius: 6px; background: var(--soft); }
    .story .im svg { display: block; width: 100%; height: 100%; transition: transform .4s; }
    .story:hover .im svg { transform: scale(1.03); }
    .story h3 { font-size: 1.05rem; }
    .story p { color: var(--muted); }
    .story-scroll-hint { display: none; }
    .split { display: grid; grid-template-columns: 1fr 1.15fr; align-items: center; gap: clamp(24px, 5vw, 64px); }
    .split .big { aspect-ratio: 4/3; overflow: hidden; border-radius: 6px; background: var(--soft); }
    .split .big svg { display: block; width: 100%; height: 100%; }
    .subs { display: flex; flex-direction: column; gap: 22px; margin: 22px 0; }
    .subs h3 { margin-bottom: 2px; font-size: 1rem; }
    .subs p { color: var(--muted); }
    .steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; margin: 0; padding: 0; counter-reset: prototype-step; }
    .steps li { padding-top: 12px; border-top: 2px solid var(--ink); list-style: none; counter-increment: prototype-step; }
    .steps li::before { display: block; color: var(--good); content: counter(prototype-step); font: 800 1.6rem var(--fd); }
    .steps p { margin-top: 4px; color: var(--muted); }
    .qcard { display: flex; flex-direction: column; justify-content: space-between; gap: 16px; margin: 0; padding: 20px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); }
    .qcard q { quotes: none; font: 600 1.15rem/1.3 var(--fd); }
    .ctab { margin-top: 20px; padding-block: 36px; background: var(--soft); }
    .ctab .wrap { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; }
    .pcard { position: relative; display: flex; flex-direction: column; gap: 10px; }
    .pcard .pim { position: relative; display: block; aspect-ratio: 1; overflow: hidden; border-radius: 6px; background: var(--soft); }
    .pcard .pim > svg, .pcard .pim > img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
    .pcard:hover .pim > svg, .pcard:hover .pim > img { transform: scale(1.03); }
    .pcard .bd { position: absolute; top: 8px; left: 8px; display: flex; flex-wrap: wrap; gap: 6px; }
    .pcard h3 a { text-decoration: none; }
    .pcard .pr { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .pcard .price { color: var(--ink); font-weight: 700; }
    .btn { min-height: 0; padding: 10px 16px; border-radius: 6px; font-size: .95rem; line-height: 1.2; }
    .btn.dk { border-color: #0F140F; background: #0F140F; color: #FFFFFF; }
    .btn.dk:hover { opacity: .88; background: #0F140F; }
    .btn.sm { padding: 6px 11px; font-size: .85rem; }
    .chip { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px; background: var(--soft); color: var(--ink); font-size: .76rem; font-weight: 700; line-height: 1.5; white-space: nowrap; }
    .chip.good { background: var(--good-bg); color: var(--good); }
    .chip.warn { background: var(--warn-bg); color: var(--warn); }
    .chip.bad { background: var(--bad-bg); color: var(--bad); }
    .detail-image > svg { display: block; width: 100%; height: 100%; min-height: 300px; }
    .product-image .product-art { width: 100%; height: 100%; }
    .filters { display: flex; flex-wrap: wrap; align-items: end; gap: 12px; }
    .filters .fld { min-width: 160px; flex: 1 1 160px; }
    .pill { display: inline-flex; align-items: center; padding: 6px 13px; border: 1px solid var(--line); border-radius: 999px; background: var(--surface); color: var(--ink); font-size: .88rem; font-weight: 600; text-decoration: none; }
    .pill.on { border-color: var(--dark); background: var(--dark); color: var(--dark-ink); }
    .price { color: var(--good); }
    .ft { margin-top: 0; padding-block: 36px; border-top: 1px solid var(--line); }
    .ft .cols { display: grid; grid-template-columns: 1.6fr repeat(3, 1fr); gap: 28px; }
    .ft h4 { margin-bottom: 10px; font-size: .9rem; }
    .ft ul { display: flex; flex-direction: column; gap: 7px; margin: 0; padding: 0; list-style: none; }
    .ft a { color: var(--muted); text-decoration: none; }
    .ft a:hover { color: var(--ink); }
    .main-nav { grid-column: 1 / -1; }
    @media (max-width: 960px) {
      .hero-carousel { height: 560px; }
      .grid3, .steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .ft .cols { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 720px) {
      .hd { grid-template-columns: 1fr auto; }
      .hsearch { grid-column: 1 / -1; order: 3; max-width: none; }
      .hero-carousel { height: 700px; }
      .hero { grid-template-columns: minmax(0, 1fr); grid-template-rows: 200px minmax(0, 1fr); }
      .hero .pic { min-height: 200px; order: -1; }
      .hero .tx { padding-bottom: 50px; }
      .split { grid-template-columns: minmax(0, 1fr); }
      .grid3, .steps { grid-template-columns: minmax(0, 1fr); }
      .story-scroll-hint { display: block; margin: -10px 0 14px; color: var(--muted); font-size: .85rem; }
      .story-carousel { grid-template-columns: none; grid-auto-columns: 100%; grid-auto-flow: column; overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x mandatory; scrollbar-width: thin; }
      .story-carousel > .story { min-width: 0; scroll-snap-align: start; }
      .ft .cols { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 480px) {
      .ft .cols { grid-template-columns: minmax(0, 1fr); }
    }
  </style>
</head>
<body>
<div class="prototype-note">Prototype operasional FORTEXIV. Pembayaran dan QR pickup masih simulasi.</div>
<header class="site-header wrap">
  <div class="hd">
    <a class="brand" href="{{ route('home') }}"><i class="brand-mark"></i>FORTEXIV</a>
    <form class="hsearch" method="get" action="{{ route('catalog.index') }}" role="search">
      <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari produk atau proyek" aria-label="Cari">
      <button type="submit" aria-label="Cari">
        <svg class="ico" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
      </button>
    </form>
    <div class="hicons">
      @auth
        @if(auth()->user()->role === 'buyer')
          <a class="ib" href="{{ route('cart.index') }}" aria-label="Keranjang">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 1.9-1.4L22 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
            @if(auth()->user()->cartItems()->count())<span class="n">{{ auth()->user()->cartItems()->count() }}</span>@endif
          </a>
          <a class="ib" href="{{ route('orders.index') }}" aria-label="Pesanan saya">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"></path><path d="M14 3v5h5M9 12h7M9 16h7"></path></svg>
          </a>
        @endif
        <details class="umenu">
          <summary class="ib" aria-label="Menu akun">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path></svg>
          </summary>
          <div class="pop">
            <div class="who"><strong>{{ auth()->user()->name }}</strong><div class="muted small">{{ auth()->user()->email }}</div></div>
            @if(auth()->user()->role === 'buyer')
              <a href="{{ route('orders.index') }}">Pesanan saya</a>
            @elseif(auth()->user()->role === 'seller')
              <a href="{{ route('seller.dashboard') }}">Portal kelas</a>
            @elseif(auth()->user()->role === 'admin')
              <a href="{{ route('admin.dashboard') }}">Portal admin</a>
            @endif
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
          </div>
        </details>
      @else
        <a class="ib" href="{{ route('login') }}" aria-label="Keranjang">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 1.9-1.4L22 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
        </a>
        <a class="ib" href="{{ route('login') }}" aria-label="Masuk">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path></svg>
        </a>
      @endauth
    </div>
  </div>
  <nav class="main-nav" aria-label="Navigasi utama">
    <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Beranda</a>
    <a href="{{ route('projects.index') }}" @class(['active' => request()->routeIs('projects.*')])>Proyek</a>
    <a href="{{ route('catalog.index') }}" @class(['active' => request()->routeIs('catalog.*', 'products.show')])>Katalog</a>
    <a href="{{ route('help') }}" @class(['active' => request()->routeIs('help')])>Bantuan</a>
    @guest
      <a href="{{ route('register') }}">Daftar</a>
    @endguest
  </nav>
</header>
<main class="app">
  @if(session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="panel errors" role="alert"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<footer class="ft">
  <div class="wrap">
    <div class="cols">
      <div>
        <a class="brand" href="{{ route('home') }}"><i class="brand-mark"></i>FORTEXIV</a>
        <p class="muted" style="max-width:36ch;margin-top:10px">Dokumentasi proyek kelas Green Ecosystem dan katalog pre-order dari kelas yang berpartisipasi.</p>
        <p class="small muted" style="margin-top:10px">Kontak contoh: <span class="code">bantuan-contoh@fortexiv.test</span></p>
      </div>
      <div><h4>Jelajahi</h4><ul><li><a href="{{ route('projects.index') }}">Proyek kelas</a></li><li><a href="{{ route('catalog.index') }}">Katalog produk</a></li><li><a href="{{ route('home') }}">Beranda</a></li></ul></div>
      <div><h4>Bantuan</h4><ul><li><a href="{{ route('help', ['section' => 'cara-memesan']) }}#cara-memesan">Cara memesan</a></li><li><a href="{{ route('help', ['section' => 'pengambilan-barang']) }}#pengambilan-barang">Pengambilan barang</a></li><li><a href="{{ route('terms') }}">Terms of Service</a></li></ul></div>
      <div><h4>Akun</h4><ul><li><a href="{{ route('login') }}">Masuk</a></li><li><a href="{{ route('register') }}">Daftar</a></li>@auth @if(auth()->user()->role === 'buyer')<li><a href="{{ route('orders.index') }}">Pesanan saya</a></li>@endif @endauth</ul></div>
    </div>
  </div>
</footer>
@stack('scripts')
</body>
</html>
