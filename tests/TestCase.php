<?php

namespace Tests;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every off-site destination the site is allowed to point at, gathered from
     * the configuration the owner confirmed. A URL missing from this list has
     * never been approved, so rendering it as a link is a defect — which is the
     * rule this replaces the old hardcoded ban on github.com and mailto:.
     *
     * @return array<int, string>
     */
    protected function approvedDestinations(): array
    {
        $destinations = array_values(array_filter(config('portfolio.contact')));

        foreach (config('content.projects') as $project) {
            foreach (['repository', 'demo'] as $key) {
                if (! empty($project[$key])) {
                    $destinations[] = $project[$key];
                }
            }
        }

        return $destinations;
    }

    /**
     * @return array<int, string> The offending hrefs, empty when the page is clean.
     */
    protected function unapprovedDestinationsIn(string $html): array
    {
        $document = $this->document($html);

        $approved = $this->approvedDestinations();
        $localHost = parse_url(config('app.url'), PHP_URL_HOST);
        $email = config('portfolio.contact.email');
        $offending = [];

        foreach ((new DOMXPath($document))->query('//a[@href]') as $link) {
            $href = $link->getAttribute('href');

            if (str_starts_with($href, '/') || $href === '' || str_starts_with($href, '#')) {
                continue;
            }

            if (str_starts_with($href, 'mailto:')) {
                if ($email === null || $href !== 'mailto:'.$email) {
                    $offending[] = $href;
                }

                continue;
            }

            $host = parse_url($href, PHP_URL_HOST);

            if ($host === $localHost) {
                continue;
            }

            if (! in_array($href, $approved, true)) {
                $offending[] = $href;
            }
        }

        return $offending;
    }

    protected function assertNoUnapprovedDestination(string $html, string $page): void
    {
        $this->assertSame(
            [],
            $this->unapprovedDestinationsIn($html),
            "An unconfirmed destination is linked on {$page}."
        );
    }

    protected function document(string $html): DOMDocument
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html);

        libxml_clear_errors();

        return $document;
    }
}
