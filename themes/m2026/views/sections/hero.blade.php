<section class="m2026-section m2026-hero" data-section-type="hero">
    <div class="m2026-container">
        @if (! empty($content['eyebrow']))
            <p class="m2026-hero__eyebrow">{{ $content['eyebrow'] }}</p>
        @endif

        @if (! empty($content['title']))
            <h2>{{ $content['title'] }}</h2>
        @endif

        @if (! empty($content['text']))
            <p class="m2026-hero__text">{{ $content['text'] }}</p>
        @endif

        @if (! empty($content['primary_label']) && ! empty($content['primary_url']))
            <div class="m2026-actions">
                <a class="m2026-button m2026-button--primary" href="{{ $content['primary_url'] }}">
                    {{ $content['primary_label'] }}
                </a>

                @if (! empty($content['secondary_label']) && ! empty($content['secondary_url']))
                    <a class="m2026-button m2026-button--secondary" href="{{ $content['secondary_url'] }}">
                        {{ $content['secondary_label'] }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>
