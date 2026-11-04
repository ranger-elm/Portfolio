@props(['slug', 'item', 'level' => 3])

@php
    $level = in_array($level, [2, 3], true) ? $level : 3;
@endphp

<li {{ $attributes->merge(['class' => 'entry']) }}>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <h{{ $level }} class="t-sub text-ink">
            <bdi>{{ __("lab.items.$slug.name") }}</bdi>
        </h{{ $level }}>

        <x-status-label :status="__('status.' . $item['status'])" />
    </div>

    <p class="t-lede mt-3 text-muted lg:mt-0">
        {{ __("lab.items.$slug.description") }}
    </p>
</li>
