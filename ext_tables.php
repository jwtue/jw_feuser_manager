<?php
defined('TYPO3_MODE') || die();

$boot = function () {	
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jwfeusermanager_editorfield');
};

$boot();
unset($boot);