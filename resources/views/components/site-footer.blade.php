@php
    $locale = app()->getLocale();
    $links = [
        'projects' => __('nav.projects'),
        'lab' => __('nav.lab'),
        'about' => __('nav.about'),
        'contact' => __('nav.contact'),
    ];

    // Config is the only source: a channel with no URL renders nothing, so the
    // site can never offer a destination the owner has not written down.
    $channels = [];
    foreach (config('portfolio.contact') as $key => $url) {
        if (empty($url)) {
            continue;
        }

        $channels[] = [
            'url' => $key === 'email' ? 'mailto:'.$url : $url,
            'label' => $key === 'email' ? __('contact.emailMe') : __("contact.channels.$key"),
        ];
    }
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

        @if (! empty($channels))
            <div class="footer-group">
                <h2 class="t-label text-muted">{{ __('contact.findMeOnline') }}</h2>

                <ul role="list" class="footer-list mt-1">
                    @foreach ($channels as $channel)
                        <li>
                            <a href="{{ $channel['url'] }}" rel="me noopener"
                               @if (str_starts_with($channel['url'], 'http')) target="_blank" @endif
                               class="link-quiet"><bdi>{{ $channel['label'] }}</bdi></a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="footer-legal">
        <p class="t-hint text-muted"><span dir="ltr">© {{ date('Y') }} {{ config('portfolio.brand') }}</span></p>

        <p class="t-hint text-muted">{{ __('common.allRightsReserved') }}</p>
    </div>
</footer>
