<x-layouts.app>
    @section('title', $article['title'] . ' — The MH Journal')

    <article class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-8 text-xs text-muted">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="rounded transition hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('home') }}#style-journal" class="rounded transition hover:text-pink-deep focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-deep">The MH Journal</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-medium text-ink">{{ $article['title'] }}</li>
            </ol>
        </nav>

        {{-- Header --}}
        <header class="mx-auto max-w-3xl text-center">
            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-pink-mauve">{{ $article['category'] }}</p>
            <h1 class="mt-4 font-display text-3xl font-semibold leading-[1.15] text-ink sm:text-4xl lg:text-5xl">
                {{ $article['title'] }}
            </h1>
            <p class="mt-5 text-xs uppercase tracking-[0.18em] text-muted">
                {{ $publishedAt }} <span class="mx-2" aria-hidden="true">·</span> {{ $article['read_time'] }} menit baca
            </p>
        </header>

        {{-- Hero image --}}
        <div class="mx-auto mt-10 max-w-5xl overflow-hidden bg-[#F6EEF1] aspect-[16/9] sm:mt-12">
            @if (file_exists(public_path($article['img'])))
                <img src="{{ asset($article['img']) }}"
                     alt="{{ $article['title'] }}"
                     fetchpriority="high"
                     class="h-full w-full object-cover">
            @else
                <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
            @endif
        </div>

        {{-- Body --}}
        <div class="mx-auto mt-12 max-w-2xl sm:mt-16">
            <div class="space-y-5 text-[15px] leading-8 text-muted sm:text-base">
                @foreach ($article['intro'] as $p)
                    <p>{{ $p }}</p>
                @endforeach
            </div>

            @foreach ($article['sections'] as $section)
                <section class="mt-12">
                    <h2 class="font-display text-2xl font-semibold leading-snug text-ink">
                        {{ $section['heading'] }}
                    </h2>

                    @if (! empty($section['body']))
                        <div class="mt-4 space-y-5 text-[15px] leading-8 text-muted sm:text-base">
                            @foreach ($section['body'] as $p)
                                <p>{{ $p }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (! empty($section['tips']))
                        <ol class="mt-6 space-y-6">
                            @foreach ($section['tips'] as $n => [$tipTitle, $tipText])
                                <li class="flex gap-4">
                                    <span class="font-display text-3xl italic leading-none text-pink-mauve" aria-hidden="true">{{ $n + 1 }}</span>
                                    <div>
                                        <h3 class="text-sm font-medium uppercase tracking-wide text-ink">{{ $tipTitle }}</h3>
                                        <p class="mt-1.5 text-[15px] leading-7 text-muted">{{ $tipText }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endforeach

            {{-- Closing --}}
            <blockquote class="mt-14 border-y border-line py-8 text-center">
                <p class="font-display text-xl italic leading-relaxed text-ink">{{ $article['closing'] }}</p>
            </blockquote>

            <p class="mt-6 text-center text-xs uppercase tracking-[0.18em] text-muted">Ditulis oleh MH Scarf</p>

            {{-- Share --}}
            <div class="mt-4 text-center">
                <a href="https://wa.me/?text={{ urlencode($article['title'] . ' — ' . url()->current()) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 border-b border-ink/40 pb-1 text-[10px] font-medium uppercase tracking-[0.2em] text-ink transition hover:border-pink-mauve hover:text-pink-mauve">
                    Bagikan via WhatsApp <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </article>

    {{-- Products --}}
    @if ($products->isNotEmpty())
        <section class="border-y border-line/60 bg-surface/50 py-14 sm:py-16 md:py-20" aria-labelledby="journal-products-h">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 id="journal-products-h" class="text-center text-xl font-normal uppercase tracking-[0.25em] text-ink/80 sm:text-2xl">
                    Pilihan untuk Gaya Ini
                </h2>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:mt-10 sm:gap-5 lg:grid-cols-4 lg:gap-6">
                    @foreach ($products as $prod)
                        <x-product-card :product="$prod" />
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <x-primary-button :href="route('shop.index', array_filter(['q' => $article['product_query']]))" class="inline-flex min-h-[46px] items-center">
                        {{ $article['cta_label'] }}
                    </x-primary-button>
                </div>
            </div>
        </section>
    @endif

    {{-- More stories --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 md:py-20 lg:px-8" aria-labelledby="journal-more-h">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-[10px] uppercase tracking-[0.3em] text-pink-mauve">The MH Journal</p>
            <h2 id="journal-more-h" class="mt-3 text-xl font-normal uppercase tracking-[0.2em] text-ink/80 sm:text-2xl">
                Cerita Lainnya
            </h2>
        </div>

        <div class="mt-10 grid gap-x-5 gap-y-10 sm:grid-cols-2 lg:mx-auto lg:max-w-4xl">
            @foreach ($others as $o)
                <a href="{{ route('journal.show', $o['slug']) }}" class="group block text-center" aria-label="Read {{ $o['title'] }}">
                    <div class="aspect-[5/4] overflow-hidden bg-[#F6EEF1]">
                        @if (file_exists(public_path($o['img'])))
                            <img src="{{ asset($o['img']) }}" alt="{{ $o['title'] }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]">
                        @else
                            <div class="h-full w-full bg-gradient-to-br from-[#FAF5F7] via-[#F6EEF1] to-[#EFE2E7]"></div>
                        @endif
                    </div>
                    <h3 class="mt-4 text-sm font-medium uppercase tracking-wide text-ink transition-colors group-hover:text-pink-mauve">{{ $o['title'] }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-xs leading-relaxed text-muted">{{ $o['desc'] }}</p>
                    <span class="mt-4 inline-flex items-center gap-2 border-b border-ink/40 pb-1 text-[10px] font-medium uppercase tracking-[0.2em] text-ink transition-all duration-300 group-hover:gap-3 group-hover:border-pink-mauve group-hover:text-pink-mauve">
                        Read More <span aria-hidden="true">→</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>