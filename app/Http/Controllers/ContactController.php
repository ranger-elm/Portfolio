<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(Request $request): View
    {
        // Server-side, in the session: a bot cannot set it by editing the form,
        // and collecting it would need a cookie the visitor never agreed to.
        $request->session()->put('contact_opened_at', time());

        return view('pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $recipient = config('portfolio.form.recipient');
        abort_unless(filled($recipient), 404);

        $throttleKey = 'contact:'.$request->ip();
        $maxAttempts = (int) config('portfolio.form.max_per_hour');

        // A filled invisible field says what the submission is. It gets the same
        // answer an accepted message gets, so nothing about the trap is confirmed
        // back, and it still spends part of that address's hourly budget.
        if (filled($request->input('company_website'))) {
            RateLimiter::hit($throttleKey, 3600);

            return $this->sent($request);
        }

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            return $this->rejected($request, __('contact.rateLimited'));
        }

        // Reaching this line means the session has held the form open for the
        // configured few seconds, so the submitter fetched the page and kept its
        // cookie — which is most of what a naive script does not do. A session
        // that never saw the form at all is refused the same way.
        $openedAt = $request->session()->get('contact_opened_at');

        if (! is_int($openedAt) || time() - $openedAt < (int) config('portfolio.form.min_read_seconds')) {
            return $this->rejected($request, __('contact.submittedTooFast'));
        }

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return $this->rejected($request, null, $validator->errors());
        }

        RateLimiter::hit($throttleKey, 3600);
        $validated = $validator->validated();

        try {
            Mail::to($recipient)->send(new ContactMessage(
                visitorName: $this->oneLine($validated['name']),
                visitorEmail: $validated['email'],
                heading: $this->heading($validated['subject'] ?? null, $validated['name']),
                body: $this->paragraphs($validated['message']),
            ));
        } catch (Throwable $exception) {
            // The transport error can name mailboxes, and a visitor has no use for
            // it, so the page says only that sending failed. It is still reported
            // server-side, without the message body, so a broken mailbox is not
            // mistaken for a quiet week.
            report($exception);

            return $this->rejected($request, __('contact.deliveryFailed'));
        }

        return $this->sent($request);
    }

    /**
     * Every way this action can end leaves the visitor on the same page, in the
     * same locale, rather than on a generic error screen.
     */
    private function rejected(Request $request, ?string $reason, ?MessageBag $errors = null): RedirectResponse
    {
        $redirect = redirect()->route('contact', ['locale' => $request->route('locale')]);

        if ($errors !== null) {
            $redirect->withErrors($errors);
        }

        if ($reason !== null) {
            $redirect->withErrors(['general' => $reason]);
        }

        // What was typed is kept, so a rejection costs a correction and not a
        // retyped message. The invisible field is the one thing not sent back:
        // echoing it tells a script which field gave it away.
        return $redirect->withInput($request->except('company_website', '_token'));
    }

    private function sent(Request $request): RedirectResponse
    {
        return redirect()->route('contact', ['locale' => $request->route('locale')])
            ->with('status', __('contact.messageSent'));
    }

    /**
     * @return array<string, array<int, string|Closure>>
     */
    private function rules(): array
    {
        $maxLinks = (int) config('portfolio.form.max_links');

        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email:filter', 'max:180'],
            'subject' => ['nullable', 'string', 'max:100'],
            'message' => [
                'required',
                'string',
                'min:'.(int) config('portfolio.form.min_message_length'),
                'max:'.(int) config('portfolio.form.max_message_length'),
                function (string $attribute, $value, Closure $fail) use ($maxLinks) {
                    if (preg_match_all('#https?://|www\.#i', (string) $value) > $maxLinks) {
                        $fail(__('contact.tooManyLinks', ['max' => $maxLinks]));
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'required' => __('contact.requiredField'),
            'min' => __('contact.tooShort'),
            'max' => __('contact.tooLong'),
            'email' => __('contact.invalidEmail'),
        ];
    }

    /**
     * Names and subjects travel inside mail headers, so they are flattened onto
     * one line and stripped of control characters first: a "\r\n" in a display
     * name is the classic way to smuggle an extra header into a message.
     */
    private function oneLine(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) preg_replace('/[\p{C}]+/u', ' ', $value)));
    }

    /**
     * The body keeps its paragraphs — they are what the visitor wrote — and loses
     * every other control character, so a text/plain message cannot be padded out
     * with header-shaped line breaks.
     */
    private function paragraphs(string $value): string
    {
        $withoutControls = (string) preg_replace('/[^\P{C}\n]/u', '', $value);

        return trim((string) preg_replace('/\n{3,}/', "\n\n", $withoutControls));
    }

    private function heading(?string $subject, string $name): string
    {
        $topic = $subject === null || trim($subject) === ''
            ? $this->oneLine($name)
            : $this->oneLine($subject);

        return config('portfolio.brand').' · '.$topic;
    }
}
