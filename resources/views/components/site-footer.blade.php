@php
    $locale = app()->getLocale();
    $links = [
        'projects' => __('nav.projects'),
        'lab' => __('nav.lab'),
        'about' => __('nav.about'),
        'contact' => __('nav.contact'),
    ];
    $email = config('portfolio.contact.email');
    $github = config('portfolio.contact.github');
    $linkedin = config('portfolio.contact.linkedin');
@endphp

<footer class="border-t border-line bg-surface">
    <div class="mx-auto flex w-full max-w-[78rem] flex-col gap-8 px-5 py-10 sm:flex-row sm:items-start sm:justify-between">
        <div class="max-w-sm">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="brand">
                <span class="brand-mark">{{ config('portfolio.brand') }}</span>
                <span class="brand-tag"><span dir="ltr">{{ config('portfolio.tagline') }}</span></span>
            </a>

            <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('home.footerTagline') }}</p>
        </div>

        <nav aria-label="{{ __('common.mainNavigation') }}">
            <ul role="list" class="flex flex-wrap gap-x-4 gap-y-1">
                @foreach ($links as $name => $label)
                    <li>
                        <a href="{{ route($name, ['locale' => $locale]) }}" class="link-quiet">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($email || $github || $linkedin)
            <div>
                <h2 class="t-label text-muted">{{ __('contact.findMeOnline') }}</h2>

                <ul role="list" class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    @if ($email)
                        <li>
                            <a href="mailto:{{ $email }}" class="link-quiet">{{ __('contact.emailMe') }}</a>
                        </li>
                    @endif
                    @if ($github)
                        <li>
                            <a href="{{ $github }}" rel="me noopener" target="_blank" class="link-quiet">GitHub</a>
                        </li>
                    @endif
                    @if ($linkedin)
                        <li>
                            <a href="{{ $linkedin }}" rel="me noopener" target="_blank" class="link-quiet">LinkedIn</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    <div class="mx-auto w-full max-w-[78rem] border-t border-line px-5 py-5">
        <p class="text-[0.8rem] leading-relaxed text-muted">
            <span dir="ltr" class="block">© {{ date('Y') }} {{ config('portfolio.brand') }}</span>
            <span class="block">{{ __('common.allRightsReserved') }}</span>
        </p>
    </div>
</footer>
