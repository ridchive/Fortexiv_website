@php
  $palette = match (Str::lower($category ?? '')) {
      'makanan' => ['#D4E6AE', '#86AB47', '#2F5B2B'],
      'alat tulis' => ['#E1DCF1', '#9087CC', '#3C3472'],
      'aksesori' => ['#D2E8E3', '#6DAAA2', '#235A56'],
      'dekorasi' => ['#F8E8AE', '#E3B63F', '#8B6310'],
      'perawatan' => ['#F5D8D6', '#DB8F8B', '#7B2F2F'],
      default => ['#D4E6AE', '#86AB47', '#2F5B2B'],
  };
@endphp
<svg class="product-art" viewBox="0 0 400 300" role="img" aria-label="Ilustrasi produk {{ $category ?? 'kelas' }}">
  <rect width="400" height="300" fill="{{ $palette[0] }}"/>
  <circle cx="330" cy="62" r="86" fill="{{ $palette[1] }}" opacity=".22"/>
  <circle cx="56" cy="262" r="70" fill="{{ $palette[2] }}" opacity=".12"/>
  @if(Str::lower($category ?? '') === 'makanan')
    <ellipse cx="200" cy="232" rx="130" ry="20" fill="{{ $palette[2] }}" opacity=".35"/>
    <path d="M92 170h112v58H92zM92 170l30-26h112l-30 26zM204 170l30-26v58l-30 26z" fill="#E8D9B0"/>
    <path d="M180 196h106v44H180zM180 196l26-22h106l-26 22zM286 196l26-22v44l-26 22z" fill="#F2E6C2"/>
    <g fill="{{ $palette[1] }}"><circle cx="124" cy="196" r="4"/><circle cx="150" cy="206" r="4"/><circle cx="176" cy="192" r="4"/><circle cx="214" cy="218" r="4"/></g>
  @elseif(Str::lower($category ?? '') === 'alat tulis')
    <rect x="118" y="58" width="164" height="196" rx="10" fill="{{ $palette[2] }}"/>
    <rect x="136" y="58" width="146" height="196" rx="8" fill="{{ $palette[1] }}"/>
    <g fill="{{ $palette[0] }}"><circle cx="127" cy="92" r="5"/><circle cx="127" cy="132" r="5"/><circle cx="127" cy="172" r="5"/><circle cx="127" cy="212" r="5"/></g>
    <path d="M158 112h100M158 140h74M158 168h88M158 196h66" stroke="{{ $palette[0] }}" stroke-width="8" stroke-linecap="round"/>
  @elseif(Str::lower($category ?? '') === 'aksesori')
    <path d="M150 118c0-50 100-50 100 0" stroke="{{ $palette[2] }}" stroke-width="10" fill="none" stroke-linecap="round"/>
    <path d="M118 110h164l12 140H106z" fill="{{ $palette[1] }}"/>
    <circle cx="200" cy="184" r="28" fill="{{ $palette[0] }}"/>
    <path d="M188 184q12-18 24 0q-12 18-24 0z" fill="{{ $palette[2] }}"/>
  @elseif(Str::lower($category ?? '') === 'dekorasi')
    <path d="M200 48c14 20 16 30 0 42-16-12-14-22 0-42z" fill="#F2B544"/>
    <path d="M198 88h4v20h-4z" fill="{{ $palette[2] }}"/>
    <path d="M148 110h104v122a16 16 0 0 1-16 16h-72a16 16 0 0 1-16-16z" fill="{{ $palette[1] }}"/>
    <rect x="148" y="110" width="104" height="14" rx="4" fill="{{ $palette[0] }}" opacity=".8"/>
    <rect x="170" y="156" width="60" height="38" rx="6" fill="{{ $palette[0] }}"/>
  @else
    <g transform="rotate(-8 200 170)"><rect x="110" y="120" width="180" height="96" rx="16" fill="{{ $palette[1] }}"/><circle cx="150" cy="150" r="14" fill="{{ $palette[0] }}" opacity=".8"/><circle cx="244" cy="188" r="18" fill="{{ $palette[0] }}" opacity=".7"/><circle cx="206" cy="146" r="8" fill="{{ $palette[0] }}"/></g>
    <path d="M300 90q20-30 50-24-6 30-30 38z" fill="{{ $palette[2] }}" opacity=".7"/>
  @endif
</svg>
