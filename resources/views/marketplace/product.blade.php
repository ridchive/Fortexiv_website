@extends('layouts.app')

@section('title', $product->name.' — FORTEXIV')

@section('content')
<a href="{{ route('home') }}">&larr; Back to marketplace</a>
<article class="detail-card" style="margin-top:18px">
  <div class="detail-image">
    @if($product->image)
      <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover">
    @else
      {{ $product->category?->name ?? 'Student-made' }}
    @endif
  </div>
  <div class="detail-copy">
    <div class="eyebrow">{{ $product->category?->name ?? 'Student-made' }} · {{ $product->seller->store_name }}</div>
    <h1>{{ $product->name }}</h1>
    <p>{{ $product->description }}</p>
    @if($product->project)
      <p>Made by <a href="{{ route('projects.show', $product->project) }}">{{ $product->project->class_name }} · {{ $product->project->title }}</a></p>
    @endif
    <p><strong>{{ $product->stock }}</strong> in stock</p>
    <div class="price" style="font-size:24px;margin:16px 0">{{ \App\Support\Money::format($product->price) }}</div>
    @auth
      @if(auth()->user()->role === 'buyer')
        <form method="post" action="{{ route('cart.store') }}">
          @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
          <div class="field"><label for="quantity">Quantity</label><input class="input" id="quantity" name="quantity" type="number" value="1" min="1" max="{{ min($product->stock, 100) }}" required></div>
          <button class="btn btn-yellow" type="submit">Add to cart</button>
        </form>
      @elseif(auth()->user()->role === 'seller')
        <p class="muted">Sign in as a buyer to add items to your cart.</p>
      @endif
    @else
      <a class="btn btn-yellow" href="{{ route('login') }}">Sign in to add to cart</a>
    @endauth
  </div>
</article>
@endsection
