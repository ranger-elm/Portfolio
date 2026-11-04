@extends('layouts.app')

@section('title', __('meta.about.title'))
@section('description', __('meta.about.description'))

@section('content')
    @php
        $locale = app()->getLocale();

        $blocks = [
            ['label' => __('about.focus'), 'body' => __('about.focusBody')],
            ['label' => __('about.approach'), 'body' => __('about.approachBody')],
            ['label' => __('about.experimenting'), 'body' => __('about.experimentingBody')],
            ['label' => __('about.direction'), 'body' => __('about.directionBody')],
        ];
    @endphp

    <x-section label-id="about-heading" :title="__('about.heading')" :level="1">
        <p class="t-lede text-muted">{{ __('about.body') }}</p>

        <dl class="spec mt-8">
            @foreach ($blocks as $block)
                <div class="spec-row">
                    <dt class="t-label text-brand-soft">{{ $block['label'] }}</dt>
                    <dd class="t-lede text-muted">{{ $block['body'] }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('projects', ['locale' => $locale]) }}" class="btn btn--primary">
                {{ __('hero.primaryCta') }}
            </a>

            <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn--quiet">
                {{ __('hero.secondaryCta') }}
            </a>
        </div>
    </x-section>
@endsection
