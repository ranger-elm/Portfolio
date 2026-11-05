<?php

namespace Tests\Feature;

use DOMXPath;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    public function test_the_confirmed_project_is_presented_with_its_honest_status(): void
    {
        $english = $this->get('/en')->assertOk()->getContent();
        $persian = $this->get('/fa')->assertOk()->getContent();

        $this->assertStringContainsString('Planino', $english);
        $this->assertStringContainsString('In Development', $english);
        $this->assertStringContainsString('در حال توسعه', $persian);
        $this->assertStringContainsString('پلتفرمی برای برنامه‌ریزی شخصی', $persian);
    }

    public function test_no_unconfirmed_destination_is_rendered_as_a_link(): void
    {
        foreach (['/en', '/fa', '/en/contact', '/fa/contact'] as $path) {
            $this->assertNoUnapprovedDestination($this->get($path)->assertOk()->getContent(), $path);
        }
    }

    public function test_every_configured_skill_is_labelled_in_words_and_not_only_by_colour(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        foreach (config('content.skills') as $skill) {
            $this->assertStringContainsString($skill['name'], $html);
        }

        $this->assertStringContainsString('Core skill', $html);
        $this->assertStringContainsString('Practical experience', $html);
    }

    public function test_the_hero_artwork_is_decorative_and_holds_no_focusable_content(): void
    {
        $xpath = new DOMXPath($this->document($this->get('/en')->getContent()));
        $stage = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " hero-stage ")]');

        $this->assertSame(1, $stage->length);
        $this->assertSame('true', $stage->item(0)->getAttribute('aria-hidden'));
        $this->assertSame(0, $xpath->query('.//a|.//button|.//input|.//*[@tabindex]', $stage->item(0))->length);
    }

    public function test_the_cursor_glow_layer_is_decorative_and_holds_no_focusable_content(): void
    {
        foreach (['/en', '/fa/lab'] as $path) {
            $xpath = new DOMXPath($this->document($this->get($path)->getContent()));
            $glow = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " cursor-glow ")]');

            $this->assertSame(1, $glow->length, $path);
            $this->assertSame('true', $glow->item(0)->getAttribute('aria-hidden'), $path);
            $this->assertSame(0, $xpath->query('.//a|.//button|.//input|.//*[@tabindex]', $glow->item(0))->length, $path);
        }
    }

    public function test_both_locales_expose_the_same_landed_sections_in_the_same_order(): void
    {
        foreach (['en', 'fa'] as $locale) {
            $xpath = new DOMXPath($this->document($this->get('/'.$locale)->getContent()));
            $labels = [];

            foreach ($xpath->query('//section/@aria-labelledby') as $attribute) {
                $labels[] = $attribute->nodeValue;
            }

            $this->assertSame([
                'hero-heading',
                'selected-projects',
                'skills',
                'about-preview',
                'lab-preview',
                'contact-cta',
            ], $labels);
        }
    }

    public function test_no_internal_link_on_the_homepage_is_broken(): void
    {
        $localHost = parse_url(config('app.url'), PHP_URL_HOST);

        foreach (['en', 'fa'] as $locale) {
            preg_match_all('/href="([^"]+)"/', $this->get('/'.$locale)->getContent(), $matches);
            $checked = 0;

            foreach ($matches[1] as $href) {
                $host = parse_url($href, PHP_URL_HOST);

                // An approved off-site destination is not this router's to check.
                if ($host !== null && $host !== $localHost) {
                    continue;
                }

                $path = parse_url($href, PHP_URL_PATH);

                if ($path === null || ! str_starts_with($path, '/')) {
                    continue;
                }

                // The web server answers for files under public/, the router for everything else.
                if (is_file(public_path(ltrim($path, '/')))) {
                    $checked++;

                    continue;
                }

                $this->assertNotSame(
                    404,
                    $this->get($href)->status(),
                    "Broken internal link: {$href}"
                );

                $checked++;
            }

            $this->assertGreaterThan(10, $checked);
        }
    }
}
