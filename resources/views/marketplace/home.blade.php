@extends('layouts.app')

@section('title', 'FORTEXIV — Konsumsi Bijak, Bumi Lestari')

@section('content')
@php
  $heroSlides = collect([[
      'kicker' => 'Green Ecosystem · PO bersama',
      'title' => 'Konsumsi Bijak, Bumi Lestari.',
      'introduction' => 'Empat kelas mengerjakan proyek nyata dari kebun, dapur, dan studio sekolah. Baca ceritanya, lalu pesan produknya lewat satu periode PO.',
      'project' => null,
  ]])->concat($projects->take(2)->map(fn ($project) => [
      'kicker' => $project->class_name,
      'title' => $project->title,
      'introduction' => $project->introduction,
      'project' => $project,
  ]));
  $poLabel = match ($window['status']) {
      'open' => 'PO aktif',
      'upcoming' => 'PO belum dibuka',
      'closed' => 'PO ditutup',
      default => 'Jadwal PO belum tersedia',
  };
  $quotes = [
      ['“Kami hitung ulang kebutuhan daun pisang supaya tidak ada yang terbuang.”', 'Tim XI Agribisnis 1'],
      ['“Dua belas botol kaca pertama berasal dari kantin sekolah kami sendiri.”', 'Tim XI Tata Boga 2'],
      ['“Setiap buku catatan sedikit berbeda, dan itu justru yang kami suka.”', 'Tim X Desain Grafis'],
  ];
@endphp

<div class="wrap" style="padding-top:6px">
  <div class="hero-carousel" data-hero-carousel role="region" aria-roledescription="carousel" aria-label="Sorotan Proyek dan Pre-Order">
    <div class="hero-track" data-hero-track>
      @foreach($heroSlides as $index => $slide)
        <section class="hero" data-hero-slide aria-roledescription="slide" aria-label="Slide {{ $index + 1 }} dari {{ $heroSlides->count() }}">
          <div class="tx">
            <span class="kick">{{ $slide['kicker'] }}</span>
            <h1>{{ $slide['title'] }}</h1>
            <p>{{ $slide['introduction'] }}</p>
            <div class="btns">
              @if($slide['project'])
                <a class="btn dk" href="{{ route('projects.show', $slide['project']) }}">Baca Proyek</a>
                <a class="btn dk" href="{{ route('catalog.index', ['class' => $slide['project']->id]) }}">Lihat Produk Kelas</a>
              @else
                <a class="btn dk" href="{{ route('projects.index') }}">Jelajahi Proyek</a>
                <a class="btn dk" href="{{ route('catalog.index') }}">Lihat Katalog</a>
              @endif
            </div>
          </div>
          <div class="pic" aria-label="Ilustrasi karya kelas">
            @if($slide['project'])
              @include('marketplace.partials.project-art', ['project' => $slide['project'], 'class' => 'main-scene'])
            @else
              @include('marketplace.partials.garden-scene', ['class' => 'main-scene'])
            @endif
            <div class="feature-art" aria-hidden="true">@include('marketplace.partials.product-art', ['category' => 'Makanan'])</div>
            <div class="feature-art secondary" aria-hidden="true">@include('marketplace.partials.product-art', ['category' => 'Dekorasi'])</div>
          </div>
        </section>
      @endforeach
    </div>
    @if($heroSlides->count() > 1)
      <button type="button" class="hero-nav prev" data-hero-prev aria-label="Slide sebelumnya">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
      </button>
      <button type="button" class="hero-nav next" data-hero-next aria-label="Slide berikutnya">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </button>
      <div class="hero-dots" role="tablist" aria-label="Pilih proyek unggulan">
        @foreach($heroSlides as $index => $slide)
          <button type="button" data-hero-index="{{ $index }}" aria-label="Tampilkan slide {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"></button>
        @endforeach
      </div>
    @endif
  </div>
  <div class="band">
    <span>{{ $window['banner'] }}</span>
    <span class="chip {{ $window['status'] === 'open' ? 'good' : ($window['status'] === 'upcoming' ? 'warn' : 'bad') }}">{{ $poLabel }}</span>
    @if($window['opensAt'] && $window['closesAt'] && $window['eventAt'])
      <span class="small">PO {{ $window['opensAt']->format('d/m/Y') }} – {{ $window['closesAt']->format('d/m/Y') }} · Pengambilan {{ $window['eventAt']->format('d/m/Y') }}</span>
    @endif
  </div>
