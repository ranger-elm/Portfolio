<?php

namespace Tests\Feature;

use DOMXPath;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * A grid that repeats with `auto-fit` picks its own column count, so the only
 * number standing between a card and the wrong side of the viewport is the floor
 * of its track. `minmax(20rem,1fr)` cannot drop below 20rem even inside a 265px
 * column, and the extra 55px opened a horizontal scroll on `/projects` at a 320px
 * viewport. The floor has to be `min(20rem,100%)` — the container's own width is
 * the one value a track can never exceed.
 */
class LayoutGuardTest extends TestCase
{
    public function test_a_track_that_ignores_its_container_is_reported(): void
    {
        // A scan that only ever agrees with what is already written proves
        // nothing, so the exact rule that caused the defect must be rejected.
        // Written as a stylesheet rather than a Tailwind class on purpose: this
        // file is scanned by the build, and a literal utility here would ship in
        // the production CSS.
        $this->assertSame(
            ['repeat(auto-fit,minmax(20rem'],
            $this->unboundedAutoFitTracks('.g{grid-template-columns:repeat(auto-fit,minmax(20rem,1fr))}')
        );

        $this->assertSame(
            ['repeat(auto-fill, minmax(18rem'],
            $this->unboundedAutoFitTracks('<style>.g{grid-template-columns:repeat(auto-fill, minmax(18rem, 1fr))}</style>')
        );

        // Both of these can shrink with the column, so neither is reported.
        $this->assertSame([], $this->unboundedAutoFitTracks(
            '.g{grid-template-columns:repeat(auto-fit,minmax(min(20rem,100%),1fr))}'
        ));
        $this->assertSame([], $this->unboundedAutoFitTracks('.g{grid-template-columns:repeat(auto-fit,minmax(0,1fr))}'));
    }

    public function test_no_grid_in_the_site_repeats_without_a_bounded_floor(): void
    {
        $offending = [];

        foreach ($this->styleSources() as $file) {
            foreach ($this->unboundedAutoFitTracks(File::get($file)) as $track) {
                $offending[] = $file.' -> '.$track;
            }
        }

        $this->assertSame([], $offending, 'These tracks cannot shrink to their container and spill on a narrow viewport.');
    }

    public function test_the_projects_card_grid_is_bounded_in_both_locales(): void
    {
        foreach (['en', 'fa'] as $locale) {
            $this->assertSame(
                [],
                $this->unboundedAutoFitTracks($this->cardGrid("/$locale/projects")->getAttribute('class')),
                "the $locale projects card grid can outgrow its container."
            );
        }
    }

    public function test_every_configured_project_is_a_card_inside_that_grid(): void
    {
        $grid = $this->cardGrid('/en/projects');
        $cards = [];

        foreach ($grid->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $cards[] = $child->getAttribute('class');
            }
        }

        // The guard above only means something if this really is the grid the
        // cards are handed to, so a card that wanders elsewhere is a failure.
        $this->assertSame(array_fill(0, count(config('content.projects')), 'project-card glow'), $cards);
    }

    /**
     * @return array<int, string> Each `repeat(auto-fit|auto-fill …)` whose minmax()
     *                            floor is a fixed length rather than a container share.
     */
    private function unboundedAutoFitTracks(string $source): array
    {
        preg_match_all('/repeat\(\s*auto-(?:fit|fill)\s*,\s*minmax\(\s*([^,)]+)/', $source, $matches, PREG_SET_ORDER);

        $offending = [];

        foreach ($matches as $match) {
            $floor = trim($match[1]);
            $bounded = str_starts_with($floor, 'min(')
                || str_starts_with($floor, '0')
                || str_ends_with($floor, '%')
                || $floor === 'auto'
                || $floor === '1fr';

            if (! $bounded) {
                $offending[] = $match[0];
            }
        }

        return $offending;
    }

    private function cardGrid(string $path): \DOMNode
    {
        $document = $this->document($this->get($path)->assertOk()->getContent());
        $grid = (new DOMXPath($document))
            ->query("//article[contains(concat(' ',normalize-space(@class),' '),' project-card ')]/parent::div")
            ->item(0);

        $this->assertNotNull($grid, "the {$path} page has no card grid at all.");

        return $grid;
    }

    /**
     * @return array<int, string> Blade views and stylesheets, repo-relative.
     */
    private function styleSources(): array
    {
        $root = str_replace('\\', '/', base_path()).'/';
        $files = [];

        foreach ([resource_path('views'), resource_path('css')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                $files[] = str_replace($root, '', str_replace('\\', '/', $file->getPathname()));
            }
        }

        return $files;
    }
}
