@php
    $locale = app()->getLocale();
    $routeName = request()->route()?->getName();
    // The detail route belongs to the Projects section in the navigation.
    $activeName = $routeName === 'project' ? 'projects' : $routeName;
    $links = [
        'home' => __('nav.home'),
        'projects' => __('nav.projects'),
        'lab' => __('nav.lab'),
        'about' => __('nav.about'),
        'contact' => __('nav.contact'),
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-night/95 backdrop-blur supports-[backdrop-filter]:bg-night/80">
    <div class="mx-auto flex w-full max-w-[78rem] items-center justify-between gap-6 px-5 py-3">
        <a href="{{ route('home', ['locale' => $locale]) }}" class="brand">
            <span class="brand-mark">{{ config('portfolio.brand') }}</span>
            <span class="brand-tag"><span dir="ltr">{{ config('portfolio.tagline') }}</span></span>
        </a>

        <nav aria-label="{{ __('common.mainNavigation') }}" class="hidden md:block">
            <ul class="flex items-center gap-1">
                @foreach ($links as $name => $label)
                    <li>
                        <a href="{{ route($name, ['locale' => $locale]) }}"
                           @if ($activeName === $name) aria-current="page" @endif
                           class="nav-link">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <x-language-switcher />

            <button type="button"
                    id="nav-toggle"
                    aria-controls="primary-navigation"
                    aria-expanded="false"
                    class="inline-flex size-11 items-center justify-center rounded-control border border-line text-ink md:hidden">
                <span class="sr-only">{{ __('common.toggleNavigation') }}</span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    <nav id="primary-navigation" hidden class="border-t border-line md:hidden" aria-label="{{ __('common.mainNavigation') }}">
        <ul class="mx-auto w-full max-w-[78rem] px-5 py-2">
            @foreach ($links as $name => $label)
                <li>
                    <a href="{{ route($name, ['locale' => $locale]) }}"
                       @if ($activeName === $name) aria-current="page" @endif
                       class="nav-link nav-link--block">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
