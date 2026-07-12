<section class="m2026-section m2026-image-text" data-section-type="image_text">
    <div class="m2026-container m2026-image-text__grid">
        @if (! empty($content['image_url']))
            <figure class="m2026-image-text__media">
                <img
                    src="{{ $content['image_url'] }}"
                    alt="{{ $content['image_alt'] ?? '' }}"
                    loading="lazy"
                    decoding="async"
                >
            </figure>
        @endif

        <div class="m2026-image-text__content">
            @if (! empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif

            @if (! empty($content['body']))
                <div class="m2026-prose">{!! nl2br(e((string) $content['body'])) !!}</div>
            @endif

            @if (! empty($content['button_label']) && ! empty($content['button_url']))
                <a class="m2026-button m2026-button--primary" href="{{ $content['button_url'] }}">
                    {{ $content['button_label'] }}
                </a>
            @endif
        </div>
    </div>
</section>
