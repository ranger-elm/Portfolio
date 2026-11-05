<?php

return [

    'brand' => 'Elia',

    'tagline' => 'Beyond the Interface',

    'default_locale' => 'en',

    'locales' => [
        'en' => [
            'dir' => 'ltr',
            'name' => 'English',
            'label' => 'EN',
            'og_locale' => 'en_US',
        ],
        'fa' => [
            'dir' => 'rtl',
            'name' => 'فارسی',
            'label' => 'FA',
            'og_locale' => 'fa_IR',
        ],
    ],

    'hero' => [
        // The label claims current activity, so it stays off until the copy is confirmed truthful.
        'show_status_label' => false,
    ],

    'contact' => [
        // Supplied by the owner across 2026-11-05. The order here is the order
        // the footer and the contact page list them in.
        //
        // The email stays unset: it exists only in local git config, and putting
        // it on a public page is a spam tradeoff the owner has not agreed to in
        // words.
        //
        // Instagram and Telegram arrived as @handles and are stored as their
        // canonical profile URLs (instagram.com/<handle>, t.me/<handle>).
        //
        // Verified from here: t.me/RangerELM answers with a non-empty Persian
        // bio where a made-up handle answers with an empty one, so that account
        // is real. LinkedIn returns 999 to every client and Instagram
        // login-walls every probe, so neither URL could be checked — a typo in
        // either would only show up when a visitor hits it.
        'email' => null,
        'github' => 'https://github.com/ranger-elm',
        'linkedin' => 'https://www.linkedin.com/in/elia-mohammadi-13972a378/',
        'instagram' => 'https://www.instagram.com/elm.ranger/',
        'telegram' => 'https://t.me/RangerELM',
    ],

    // The message form. `recipient` is deliberately read from the environment and
    // never rendered: the address reaches the inbox without appearing anywhere a
    // scraper can collect it, which is the one advantage a form has over a
    // mailto: link. With it unset the form is not built at all, so the site has a
    // working path or none rather than a button that quietly goes nowhere.
    'form' => [
        'recipient' => env('CONTACT_TO'),
        // A real visitor has to read four labels and type four fields; a naive
        // bot posts the moment it can. Under this many seconds the session never
        // opened the form or was not read, and the submission is dropped.
        'min_read_seconds' => 5,
        // Per source address, per hour. A shared office NAT is the worst case
        // this number has to survive, so it is deliberately not one.
        'max_per_hour' => 3,
        // Links are what spam is selling, so a message carrying more than this
        // many is refused outright instead of reaching the inbox.
        'max_links' => 2,
        'min_message_length' => 20,
        'max_message_length' => 3000,
    ],

    // The order the project detail page walks its case study in, per §6.2. The
    // parity test reads this same list, so a section can never be added to the
    // page without a heading in both locales.
    'project_sections' => [
        'problem',
        'goals',
        'role',
        'decisions',
        'focus',
        'features',
        'challenges',
        'lessons',
    ],

];
