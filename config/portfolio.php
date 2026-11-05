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

    // Brand names, deliberately not in lang/{en,fa}: they are the same words in
    // both locales and translating them would read as an untranslated leak
    // everywhere else on the Persian site. Email is the exception — its label is
    // a sentence, so it comes from contact.emailMe instead.
    'contact_labels' => [
        'github' => 'GitHub',
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'telegram' => 'Telegram',
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
