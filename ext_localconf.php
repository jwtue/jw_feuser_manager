<?php

defined('TYPO3') or die;

$boot = function () {
	$extKey = "jw_feuser_manager";
    /* ===========================================================================
        Extbase-based plugin
    =========================================================================== */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JwFeUserManager',
        'ListOfUsers',
        // cacheable actions
        [
            \JwTue\FeUserManager\Controller\ShowFeUserController::class => 'list,detail',
        ],
        // non-cacheable actions
        [
		]
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JwFeUserManager',
        'EditUser',
        // cacheable actions
        [
            \JwTue\FeUserManager\Controller\EditFeUserController::class => 'edit',
        ],
        // non-cacheable actions
        [
            \JwTue\FeUserManager\Controller\EditFeUserController::class => 'edit',
		]
    );
	
    /**
     * Page TypoScript for mod wizards
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TsConfig/ModWizards.typoscript">'
    );
	
	/**
	 * Register icons
	 */
	$iconRegistry =
		\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
	$iconRegistry->registerIcon(
		'extension-' . $extKey . '-listofusers',
		\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
		['source' => 'EXT:' . $extKey . '/Resources/Public/Icons/ContentElements/ListOfUsers.png']
	);
	$iconRegistry->registerIcon(
		'extension-' . $extKey . '-edituser',
		\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
		['source' => 'EXT:' . $extKey . '/Resources/Public/Icons/ContentElements/ListOfUsers.png']
	);
};

$boot();
unset($boot);

// Die Upgrade-Wizards (Classes/Updates/) registrieren sich ueber das Attribut
// #[UpgradeWizard('...')] und die Autoconfiguration in Configuration/Services.yaml.
// Eine SC_OPTIONS-Registrierung ist ab TYPO3 v12.3 nicht mehr noetig — und wuerde die
// Wizards per makeInstance() ohne Dependency-Injection instanziieren, was am
// ConnectionPool-Konstruktorargument scheitert.