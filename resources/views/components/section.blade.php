@props([
    'labelId',
    'title',
    'level' => 2,
    'link' => null,
    'linkLabel' => null,
])

@php
    $level = in_array($level, [1, 2, 3], true) ? $level : 2;
@endphp

<section {{ $attributes->merge(['class' => 'section']) }} aria-labelledby="{{ $labelId }}">
    <div class="section-inner">
        <header class="section-rail">
            <div class="rail-inner">
                <h{{ $level }} id="{{ $labelId }}" class="t-title text-ink">
                    {{ $title }}
                </h{{ $level }}>

                @if ($link && $linkLabel)
                    <a href="{{ $link }}" class="link-quiet link-rail">{{ $linkLabel }}</a>
                @endif
            </div>
        </header>

        <div class="section-body">
            {{ $slot }}
        </div>
    </div>
</section>
