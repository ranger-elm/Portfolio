<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class InternalPagesTest extends TestCase
{
    /**
     * @param  array<string,string>  $pages  route name => path
     */
    public static function providerPages(): array
    {
        return [
            'home' => '/%s',
            'projects' => '/%s/projects',
            'project' => '/%s/projects/planino',
            'lab' => '/%s/lab',
            'about' => '/%s/about',
            'contact' => '/%s/contact',
        ];
    }

    public function test_every_internal_page_responds_in_both_locales(): void
    {
        foreach (self::providerPages() as $name => $template) {
            foreach (['en' => 'ltr', 'fa' => 'rtl'] as $locale => $dir) {
                $response = $this->get(sprintf($template, $locale));

                $response->assertOk();
                $response->assertSee(sprintf('<html lang="%s" dir="%s">', $locale, $dir), false);
            }
        }
    }

    public function test_each_page_has_exactly_one_top_level_heading(): void
    {
        foreach (self::providerPages() as $name => $template) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get(sprintf($template, $locale))->getContent();
                $nodes = (new DOMXPath($this->document($html)))->query('//h1');

                $this->assertSame(
                    1,
                    $nodes->length,
                    sprintf('%s (%s) should render exactly one <h1>', $name, $locale)
                );
            }
        }
    }

    public function test_project_detail_presents_the_confirmed_content(): void
    {
        $response = $this->get('/en/projects/planino');

        $response->assertOk();
        $response->assertSee('Planino');
        $response->assertSee('Project Overview');
        $response->assertSee('A personal planning and productivity platform', false);
        $response->assertSee('What it brings together');
        $response->assertSee('Study plans');
        $response->assertSee('In Development');
        $response->assertSee('Back to Projects', false);
        $response->assertSee('http://localhost/en/projects/planino', false);
        $response->assertSee('hreflang="fa"', false);
        $response->assertSee('http://localhost/fa/projects/planino', false);
    }

    public function test_persian_detail_page_uses_persian_copy_for_every_section_label(): void
    {
        $response = $this->get('/fa/projects/planino');

        $response->assertOk();
        $response->assertSee('معرفی پروژه', false);
        $response->assertSee('چه چیزی را یکپارچه می‌کند', false);
        $response->assertSee('برنامه‌های مطالعاتی', false);
        $response->assertSee('در حال توسعه', false);
        $response->assertSee('بازگشت به پروژه‌ها', false);
        // The section labels are translated, so no English dictionary key leaks through.
        $response->assertDontSee('Project Overview', false);
    }

    public function test_sections_without_supplied_copy_render_no_heading(): void
    {
        foreach (['/en/projects/planino' => [
            'Problem & motivation',
            'Goals',
            'My Role',
            'Implementation decisions',
            'Key Features',
            'Challenges',
            'Lessons Learned',
            'Screenshots',
        ], '/fa/projects/planino' => [
            'مسئله و انگیزه',
            'اهداف',
            'نقش من',
            'تصمیم‌های پیاده‌سازی',
            'قابلیت‌های اصلی',
            'چالش‌ها',
            'آموخته‌ها',
            'تصاویر',
        ]] as $path => $absent) {
            $response = $this->get($path);

            foreach ($absent as $label) {
                $response->assertDontSee($label, false);
            }
        }
    }

    public function test_every_case_study_section_has_a_heading_in_both_locales(): void
    {
        foreach (config('portfolio.project_sections') as $key) {
            foreach (['en', 'fa'] as $locale) {
                $heading = Lang::get("projects.detail.$key", [], $locale);

                $this->assertNotSame(
                    "projects.detail.$key",
                    $heading,
                    "The detail page walks [$key], so projects.detail.$key must exist in $locale."
                );
                $this->assertNotSame('', trim($heading), "[$key] has an empty heading in $locale.");
            }

            // A Persian heading copied from English is the other failure this
            // guards: the page would render, just in the wrong language.
            $this->assertNotSame(
                Lang::get("projects.detail.$key", [], 'en'),
                Lang::get("projects.detail.$key", [], 'fa'),
                "[$key] carries the same heading in both locales."
            );
        }
    }

    public function test_unknown_and_nested_project_slugs_are_not_found(): void
    {
        $this->get('/en/projects/unknown-project')->assertNotFound();
        $this->get('/fa/projects/unknown-project')->assertNotFound();
        $this->get('/en/projects/planino/extra')->assertNotFound();
        $this->get('/en/projects/planino/edit')->assertNotFound();
    }

    public function test_not_found_pages_keep_the_language_of_their_url(): void
    {
        foreach (['en' => 'ltr', 'fa' => 'rtl'] as $locale => $direction) {
            $response = $this->get('/'.$locale.'/does-not-exist');
            $html = $response->getContent();

            $response->assertNotFound();
            $this->assertStringContainsString('<html lang="'.$locale.'" dir="'.$direction.'"', $html);
            $this->assertStringContainsString(__('common.notFoundTitle', [], $locale), $html);
            $this->assertStringContainsString('content="noindex, follow"', $html);
            $this->assertStringNotContainsString('Not Found</title>', $html);

            // An error page is still inside the site: navigation and switching survive.
            $this->assertStringContainsString('/'.$locale.'/contact', $html);
        }
    }

    public function test_project_detail_shows_the_section_as_current_in_the_navigation(): void
    {
        $html = $this->get('/en/projects/planino')->getContent();
        $links = (new DOMXPath($this->document($html)))->query('//a[@aria-current="page"]');

        // The desktop and mobile navs each render the current link once.
        $this->assertSame(2, $links->length);

        foreach ($links as $link) {
            $this->assertSame('http://localhost/en/projects', $link->getAttribute('href'));
        }
    }

    public function test_no_unconfirmed_destination_appears_on_any_internal_page(): void
    {
        foreach (self::providerPages() as $name => $template) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get(sprintf($template, $locale))->getContent();

                foreach (['mailto:', 'github.com', 'linkedin.com'] as $forbidden) {
                    $this->assertStringNotContainsString($forbidden, $html, sprintf('%s leaked into %s (%s)', $forbidden, $name, $locale));
                }
            }
        }
    }

    public function test_no_internal_link_on_any_internal_page_is_broken(): void
    {
        $checked = 0;

        foreach (self::providerPages() as $name => $template) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get(sprintf($template, $locale))->getContent();
                $nodes = (new DOMXPath($this->document($html)))->query('//a/@href');

                foreach ($nodes as $attribute) {
                    $href = $attribute->nodeValue;

                    if (! str_starts_with($href, 'http') || str_contains($href, '/build/') || str_contains($href, '/fonts/')) {
                        continue;
                    }

                    $path = '/'.ltrim(parse_url($href, PHP_URL_PATH) ?? '/', '/');

                    if ($path === '/') {
                        continue;
                    }

                    $this->assertNotSame(
                        404,
                        $this->get($path)->getStatusCode(),
                        sprintf('%s on %s (%s) is a dead link', $path, $name, $locale)
                    );

                    $checked++;
                }
            }
        }

        $this->assertGreaterThan(20, $checked);
    }

    public function test_every_configured_project_and_experiment_has_a_translated_page(): void
    {
        // Adding an entry to config/content.php must be enough: a missing lang
        // line would surface as a raw dictionary key, which is what this guards.
        $forbidden = ['projects.items.', 'projects.detail.', 'lab.items.', 'skills.items.', 'status.in', 'about.focus', 'about.experimenting'];

        $slugs = array_keys(config('content.projects'));
        $this->assertNotEmpty($slugs);

        foreach ($slugs as $slug) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get(sprintf('/%s/projects/%s', $locale, $slug))->assertOk()->getContent();

                foreach ($forbidden as $leak) {
                    $this->assertStringNotContainsString($leak, $html, sprintf('%s leaked on %s project page', $leak, $locale));
                }
            }
        }

        foreach (['en', 'fa'] as $locale) {
            $html = $this->get(sprintf('/%s/lab', $locale))->assertOk()->getContent();
            $this->assertStringNotContainsString('lab.items.', $html);
            $this->assertStringNotContainsString('status.exploring', $html);
        }
    }

    public function test_the_typography_comes_from_one_system_in_both_locales(): void
    {
        // Tracked, uppercased mono labels are the one treatment that cannot
        // survive translation: letter spacing unjoins Persian cursive and the
        // Latin mono face carries no Arabic glyphs. Direction is handled by the
        // stylesheet instead, per locale.
        $forbidden = ['tracking-[', 'uppercase', 'font-mono'];

        // Physical direction utilities are the tell of a layout that is merely
        // mirrored rather than designed for both readings.
        $physical = [
            '/^border-[lr](?:-[a-z0-9]+)?$/',
            '/^(?:ml|mr|pl|pr)-[a-z0-9.]+$/',
            '/^(?:left|right)-[a-z0-9.]+$/',
            '/^text-(?:left|right)$/',
        ];

        foreach (self::providerPages() as $name => $template) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get(sprintf($template, $locale))->assertOk()->getContent();

                foreach ($forbidden as $utility) {
                    $this->assertStringNotContainsString($utility, $html, "{$utility} leaked onto the {$locale} {$name} page");
                }

                $this->assertMatchesRegularExpression(
                    '/class="[^"]*\b(t-display|t-title|t-sub|t-lede|t-label)\b/',
                    $html,
                    "the type scale is not applied on the {$locale} {$name} page"
                );

                $xpath = new DOMXPath($this->document($html));

                foreach ($xpath->query('//*[@class]') as $element) {
                    foreach (preg_split('/\s+/', trim($element->getAttribute('class'))) as $token) {
                        $position = strrpos($token, ':');
                        $base = $position === false ? $token : substr($token, $position + 1);

                        foreach ($physical as $pattern) {
                            $this->assertFalse(
                                preg_match($pattern, $base) === 1,
                                "physical direction utility {$token} on the {$locale} {$name} page"
                            );
                        }
                    }
                }
            }
        }
    }

    public function test_every_section_pairs_a_rail_heading_with_a_body_column(): void
    {
        foreach (['en', 'fa'] as $locale) {
            $xpath = new DOMXPath($this->document($this->get('/'.$locale)->getContent()));

            $rails = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " section-rail ")]');
            $bodies = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " section-body ")]');

            $this->assertSame(5, $rails->length, 'five rail sections expected');
            $this->assertSame($rails->length, $bodies->length);

            // The rail heading is what its own section is labelled by.
            foreach ($rails as $rail) {
                $heading = $xpath->query('.//h1[@id] | .//h2[@id] | .//h3[@id]', $rail)->item(0);
                $label = $xpath->query('ancestor::section[1]/@aria-labelledby', $rail)->item(0);

                $this->assertNotNull($heading, 'a rail has no heading');
                $this->assertNotNull($label, 'a rail section has no accessible label');
                $this->assertSame($label->nodeValue, $heading->getAttribute('id'));
            }
        }
    }

    private function document(string $html): DOMDocument
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html);

        libxml_clear_errors();

        return $document;
    }
}
