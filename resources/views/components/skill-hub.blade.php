@php
    $skills = config('content.skills');
    $slugs = array_keys($skills);
    $core = array_keys(array_filter($skills, fn ($skill) => $skill['tier'] === 'core'));
    $featured = $core[0] ?? $slugs[0];
    $featuredIndex = array_search($featured, $slugs, true);
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center lg:gap-10"
     data-skill-hub
     data-skill-hub-label="{{ __('home.skillsHeading') }}"
     data-skill-active="{{ $featuredIndex }}"
     style="--hub-count: {{ count($slugs) }}; --hub-active: {{ $featuredIndex }};">

    <div data-skill-ring class="hub-ring">
        <div data-skill-track class="hub-track">
            @foreach ($slugs as $index => $slug)
                @php $tier = $skills[$slug]['tier']; @endphp

                <button type="button"
                        data-skill-card
                        data-skill-panel="panel-{{ $slug }}"
                        aria-controls="panel-{{ $slug }}"
                        id="tab-{{ $slug }}"
                        style="--i: {{ $index }};"
                        class="hub-card skill-card {{ $tier === 'core' ? 'hub-card--core skill-card--core' : '' }}">
                    <span class="hub-card-name"><bdi>{{ $skills[$slug]['name'] }}</bdi></span>
                    <span class="hub-card-tier">{{ __("skills.tiers.$tier") }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div>
        <div class="hub-panels">
            @foreach ($slugs as $slug)
                <article data-skill-panel="panel-{{ $slug }}"
                         id="panel-{{ $slug }}"
                         aria-labelledby="tab-{{ $slug }}"
                         class="hub-panel panel">
                    <p class="t-label panel-eyebrow">{{ __('skills.hubLabel') }}</p>

                    <h3 class="t-title mt-2 text-ink">
                        <bdi>{{ $skills[$slug]['name'] }}</bdi>
                    </h3>

                    <p class="t-lede mt-3 text-muted">
                        {{ __("skills.items.$slug.description") }}
                    </p>

                    <p class="t-label mt-4 text-muted">
                        {{ __("skills.tiers.{$skills[$slug]['tier']}") }}
                    </p>

                    @if (! empty($skills[$slug]['related']))
                        <p class="mt-5 border-t border-line pt-4 text-sm text-muted">
                            <span class="t-label block text-brand-soft">{{ __('skills.relatedProjects') }}</span>

                            @foreach ($skills[$slug]['related'] as $related)
                                <bdi class="text-ink">{{ config("content.projects.$related.name") }}</bdi>
                            @endforeach
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="button"
                    data-skill-prev
                    class="btn btn--quiet btn--icon">
                <span class="sr-only">{{ __('skills.previous') }}</span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" class="rtl:rotate-180">
                    <path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <button type="button"
                    data-skill-next
                    class="btn btn--quiet btn--icon">
                <span class="sr-only">{{ __('skills.next') }}</span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" class="rtl:rotate-180">
                    <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <p class="t-hint max-w-xs text-muted">{{ __('skills.hint') }}</p>
        </div>
    </div>
</div>
