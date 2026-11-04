@extends('layouts.app')

@section('title', __('meta.contact.title'))
@section('description', __('meta.contact.description'))

@section('content')
    @php
        $email = config('portfolio.contact.email');

        // Destinations come from config only; an unset channel renders nothing,
        // so the page can never offer a link that was never confirmed.
        $channels = [
            ['url' => $email ? 'mailto:' . $email : null, 'label' => __('contact.emailMe'), 'value' => $email],
            ['url' => config('portfolio.contact.github'), 'label' => 'GitHub', 'value' => config('portfolio.contact.github')],
            ['url' => config('portfolio.contact.linkedin'), 'label' => 'LinkedIn', 'value' => config('portfolio.contact.linkedin')],
        ];

        $available = array_filter($channels, fn ($channel) => ! empty($channel['url']));
    @endphp

    <x-section label-id="contact-heading" :title="__('contact.heading')" :level="1">
        <p class="t-lede text-muted">{{ __('contact.supportingText') }}</p>

        <p class="t-title mt-10 text-ink">{{ __('contact.letsBuild') }}</p>

        @if (! empty($available))
            <h2 class="t-label mt-8 text-muted">{{ __('contact.findMeOnline') }}</h2>

            <dl class="spec mt-2">
                @foreach ($available as $channel)
                    <div class="spec-row">
                        <dt class="t-label text-brand-soft">{{ $channel['label'] }}</dt>

                        <dd>
                            <a href="{{ $channel['url'] }}" rel="me noopener"
                               @if (str_starts_with($channel['url'], 'http')) target="_blank" @endif
                               class="link-quiet text-ink">
                                <bdi>{{ $channel['value'] }}</bdi>
                            </a>
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </x-section>
@endsection
