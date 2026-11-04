@php
    $current = app()->getLocale();
    $route = request()->route();
@endphp

<nav aria-label="{{ __('common.language') }}">
    <ul role="list" class="switcher">
        @foreach (config('portfolio.locales') as $code => $localeMeta)
            <li>
                <a href="{{ $route
                        ? route($route->getName(), array_merge($route->parameters, ['locale' => $code]))
                        : route('home', ['locale' => $code]) }}"
                   title="{{ $localeMeta['name'] }}"
                   @if ($code === $current) aria-current="true" @endif
                   class="switcher-link">
                    {{ $localeMeta['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
