@extends('layouts.admin')
@section('title', 'Ақпараттық беттер — Білім')
@section('admin-content')<h1>Ақпараттық беттер</h1><p class="muted">Бет мәтінін өзгертіп, жариялауды басқарыңыз.</p><div class="admin-list">@foreach($pages as $page)<article class="admin-row"><div><strong>{{ $page->title }}</strong><small>{{ $page->is_published ? 'Жарияланған' : 'Жасырылған' }}</small></div><a class="outline" href="{{ route('admin.pages.edit', $page) }}">Өңдеу</a></article>@endforeach</div>@endsection
