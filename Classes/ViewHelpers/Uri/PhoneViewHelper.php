<?php

namespace JwTue\FeUserManager\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class PhoneViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
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
     * Resizes the image (if required) and returns its path. If the image was not resized, the path will be equal to $src
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws Exception
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {		
		if (!empty($arguments["tel"])) {
			$tel = $arguments["tel"];
		} else {
			$tel = $renderChildrenClosure();
		}
		if (!empty($arguments["defaultInternationalPrefix"])) {
			return self::formatPhoneNumber($tel, true, $arguments["defaultInternationalPrefix"]);
		} else {
			return self::formatPhoneNumber($tel, true);
		}
    }
}