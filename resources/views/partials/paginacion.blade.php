@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="pag pag--off">‹ Anterior</span>
        @else
            <a class="pag" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a>
        @endif

        <span class="pag-info">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="pag" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a>
        @else
            <span class="pag pag--off">Siguiente ›</span>
        @endif
    </nav>
@endif