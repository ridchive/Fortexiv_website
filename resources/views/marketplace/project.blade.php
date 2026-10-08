@extends('layouts.app')

@section('title', $project->title.' — FORTEXIV')

@section('content')
<a href="{{ route('home') }}">&larr; Back to showcase</a>
<article class="detail-card" style="margin-top:18px">
  <div class="detail-image">
    @if($project->image)
      <img src="{{ Storage::disk('public')->url($project->image) }}" alt="{{ $project->title }}" style="width:100%;height:100%;object-fit:cover">
    @else
      {{ $project->class_name }}
    @endif
  </div>
  <div class="detail-copy">
    <div class="eyebrow">CLASS · GRADE {{ $project->grade }}</div>
    <h1>{{ $project->title }}</h1>
    <p>{{ $project->introduction }}</p>
    <h3>The project</h3>
    <p>{{ $project->story }}</p>
    <h3>Outcome</h3>
    <p>{{ $project->outcome }}</p>
  </div>
</article>
<div class="section-head"><h2>Products from {{ $project->class_name }}</h2></div>
<div class="grid">
  @forelse($project->products as $product)
    <a class="product-card" href="{{ route('products.show', $product) }}">
      <div class="product-image">{{ $product->category?->name ?? 'Student-made' }}</div>
      <div class="product-body"><h3>{{ $product->name }}</h3><div class="seller">{{ $product->seller->store_name }}</div><div class="product-row"><span class="price">{{ \App\Support\Money::format($product->price) }}</span><span>{{ $product->stock }} available</span></div></div>
    </a>
  @empty
    <div class="empty">This class has not published any products yet.</div>
  @endforelse
</div>
@endsection
