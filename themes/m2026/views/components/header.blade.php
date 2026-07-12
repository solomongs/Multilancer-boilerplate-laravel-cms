<header class="m2026-header" data-m2026-component="header">
    <div class="m2026-header__inner">
        <a class="m2026-brand" href="{{ url('/') }}" aria-label="{{ config('app.name', 'Multilancer Limited') }} home">
            {{ config('app.name', 'Multilancer Limited') }}
        </a>

        <nav class="m2026-navigation" aria-label="Primary navigation">
            @foreach (($primaryNavigation ?? []) as $item)
                <a href="{{ $item['url'] ?? '#' }}" @if (! empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif>
                    {{ $item['label'] ?? '' }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
