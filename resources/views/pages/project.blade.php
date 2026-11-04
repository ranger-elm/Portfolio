@extends('layouts.app')

@section('title', __('meta.project.title', ['project' => config("content.projects.$slug")['name']]))
@section('description', __("projects.items.$slug.description"))

@section('content')
    @php
        $project = config("content.projects.$slug");
        $status = __('status.' . $project['status']);
        $description = __("projects.items.$slug.description");
        $locale = app()->getLocale();
        $screenshots = $project['screenshots'] ?? [];

        // A section renders only when this locale actually supplies copy, so no
        // heading ever sits above an empty block.
        $sections = [];
        foreach (['problem', 'goals', 'role', 'decisions', 'focus', 'features', 'challenges', 'lessons'] as $key) {
            $langKey = "projects.items.$slug.$key";
            if (Lang::has($langKey)) {
                $sections[] = ['label' => __("projects.detail.$key"), 'body' => __($langKey)];
            }
        }

        $usedSkills = array_keys(array_filter(
            config('content.skills'),
            fn ($skill) => in_array($slug, $skill['related'] ?? [], true)
        ));

        // A technology chip links to the skill hub only when the same word is a
        // configured skill, so the aside never repeats the list twice.
        $skillByName = [];
        foreach ($usedSkills as $skillSlug) {
            $skillByName[config("content.skills.$skillSlug.name")] = $skillSlug;
        }
    @endphp

    <section class="section" aria-labelledby="project-heading">
        <div class="mx-auto w-full max-w-[78rem] px-5">
            <a href="{{ route('projects', ['locale' => $locale]) }}" class="link-quiet group">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" class="transition-transform group-hover:-translate-x-0.5 rtl:rotate-180 rtl:group-hover:translate-x-0.5">
                    <path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('nav.backToProjects') }}
            </a>

            <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)] lg:gap-12">
                <article>
                    <p class="t-label text-brand-soft">{{ __('projects.projectOverview') }}</p>

                    <h1 id="project-heading" class="t-title mt-3 text-ink">
                        <bdi>{{ $project['name'] }}</bdi>
                    </h1>

                    <p class="t-lede mt-5 text-muted">{{ $description }}</p>

                    @foreach ($sections as $section)
                        <section class="mt-10">
                            <h2 class="t-label text-muted">{{ $section['label'] }}</h2>

                            @if (is_array($section['body']))
                                <ul role="list" class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($section['body'] as $entry)
                                        <li>
                                            <span class="chip"><bdi>{{ $entry }}</bdi></span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="t-lede mt-4 text-muted">{{ $section['body'] }}</p>
                            @endif
                        </section>
                    @endforeach

                    @if (! empty($screenshots))
                        <section class="mt-10">
                            <h2 class="t-label text-muted">{{ __('projects.detail.screenshots') }}</h2>

                            <ul role="list" class="mt-4 grid gap-4">
                                @foreach ($screenshots as $screenshot)
                                    <li>
                                        <img src="{{ asset($screenshot['path']) }}"
                                             alt="{{ $screenshot['alt'] }}"
                                             width="{{ $screenshot['width'] }}"
                                             height="{{ $screenshot['height'] }}"
                                             loading="lazy"
                                             class="w-full rounded-card border border-line">
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </article>

                <aside class="lg:border-s lg:border-line lg:ps-8">
                    <dl class="spec spec--flush">
                        <div class="spec-row">
                            <dt class="t-label text-muted">{{ __('projects.status') }}</dt>

                            <dd>
                                <x-status-label :status="$status" />
                            </dd>
                        </div>

                        <div class="spec-row">
                            <dt class="t-label text-muted">{{ __('projects.technologies') }}</dt>

                            <dd>
                                <ul role="list" class="flex flex-wrap gap-1.5">
                                    @foreach ($project['technologies'] as $technology)
                                        <li>
                                            @if (isset($skillByName[$technology]))
                                                <a href="{{ route('home', ['locale' => $locale]) }}#skills" class="chip">
                                                    <bdi>{{ $technology }}</bdi>
                                                </a>
                                            @else
                                                <span class="chip"><bdi>{{ $technology }}</bdi></span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>

                        @if ($project['repository'] || $project['demo'])
                            <div class="spec-row">
                                <dt class="t-label text-muted">{{ __('projects.repository') }}</dt>

                                <dd class="flex flex-wrap gap-3">
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
                                </dd>
                            </div>
                        @endif
                    </dl>
                </aside>
            </div>
        </div>
    </section>
@endsection
