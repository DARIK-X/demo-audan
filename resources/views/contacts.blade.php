@extends('layouts.app')
@section('title', 'Байланыс — Білім')
@section('content')<section class="wrap simple-page"><div class="eyebrow">БІЗБЕН БАЙЛАНЫС</div><h1>Байланыс мәліметтері</h1><div class="contact-grid">
@foreach(['contact_address' => 'Мекенжай', 'contact_phone' => 'Телефон', 'contact_email' => 'Email', 'office_hours' => 'Жұмыс / қабылдау уақыты'] as $key => $label)<article class="topic-card"><h3>{{ $label }}</h3><p>{{ ($portalSettings[$key] ?? '') ?: 'Әзірге енгізілмеген' }}</p></article>@endforeach
</div>@if($portalSettings['contact_note'] ?? null)<p class="prose">{{ $portalSettings['contact_note'] }}</p>@endif</section>@endsection
