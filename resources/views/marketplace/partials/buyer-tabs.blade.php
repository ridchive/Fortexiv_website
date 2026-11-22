@php
  $user = auth()->user();
  $cartCount = $user ? $user->cartItems()->count() : 0;
  $activePickupsCount = $user ? $user->orders()->whereIn('status', ['paid', 'partially_picked_up'])->count() : 0;
@endphp

<style>
.buyer-subtabs-wrap {
  margin: 18px 0 24px;
}
.buyer-subtabs {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--surface);
  border: 1px solid var(--line);
  padding: 6px;
  border-radius: 12px;
  max-width: 100%;
  overflow-x: auto;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}
.buyer-subtab {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  text-decoration: none;
  color: var(--muted);
  transition: all .2s ease;
  white-space: nowrap;
}
.buyer-subtab:hover {
  color: var(--ink);
  background: var(--soft);
}
.buyer-subtab.active {
  background: var(--dark);
  color: var(--dark-ink);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.buyer-subtab-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 2px 7px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  background: var(--soft);
  color: var(--ink);
  line-height: 1.2;
}
.buyer-subtab.active .buyer-subtab-badge {
  background: rgba(255, 255, 255, 0.22);
  color: #fff;
}
.buyer-subtab-badge.lime {
  background: var(--lime);
  color: var(--lime-ink);
}
</style>

<div class="buyer-subtabs-wrap">
  <nav class="buyer-subtabs" aria-label="Aktivitas Belanja">
    <a href="{{ route('cart.index') }}" class="buyer-subtab {{ ($activeTab ?? '') === 'cart' ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 1.9-1.4L22 8H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
      <span>Keranjang</span>
      @if($cartCount > 0)
        <span class="buyer-subtab-badge">{{ $cartCount }}</span>
      @endif
    </a>
    <a href="{{ route('orders.index') }}" class="buyer-subtab {{ ($activeTab ?? '') === 'orders' ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"></path><path d="M14 3v5h5M9 12h7M9 16h7"></path></svg>
      <span>Pesanan Saya</span>
    </a>
    <a href="{{ Route::has('tickets.hub') ? route('tickets.hub') : url('/tickets') }}" class="buyer-subtab {{ ($activeTab ?? '') === 'tickets' ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
      <span>Tiket Pickup</span>
      @if($activePickupsCount > 0)
        <span class="buyer-subtab-badge lime">{{ $activePickupsCount }} Siap</span>
      @endif
    </a>
  </nav>
</div>
