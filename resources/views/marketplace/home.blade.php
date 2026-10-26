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
@endsection
