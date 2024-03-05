<?php
namespace JwTue\FeUserManager\ViewHelpers\Form;

use \TYPO3\CMS\Core\Page\PageRenderer;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;

class DateTimePickerViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper
     * @internal
     */
    public function injectPropertyMapper(\TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * Initialize the arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('size', 'int', 'The size of the input field');
        $this->registerTagAttribute('placeholder', 'string', 'Specifies a short hint that describes the expected value of an input element');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', false, 'f3-form-error');
        $this->registerArgument('initialDate', 'string', 'Initial date (@see http://www.php.net/manual/en/datetime.formats.php for supported formats)');
        $this->registerArgument('enableDatePicker', 'bool', 'Enable the Datepicker', false, true);
        $this->registerArgument('enableTimePicker', 'bool', 'Enable the Datepicker', false, true);
        $this->registerArgument('previewMode', 'bool', 'Preview mde flag', true, false);
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the text field, hidden field and required javascript
     *
     * @return string
     * @api
     */
    public function render()
    {
        $enableDatePicker = $this->arguments['enableDatePicker'];
        $enableTimePicker = $this->arguments['enableTimePicker'];
        $previewMode = (bool)$this->arguments['previewMode'];
		$initialDate = $this->arguments['initialDate'];

        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

		if ($enableDatePicker && $enableTimePicker) {
			$this->tag->addAttribute('type', 'datetime-local');
            $this->tag->addAttribute('value', date("Y-m-d\TH:i", $initialDate));
		} else if ($enableTimePicker) {
			$this->tag->addAttribute('type', 'time');
            $this->tag->addAttribute('value', date("H:i:s", $initialDate));
		} else {
			$this->tag->addAttribute('type', 'date');
            $this->tag->addAttribute('value', date("Y-m-d", $initialDate));
		}
        $this->tag->addAttribute('name', $name);

        if ($this->hasArgument('id')) {
            $id = $this->arguments['id'];
        } else {
            $id = 'field' . md5(uniqid());
        }

        $this->tag->addAttribute('id', $id);

        $this->setErrorClassAttribute();
        $content = '';
        $content .= $this->tag->render();
       // $content .= '<input type="hidden" name="' . $name . '[dateFormat]" value="' . htmlspecialchars($dateFormat) . '" />';

        return $content;
    }

    /**
     * @return PageRenderer
     */
    protected function getPageRenderer(): PageRenderer
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
