<?php

namespace JwTue\FeUserManager\ViewHelpers\Link;

class PhoneViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{
	protected $tagName = 'a';
   
    /**
     * Initialize arguments
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        // TYPO3 v14 removed registerUniversalTagAttributes(); AbstractTagBasedViewHelper
        // registers the universal tag attributes on its own now.

        $this->registerArgument('tel', 'string', 'phone number to format and link to', true);
        $this->registerArgument('defaultInternationalPrefix', 'string', 'phone number to format', false, "+49");
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
    public function render(): string
    {
		if (!empty($this->arguments["defaultInternationalPrefix"])) {
			$number = \JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::formatPhoneNumber($this->arguments['tel'], true, $this->arguments["defaultInternationalPrefix"]);
		} else {
			$number = \JwTue\FeUserManager\ViewHelpers\Format\PhoneViewHelper::formatPhoneNumber($this->arguments['tel'], true);
		}
		if (!empty($number)) {
			$this->tag->addAttribute('href', "tel:".$number);
			$this->tag->setContent($this->renderChildren());
			$result = $this->tag->render();
		} else {
			$result = $this->renderChildren();
		}
		return $result;
    }
}