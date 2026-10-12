@extends('layouts.app')
@section('title', $page->title.' — Білім')
@section('content')<section class="wrap simple-page content-article"><div class="eyebrow">АҚПАРАТТЫҚ БЕТ</div><h1>{{ $page->title }}</h1><div class="prose">{{ $page->body }}</div><div class="page-links"><a class="outline" href="{{ route('contacts') }}">Байланыс →</a><a class="outline" href="{{ route('content.index', 'documents') }}">Құжаттар →</a><a class="outline" href="{{ route('content.index', 'announcements') }}">Хабарландырулар →</a></div></section>@endsection
