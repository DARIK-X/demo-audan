<!doctype html>
<html lang="kk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#163fbb"><title>@yield('title', 'Білім — аудандық білім порталы')</title><link rel="stylesheet" href="{{ asset('css/site.css') }}"><script src="{{ asset('js/site.js') }}" defer></script></head>
<body><a class="skip-link" href="#main-content">Мазмұнға өту</a>
<div class="utility"><div class="wrap"><span>АУДАНДЫҚ БІЛІМ БӨЛІМІ · АҚПАРАТТЫҚ ПОРТАЛ</span><a href="{{ route('contacts') }}">Байланыс ↗</a></div></div>
<header class="wrap"><a class="brand" href="{{ route('home') }}"><span class="logo">б<span>•</span></span><span>білім<span class="brand-sub">{{ $portalSettings['site_title'] ?? 'Аудандық білім порталы' }}</span></span></a><nav aria-label="Негізгі мәзір"><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Басты бет</a><a href="{{ route('schools.index') }}" @class(['active' => request()->routeIs('schools.*')])>Мектептер</a><a href="{{ route('content.index', 'news') }}">Жаңалықтар</a><a href="{{ route('contacts') }}">Байланыс</a></nav><a class="outline" href="{{ auth()->user()?->can('manage-portal') ? route('admin.index') : route('login') }}">⚙ Басқару</a></header>
<nav class="section-nav wrap" aria-label="Портал бөлімдері">
<a href="{{ route('about') }}" @class(['selected' => request()->routeIs('about')])>Бөлім туралы</a>
@foreach(config('portal.categories') as $key => $label)<a href="{{ route('content.index', $key) }}" @class(['selected' => request()->route('category') === $key])>{{ $label }}</a>@endforeach
@foreach(config('portal.pages') as $key => $label) @if($key !== 'about')<a href="{{ route('pages.show', $key) }}" @class(['selected' => request()->route('slug') === $key])>{{ $label }}</a>@endif @endforeach
<a href="{{ route('resources') }}" @class(['selected' => request()->routeIs('resources')])>Пайдалы сілтемелер</a>
</nav>
<main id="main-content">@yield('content')</main>
<footer class="wrap"><a class="brand" href="{{ route('home') }}"><span class="logo">б<span>•</span></span><span>білім</span></a><span>© {{ date('Y') }} · {{ $portalSettings['site_title'] ?? 'Аудандық білім порталы' }}</span><a href="{{ route('contacts') }}">Байланыс →</a></footer>
</body></html>
