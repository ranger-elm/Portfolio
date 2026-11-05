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
        'email' => null,
        'github' => null,
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
