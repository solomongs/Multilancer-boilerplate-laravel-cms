<footer class="m2026-footer" data-m2026-component="footer">
    <div class="m2026-footer__inner">
        <p>&copy; {{ now()->year }} {{ config('app.name', 'Multilancer Limited') }}. All rights reserved.</p>

        <nav aria-label="Footer navigation">
            @foreach (($footerNavigation ?? []) as $item)
                <a href="{{ $item['url'] ?? '#' }}" @if (! empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif>
                    {{ $item['label'] ?? '' }}
                </a>
            @endforeach
        </nav>
    </div>
</footer>
