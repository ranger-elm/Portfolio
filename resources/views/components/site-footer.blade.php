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

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-id">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="brand">
                <span class="brand-mark">{{ config('portfolio.brand') }}</span>
                <span class="brand-tag"><span dir="ltr">{{ config('portfolio.tagline') }}</span></span>
            </a>

            <p class="footer-note">{{ __('home.footerTagline') }}</p>
        </div>

        <nav class="footer-group" aria-label="{{ __('common.mainNavigation') }}">
            <ul role="list" class="footer-list">
                @foreach ($links as $name => $label)
                    <li>
                        <a href="{{ route($name, ['locale' => $locale]) }}" class="link-quiet">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($email || $github || $linkedin)
            <div class="footer-group">
                <h2 class="t-label text-muted">{{ __('contact.findMeOnline') }}</h2>

                <ul role="list" class="footer-list mt-1">
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

    <div class="footer-legal">
        <p class="t-hint text-muted"><span dir="ltr">© {{ date('Y') }} {{ config('portfolio.brand') }}</span></p>

        <p class="t-hint text-muted">{{ __('common.allRightsReserved') }}</p>
    </div>
</footer>
