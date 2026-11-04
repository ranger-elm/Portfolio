@extends('layouts.app')

@section('title', __('common.notFoundTitle'))
@section('description', __('common.notFoundBody'))
@section('robots', 'noindex, follow')

@section('content')
    <x-section label-id="not-found-heading" :title="__('common.notFoundTitle')" :level="1">
        <p class="t-label text-brand-soft">404</p>

        <p class="t-lede mt-4 text-muted">{{ __('common.notFoundBody') }}</p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn--primary">
                {{ __('common.backToHome') }}
            </a>

            <a href="{{ route('projects', ['locale' => app()->getLocale()]) }}" class="btn btn--quiet">
                {{ __('nav.projects') }}
            </a>
        </div>
    </x-section>
@endsection
