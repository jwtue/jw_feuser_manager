<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Adds the redirect field to the fe_groups table
$additionalColumns = [
	'image' => [
		'exclude' => true,
		'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.image',
		'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
			'image',
			[
				'type' => 'inline',
				'appearance' => [
					'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference',
                    'elementBrowserType' => 'file',
					'elementBrowserAllowed' => 'png,jpeg,gif,jpg,svg',
				],
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					)
				),
				'minitems' => 0,
				'maxitems' => 1,
				'foreign_match_fields' => [
					'fieldname' => 'image',
					'tablenames' => 'fe_groups',
					'table_local' => 'sys_file',
				],
			],
			$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
		),
	],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $additionalColumns);
ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'image', '', 'after:subgroup');
