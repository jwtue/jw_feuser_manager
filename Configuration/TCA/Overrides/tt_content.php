<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JwTue.FeUserManager',
    'ListOfUsers',
    'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:tt_content.list_type_listofusers',
    'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JwTue.FeUserManager',
    'EditUser',
    'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:tt_content.list_type_edituser',
    'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png'
);
/*
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:tt_content.list_type_listofusers',
        'jwfrontendusermanager_listofusers',
		'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png'
    ],
    'list_type',
    'jwfrontendusermanager'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:tt_content.list_type_edituser',
        'jwfrontendusermanager_edituser',
		'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png'
    ],
    'list_type',
    'jwfrontendusermanager'
);*/

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['jwfrontendusermanager_listofusers'] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['jwfrontendusermanager_listofusers'] = 'pi_flexform';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['jwfrontendusermanager_edituser'] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['jwfrontendusermanager_edituser'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'jwfrontendusermanager_listofusers',
    'FILE:EXT:jw_feuser_manager/Configuration/FlexForms/listofusers.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'jwfrontendusermanager_edituser',
    'FILE:EXT:jw_feuser_manager/Configuration/FlexForms/edituser.xml'
);
