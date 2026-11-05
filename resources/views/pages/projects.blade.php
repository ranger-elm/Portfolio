@extends('layouts.app')

@section('title', __('meta.projects.title'))
@section('description', __('meta.projects.description'))

@section('content')
    <x-section label-id="projects-heading" :title="__('nav.projects')" :level="1">
        <p class="t-lede text-muted">{{ __('projects.intro') }}</p>

        {{-- The 20rem floor is what gives a card its shape, but a track cannot be
             wider than the column it lives in: below ~340px of content the bare
             20rem would push the card past the viewport and open a horizontal
             scroll. min() lets the floor fall to the container's own width and
             no lower, so the grid narrows instead of spilling. --}}
        <div class="mt-10 grid gap-6 grid-cols-[repeat(auto-fit,minmax(min(20rem,100%),1fr))]">
            @foreach (array_keys(config('content.projects')) as $slug)
                <x-project-card slug="{{ $slug }}" />
            @endforeach
        </div>
    </x-section>
@endsection
