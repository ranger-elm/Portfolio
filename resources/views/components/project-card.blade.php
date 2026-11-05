@props(['slug'])

@php
    $project = config("content.projects.$slug");
    $description = __("projects.items.$slug.description");
    $detailUrl = route('project', ['locale' => app()->getLocale(), 'slug' => $slug]);
@endphp

<article class="project-card glow">
    <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-3">
        <h3 class="t-sub">
            <a href="{{ $detailUrl }}" class="text-ink no-underline transition-colors hover:text-brand-soft">
                <bdi>{{ $project['name'] }}</bdi>
            </a>
        </h3>

        <x-status-label :status="__('status.' . $project['status'])" />
    </div>

    <p class="t-lede mt-4 text-muted">{{ $description }}</p>

    <dl class="spec mt-5">
        <div class="spec-row">
            <dt class="t-label text-muted">{{ __('projects.technologies') }}</dt>

            <dd>
                <ul role="list" class="flex flex-wrap gap-1.5">
                    @foreach ($project['technologies'] as $technology)
                        <li>
                            <span class="chip"><bdi>{{ $technology }}</bdi></span>
                        </li>
                    @endforeach
                </ul>
            </dd>
        </div>
    </dl>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ $detailUrl }}" class="btn btn--primary">{{ __('projects.viewProject') }}</a>

        @if ($project['repository'])
            <a href="{{ $project['repository'] }}" rel="noopener" target="_blank" class="btn btn--quiet">
                {{ __('projects.viewSource') }}
            </a>
        @endif

        @if ($project['demo'])
            <a href="{{ $project['demo'] }}" rel="noopener" target="_blank" class="btn btn--quiet">
                {{ __('projects.liveDemo') }}
            </a>
        @endif
    </div>
</article>
