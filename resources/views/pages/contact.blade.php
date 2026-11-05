@extends('layouts.app')

@section('title', __('meta.contact.title'))
@section('description', __('meta.contact.description'))

@section('content')
    @php
        // Destinations come from config only; an unset channel renders nothing,
        // so the page can never offer a link that was never confirmed.
        $channels = [];
        foreach (config('portfolio.contact') as $key => $url) {
            if (empty($url)) {
                continue;
            }

            $channels[] = [
                'url' => $key === 'email' ? 'mailto:'.$url : $url,
                'label' => $key === 'email' ? __('contact.emailMe') : __("contact.channels.$key"),
                'value' => $url,
            ];
        }

        // No destination named in the environment means no address to send to, so
        // there is no form to draw and no endpoint behind the page either.
        $formOpen = filled(config('portfolio.form.recipient'));
        $minLength = (int) config('portfolio.form.min_message_length');
    @endphp

    <x-section label-id="contact-heading" :title="__('contact.heading')" :level="1">
        <p class="t-lede text-muted">{{ __('contact.supportingText') }}</p>

        @if (session('status'))
            <div class="form-outcome form-outcome--ok mt-8" role="status">
                <p class="t-label text-ink">{{ __('contact.successfulSubmit') }}</p>
                <p class="t-hint text-muted mt-1">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="form-outcome form-outcome--bad mt-8" role="alert">
                <p class="t-label">{{ __('contact.fixErrors') }}</p>
                <ul role="list" class="mt-1">
                    @foreach ($errors->all() as $problem)
                        <li class="t-hint">{{ $problem }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($formOpen)
            <h2 class="t-title mt-10 text-ink">{{ __('contact.sendMessage') }}</h2>

            <form method="POST" action="{{ route('contact.send', ['locale' => app()->getLocale()]) }}" class="mt-4">
                @csrf

                {{-- Bots fill every field they find; a person never finds this one.
                     It is clipped rather than display:none so a script reading the
                     stylesheet still sees a fillable input, while aria-hidden keeps
                     it out of the accessibility tree and tabindex keeps it out of
                     the tab order. --}}
                <div class="sr-only" aria-hidden="true">
                    <label for="company_website">Company website</label>
                    <input type="text" name="company_website" id="company_website"
                           tabindex="-1" autocomplete="off">
                </div>

                <div class="form-grid">
                    {{-- dir="auto" on each field: an email address or an English
                         sentence typed on the Persian page would otherwise be
                         right-aligned and read backwards, while a Persian answer
                         still flows from the right. --}}
                    <div class="field">
                        <label class="field-label" for="name">{{ __('contact.yourName') }}</label>
                        <input class="input" type="text" id="name" name="name" dir="auto"
                               value="{{ old('name') }}" required maxlength="80" autocomplete="name"
                               @error('name') aria-invalid="true" aria-describedby="name-problem" @enderror>
                        @error('name')
                            <p class="field-problem" id="name-problem">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field-label" for="email">{{ __('contact.yourEmail') }}</label>
                        <input class="input" type="email" id="email" name="email" dir="auto"
                               value="{{ old('email') }}" required maxlength="180" autocomplete="email"
                               @error('email') aria-invalid="true" aria-describedby="email-problem" @enderror>
                        @error('email')
                            <p class="field-problem" id="email-problem">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field field--wide">
                        <label class="field-label" for="subject">
                            {{ __('contact.subject') }}
                            <span class="t-hint text-muted">{{ __('contact.optional') }}</span>
                        </label>
                        <input class="input" type="text" id="subject" name="subject" dir="auto"
                               value="{{ old('subject') }}" maxlength="100"
                               @error('subject') aria-invalid="true" aria-describedby="subject-problem" @enderror>
                        @error('subject')
                            <p class="field-problem" id="subject-problem">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field field--wide">
                        <label class="field-label" for="message">{{ __('contact.yourMessage') }}</label>
                        <textarea class="textarea" id="message" name="message" rows="6" required dir="auto"
                                  maxlength="{{ (int) config('portfolio.form.max_message_length') }}"
                                  @error('message') aria-invalid="true" aria-describedby="message-hint message-problem" @enderror>{{ old('message') }}</textarea>
                        <p class="t-hint text-muted" id="message-hint">{{ __('contact.messageHint', ['min' => $minLength]) }}</p>
                        @error('message')
                            <p class="field-problem" id="message-problem">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn--primary mt-6">{{ __('contact.send') }}</button>

                <p class="t-hint text-muted mt-3">{{ __('contact.privacyNote') }}</p>
            </form>
        @endif

        <p class="t-title mt-10 text-ink">{{ __('contact.letsBuild') }}</p>

        @if (! empty($channels))
            <h2 class="t-label mt-8 text-muted">{{ __('contact.findMeOnline') }}</h2>

            <dl class="spec mt-2">
                @foreach ($channels as $channel)
                    <div class="spec-row">
                        <dt class="t-label text-brand-soft"><bdi>{{ $channel['label'] }}</bdi></dt>

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
