<?php

/*
|--------------------------------------------------------------------------
| Portfolio content
|--------------------------------------------------------------------------
|
| Locale-independent structure only: slugs, statuses, emphasis tiers and
| technology names. Every user-facing sentence lives in lang/{en,fa}.
|
| Honesty rule: a project or experiment may appear here only when its status
| is confirmed. Repository and demo URLs stay null until a real destination is
| supplied, and the components hide the link when it is null.
|
*/

return [

    'projects' => [

        'planino' => [
            'name' => 'Planino',
            'status' => 'inDevelopment',
            'featured' => true,
            // Read from the composer.json and package.json of the copy that holds
            // the current work, not from what the portfolio used to claim.
            'technologies' => ['Laravel', 'PHP', 'Livewire', 'MySQL', 'Tailwind CSS'],
            // Planino_v1 is the repository the newest code is pushed to (last commit
            // 2026-10-09, ahead of Planino's 2026-07-27), despite the older-sounding name.
            'repository' => 'https://github.com/ranger-elm/Planino_v1',
            // Nothing is deployed: both .env files say APP_URL=http://localhost and the
            // repo carries no deploy configuration, so there is no demo to link.
            'demo' => null,
        ],

    ],

    'lab' => [

        'ai-experiments' => [
            'status' => 'exploring',
        ],

    ],

    'skills' => [

        'laravel' => [
            'name' => 'Laravel',
            'tier' => 'core',
            'related' => ['planino'],
        ],

        'php' => [
            'name' => 'PHP',
            'tier' => 'core',
            'related' => ['planino'],
        ],

        'mysql' => [
            'name' => 'MySQL',
            'tier' => 'practical',
            'related' => ['planino'],
        ],

        'javascript' => [
            'name' => 'JavaScript',
            'tier' => 'practical',
        ],

        'html' => [
            'name' => 'HTML',
            'tier' => 'practical',
        ],

        'css' => [
            'name' => 'CSS',
            'tier' => 'practical',
        ],

        'git' => [
            'name' => 'Git',
            'tier' => 'practical',
        ],

    ],

];
