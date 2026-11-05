<?php

namespace Tests\Feature;

use DOMXPath;
use Illuminate\Support\Facades\Lang;
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

    public function test_the_email_channel_stays_closed_until_it_is_approved(): void
    {
        // The owner approved this mailbox as the recipient of every message, which
        // is a different thing from publishing it: neither a mailto: nor the
        // address itself may reach a rendered page, where a crawler reads it.
        $this->assertNull(config('portfolio.contact.email'));

        foreach (['/en', '/fa', '/en/contact', '/fa/contact'] as $path) {
            $this->assertStringNotContainsString('mailto:', $this->get($path)->assertOk()->getContent(), $path);
        }
    }

    public function test_every_confirmed_channel_renders_in_the_footer_and_on_the_contact_page(): void
    {
        $confirmed = array_filter(config('portfolio.contact'));
        $this->assertNotEmpty($confirmed);

        foreach (['en', 'fa'] as $locale) {
            $home = $this->get("/$locale")->assertOk()->getContent();
            $contact = $this->get("/$locale/contact")->assertOk()->getContent();

            // Compared against the config list itself, so a channel that
            // disappears, duplicates or changes place is a failure rather than a
            // silent reordering — and nothing unconfirmed can slip into the group.
            $this->assertSame(array_values($confirmed), array_values(array_intersect($this->hrefs($home, '//footer//a/@href'), $confirmed)), "the $locale footer channel list.");
            $this->assertSame(array_values($confirmed), array_values(array_intersect($this->hrefs($contact, '//main//a/@href'), $confirmed)), "the $locale contact page channel list.");

            foreach (array_keys($confirmed) as $key) {
                $label = Lang::get("contact.channels.$key", [], $locale);

                $this->assertStringContainsString($label, $home, "the $key channel has no label in the $locale footer.");
                $this->assertStringContainsString($label, $contact, "the $key channel has no label on the $locale contact page.");
            }
        }
    }

    public function test_the_persian_channel_labels_are_persian(): void
    {
        // Brand names are the one class of label that survives a localization
        // pass untranslated, and on a page that translates everything else it
        // reads as a defect rather than as a brand. Email is not in this map:
        // its label is a sentence, so it comes from contact.emailMe.
        $labels = Lang::get('contact.channels', [], 'en');

        $this->assertSame(
            array_values(array_diff(array_keys(config('portfolio.contact')), ['email'])),
            array_keys($labels),
            'a configured channel has no label, or a label has no channel behind it.'
        );

        foreach ($labels as $key => $english) {
            $persian = Lang::get("contact.channels.$key", [], 'fa');

            $this->assertNotSame($english, $persian, "the $key channel carries the English label in Persian.");
            $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $persian, "the $key Persian label has no Persian script in it.");
        }
    }

    private function hrefs(string $html, string $query): array
    {
        $hrefs = [];

        foreach ((new DOMXPath($this->document($html)))->query($query) as $attribute) {
            $hrefs[] = $attribute->nodeValue;
        }

        return $hrefs;
    }
}
