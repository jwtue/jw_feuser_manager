<?php
defined('TYPO3_MODE') || die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$languageFile = 'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:';

$temporaryColumns = [
    'mobilephone' => [
        'label' => $languageFile . 'fe_users.mobilephone',
        'config' => [
            'type' => 'input',
            'size' => 20,
        ]
    ],
	'phone_business' => [
        'label' => $languageFile . 'fe_users.phone_business',
        'config' => [
            'type' => 'input',
            'size' => 20,
        ]
    ],
	'date_of_birth' => [
        'label' => $languageFile . 'fe_users.date_of_birth',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'date',
		  'autocomplete' => false,
        ]
    ],
	'image' => [
		'label' => $languageFile . 'fe_users.image',
		'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
			'image',
			[
				// custom configuration for displaying fields in the overlay/reference table
				// to use the image overlay palette instead of the basic overlay palette
				'overrideChildTca' => [
					'types' => [
						'0' => [
							'showitem' => '
								--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
								--palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
							'showitem' => '
								--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
								--palette--;;filePalette'
						],
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
					],
				],
			],
        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
		),
	],
    'tx_jwfeusermanager_newsletter_subscribed' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_newsletter_subscribed',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_lastupdated' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_lastupdated',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'datetime',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_text_1' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_text_1',
        'config' => [
		  'type' => 'text',
		  'rows' => '3',
		  'eval' => 'trim',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_text_2' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_text_2',
        'config' => [
		  'type' => 'text',
		  'rows' => '3',
		  'eval' => 'trim',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_text_3' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_text_3',
        'config' => [
		  'type' => 'text',
		  'rows' => '3',
		  'eval' => 'trim',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_text_4' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_text_4',
        'config' => [
		  'type' => 'text',
		  'rows' => '3',
		  'eval' => 'trim',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_text_5' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_text_5',
        'config' => [
		  'type' => 'text',
		  'rows' => '3',
		  'eval' => 'trim',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_boolean_1' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_boolean_1',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_additional_boolean_2' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_boolean_2',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_additional_boolean_3' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_boolean_3',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_additional_boolean_4' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_boolean_4',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_additional_boolean_5' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_boolean_5',
        'config' => [
            'type' => 'check'
        ]
    ],
    'tx_jwfeusermanager_additional_bitfield_1' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_bitfield_1',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'int',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_bitfield_2' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_bitfield_2',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'int',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_bitfield_3' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_bitfield_3',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'int',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_bitfield_4' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_bitfield_4',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'int',
		  'autocomplete' => false,
        ]
    ],
    'tx_jwfeusermanager_additional_bitfield_5' => [
        'exclude' => true,
        'label' => $languageFile . 'fe_users.tx_jwfeusermanager_additional_bitfield_5',
        'config' => [
		  'type' => 'input',
		  'size' => '20',
		  'eval' => 'int',
		  'autocomplete' => false,
        ]
    ],
];

ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'date_of_birth', '', 'after:last_name');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobilephone', '', 'after:telephone');
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'phone_business', '', 'after:telephone');
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:fe_users.div.jwfrontendusermanager,
    tx_jwfeusermanager_newsletter_subscribed,
    tx_jwfeusermanager_lastupdated,
    tx_jwfeusermanager_additional_text_1,
    tx_jwfeusermanager_additional_text_2,
    tx_jwfeusermanager_additional_text_3,
    tx_jwfeusermanager_additional_text_4,
    tx_jwfeusermanager_additional_text_5,
    tx_jwfeusermanager_additional_boolean_1,
    tx_jwfeusermanager_additional_boolean_2,
    tx_jwfeusermanager_additional_boolean_3,
    tx_jwfeusermanager_additional_boolean_4,
    tx_jwfeusermanager_additional_boolean_5,
    tx_jwfeusermanager_additional_bitfield_1,
    tx_jwfeusermanager_additional_bitfield_2,
    tx_jwfeusermanager_additional_bitfield_3,
    tx_jwfeusermanager_additional_bitfield_4,
    tx_jwfeusermanager_additional_bitfield_5'
);
