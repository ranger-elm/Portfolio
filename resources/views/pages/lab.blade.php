@extends('layouts.app')

@section('title', __('meta.lab.title'))
@section('description', __('meta.lab.description'))

@section('content')
    <x-section label-id="lab-heading" :title="__('lab.heading')" :level="1">
        <p class="t-lede text-muted">{{ __('lab.intro') }}</p>

        <ul role="list" class="mt-8">
            @foreach (config('content.lab') as $slug => $item)
                <x-lab-item :slug="$slug" :item="$item" :level="2" />
            @endforeach
        </ul>
    </x-section>
@endsection
