<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Frontend User Manager',
    'description' => 'Frontend user management from the frontend',
    'category' => 'plugin',
    'author' => 'Jonas Wolf',
    'author_email' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.4.0-14.99.99',
            'felogin' => '14.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
