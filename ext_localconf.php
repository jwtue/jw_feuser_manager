<?php
defined('TYPO3_MODE') || die();

$boot = function () {
	$extKey = "jw_frontendusermanager";
    /* ===========================================================================
        Extbase-based plugin
    =========================================================================== */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JwTue.FeUserManager',
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
        'JwTue.FeUserManager',
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
	
	//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['jwfrontendusermanager_listofusers'] = \JwTue\FeUserManager\Hooks\PageLayoutView::class;
	//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['jwfrontendusermanager_edituser'] = \JwTue\FeUserManager\Hooks\PageLayoutView::class;
	
	
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