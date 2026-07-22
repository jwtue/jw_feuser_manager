<?php

namespace JwTue\FeUserManager\ViewHelpers\Uri;

class PhoneViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('tel', 'string', 'phone number to format');
        $this->registerArgument('defaultInternationalPrefix', 'string', 'phone number to format');
    }

    /**
     * Returns the phone number in machine format (for a tel: URI).
     *
     * Instance render() instead of the former renderStatic() +
     * CompileWithContentArgumentAndRenderStatic trait, which TYPO3 v14 removed.
     * formatPhoneNumber lives in the Format\PhoneViewHelper (self:: was wrong here).
     */
    public function render(): string
    {
		if (!empty($this->arguments["tel"])) {
			$tel = $this->arguments["tel"];
		} else {
			$tel = $this->renderChildren();
		}
		if (!empty($this->arguments["defaultInternationalPrefix"])) {
			return \JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::formatPhoneNumber($tel, true, $this->arguments["defaultInternationalPrefix"]);
		} else {
			return \JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::formatPhoneNumber($tel, true);
		}
    }
}