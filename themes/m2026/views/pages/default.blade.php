@extends(theme_layout('app'))

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: ($page->excerpt ?? ''))

@section('content')
    <article class="m2026-page" data-page-slug="{{ $page->slug }}">
        <header class="m2026-page__header">
            <div class="m2026-container">
                <h1>{{ $page->title }}</h1>

                @if ($page->excerpt)
                    <p>{{ $page->excerpt }}</p>
                @endif
            </div>
        </header>

        @foreach (($sections ?? []) as $section)
            @php
                $type = is_array($section) ? ($section['type'] ?? null) : ($section->section_type ?? null);
                $data = is_array($section) ? ($section['content'] ?? []) : ($section->content ?? []);
            @endphp

            @if (is_string($type) && preg_match('/^[a-z0-9_-]+$/', $type))
                @includeIf("sections.{$type}", ['section' => $section, 'content' => $data])
            @endif
        @endforeach
    </article>
@endsection
