@if ($paginator->hasPages())
<nav class="portal-pagination" aria-label="Беттерді ауыстыру">
    @if ($paginator->onFirstPage())<span aria-disabled="true">← Алдыңғы</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Алдыңғы</a>@endif
    <span>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Келесі →</a>@else<span aria-disabled="true">Келесі →</span>@endif
</nav>
@endif
