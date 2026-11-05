<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    private const OWNER = 'owner@example.test';

    protected function setUp(): void
    {
        parent::setUp();

        // Named here rather than read from the machine's .env, so the suite
        // answers the same on every checkout.
        config()->set('portfolio.form.recipient', self::OWNER);
    }

    /**
     * @param  array<string,string|null>  $overrides
     * @return array<string,string|null>
     */
    private function message(array $overrides = []): array
    {
        return array_merge([
            'name' => 'A Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'A planning tool',
            'message' => 'I would like to talk about a planning tool for students.',
        ], $overrides);
    }

    /** A session that has already held the form open long enough to read it. */
    private function openedForm(): static
    {
        return $this->withSession(['contact_opened_at' => time() - 30]);
    }

    /**
     * The message the array transport kept, so a test can read the headers and
     * the body that would have gone out rather than the code that built them.
     */
    private function sentEmail(): Email
    {
        return app('mailer')->getSymfonyTransport()->messages()[0]->getOriginalMessage();
    }

    public function test_the_page_records_when_the_form_was_opened(): void
    {
        $this->get('/en/contact')->assertOk()->assertSessionHas('contact_opened_at');

        $this->assertIsInt(session('contact_opened_at'));
    }

    public function test_a_message_that_took_the_time_to_write_reaches_the_owner(): void
    {
        $this->openedForm()->post('/en/contact', $this->message())
            ->assertRedirect('/en/contact')
            ->assertSessionHas('status', __('contact.messageSent'))
            ->assertSessionMissing('errors');
    }

    public function test_the_visitor_is_a_reply_address_and_never_a_recipient(): void
    {
        $this->openedForm()->post('/en/contact', $this->message());

        $sent = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);

        // A visitor can ask for a reply; they cannot point the form at somebody
        // else's inbox, because the recipient list is never taken from the request.
        $this->assertSame([self::OWNER], array_map(
            fn ($address) => $address->getAddress(),
            $sent[0]->getEnvelope()->getRecipients()
        ));

        $email = $sent[0]->getOriginalMessage();

        $this->assertSame('visitor@example.test', $email->getReplyTo()[0]->getAddress());
        $this->assertSame('A Visitor', $email->getReplyTo()[0]->getName());
        $this->assertNotNull($email->getTextBody());
        $this->assertNull($email->getHtmlBody(), 'A contact message should carry no HTML for a client to render.');
    }

    public function test_the_message_is_carried_into_the_email_untouched(): void
    {
        $body = "Two questions:\n\nAbout scheduling.";

        $this->openedForm()->post('/en/contact', $this->message(['message' => $body]));

        $email = $this->sentEmail();

        $this->assertStringContainsString($body, $email->getTextBody());
        $this->assertStringContainsString('A Visitor', $email->getTextBody());
        $this->assertStringContainsString('visitor@example.test', $email->getTextBody());
        $this->assertStringStartsWith(config('portfolio.brand').' · ', $email->getSubject());
    }

    public function test_an_absent_subject_leaves_the_sender_in_the_line_instead(): void
    {
        $this->openedForm()->post('/en/contact', $this->message(['subject' => null]));

        $this->assertSame(
            config('portfolio.brand').' · A Visitor',
            $this->sentEmail()->getSubject()
        );
    }

    public function test_line_breaks_cannot_smuggle_a_header_into_the_sender_name(): void
    {
        $this->openedForm()->post('/en/contact', $this->message([
            'name' => "A Visitor\r\nBcc: somebody@example.test",
            'subject' => "Ideas\nX-Header: injected",
        ]));

        $sent = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent, 'A second message would mean the payload opened its own send.');

        $email = $sent[0]->getOriginalMessage();
        $raw = $email->toString();

        // Flattened onto one line the payload is still a name a person can read;
        // what it must not become is the start of a header line.
        $this->assertStringNotContainsString("\n", $email->getSubject());
        $this->assertStringNotContainsString("\nBcc:", $raw, 'A Bcc header was opened by the display name.');
        $this->assertStringNotContainsString("\nX-Header:", $raw, 'A custom header was injected.');
        $this->assertCount(1, $email->getTo());
        $this->assertSame(self::OWNER, $email->getTo()[0]->getAddress());
        $this->assertCount(1, $sent[0]->getEnvelope()->getRecipients());
    }

    public function test_the_invisible_field_is_outside_both_the_tab_order_and_the_accessibility_tree(): void
    {
        $xpath = new DOMXPath($this->document($this->get('/en/contact')->getContent()));
        $field = $xpath->query('//form//input[@name="company_website"]')->item(0);

        $this->assertNotNull($field, 'The form has no honeypot field.');
        $this->assertSame('-1', $field->getAttribute('tabindex'));
        $this->assertSame(
            'true',
            $xpath->query('//form//input[@name="company_website"]/ancestor::div[1]/@aria-hidden')->item(0)->nodeValue
        );
    }

    public function test_a_post_that_never_opened_the_form_is_refused(): void
    {
        Mail::fake();

        $this->post('/en/contact', $this->message())
            ->assertRedirect('/en/contact')
            ->assertSessionHasErrors('general');

        $this->assertSame(__('contact.submittedTooFast'), session('errors')->first('general'));
        Mail::assertNothingSent();
    }

    public function test_the_reading_time_is_measured_on_the_server(): void
    {
        $xpath = new DOMXPath($this->document($this->get('/en/contact')->getContent()));

        // The token is the only value the page authors by itself, so it is the
        // only one a submission can carry unseen — and a token cannot be a clock.
        $hidden = $xpath->query("//form//input[@type='hidden']");

        $this->assertSame(1, $hidden->length);
        $this->assertSame('_token', $hidden->item(0)->getAttribute('name'));

        // Every other input is a field someone either reads and types into or
        // cannot see at all; none of them is a timestamp a bot could set.
        $visible = array_map(
            fn ($field) => $field->getAttribute('name'),
            iterator_to_array($xpath->query('//form//input[@type!="hidden"]'))
        );

        $this->assertSame(['company_website', 'name', 'email', 'subject'], $visible);
    }

    public function test_each_source_address_gets_three_messages_an_hour(): void
    {
        Mail::fake();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->openedForm()->post('/en/contact', $this->message())->assertSessionHas('status');
        }

        Mail::assertSentCount(3);

        $this->openedForm()->post('/en/contact', $this->message())
            ->assertSessionHasErrors('general');

        Mail::assertSentCount(3);
        $this->assertSame(__('contact.rateLimited'), session('errors')->first('general'));
    }

    public function test_incomplete_and_invalid_details_are_reported_per_field(): void
    {
        Mail::fake();

        $this->openedForm()->post('/en/contact', $this->message([
            'name' => '',
            'email' => 'not-an-address',
            'message' => 'too short',
        ]))
            ->assertRedirect('/en/contact')
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertSame(__('contact.requiredField'), session('errors')->first('name'));
        $this->assertSame(__('contact.invalidEmail'), session('errors')->first('email'));
        $this->assertSame(
            __('contact.tooShort', ['min' => config('portfolio.form.min_message_length')]),
            session('errors')->first('message')
        );
        Mail::assertNothingSent();
    }

    public function test_a_link_heavy_message_is_refused(): void
    {
        Mail::fake();

        $this->openedForm()->post('/en/contact', $this->message([
            'message' => 'Buy now https://a.example.test https://b.example.test https://c.example.test',
        ]))->assertSessionHasErrors('message');

        $this->assertSame(
            __('contact.tooManyLinks', ['max' => config('portfolio.form.max_links')]),
            session('errors')->first('message')
        );
        Mail::assertNothingSent();
    }

    public function test_two_links_still_arrive_because_people_paste_references(): void
    {
        Mail::fake();

        $this->openedForm()->post('/en/contact', $this->message([
            'message' => 'See https://github.com/ranger-elm/Planino_v1 and https://t.me/RangerELM for context on the idea.',
        ]))->assertSessionHas('status');

        Mail::assertSent(ContactMessage::class);
    }

    public function test_what_was_typed_survives_a_rejection(): void
    {
        $this->openedForm()->post('/en/contact', $this->message(['name' => '']))
            ->assertSessionHasInput('email', 'visitor@example.test')
            ->assertSessionHasInput('message', 'I would like to talk about a planning tool for students.');
    }

    public function test_a_submission_that_filled_the_invisible_field_is_never_sent_and_never_echoed(): void
    {
        Mail::fake();

        $this->openedForm()->post('/en/contact', $this->message(['company_website' => 'https://spam.example.test']))
            ->assertSessionHas('status', __('contact.messageSent'))
            ->assertSessionMissing('_old_input');

        // A bot hears the same thank-you a person hears and gets none of its own
        // words back, so nothing in the reply names the field that gave it away.
        $this->get('/en/contact')->assertDontSee('spam.example.test');

        Mail::assertNothingSent();
    }

    public function test_every_field_decides_its_own_direction(): void
    {
        $xpath = new DOMXPath($this->document($this->get('/fa/contact')->getContent()));

        // A Persian page still receives Latin addresses and links, and those
        // read backwards when the document direction is forced on them.
        foreach (['name', 'email', 'subject', 'message'] as $field) {
            $node = $xpath->query("//form//*[@id='$field']")->item(0);

            $this->assertNotNull($node, "the form has no $field field");
            $this->assertSame('auto', $node->getAttribute('dir'), "the $field field forces the page's direction");
        }
    }

    public function test_the_persian_page_answers_persian_errors_and_keeps_its_locale(): void
    {
        $this->openedForm()->post('/fa/contact', $this->message(['email' => 'not-an-address', 'name' => '']))
            ->assertRedirect('/fa/contact')
            ->assertSessionHasErrors(['name', 'email']);

        $html = $this->get('/fa/contact')->assertOk()->getContent();

        $this->assertStringContainsString(__('contact.requiredField', [], 'fa'), $html);
        $this->assertStringContainsString(__('contact.invalidEmail', [], 'fa'), $html);
        $this->assertStringNotContainsString(__('contact.requiredField', [], 'en'), $html);
        $this->assertStringContainsString('<html lang="fa" dir="rtl">', $html);
    }

    public function test_the_failure_explains_the_way_around_it(): void
    {
        // The transport is what breaks, so the guard has to be the mailer itself.
        Mail::shouldReceive('to')->andThrow(new RuntimeException('connection refused'));

        $this->openedForm()->post('/en/contact', $this->message())
            ->assertSessionHasErrors('general')
            ->assertSessionHasInput('message');

        $this->assertSame(__('contact.deliveryFailed'), session('errors')->first('general'));
    }

    public function test_a_sent_message_leaves_nothing_behind_on_this_machine(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $log = storage_path('logs/laravel.log');
        $before = file_exists($log) ? filesize($log) : 0;

        $this->openedForm()->post('/en/contact', $this->message(['message' => 'A private note about a private idea.']))
            ->assertSessionHas('status');

        // The site has no database, and this form is the first thing that could
        // have made one necessary. It did not: nothing was queried, and the text
        // of the message was not written to the log either.
        $this->assertSame([], $queries);
        $this->assertSame($before, file_exists($log) ? filesize($log) : 0);
        $this->assertStringNotContainsString(
            'A private note about a private idea.',
            file_exists($log) ? (string) file_get_contents($log) : ''
        );
    }

    public function test_no_form_is_drawn_and_no_endpoint_answers_without_a_destination(): void
    {
        config()->set('portfolio.form.recipient', null);

        $this->get('/en/contact')->assertOk()->assertDontSee('<form', false);
        $this->openedForm()->post('/en/contact', $this->message())->assertNotFound();
    }

    public function test_the_form_posts_only_to_the_page_it_sits_on(): void
    {
        foreach (['en', 'fa'] as $locale) {
            $form = (new DOMXPath($this->document($this->get("/$locale/contact")->getContent())))
                ->query("//form[@method='POST']")->item(0);

            $this->assertNotNull($form, "the $locale page has no form");
            $this->assertSame("/$locale/contact", parse_url($form->getAttribute('action'), PHP_URL_PATH));
        }
    }

    public function test_the_persian_copy_keeps_every_key_the_form_uses(): void
    {
        $english = require lang_path('en/contact.php');
        $persian = require lang_path('fa/contact.php');

        $this->assertSame(array_keys($english), array_keys($persian));

        // The hint under the message box states the minimum the validator will
        // apply, in the digits that locale reads, so the two cannot drift apart.
        $minimum = (string) config('portfolio.form.min_message_length');

        $this->assertStringContainsString($minimum, Lang::get('contact.messageHint', ['min' => $minimum], 'en'));
        $this->assertStringContainsString(
            $this->persianDigits($minimum),
            Lang::get('contact.messageHint', ['min' => $minimum], 'fa')
        );
    }

    public function test_the_form_adds_no_new_destination_to_the_page(): void
    {
        foreach (['en', 'fa'] as $locale) {
            $this->assertNoUnapprovedDestination(
                $this->get("/$locale/contact")->assertOk()->getContent(),
                "the $locale contact page"
            );
        }
    }

    private function persianDigits(string $value): string
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            $value
        );
    }
}
