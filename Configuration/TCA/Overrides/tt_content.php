<?php
defined('TYPO3') or die;

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$ll = 'LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang_be.xlf:';
$icon = 'EXT:jw_feuser_manager/Resources/Public/Icons/ContentElements/ListOfUsers.png';

// TYPO3 v14 removed the list_type mechanism — each plugin is now its own CType.
// registerPlugin() creates the CType jwfeusermanager_<plugin>, adds it to the
// "plugins" group of the new-content-element wizard and (7th argument) wires up the
// FlexForm. The generated CType names equal the former list_type values
// (jwfeusermanager_listofusers / _edituser), so existing content elements migrate
// 1:1 — see the LegacyListTypeToCTypeUpgrade wizard.
ExtensionUtility::registerPlugin(
    'JwFeUserManager',
    'ListOfUsers',
    $ll . 'tt_content.list_type_listofusers',
    $icon,
    'plugins',
    $ll . 'tt_content.list_type_listofusers_description',
    'FILE:EXT:jw_feuser_manager/Configuration/FlexForms/listofusers.xml'
);
ExtensionUtility::registerPlugin(
    'JwFeUserManager',
    'EditUser',
    $ll . 'tt_content.list_type_edituser',
    $icon,
    'plugins',
    $ll . 'tt_content.list_type_edituser_description',
    'FILE:EXT:jw_feuser_manager/Configuration/FlexForms/edituser.xml'
);
