<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'FORTEXIV Marketplace')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--bg:#EEF2F8;--surface:#fff;--line:#E1E7F0;--ink:#1B2333;--muted:#6B7688;--blue:#3568E0;--blue-soft:#E8EEFC;--blue-dark:#26499E;--yellow:#F4B740;--green:#1F7A46;--red:#B42318;--radius:16px;--shadow:0 10px 30px -12px rgba(30,50,90,.18);--sans:'Inter',system-ui,sans-serif;--head:'Space Grotesk',var(--sans)}
    *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--sans);line-height:1.55}h1,h2,h3{font-family:var(--head);line-height:1.2;margin:0}a{color:var(--blue-dark)}button,input,select,textarea{font:inherit}.nav{position:sticky;top:0;background:rgba(238,242,248,.93);backdrop-filter:blur(10px);border-bottom:1px solid var(--line);z-index:3}.nav-inner,.app{max-width:1180px;margin:0 auto;padding:14px 20px}.nav-inner{display:flex;justify-content:space-between;align-items:center;gap:16px}.logo{font:700 22px var(--head);color:var(--ink);text-decoration:none;letter-spacing:-.03em}.logo span{color:var(--blue)}.nav-links{display:flex;flex-wrap:wrap;align-items:center;gap:14px}.nav-links a,.link-button{color:var(--ink);text-decoration:none;font-size:13px;font-weight:600}.inline{display:inline}.link-button{border:0;background:none;padding:0;cursor:pointer}.app{min-height:calc(100vh - 145px);padding-top:26px;padding-bottom:52px}.hero{text-align:center;padding:40px 0 28px}.eyebrow,.muted,.seller,.project-meta{color:var(--muted)}.eyebrow{font-size:13px}.hero h1{font-size:clamp(28px,5vw,40px);max-width:700px;margin:8px auto}.hero p{max-width:60ch;margin:12px auto;color:var(--muted)}.section-head{display:flex;align-items:baseline;justify-content:space-between;gap:16px;margin:30px 0 16px}.section-head h2{font-size:21px}.section-head a{font-size:13px}.project-strip{display:grid;grid-auto-columns:minmax(235px,1fr);grid-auto-flow:column;gap:16px;overflow-x:auto;padding:2px 2px 14px}.project-card,.card,.product-card,.stat,.auth-card,.detail-card,.notice{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}.project-card{display:block;min-height:148px;padding:20px;text-decoration:none;color:var(--ink);transition:transform .18s}.project-card:hover,.product-card:hover{transform:translateY(-3px)}.project-card .class,.category-name{color:var(--blue);font-weight:700;font-size:12px}.project-card h3{font-size:18px;margin:7px 0}.project-card p,.project-meta{font-size:12px;margin:0}.searchbar{display:flex;gap:10px;margin:18px 0}.input{width:100%;padding:11px 13px;border:1px solid var(--line);border-radius:10px;background:#FBFCFE;color:var(--ink)}.searchbar .input{flex:1}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(205px,1fr));gap:17px}.product-card{display:block;overflow:hidden;color:var(--ink);text-decoration:none;transition:transform .18s}.product-image{height:155px;display:grid;place-items:center;background:linear-gradient(135deg,#DCE6FB,#F4E9CE);color:var(--blue-dark);font:700 17px var(--head);text-align:center;padding:18px}.product-body{padding:15px}.product-body h3{font-size:16px}.seller{font-size:12px;margin-top:3px}.product-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px}.price{font-weight:700;color:var(--blue-dark)}.btn{display:inline-flex;justify-content:center;align-items:center;gap:7px;border:0;border-radius:999px;padding:10px 17px;background:var(--blue);color:#fff;text-decoration:none;font-weight:700;font-size:13px;cursor:pointer}.btn:hover{background:var(--blue-dark)}.btn-outline{background:#fff;border:1px solid var(--line);color:var(--ink)}.btn-yellow{background:var(--yellow);color:#3A2A00}.btn-danger{background:#fff0ef;color:var(--red)}.btn-small{padding:7px 12px;font-size:12px}.chip-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}.chip{display:inline-flex;padding:6px 12px;border-radius:999px;background:var(--blue-soft);color:var(--blue-dark);font-size:12px;text-decoration:none}.detail-card{display:grid;grid-template-columns:1fr 1fr;gap:30px;padding:28px}.detail-image{min-height:310px;border-radius:12px;background:linear-gradient(135deg,#DCE6FB,#F4E9CE);display:grid;place-items:center;text-align:center;padding:20px;color:var(--blue-dark);font:700 22px var(--head)}.detail-copy{align-self:center}.detail-copy h1{font-size:clamp(25px,5vw,36px);margin:7px 0 14px}.detail-copy p{color:var(--muted)}.panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:20px;margin-bottom:16px}.form-card,.auth-card{max-width:620px;margin:22px auto;padding:26px}.auth-card{max-width:430px}.field{margin:13px 0}.field label{display:block;margin-bottom:6px;font-size:13px;font-weight:700}.errors{color:var(--red);font-size:13px}.flash{padding:12px 15px;margin:0 0 16px;background:#e6f5eb;border:1px solid #b7e1c5;border-radius:10px;color:#1F7A46}.table-wrap{overflow:auto;border:1px solid var(--line);border-radius:12px;background:white;margin:14px 0 22px}table{width:100%;border-collapse:collapse;min-width:630px}th,td{text-align:left;padding:12px 14px;border-bottom:1px solid var(--line);font-size:13px}th{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.04em}tr:last-child td{border:0}.badge{display:inline-flex;padding:4px 9px;border-radius:99px;background:var(--blue-soft);color:var(--blue-dark);font-size:11px;font-weight:700}.badge.pending_payment,.badge.waiting_verification,.badge.pending{background:#FDF1D9;color:#8A6100}.badge.cancelled,.badge.rejected{background:#fff0ef;color:var(--red)}.badge.paid,.badge.verified,.badge.picked_up{background:#DFF3E6;color:var(--green)}.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:12px;margin:18px 0 24px}.stat{padding:17px}.stat strong{display:block;font:700 24px var(--head);color:var(--blue-dark)}.stat span{color:var(--muted);font-size:12px}.pagination{margin-top:20px}.footer{text-align:center;color:var(--muted);padding:26px 15px;font-size:12px}.stack{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.empty{padding:34px;text-align:center;color:var(--muted);background:#fff;border:1px dashed var(--line);border-radius:var(--radius)}.ticket{max-width:600px;margin:20px auto;padding:28px;border:2px dashed var(--blue);border-radius:var(--radius);background:#fff;text-align:center}.ticket-code{font:700 clamp(22px,6vw,34px) var(--head);letter-spacing:.08em;color:var(--blue-dark);margin:18px 0}.receipt{white-space:pre-wrap;word-break:break-word}.inline-form{display:inline-flex;gap:7px;align-items:center}.inline-form .input{width:auto}
    @media(max-width:700px){.nav-inner{align-items:flex-start;flex-direction:column}.nav-links{gap:11px}.hero{padding-top:24px}.detail-card{grid-template-columns:1fr;padding:18px}.detail-image{min-height:230px}.section-head{align-items:flex-start;flex-direction:column}.searchbar{flex-direction:column}}
  </style>
</head>
<body>
<header class="nav">
  <div class="nav-inner">
    <a class="logo" href="{{ route('home') }}">FORTE<span>XIV</span></a>
    <nav class="nav-links" aria-label="Navigasi utama">
      <a href="{{ route('home') }}">Marketplace</a>
      @auth
        <a href="{{ route('account') }}">Account</a>
        @if(auth()->user()->role === 'buyer')
          <a href="{{ route('cart.index') }}">Cart ({{ auth()->user()->cartItems()->count() }})</a>
          <a href="{{ route('orders.index') }}">My orders</a>
        @elseif(auth()->user()->role === 'seller')
          <a href="{{ route('seller.dashboard') }}">Seller dashboard</a>
        @else
          <a href="{{ route('admin.dashboard') }}">Admin dashboard</a>
        @endif
        <span class="muted">{{ auth()->user()->name }}</span>
        <form class="inline" method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Log out</button></form>
      @else
        <a href="{{ route('login') }}">Sign in</a>
        <a class="btn btn-small" href="{{ route('register') }}">Join marketplace</a>
      @endauth
    </nav>
  </div>
</header>
<main class="app">
  @if(session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="panel errors" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<footer class="footer">FORTEXIV · a school project exhibition &amp; marketplace</footer>
</body>
</html>
