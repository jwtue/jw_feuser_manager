<?php

defined('TYPO3') or die;

$boot = function () {
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

	// v14 notes:
	// - The new-content-element wizard entries are added by ExtensionUtility::registerPlugin()
	//   in Configuration/TCA/Overrides/tt_content.php (group "plugins"). The former
	//   ModWizards page TSConfig + addPageTSConfig() are obsolete (addPageTSConfig() was removed).
	// - Icons are declared in Configuration/Icons.php; instantiating the IconRegistry here
	//   is no longer allowed.
};

$boot();
unset($boot);

// The upgrade wizards (Classes/Updates/) register themselves via the attribute
// #[UpgradeWizard('...')] and the autoconfiguration in Configuration/Services.yaml.
// An SC_OPTIONS registration is no longer needed as of TYPO3 v12.3 — and would
// instantiate the wizards via makeInstance() without dependency injection, which fails
// on the ConnectionPool constructor argument.