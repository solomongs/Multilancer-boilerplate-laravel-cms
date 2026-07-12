<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Multilancer Limited'))</title>
    <meta name="description" content="@yield('meta_description', 'Multilancer Limited technology, training and digital services.')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', config('app.name', 'Multilancer Limited'))">
    <meta property="og:description" content="@yield('meta_description', 'Multilancer Limited technology, training and digital services.')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name', 'Multilancer Limited'))">
    <meta name="twitter:description" content="@yield('meta_description', 'Multilancer Limited technology, training and digital services.')">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @themeCss
    @stack('head')
    @livewireStyles
</head>
<body class="m2026-site">
    <a class="m2026-skip-link" href="#main-content">Skip to content</a>

    @if (! empty($isPreview))
        <div class="m2026-preview-banner" role="status">
            Preview mode: this page may not be publicly published.
        </div>
    @endif

    @includeIf('components.header')

    <main id="main-content">
        @yield('content')
    </main>

    @includeIf('components.footer')

    @livewireScripts
    @themeJs
    @stack('scripts')
</body>
</html>
