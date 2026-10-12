@extends('layouts.admin')
@section('title', 'Басқару панелі — Білім')
@section('admin-content')<div class="admin-heading"><div><h1>Порталға шолу</h1><p>Мектептер, материалдар және ақпараттық беттер.</p></div></div>
<div class="admin-stats"><div><strong>{{ $schools }}</strong><span>Мектеп</span></div><div><strong>{{ $articles }}</strong><span>Материал</span></div><div><strong>{{ $pages }}</strong><span>Ақпараттық бет</span></div></div>
<div class="admin-actions"><a class="button" href="{{ route('admin.schools.create') }}">＋ Мектеп қосу</a><a class="outline" href="{{ route('admin.articles.create') }}">＋ Материал қосу</a><a class="outline" href="{{ route('admin.pages.index') }}">Беттерді өңдеу</a></div>
<h2>Соңғы материалдар</h2><div class="admin-list">@forelse($latest as $article)<article class="admin-row"><div><strong>{{ $article->title }}</strong><small>{{ config('portal.categories.'.$article->category) }} · {{ $article->is_published ? 'Жариялауға белгіленген' : 'Жоба' }}</small></div><a class="outline" href="{{ route('admin.articles.edit', $article) }}">Өңдеу</a></article>@empty<p>Әзірге материал қосылмаған.</p>@endforelse</div>@endsection
