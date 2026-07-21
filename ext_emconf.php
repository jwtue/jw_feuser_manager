<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Frontend User Manager',
    'description' => 'Frontend user management from the frontend',
    'category' => 'plugin',
    'author' => 'Jonas Wolf',
    'author_email' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '12.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'felogin' => '12.4.0-12.4.99',
            'vhs' => '7.0.0-7.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
