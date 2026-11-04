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
            'technologies' => ['Laravel', 'PHP', 'MySQL'],
            'repository' => null,
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
