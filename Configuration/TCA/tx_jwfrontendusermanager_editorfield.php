<?php

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$ll = 'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xml:';
$extensionPath = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('jw_feuser_manager'));

$editorFieldTca = [
    'ctrl' => [
        'title' => $ll . 'edituser_field_flexform',
        'label' => 'title',
       // 'label_userFunc' => 'ArminVieweg\Dce\UserFunction\CustomLabels\DceFieldLabel->getLabel',
        'hideTable' => true,
        'adminOnly' => false,
        'rootLevel' => false,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'requestUpdate' => 'type',
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            '0' => 'ext-dce-dcefield-type-element',
            '1' => 'ext-dce-dcefield-type-tab',
            '2' => 'ext-dce-dcefield-type-section',
            '3' => 'ext-dce-dcefield-type-section',
            '4' => 'ext-dce-dcefield-type-section',
            '5' => 'ext-dce-dcefield-type-section',
            '6' => 'ext-dce-dcefield-type-section',
            '7' => 'ext-dce-dcefield-type-section',
            '8' => 'ext-dce-dcefield-type-section',
            '9' => 'ext-dce-dcefield-type-section'
        ],
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,type',
    ],
    'types' => [
        '0' => [ // db field
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;dbfield',
        ],
        '1' => [ // password
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;password'
        ],
        '2' => [ // image
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;image'
        ],
        '3' => [ // additional richtext
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;additionalRichtext'
        ],
        '9' => [ // additional entries
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;dbfield,'.
                          '--palette--;;additionalEntries',
        ],
        '4' => [ // separator
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab'
        ],
        '5' => [ // db field readonly
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
                          '--palette--;;dbfield'
        ],
        '6' => [ // delete user
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
						  '--palette--;;deleteuser'
        ],
        '7' => [ // usergroups
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab'
        ],
        '8' => [ // send e-mail
            'showitem' => '--palette--;;general_header;;;fixed-font:enable-tab,' .
						  '--palette--;;email'
        ],
    ],
    'palettes' => [
        'general_header' => ['showitem' => 'type,title,hidden,required', 'canNotCollapse' => true],
        'dbfield' => ['showitem' => 'db_field,db_mode', 'canNotCollapse' => true],
        'password' => ['showitem' => 'password_generator', 'canNotCollapse' => true],
        'image' => ['showitem' => 'image_path,image_filename', 'canNotCollapse' => true],
        'additionalRichtext' => ['showitem' => 'content', 'canNotCollapse' => true],
        'additionalEntries' => ['showitem' => 'selectoption_entries', 'canNotCollapse' => true],
        'deleteuser' => ['showitem' => 'redirect_page', 'canNotCollapse' => true],
        'email' => ['showitem' => 'email_mode,email_recipient,--linebreak--,email_subject,--linebreak--,email_content', 'canNotCollapse' => true],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_jwfeusermanager_editorfield',
                'foreign_table_where' => 'AND tx_jwfeusermanager_editorfield.pid=###CURRENT_PID### ' .
                    'AND tx_jwfeusermanager_editorfield.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => $ll . 'edituser_field_flexform.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'required' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.required',
            'config' => [
                'type' => 'check',
            ],
        ],
        'password_generator' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.password_generator',
            'config' => [
                'type' => 'check',
            ],
        ],
        'sorting' => [
            'label' => 'Sorting',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'type' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'edituser_field_flexform.type.db_field', 0],
                    [$ll . 'edituser_field_flexform.type.db_field_readonly', 5],
                    [$ll . 'edituser_field_flexform.type.additional_entries', 9],
                    [$ll . 'edituser_field_flexform.type.password', 1],
                    [$ll . 'edituser_field_flexform.type.image', 2],
                    [$ll . 'edituser_field_flexform.type.additional_richtext', 3],
                    [$ll . 'edituser_field_flexform.type.separator', 4],
                    [$ll . 'edituser_field_flexform.type.delete_user', 6],
                    [$ll . 'edituser_field_flexform.type.usergroups', 7],
                    [$ll . 'edituser_field_flexform.type.send_email', 8],
                ],
            ],
        ],
        'db_mode' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.db_mode',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'edituser_field_flexform.db_mode.text', 0],
                    [$ll . 'edituser_field_flexform.db_mode.text_multiline', 1],
                    [$ll . 'edituser_field_flexform.db_mode.email', 2],
                    [$ll . 'edituser_field_flexform.db_mode.boolean', 3],
                    [$ll . 'edituser_field_flexform.db_mode.date', 4],
                    [$ll . 'edituser_field_flexform.db_mode.time', 5],
                    [$ll . 'edituser_field_flexform.db_mode.datetime', 6],
                    [$ll . 'edituser_field_flexform.db_mode.multiselect', 7],
                    [$ll . 'edituser_field_flexform.db_mode.options', 8],
                ],
            ],
        ],
		'db_field' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.db_field',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
				'itemsProcFunc' => 'JwTue\FeUserManager\Utility\Helper->getEditableFieldNames',
            ],
		],
        'email_mode' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.email_mode',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$ll . 'edituser_field_flexform.email_mode.always', 0],
                    [$ll . 'edituser_field_flexform.email_mode.checkbox_default_yes', 1],
                    [$ll . 'edituser_field_flexform.email_mode.checkbox_default_no', 2],
                    [$ll . 'edituser_field_flexform.email_mode.bcc_checkbox_default_yes', 3],
                    [$ll . 'edituser_field_flexform.email_mode.bcc_checkbox_default_no', 4],
                ],
            ],
        ],
        'email_recipient' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.email_recipient',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,email'
            ],
        ],
        'email_subject' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.email_subject',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'email_content' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.email_content',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
            ],
			'defaultExtras' => 'richtext[*]:rte_transform[flag=rte_enabled|mode=ts_css]',
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'Titel',
            'config' => [
                'type' => 'input',
                'size' => 15,
                'eval' => 'trim,required'
            ],
        ],
        'content' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.content',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
            ],
			'defaultExtras' => 'richtext[*]:rte_transform[flag=rte_enabled|mode=ts_css]',
        ],
        'selectoption_entries' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.selectoption_entries',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
                'eval' => 'trim',
                'enableRichtext' => false
            ],
        ],
		'redirect_page' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.redirect_page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => '1',
                'maxitems' => '1',
                'minitems' => '0',
                'show_thumbs' => '1',
            ],
		],
		'image_path' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.image_path',
            'config' => [
                'type' => 'input',
                'size' => '48',
                'eval' => 'trim',
				'wizards' => array(
					'link' => array(
						'type' => 'popup',
						'title' => $ll . 'edituser_field_flexform.image_path_wizard_title',
						'icon' => 'EXT:backend/Resources/Public/Images/filetree-folder-default.gif',
						'module' => array(
						   'name' => 'wizard_link',
						),
						'params' => array(
							'blindLinkOptions' => 'page,url,mail,spec,file',
						),
						'JSopenParams' => 'height=800,width=600,status=0,menubar=0,scrollbars=1'
					)
				),
			  'softref' => 'typolink'
            ],
		],
		'image_filename' => [
            'exclude' => 0,
            'label' => $ll . 'edituser_field_flexform.image_filename',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ["User id", 0],
                    ["User name", 1],
                ],
            ],
		],
        'parent_ce' => [
            'exclude' => 0,
            'label' => $ll . 'tx_jwfeusermanager_editorfield.parent_ce',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];

return $editorFieldTca;
