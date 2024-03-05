<?php
defined('TYPO3_MODE') || die();

// Add an entry in the static template list found in sys_templates for static TS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
   'jw_feuser_manager',
   'Configuration/Typoscript',
   'JW Frontend User Manager'
);