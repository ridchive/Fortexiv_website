<article class="pcard">
  <a href="{{ route('products.show', $product) }}" class="pim" aria-label="Lihat {{ $product->name }}">
    @if($product->image)
      <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}">
    @else
      @include('marketplace.partials.product-art', ['category' => $product->category?->name])
    @endif
    <span class="bd">
      @if($product->stock > 0)
        <span class="chip good">Stok tersedia</span>
      @else
        <span class="chip bad">Stok habis</span>
      @endif
      @if($product->stock > 0 && $product->stock <= 5)
        <span class="chip warn">Sisa {{ $product->stock }}</span>
      @endif
    </span>
  </a>
  <div>
    <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
    <p class="muted small">{{ $product->project?->class_name ?? $product->seller->project?->class_name ?? $product->seller->store_name }} · {{ $product->category?->name ?? 'Karya kelas' }}</p>
    <p class="project-meta">{{ Str::limit($product->description, 96) }}</p>
  </div>
  <div class="pr">
    <span class="price">{{ \App\Support\Money::format($product->price) }}</span>
    @if($product->stock > 0 && ($window['status'] ?? 'open') === 'open')
      @auth
        @if(auth()->user()->role === 'buyer')
          <form method="post" action="{{ route('cart.store') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button class="btn sec sm" type="submit">+ Keranjang</button>
          </form>
        @else
          <a class="btn sec sm" href="{{ route('products.show', $product) }}">Lihat</a>
        @endif
      @else
        <a class="btn sec sm" href="{{ route('login') }}">Masuk</a>
      @endauth
    @endif
  </div>
</article>