</div>

<section class="sec-b" style="margin-top:28px">
  <div class="wrap">
    <div class="sec-h"><h2>Cerita dari kelas</h2><a class="btn sec sm" href="{{ route('projects.index') }}">Semua proyek</a></div>
    <p class="story-scroll-hint">Geser ke samping untuk melihat cerita lainnya</p>
    <div class="story-strip" role="region" aria-label="Cerita proyek kelas" tabindex="0">
      @forelse($projects as $project)
        <a class="story" href="{{ route('projects.show', $project) }}">
          <div class="im">@include('marketplace.partials.project-art', ['project' => $project])</div>
          <span class="chip">{{ $project->class_name }}</span>
          <h3>{{ $project->title }}</h3>
          <p>{{ $project->introduction }}</p>
          <span class="btn sec sm" style="align-self:flex-start">Baca proyek</span>
        </a>
      @empty
        <div class="empty"><h3>Cerita kelas sedang disiapkan</h3><p>Proyek yang diterbitkan akan muncul di sini.</p></div>
      @endforelse
    </div>
  </div>
</section>

<section class="sec-b">
  <div class="wrap split">
    <div>
      <h2>Kelas yang berpartisipasi</h2>
      <div class="subs">
        @forelse($projects as $project)
          <div>
            <h3>{{ $project->class_name }}</h3>
            <p>{{ $project->title }}. {{ $project->published_products_count }} produk dalam katalog.</p>
          </div>
        @empty
          <p class="muted">Informasi kelas akan ditampilkan setelah proyek diterbitkan.</p>
        @endforelse
      </div>
      <div class="btns">
        <a class="btn" href="{{ route('projects.index') }}">Baca proyek</a>
        <a class="btn sec" href="{{ route('catalog.index') }}">Buka katalog</a>
      </div>
    </div>
    <div class="big">@include('marketplace.partials.garden-scene', ['class' => ''])</div>
  </div>
</section>

<section class="sec-b">
  <div class="wrap">
    <div class="sec-h"><h2>Cara pre-order dan pengambilan</h2></div>
    <ol class="steps">
      <li><h3>Pilih produk</h3><p>Boleh dari beberapa kelas sekaligus dalam satu pesanan.</p></li>
      <li><h3>Bayar sebelum tenggat</h3><p>@if($window['paymentDeadline'])Batas pembayaran {{ $window['paymentDeadline']->format('d/m/Y H:i') }}.@endif Pembayaran di prototype ini simulasi.</p></li>
      <li><h3>Terima QR per kelas</h3><p>Tiap kelas yang kamu pesan punya QR pengambilan sendiri.</p></li>
      <li><h3>Ambil di acara</h3><p>@if($window['eventAt']){{ $window['eventAt']->format('d/m/Y') }}@endif{{ $window['location'] ? ', '.Str::before($window['location'], ',') : '' }}. Tanpa ekspedisi.</p></li>
    </ol>
  </div>
</section>

<section class="sec-b">
  <div class="wrap">
    <div class="sec-h"><h2>Catatan dari tim kelas</h2></div>
    <div class="grid3">
      @foreach($quotes as [$quote, $team])
        <figure class="qcard">
          <q>{{ $quote }}</q>
          <figcaption><b>{{ $team }}</b><div class="muted small">Catatan lapangan (contoh)</div></figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>

