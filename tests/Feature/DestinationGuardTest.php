<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The site used to forbid github.com and mailto: by name. That rule was only a
 * proxy for "the owner has not confirmed this destination", and it broke the
 * moment a confirmed destination arrived, so the proxy is gone: the allow-list
 * now comes from the configuration the owner approved. These tests prove the
 * new rule still bites rather than simply permitting whatever is configured.
 */
class DestinationGuardTest extends TestCase
{
    public function test_a_destination_outside_the_allow_list_is_still_rejected(): void
    {
        $this->assertSame(
            ['https://example.com/elia'],
            $this->unapprovedDestinationsIn('<main><a href="https://example.com/elia">Profile</a></main>')
        );

        // An unconfirmed email is rejected even though mailto: itself is legal.
        $this->assertSame(
            ['mailto:someone@example.org'],
            $this->unapprovedDestinationsIn('<a href="mailto:someone@example.org">Email me</a>')
        );

        // The same string inside a paragraph is prose, not a linked destination.
        $this->assertSame([], $this->unapprovedDestinationsIn('<p>example.com/elia</p>'));
    }

    public function test_an_approved_destination_and_the_local_site_pass_the_guard(): void
    {
        $approved = $this->approvedDestinations();
        $this->assertNotEmpty($approved, 'no destination has been approved yet.');

        foreach ($approved as $href) {
            $this->assertSame([], $this->unapprovedDestinationsIn("<a href=\"{$href}\">x</a>"), "{$href} is approved but rejected.");
        }

        $this->assertSame(
            [],
            $this->unapprovedDestinationsIn('<a href="'.config('app.url').'/en/contact">Contact</a><a href="/en">Home</a>')
        );
    }

    public function test_every_approved_destination_is_reachable_on_the_site(): void
    {
        $pages = '';

        foreach (['/en', '/fa', '/en/projects/planino', '/fa/projects/planino', '/en/contact', '/fa/contact'] as $path) {
            $pages .= $this->get($path)->assertOk()->getContent();
        }

        foreach ($this->approvedDestinations() as $href) {
            $this->assertStringContainsString($href, $pages, "{$href} is configured but never linked.");
        }
    }

    public function test_the_guard_reads_a_real_rendered_page_and_not_only_fixtures(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertSame([], $this->unapprovedDestinationsIn($html));

        // The same document with one planted off-site link must be reported,
        // which proves the empty result above was a real pass rather than a
        // parser that never reached an <a>.
        $this->assertSame(
            ['https://example.com/planted'],
            $this->unapprovedDestinationsIn(str_replace('</body>', '<a href="https://example.com/planted">planted</a></body>', $html))
        );
    }

    public function test_an_unconfirmed_contact_channel_still_renders_nothing(): void
    {
        // LinkedIn was never supplied, so neither its URL nor its label may appear.
        $this->assertNull(config('portfolio.contact.linkedin'));
        $this->assertNull(config('portfolio.contact.email'));

        foreach (['/en', '/fa'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertStringNotContainsString('linkedin.com', $html);
            $this->assertStringNotContainsString('mailto:', $html);
        }
    }
}
