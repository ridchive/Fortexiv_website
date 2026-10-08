@extends('layouts.app')

@section('title', 'FORTEXIV — Discover student projects')

@section('content')
<section class="hero">
  <span class="eyebrow">PROJECT SHOWCASE · {{ $projects->count() }} CLASSES EXHIBITING</span>
  <h1>What students are building this term</h1>
  <p>A digital exhibition of school projects — meet the classes, explore their stories, and discover what they made.</p>
</section>

<div class="project-strip" aria-label="Student projects">
  @forelse($projects as $project)
    <a class="project-card" href="{{ route('projects.show', $project) }}">
      <div class="class">GRADE {{ $project->grade }} · {{ $project->class_name }}</div>
      <h3>{{ $project->title }}</h3>
      <p class="project-meta">{{ Str::limit($project->introduction, 95) }}</p>
    </a>
  @empty
    <div class="empty">Project stories will appear here soon.</div>
  @endforelse
</div>

<div class="section-head">
  <h2>Product marketplace</h2>
  <span class="eyebrow">Made by the classes above</span>
</div>
<form class="searchbar" method="get" action="{{ route('home') }}">
  <input class="input" name="search" value="{{ request('search') }}" placeholder="Search products" aria-label="Search products">
  <select class="input" name="category" aria-label="Filter by category">
    <option value="">All categories</option>
    @foreach($categories as $category)
      <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
    @endforeach
  </select>
  <button class="btn" type="submit">Search</button>
</form>
<div class="grid">
  @forelse($products as $product)
    <a class="product-card" href="{{ route('products.show', $product) }}">
      <div class="product-image">
        @if($product->image)
          <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover">
        @else
          {{ $product->category?->name ?? 'FORTEXIV' }}
        @endif
      </div>
      <div class="product-body">
        <div class="category-name">{{ $product->category?->name ?? 'Student-made' }}</div>
        <h3>{{ $product->name }}</h3>
        <div class="seller">{{ $product->seller->store_name }}@if($product->project) · {{ $product->project->class_name }}@endif</div>
        <div class="product-row"><span class="price">{{ \App\Support\Money::format($product->price) }}</span><span class="muted">{{ $product->stock }} available</span></div>
      </div>
    </a>
  @empty
    <div class="empty" style="grid-column:1/-1">
      <strong>No products found yet.</strong>
      <p>Sign up as a seller to bring your class project to the marketplace.</p>
      <a class="btn" href="{{ route('register') }}">Join as a seller</a>
    </div>
  @endforelse
</div>
<div class="pagination">{{ $products->links() }}</div>
@endsection
