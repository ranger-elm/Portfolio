@extends('layouts.app')

@section('title', __('meta.home.title'))
@section('description', __('meta.home.description'))

@php
    $locale = app()->getLocale();
    $featuredProjects = array_keys(array_filter(
        config('content.projects'),
        fn ($project) => $project['featured'] ?? false
    ));
    $labItems = config('content.lab');
@endphp

@section('content')
    <section class="section" aria-labelledby="hero-heading">
        <div class="mx-auto w-full max-w-[78rem] px-5">
            <div class="hero-rise grid items-center gap-12 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:gap-10">
                <div>
                    <p class="t-label text-brand-soft">
                        {{ __('hero.eyebrow') }}
                    </p>

                    <h1 id="hero-heading" class="t-display mt-5 text-ink">
                        {{ __('hero.headline') }}
                    </h1>

                    <p class="t-lede mt-6 text-muted">
                        {{ __('hero.introduction') }}
                    </p>

                    @if (config('portfolio.hero.show_status_label'))
                        <p class="mt-6">
                            <x-status-label :status="__('hero.statusLabel')" />
                        </p>
                    @endif

                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        <a href="{{ route('projects', ['locale' => $locale]) }}" class="btn btn--primary">
                            {{ __('hero.primaryCta') }}
                        </a>

                        <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn--quiet">
                            {{ __('hero.secondaryCta') }}
                        </a>
                    </div>
                </div>

                <x-hero-art />
            </div>
        </div>
    </section>

    <x-section
        label-id="selected-projects"
        :title="__('home.selectedProjectsHeading')"
        :link="route('projects', ['locale' => $locale])"
        :link-label="__('common.viewAll')"
    >
        <div class="grid gap-5">
            @foreach ($featuredProjects as $slug)
                <x-project-card slug="{{ $slug }}" />
            @endforeach
        </div>
    </x-section>

    <x-section label-id="skills" :title="__('home.skillsHeading')">
        <x-skill-hub />
    </x-section>

    <x-section label-id="about-preview" :title="__('home.aboutHeading')">
        <p class="t-lede text-muted">{{ __('about.body') }}</p>

        <a href="{{ route('about', ['locale' => $locale]) }}" class="link-quiet mt-6">
            {{ __('common.readMore') }}
        </a>
    </x-section>

    <x-section
        label-id="lab-preview"
        :title="__('home.labHeading')"
        :link="route('lab', ['locale' => $locale])"
        :link-label="__('common.viewAll')"
    >
        <ul role="list">
            @foreach ($labItems as $slug => $item)
                <x-lab-item :slug="$slug" :item="$item" :level="3" />
            @endforeach
        </ul>
    </x-section>

    <x-section label-id="contact-cta" :title="__('home.contactHeading')">
        <div class="frame">
            <p class="t-lede text-muted">{{ __('home.contactSupportingText') }}</p>

            <a href="{{ route('contact', ['locale' => $locale]) }}" class="btn btn--primary mt-8">
                {{ __('contact.letsConnect') }}
            </a>
        </div>
    </x-section>
@endsection
