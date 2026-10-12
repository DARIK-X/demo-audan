@extends('layouts.app')
@section('title', $title.' — Білім')
@section('content')<section class="wrap portal-view"><div class="page-heading"><div class="eyebrow">АУДАНДЫҚ БІЛІМ ПОРТАЛЫ</div><h1>{{ $title }}</h1><p>Білім бөлімі жариялаған өзекті ақпарат пен материалдар.</p></div>
<form class="filters" method="get"><label class="search"><span>⌕</span><input type="search" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Материал атауын іздеу" aria-label="Материал іздеу"></label><button class="button" type="submit">Іздеу</button></form>
<p class="muted">{{ $articles->total() }} материал табылды</p><div class="material-grid">@forelse($articles as $article) @include('content.card') @empty<div class="portal-empty"><h3>Материал табылмады</h3><p>Әзірге жарияланым жоқ немесе іздеу сұрауын өзгерту керек.</p></div>@endforelse</div><div class="pagination">{{ $articles->links('partials.pagination') }}</div></section>@endsection
