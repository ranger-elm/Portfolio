@extends('layouts.app')

@section('title', __('meta.projects.title'))
@section('description', __('meta.projects.description'))

@section('content')
    <x-section label-id="projects-heading" :title="__('nav.projects')" :level="1">
        <p class="t-lede text-muted">{{ __('projects.intro') }}</p>

        <div class="mt-10 grid gap-6 grid-cols-[repeat(auto-fit,minmax(20rem,1fr))]">
            @foreach (array_keys(config('content.projects')) as $slug)
                <x-project-card slug="{{ $slug }}" />
            @endforeach
        </div>
    </x-section>
@endsection
