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
        // Approved on 2026-11-05 from the tables in the URL stage report. The email
        // stays unset: it exists only in local git config, and putting it on a
        // public page is a spam tradeoff the owner has not agreed to in words.
        'email' => null,
        'github' => 'https://github.com/ranger-elm',
        'linkedin' => null,
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
