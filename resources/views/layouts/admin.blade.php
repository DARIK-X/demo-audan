@extends('layouts.app')
@section('content')
<section class="wrap admin-page"><div class="admin-toolbar"><div><div class="eyebrow">ПОРТАЛДЫ БАСҚАРУ</div><span>{{ auth()->user()->name }}</span></div><form method="post" action="{{ route('logout') }}">@csrf<button class="outline" type="submit">Шығу</button></form></div>
<nav class="admin-nav" aria-label="Басқару мәзірі">
@foreach(['admin.index' => 'Шолу', 'admin.schools.index' => 'Мектептер', 'admin.articles.index' => 'Материалдар', 'admin.pages.index' => 'Ақпараттық беттер', 'admin.links.index' => 'Пайдалы сілтемелер', 'admin.settings.edit' => 'Байланыс / баптаулар', 'admin.account.edit' => 'Құпиясөз'] as $route => $label)<a href="{{ route($route) }}" @class(['active' => request()->routeIs($route)])>{{ $label }}</a>@endforeach
</nav>
@if(session('status'))<p class="success" role="status">{{ session('status') }}</p>@endif
@if(session('school_id'))<div class="save-links">@if(session('school_published'))<a class="outline" href="{{ route('schools.show', session('school_id')) }}" target="_blank" rel="noopener">Сайтта көру ↗</a>@endif<a class="outline" href="{{ route('admin.schools.edit', session('school_id')) }}">Мектепті өңдеу →</a></div>@endif
@if($errors->any())<div class="validation-summary" role="alert"><strong>Мәліметтерді тексеріңіз:</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
@yield('admin-content')
</section>
@endsection
