@extends('layouts.admin')
@section('title', 'Материалды өңдеу — Білім')
@section('admin-content')<h1>{{ $article->exists ? 'Материалды өңдеу' : 'Материал қосу' }}</h1><form class="admin-form" method="post" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">@csrf @if($article->exists) @method('PUT') @endif
<label>Атауы<input name="title" value="{{ old('title', $article->title) }}" maxlength="180" required></label>
<label>Бөлім<select name="category">@foreach(config('portal.categories') as $key => $label)<option value="{{ $key }}" @selected(old('category', $article->category) === $key)>{{ $label }}</option>@endforeach</select></label>
<label>Қысқаша сипаттама<textarea name="excerpt" maxlength="400" rows="3">{{ old('excerpt', $article->excerpt) }}</textarea></label>
<label>Толық мәтін<textarea name="body" maxlength="30000" rows="12" required>{{ old('body', $article->body) }}</textarea></label>
<label>Фотосурет (JPG / PNG / WebP, 4 МБ дейін)<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
@if($article->image_path)<div class="current-photo"><img src="{{ asset('storage/'.$article->image_path) }}" alt="Қазіргі сурет"><label class="inline-check"><input name="remove_image" type="checkbox" value="1"> Суретті өшіру</label></div>@endif
<label>PDF құжаты (10 МБ дейін)<input name="attachment" type="file" accept="application/pdf"></label>@if($article->attachment_path)<p>{{ $article->attachment_name }}</p><label class="inline-check"><input name="remove_attachment" type="checkbox" value="1"> Құжатты өшіру</label>@endif
<label>Жариялау уақыты<input name="published_at" type="datetime-local" value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"><small>Бос қалдырсаңыз, жарияланған материал бірден көрінеді. Уақыт белдеуі: Asia/Qyzylorda.</small></label>
<input type="hidden" name="is_published" value="0"><label class="inline-check"><input name="is_published" type="checkbox" value="1" @checked(old('is_published', $article->is_published))> Сайтта жариялау</label><button class="button" type="submit">Сақтау ✓</button></form>@endsection
