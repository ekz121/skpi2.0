@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Navigasi halaman">
        <p class="pager-summary">
            Menampilkan <strong>{{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}</strong>
            dari <strong>{{ $paginator->total() }}</strong> data
        </p>

        <div class="pager-controls">
            @if ($paginator->onFirstPage())
                <span class="pager-direction is-disabled" aria-disabled="true">
                    @include('partials.icon', ['name' => 'chevron', 'class' => 'pager-chevron pager-chevron-back'])
                    <span>Sebelumnya</span>
                </span>
            @else
                <a class="pager-direction" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    @include('partials.icon', ['name' => 'chevron', 'class' => 'pager-chevron pager-chevron-back'])
                    <span>Sebelumnya</span>
                </a>
            @endif

            <span class="pager-mobile-position">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

            <div class="pager-pages" aria-label="Nomor halaman">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="pager-ellipsis" aria-hidden="true">&hellip;</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pager-page is-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                            @else
                                <a class="pager-page" href="{{ $url }}" aria-label="Buka halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a class="pager-direction" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Berikutnya</span>
                    @include('partials.icon', ['name' => 'chevron', 'class' => 'pager-chevron'])
                </a>
            @else
                <span class="pager-direction is-disabled" aria-disabled="true">
                    <span>Berikutnya</span>
                    @include('partials.icon', ['name' => 'chevron', 'class' => 'pager-chevron'])
                </span>
            @endif
        </div>
    </nav>
@endif
