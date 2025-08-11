<?php
 defined('TYPO3') or die;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Adds the redirect field to the fe_groups table
$additionalColumns = [
	'image' => [
		'exclude' => true,
		'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.image',
		'config' => [
            'type' => 'file',
			'minitems' => 0,
            'maxitems' => 1,
            'allowed' => 'common-image-types'
        ],
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $additionalColumns);
ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'image', '', 'after:subgroup');
