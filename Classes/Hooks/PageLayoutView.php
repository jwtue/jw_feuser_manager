<?php

namespace JwTue\FeUserManager\Hooks;

use \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use \TYPO3\CMS\Backend\View\PageLayoutView;

class PageLayoutView implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {
    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView $parentObject Calling parent object
     * @param boolean $drawItem Whether to draw the item using the default functionalities
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     * @return void
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {

        $flexform = $row['pi_flexform'];
				
		if ($row['list_type'] == 'jwfrontendusermanager_listofusers') {
		//	$drawItem = FALSE;
		//	$headerContent = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate("tt_content.list_type_listofusers", "jw_feuser_manager");
			return;
		} else if ($row['list_type'] == 'jwfrontendusermanager_edituser') {
		//	$drawItem = FALSE;
		//	$headerContent = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate("tt_content.list_type_edituser", "jw_feuser_manager");
			$itemContent = "test";
			/*ob_start();
			print_r($row);
			$itemContent = ob_get_clean();*/
		}
		return;
	}
}