<section class="ctab">
  <div class="wrap">
    <h2>Siap ikut PO bersama?</h2>
    <div class="btns"><a class="btn" href="{{ route('catalog.index') }}">Lihat katalog</a><a class="btn sec" href="{{ route('help') }}">Baca cara pemesanan</a></div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const carousel = document.querySelector('[data-hero-carousel]');
  if (!carousel) return;

  const track = carousel.querySelector('[data-hero-track]');
  const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
  const controls = [...carousel.querySelectorAll('[data-hero-index]')];
  const prevBtn = carousel.querySelector('[data-hero-prev]');
  const nextBtn = carousel.querySelector('[data-hero-next]');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  if (slides.length <= 1) {
    if (prevBtn) prevBtn.style.display = 'none';
    if (nextBtn) nextBtn.style.display = 'none';
    return;
  }

  const slideDuration = 5000;
  let activeIndex = 0;
  let autoPlayId = null;
  let pointerInside = false;
  let focusInside = false;
  let isPointerDown = false;
  let isDragging = false;
  let startX = 0;
  let currentX = 0;
  let startY = 0;
  let hasDeterminedDirection = false;
  let isHorizontalDrag = false;
  let dragOffset = 0;

  const stopAutoPlay = () => {
    if (autoPlayId !== null) {
      clearInterval(autoPlayId);
      autoPlayId = null;
    }
  };

  const startAutoPlay = () => {
    stopAutoPlay();
    if (slides.length < 2 || pointerInside || isPointerDown || focusInside) {
      return;
    }
    autoPlayId = setInterval(() => {
      goToSlide((activeIndex + 1) % slides.length);
    }, slideDuration);
  };

  const updateSlidePosition = () => {
    if (!track) return;
    track.style.transform = `translate3d(-${activeIndex * 100}%, 0, 0)`;
  };

  const goToSlide = (nextIndex) => {
    activeIndex = (nextIndex + slides.length) % slides.length;
    updateSlidePosition();

    controls.forEach((button, index) => {
      button.setAttribute('aria-pressed', index === activeIndex ? 'true' : 'false');
    });
  };

  // Dot button controls
  controls.forEach((control) => {
    control.addEventListener('click', () => {
      const targetIndex = Number(control.dataset.heroIndex);
      if (Number.isInteger(targetIndex)) {
        goToSlide(targetIndex);
        stopAutoPlay();
        startAutoPlay();
      }
    });
  });

  // Prev & Next Buttons
  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goToSlide(activeIndex - 1);
      stopAutoPlay();
      startAutoPlay();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      goToSlide(activeIndex + 1);
      stopAutoPlay();
      startAutoPlay();
    });
  }

  // Pointer drag events (supports both touch on mobile & mouse drag on desktop)
  const onPointerDown = (e) => {
    // Only primary button (left click) or touch
    if (e.button !== undefined && e.button !== 0) return;
    isPointerDown = true;
    startX = e.clientX;
    currentX = e.clientX;
    startY = e.clientY;
    dragOffset = 0;
    isDragging = false;
    hasDeterminedDirection = false;
    isHorizontalDrag = false;
    stopAutoPlay();
  };

  const onPointerMove = (e) => {
    if (!isPointerDown) return;
    currentX = e.clientX;
    const diffX = currentX - startX;
    const diffY = e.clientY - startY;

    if (!hasDeterminedDirection) {
      if (Math.abs(diffX) > 7 || Math.abs(diffY) > 7) {
        hasDeterminedDirection = true;
        if (Math.abs(diffX) >= Math.abs(diffY)) {
          isHorizontalDrag = true;
          isDragging = true;
          track.classList.add('is-dragging');
          carousel.classList.add('is-dragging');
          if (carousel.setPointerCapture) {
            try { carousel.setPointerCapture(e.pointerId); } catch (_) {}
          }
        } else {
          // Vertical scrolling intent: release so browser scrolls normally
          isHorizontalDrag = false;
          isPointerDown = false;
          return;
        }
      }
    }

    if (isDragging && isHorizontalDrag) {
      dragOffset = diffX;
      // Real-time smooth cursor follow
      const basePercent = -activeIndex * 100;
      track.style.transform = `translate3d(calc(${basePercent}% + ${dragOffset}px), 0, 0)`;
    }
  };

  const onPointerUp = (e) => {
    if (!isPointerDown && !isDragging) return;
    const wasDragging = isDragging;
    isPointerDown = false;
    isDragging = false;
    track.classList.remove('is-dragging');
    carousel.classList.remove('is-dragging');

    if (carousel.releasePointerCapture) {
      try { carousel.releasePointerCapture(e.pointerId); } catch (_) {}
    }

    if (wasDragging) {
      const carouselWidth = carousel.clientWidth || 1;
      const threshold = Math.min(carouselWidth * 0.15, 60);

      if (dragOffset < -threshold) {
        goToSlide(activeIndex + 1);
      } else if (dragOffset > threshold) {
        goToSlide(activeIndex - 1);
      } else {
        goToSlide(activeIndex);
      }

      // Prevent accidental click on links if user dragged
      if (Math.abs(dragOffset) > 10) {
        const preventClick = (clickEvent) => {
          clickEvent.preventDefault();
          clickEvent.stopPropagation();
          carousel.removeEventListener('click', preventClick, true);
        };
        carousel.addEventListener('click', preventClick, true);
        setTimeout(() => {
          carousel.removeEventListener('click', preventClick, true);
        }, 60);
      }
    }

    dragOffset = 0;
    pointerInside = false;
    startAutoPlay();
  };

  carousel.addEventListener('pointerdown', onPointerDown);
  carousel.addEventListener('pointermove', onPointerMove);
  carousel.addEventListener('pointerup', onPointerUp);
  carousel.addEventListener('pointercancel', onPointerUp);
  carousel.addEventListener('pointerleave', () => {
    pointerInside = false;
    if (!isPointerDown) {
      startAutoPlay();
    }
  });

  // Desktop horizontal wheel / Trackpad 2-finger scroll
  let wheelTimeout = null;
  let accumulatedDeltaX = 0;

  carousel.addEventListener('wheel', (e) => {
    const isHorizontalIntent = Math.abs(e.deltaX) > Math.abs(e.deltaY) || (e.shiftKey && Math.abs(e.deltaY) > 0);
    if (!isHorizontalIntent) {
      // Normal vertical scrolling: do not interrupt!
      return;
    }

    e.preventDefault();
    stopAutoPlay();

    const delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
    accumulatedDeltaX += delta;

    if (Math.abs(accumulatedDeltaX) >= 45) {
      if (accumulatedDeltaX > 0) {
        goToSlide(activeIndex + 1);
      } else {
        goToSlide(activeIndex - 1);
      }
      accumulatedDeltaX = 0;
    }

    clearTimeout(wheelTimeout);
    wheelTimeout = setTimeout(() => {
      accumulatedDeltaX = 0;
      startAutoPlay();
    }, 350);
  }, { passive: false });

  // Keyboard navigation when carousel is focused
  carousel.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      goToSlide(activeIndex - 1);
      stopAutoPlay();
      startAutoPlay();
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      goToSlide(activeIndex + 1);
      stopAutoPlay();
      startAutoPlay();
    }
  });

  // Hover and focus listeners for smart autoplay
  carousel.addEventListener('mouseenter', () => {
    pointerInside = true;
    stopAutoPlay();
  });
  carousel.addEventListener('mouseleave', () => {
    pointerInside = false;
    startAutoPlay();
  });
  carousel.addEventListener('focusin', (e) => {
    if (e.target && (e.target.matches('button, a') && e.target.matches(':focus-visible'))) {
      focusInside = true;
      stopAutoPlay();
    }
  });
  carousel.addEventListener('focusout', (event) => {
    if (!carousel.contains(event.relatedTarget)) {
      focusInside = false;
      startAutoPlay();
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoPlay();
    } else {
      pointerInside = false;
      isPointerDown = false;
      startAutoPlay();
    }
  });

  goToSlide(0);
  startAutoPlay();
})();
</script>
@endpush
