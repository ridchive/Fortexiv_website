@if($project->image)
  <img class="{{ $class ?? '' }} project-photo" src="{{ $project->imageUrl() ?? Storage::disk('public')->url($project->image) }}" alt="{{ $alt ?? $project->title }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.hidden=false; this.nextElementSibling.style.display='block'; }">
  @include('marketplace.partials.project-art', ['project' => $project, 'class' => $class ?? '', 'hidden' => true])
@else
  @php
    $projectText = Str::lower($project->class_name.' '.$project->title.' '.$project->introduction);
    $referencePhoto = match (true) {
        Str::contains($projectText, ['agribisnis', 'kebun', 'farming', 'garden', 'green economy']) => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['boga', 'kuliner', 'dapur', 'food']) => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['desain', 'grafis', 'digital art', 'kertas', 'paper', 'craft']) => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['ocean', 'laut', 'water', 'air', 'cleanup']) => 'https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['textile', 'kain', 'tas']) => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['solar', 'robot', 'car', 'mobil']) => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
        Str::contains($projectText, ['candle', 'lilin', 'jelantah', 'kriya', 'lampu', 'dekorasi']) => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1000&q=80',
        default => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1000&q=80',
    };
  @endphp
  <img class="{{ $class ?? '' }} project-photo" src="{{ $referencePhoto }}" alt="{{ $alt ?? 'Foto referensi proyek '.$project->title }}" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.hidden=false; this.nextElementSibling.style.display='block'; }">
  @include('marketplace.partials.project-art', ['project' => $project, 'class' => $class ?? '', 'hidden' => true])
@endif
