@extends('layouts.admin')
@section('title', 'Баптаулар — Білім')
@section('admin-content')<h1>Байланыс және баптаулар</h1><form class="admin-form" method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
@foreach(['site_title' => ['Портал атауы', 120], 'contact_address' => ['Мекенжай', 400], 'contact_phone' => ['Телефон', 80], 'contact_email' => ['Email', 180], 'office_hours' => ['Жұмыс / қабылдау уақыты', 400]] as $key => $field)<label>{{ $field[0] }}<input name="{{ $key }}" maxlength="{{ $field[1] }}" type="{{ $key === 'contact_email' ? 'email' : 'text' }}" value="{{ old($key, $settings[$key] ?? '') }}" @required($key === 'site_title')></label>@endforeach
<label>Қосымша ақпарат<textarea name="contact_note" maxlength="2000" rows="5">{{ old('contact_note', $settings['contact_note'] ?? '') }}</textarea></label><button class="button" type="submit">Сақтау ✓</button></form>@endsection
