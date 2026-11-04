<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('portfolio.locales')[app()->getLocale()]['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0d12">
    <script>document.documentElement.classList.add('js');</script>

    <title>@yield('title', __('meta.home.title'))</title>
    <meta name="description" content="@yield('description', __('meta.home.description'))">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    @if (request()->route())
        @foreach (config('portfolio.locales') as $code => $localeMeta)
            <link rel="alternate" hreflang="{{ $code }}"
                  href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters, ['locale' => $code])) }}">
        @endforeach

        <link rel="alternate" hreflang="x-default"
              href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters, ['locale' => config('portfolio.default_locale')])) }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('portfolio.brand') }}">
    <meta property="og:title" content="@yield('title', __('meta.home.title'))">
    <meta property="og:description" content="@yield('description', __('meta.home.description'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ config('portfolio.locales')[app()->getLocale()]['og_locale'] }}">
    @foreach (config('portfolio.locales') as $code => $localeMeta)
        @if ($code !== app()->getLocale())
            <meta property="og:locale:alternate" content="{{ $localeMeta['og_locale'] }}">
        @endif
    @endforeach

    @if (app()->getLocale() === 'fa')
        <link rel="preload" as="font" type="font/woff2" crossorigin href="/fonts/vazirmatn-arabic-var.woff2">
        <link rel="preload" as="font" type="font/woff2" crossorigin href="/fonts/vazirmatn-latin-var.woff2">
    @else
        <link rel="preload" as="font" type="font/woff2" crossorigin href="/fonts/space-grotesk-latin-var.woff2">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col bg-night text-ink">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-50 focus:rounded-md focus:bg-elevated focus:px-4 focus:py-2 focus:text-ink focus:outline focus:outline-brand">
        {{ __('common.skipToContent') }}
    </a>

    <x-site-header />

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <x-site-footer />
</body>
</html>
