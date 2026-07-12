<section class="m2026-section m2026-cta" data-section-type="call_to_action">
    <div class="m2026-container m2026-cta__inner">
        <div>
            @if (! empty($content['heading']))
                <h2>{{ $content['heading'] }}</h2>
            @endif

            @if (! empty($content['text']))
                <p>{{ $content['text'] }}</p>
            @endif
        </div>

        @if (! empty($content['button_label']) && ! empty($content['button_url']))
            <a class="m2026-button m2026-button--primary" href="{{ $content['button_url'] }}">
                {{ $content['button_label'] }}
            </a>
        @endif
    </div>
</section>
