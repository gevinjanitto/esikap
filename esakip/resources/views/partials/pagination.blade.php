@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3 pt-5" data-testid="pagination">
        <p class="text-xs text-slate-500">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="icon-btn-sm opacity-40"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="icon-btn-sm" data-testid="pagination-prev"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
            @endif
            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 rounded-full grid place-items-center text-xs font-semibold {{ $page == $paginator->currentPage() ? 'bg-ink text-white' : 'text-slate-500 hover:bg-white' }}">{{ $page }}</a>
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="icon-btn-sm" data-testid="pagination-next"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
            @else
                <span class="icon-btn-sm opacity-40"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
            @endif
        </div>
    </nav>
@endif
