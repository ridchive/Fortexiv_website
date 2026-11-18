@php
  $referencePhoto = match (Str::lower($category ?? '')) {
      'makanan' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
      'alat tulis' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=800&q=80',
      'aksesori' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
      'dekorasi' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80',
      'perawatan' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=800&q=80',
      default => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80',
  };
@endphp
<div class="product-visual">
  @include('marketplace.partials.product-art', ['category' => $category ?? null])
  <img class="product-reference-photo" src="{{ $referencePhoto }}" alt="{{ $alt ?? 'Foto referensi produk' }}" loading="lazy" onerror="this.hidden = true">
</div>
