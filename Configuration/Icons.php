<?php

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

// Icon registration for TYPO3 v14: instantiating the IconRegistry in ext_localconf.php
// is no longer allowed. Icons are declared here (auto-loaded).
return [
    'extension-jw_feuser_manager-listofusers' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png',
    ],
    'extension-jw_feuser_manager-edituser' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png',
    ],
];
