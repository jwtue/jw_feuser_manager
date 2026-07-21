<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JW Frontend User Manager',
    'description' => 'Frontend user management from the frontend',
    'category' => 'plugin',
    'author' => 'Jonas Wolf',
    'author_email' => '',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '13.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'felogin' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
