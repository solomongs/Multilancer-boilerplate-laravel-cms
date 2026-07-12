<section class="m2026-section m2026-rich-text" data-section-type="rich_text">
    <div class="m2026-container">
        @if (! empty($content['heading']))
            <h2>{{ $content['heading'] }}</h2>
        @endif

        @if (! empty($content['body']))
            <div class="m2026-prose">{!! nl2br(e((string) $content['body'])) !!}</div>
        @endif
    </div>
</section>
