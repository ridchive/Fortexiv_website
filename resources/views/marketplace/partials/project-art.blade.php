@php
  $projectPalette = match (true) {
      Str::contains(Str::lower($project->class_name), ['agribisnis', 'kebun']) => ['#D4E6AE', '#86AB47', '#2F5B2B'],
      Str::contains(Str::lower($project->class_name), ['boga', 'kuliner']) => ['#F3DDC4', '#D39B69', '#8A4E28'],
      Str::contains(Str::lower($project->class_name), ['desain', 'grafis']) => ['#E1DCF1', '#9087CC', '#3C3472'],
      default => ['#F8E8AE', '#E3B63F', '#8B6310'],
  };
@endphp
<svg class="{{ $class ?? '' }}" viewBox="0 0 400 300" role="img" aria-label="Ilustrasi proyek {{ $project->title }}">
  <rect width="400" height="300" fill="{{ $projectPalette[0] }}"/>
  <circle cx="326" cy="66" r="86" fill="{{ $projectPalette[1] }}" opacity=".25"/>
  <circle cx="64" cy="258" r="72" fill="{{ $projectPalette[2] }}" opacity=".14"/>
  <ellipse cx="200" cy="244" rx="140" ry="20" fill="{{ $projectPalette[2] }}" opacity=".2"/>
  @if(Str::contains(Str::lower($project->class_name), ['agribisnis', 'kebun']))
    <path d="M198 202c-68-12-94-58-80-104 55 8 89 44 80 104zM202 202c58-22 82-76 58-122-49 18-73 66-58 122z" fill="{{ $projectPalette[1] }}"/>
    <path d="M200 210V126" stroke="{{ $projectPalette[2] }}" stroke-width="8"/>
    <path d="M136 208h128l-17 70h-94z" fill="#D39B69"/>
    <path d="M126 194h148v22H126z" fill="#8A4E28"/>
  @elseif(Str::contains(Str::lower($project->class_name), ['boga', 'kuliner']))
    <path d="M92 178h216a108 54 0 0 1-216 0z" fill="{{ $projectPalette[1] }}"/>
    <ellipse cx="200" cy="178" rx="108" ry="54" fill="#F8E8D3"/>
    <path d="M118 166q82-45 164 0M132 186q68-37 136 0" fill="none" stroke="{{ $projectPalette[2] }}" stroke-width="10" stroke-linecap="round"/>
    <path d="M172 96q28-38 56 0M200 96V68" fill="none" stroke="{{ $projectPalette[2] }}" stroke-width="8" stroke-linecap="round"/>
  @elseif(Str::contains(Str::lower($project->class_name), ['desain', 'grafis']))
    <path d="M108 88h184v148H108z" fill="#FFFFFF" opacity=".85"/>
    <path d="M132 112h116v84H132z" fill="{{ $projectPalette[1] }}"/>
    <circle cx="212" cy="148" r="28" fill="{{ $projectPalette[0] }}"/>
    <path d="M152 180q30-38 60 0" fill="none" stroke="{{ $projectPalette[2] }}" stroke-width="9" stroke-linecap="round"/>
    <path d="M264 112v124M108 212h184" stroke="{{ $projectPalette[2] }}" stroke-width="5"/>
  @else
    <path d="M144 132h112v104a18 18 0 0 1-18 18h-76a18 18 0 0 1-18-18z" fill="{{ $projectPalette[1] }}"/>
    <path d="M200 122c22-30 18-48 0-68-18 20-22 38 0 68z" fill="#F2B544"/>
    <path d="M200 116v28" stroke="{{ $projectPalette[2] }}" stroke-width="5"/>
    <rect x="162" y="170" width="76" height="40" rx="6" fill="{{ $projectPalette[0] }}"/>
  @endif
</svg>
