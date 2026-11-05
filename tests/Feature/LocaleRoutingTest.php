<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleRoutingTest extends TestCase
{
    public function test_root_redirects_to_the_default_locale(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_each_locale_declares_its_language_and_direction_at_the_document_level(): void
    {
        $this->get('/en')->assertOk()->assertSee('<html lang="en" dir="ltr">', false);
        $this->get('/fa')->assertOk()->assertSee('<html lang="fa" dir="rtl">', false);
    }

    public function test_unsupported_locale_and_unprefixed_paths_are_not_found(): void
    {
        $this->get('/fr')->assertNotFound();
        $this->get('/projects')->assertNotFound();
        $this->get('/en/typo')->assertNotFound();
    }

    public function test_alternate_locales_are_advertised_for_the_same_page(): void
    {
        $this->get('/fa/projects')
            ->assertOk()
            ->assertSee('hreflang="en"', false)
            ->assertSee('http://localhost/en/projects', false)
            ->assertSee('<link rel="canonical" href="http://localhost/fa/projects">', false);
    }

    public function test_the_bundled_stylesheet_is_used_and_no_font_cdn_is_referenced(): void
    {
        $html = $this->get('/fa')->assertOk()->getContent();

        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.bunny.net', $html);
        $this->assertStringContainsString('/fonts/estedad-arabic-var.woff2', $html);
        $this->assertStringContainsString('/fonts/estedad-latin-var.woff2', $html);
    }
}
