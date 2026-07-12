@extends(theme_layout('app'))

@section('title', $page->meta_title ?? $page->title ?? config('app.name', 'Multilancer Limited'))
@section('meta_description', $page->meta_description ?? '')
@section('robots', ! empty($isPreview) ? 'noindex,nofollow' : ($page->robots ?: 'index,follow'))
@section('canonical_url', $page->canonical_url ?: url()->current())
@section('og_type', 'website')

@section('content')
    @forelse (($sections ?? []) as $section)
        @php
            $type = is_array($section) ? ($section['type'] ?? null) : ($section->section_type ?? null);
            $data = is_array($section) ? ($section['content'] ?? []) : ($section->content ?? []);
        @endphp

        @if (is_string($type) && preg_match('/^[a-z0-9_-]+$/', $type))
            @includeIf("sections.{$type}", ['section' => $section, 'content' => $data])
        @endif
    @empty
        <section data-m2026-placeholder="home">
            {{-- The audited M2026 homepage sections will replace this scaffold. --}}
        </section>
    @endforelse
@endsection
