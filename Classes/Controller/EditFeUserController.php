<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace JwTue\FeUserManager\Controller;

use JwTue\FeUserManager\Domain\Repository\FrontendUserRepository;
use JwTue\FeUserManager\Domain\Repository\FrontendUserGroupRepository;
use JwTue\FeUserManager\Domain\Repository\EditorFieldRepository;
use JwTue\FeUserManager\Domain\Model\EditorField;
use JwTue\FeUserManager\Utility\Helper;
use JwTue\FeUserManager\Validation\Validator\UniqueUsernameValidator;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Form\Domain\Model\FormDefinition;
use \TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * File controller.
 *
 */
class EditFeUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	private const DEFAULT_CROP = '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":null}}';
	
    private LanguageService $languageService;

    /**
     * User repository
     *
     * @var \JwTue\FeUserManager\Domain\Repository\FrontendUserRepository
	 * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $userRepository;

    /**
     * User group repository
     *
     * @var \JwTue\FeUserManager\Domain\Repository\FrontendUserGroupRepository
	 * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $userGroupRepository;

	
    /**
     * @param FrontendUserRepository $userRepository
     *
     * @return void
     */
    public function injectUserRepository(FrontendUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
	
    /**
     * @param FrontendUserGroupRepository $userGroupRepository
     *
     * @return void
     */
    public function injectUserGroupRepository(FrontendUserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }
	
    /**
     * Editor field repository
     *
     * @var \JwTue\FeUserManager\Domain\Repository\EditorFieldRepository
	 * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $editorFieldRepository;
	

	/**
	 * Content-Object des Plugins. In TYPO3 v12 deklariert der ActionController diese
	 * Eigenschaft nicht mehr selbst — sie wird in initializeView() aus dem
	 * ConfigurationManager gesetzt und muss deshalb hier deklariert sein (ab PHP 8.2
	 * sind dynamische Eigenschaften deprecated).
	 */
	protected ?ContentObjectRenderer $contentObj = null;

	public function __construct(
		private readonly LanguageServiceFactory $languageServiceFactory,
		private readonly ResourceFactory $resourceFactory,
		private readonly PersistenceManager $persistenceManager,
	) {}


    /**
     * @param EditorFieldRepository $editorFieldRepository
     *
     * @return void
     */
    public function injectEditorFieldRepository(EditorFieldRepository $editorFieldRepository)
    {
        $this->editorFieldRepository = $editorFieldRepository;
    }
	
	/**
	 * cacheUtility
	 *
	 * @var \TYPO3\CMS\Core\Cache\CacheManager
	 */
	//protected $cacheInstance;

	private $cacheService;
	public function initializeAction() {
	   //$this->cacheInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache("myExtKey");
		$cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
		$this->cacheService = new \TYPO3\CMS\Extbase\Service\CacheService($this->configurationManager, $cacheManager);
	}

		
	
	use ViewConfigurationTrait;

    /**
     * Initializes the view before invoking an action method.
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param $view The view to be initialized
     */
     protected function initializeView($view)
     {
		$view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
		
		if (method_exists($this->configurationManager, "initializeObject")) {
			$this->configurationManager->initializeObject();
		}
		$this->contentObj = $this->configurationManager->getContentObjectRenderer();
		
		$view->getRequest()->setControllerExtensionName($this->request->getControllerExtensionName());
		$view->getRequest()->setPluginName("EditUser");
		$view->getRequest()->setControllerName("EditUser");
		$view->setFormat('html');
		
		$this->setViewConfiguration($view);
		
		$view->assign("TSFE", $GLOBALS['TSFE']);
		$view->assign("page", $GLOBALS['TSFE']->page);
		
		$conf = $this->getFullTyposcriptConfiguration();
		$view->assign("fluidSettings", $conf['lib']['contentElement']['settings']);
		
		$view->assign("contentObject", $this->configurationManager->getContentObjectRenderer());
	    $view->assign("settings", $this->settings);
					
		$pageRender = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
		$jsFile = "EXT:jw_feuser_manager/Resources/Public/JavaScript/cropper.min.js";
		$cssFile = "EXT:jw_feuser_manager/Resources/Public/Css/cropper.min.css";
		$pageRender->addJsFooterFile($jsFile, 'text/javascript', true, false, '', true);
		$pageRender->addCssFile($cssFile);
					
		$this->view = $view;
     }
	
    /**
     * Listing of files.
     *
     * @param int $filter Optional argument to list only users from group with given uid
	 * @param string $download The format to create a download, or null for default list
     * @return void|string
     */
    public function editAction($user = 0) {
		/*
		$user = $this->request->getQueryParams()['user'] ?? 0;
		
		if ($user == 0) {
			$user = $GLOBALS['TSFE']->fe_user->user['uid'];
		}
		
		$user = $this->userRepository->findByUid($user);

		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($user);
		//die();
		
		if ($user == null) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}*/
		
		switch ($this->settings['mode']) {
			case 1:
				$user = null;
				break;
			case 2:
				if ($this->request->getQueryParams()['user']) {
					$user = $this->userRepository->findByUid($this->request->getQueryParams()['user']);
					break;
				}
			case 0:
			default:
				$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}
		/*
		if ($this->settings['mode'] == 1) {
			$user = null;
		}*/
		
		$this->view->getRequest()->setControllerActionName("edit");
		$this->view->setTemplate("EditUserEdit");
		
		$columns = $this->editorFieldRepository->findForElement($GLOBALS['TSFE']->page['uid'], $this->contentObj->data['uid'])->toArray();
		
		//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($dump);
				
		$this->languageService = $this->getLanguageService($this->request);
				
        $prototypeName = 'standard';
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $prototypeConfiguration = $configurationService->getPrototypeConfiguration($prototypeName);
						
		$prototypeConfiguration["formElementsDefinition"]["Html"] = $prototypeConfiguration["formElementsDefinition"]["Text"];
		$prototypeConfiguration["formElementsDefinition"]["Fluid"] = $prototypeConfiguration["formElementsDefinition"]["Text"];
		$prototypeConfiguration["formElementsDefinition"]["LabeledFluid"] = $prototypeConfiguration["formElementsDefinition"]["Text"];
		$prototypeConfiguration["formElementsDefinition"]["LabeledStaticText"] = $prototypeConfiguration["formElementsDefinition"]["StaticText"];
		$prototypeConfiguration["formElementsDefinition"]["DateTimePicker"] = $prototypeConfiguration["formElementsDefinition"]["Text"];
				
		$conf = $this->getFullTyposcriptConfiguration();
		
		$form = GeneralUtility::makeInstance(FormDefinition::class, 'jwfeusermanager-edituser-'.$this->contentObj->data['uid'], $prototypeConfiguration);
        $form->setRenderingOption('controllerAction', 'edit');
        if ($user != null) $form->setRenderingOption('additionalParams', array("user" => $user->getUid()));
		$form->setRenderingOption('templateRootPaths ', $form->getRenderingOptions()['templateRootPaths']+
			array(time() => "EXT:jw_feuser_manager/Resources/Private/Form-Frontend/Templates/")
			);
		$form->setRenderingOption('partialRootPaths', $form->getRenderingOptions()['partialRootPaths']+
			array(time() => "EXT:jw_feuser_manager/Resources/Private/Form-Frontend/Partials/")
			);
		$form->setRenderingOption('layoutRootPaths ', $form->getRenderingOptions()['layoutRootPaths']+
			array(time() => "EXT:jw_feuser_manager/Resources/Private/Form-Frontend/Layouts/")
			);
		
        $page1 = $form->createPage('page1');
		
		$colsById = array();
				
		foreach ($columns as $k => $col) {
            if ($col->isHidden()) continue;
			switch ($col->getType()) {
				case EditorField::TYPE_DB_FIELD:
				case EditorField::TYPE_ADDITIONAL_ENTRIES:
					$title = $col->getTitle();
				
					if ($col->getDbMode() == EditorField::MODE_DB_TEXT) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'Text');	
						if ($user != null) $el->setDefaultValue($user->getFields()[$col->getUsableDbField()]);
						if (substr($title, -1) != ":") $title .= ":";
					} else if ($col->getDbMode() == EditorField::MODE_DB_TEXT_MULTILINE) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'Textarea');	
                        $el->setProperty("rows", 3);
						if ($user != null) $el->setDefaultValue($user->getFields()[$col->getUsableDbField()]);
                        if (substr($title, -1) != ":") $title .= ":";				
					} else if ($col->getDbMode() == EditorField::MODE_DB_EMAIL) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'Text');
						if ($user != null) $el->setDefaultValue($user->getFields()[$col->getUsableDbField()]);
						$el->addValidator(GeneralUtility::makeInstance(EmailAddressValidator::class));
                        if (substr($title, -1) != ":") $title .= ":";
					} else if ($col->getDbMode() == EditorField::MODE_DB_BOOLEAN) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'Checkbox');
						if ($user != null) $el->setDefaultValue($user->getFields()[$col->getUsableDbField()]);
					} else if ($col->getDbMode() == EditorField::MODE_DB_DATE) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'DateTimePicker');
						$el->setProperty("displayDateSelector", true);
						$el->setProperty("displayTimeSelector", false);
						if ($user != null && $user->getFields()[$col->getUsableDbField()]) {
							$el->setProperty("initialDate", $user->getFields()[$col->getUsableDbField()]);
						}
						$ell = $page1->createElement("editorfield_".$col->getUid()."_editable", 'Fluid');
						$ell->setProperty("content", '<script type="text/javascript">document.getElementById(\'jwfeusermanager-edituser-'.$this->contentObj->data['uid'].'-editorfield_'.$col->getUid().'\').readOnly = false;</script>');
                        if (substr($title, -1) != ":") $title .= ":";
					} else if ($col->getDbMode() == EditorField::MODE_DB_TIME) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'DateTimePicker');
						$el->setProperty("displayDateSelector", false);
						$el->setProperty("displayTimeSelector", true);
						if ($user != null && $user->getFields()[$col->getUsableDbField()]) {
							$el->setProperty("initialDate", $user->getFields()[$col->getUsableDbField()]);
						}
                        if (substr($title, -1) != ":") $title .= ":";
					} else if ($col->getDbMode() == EditorField::MODE_DB_DATETIME) {
						$el = $page1->createElement("editorfield_".$col->getUid(), 'DateTimePicker');
						$el->setProperty("displayDateSelector", true);
						$el->setProperty("displayTimeSelector", true);
						if ($user != null && $user->getFields()[$col->getUsableDbField()]) {
							$el->setProperty("initialDate", $user->getFields()[$col->getUsableDbField()]);
						}
                        if (substr($title, -1) != ":") $title .= ":";
					} else if ($col->getDbMode() == EditorField::MODE_DB_MULTISELECT) {                        
                        $el = $page1->createElement("editorfield_".$col->getUid(), 'MultiCheckbox');
                        if (substr($title, -1) != ":") $title .= ":";
                        $groups = array_map("trim", explode("\n", $col->getSelectoptionEntries()));
                        $options = array();
                        $selected = array();
                        for ($i = 0; $i < count($groups); $i++) {
                            $options[$i+1] = $groups[$i];
                            if ($user != null && (($user->getFields()[$col->getUsableDbField()] >> ($i+1)) & 1) != 0) {
                                $selected[] = $i+1;
                            }
                        }
                        $el->setProperty("options", $options);
                        $el->setDefaultValue($selected);
                    } else if ($col->getDbMode() == EditorField::MODE_DB_OPTIONS) {                        
                        $el = $page1->createElement("editorfield_".$col->getUid(), 'RadioButton');
                        if (substr($title, -1) != ":") $title .= ":";
                        $groups = array_map("trim", explode("\n", $col->getSelectoptionEntries()));
                        $options = array();
                        $selected = 0;
                        for ($i = 0; $i < count($groups); $i++) {
                            $options[$i+1] = $groups[$i];
                            if ($user != null && (($user->getFields()[$col->getUsableDbField()] >> ($i+1)) & 1) != 0) {
                                $selected = $i+1;
                            }
                        }
                        $el->setProperty("options", $options);
                        //$el->setDefaultValue(round(log($user->getFields()[$col->getUsableDbField()], 2)));
                        $el->setDefaultValue($selected);
                    }
					if ($user == null && $col->getDbField() == "username") {
						$el->addValidator(new UniqueUsernameValidator($this->userRepository));
                        if (substr($title, -1) != ":") $title .= ":";
					}
					$el->setLabel($title);
					if ($col->getRequired()) {
						$el->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));
					}
					$colsById["editorfield_".$col->getUid()] = $col;
					break;
				case EditorField::TYPE_PASSWORD:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'AdvancedPassword');
					$col->setDbField("password");
					$title = $col->getTitle();
					$el->setLabel($title.":");
					$el->setProperty("confirmationLabel", str_replace("%s", $col->getTitle(), $this->languageService->sL("LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang.xlf:edituser.confirm")).":");
					if ($col->getRequired()) {
						$el->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));
					}
					$colsById["editorfield_".$col->getUid()] = $col;
					if ($col->getPasswordGenerator()) {
						$el = $page1->createElement("editorfield_".$col->getUid()."_randompw", 'LabeledFluid');
						$el->setLabel("Zufallspasswort:");
						$el->setProperty("content", "<button onclick=\"var pw = '';var possible = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz123456789';for (var i = 0; i < 8; i++) {pw += possible.charAt(Math.floor(Math.random() * possible.length)); }document.getElementById('jwfeusermanager-edituser-".$this->contentObj->data['uid']."-editorfield_".$col->getUid()."').value=pw;document.getElementById('jwfeusermanager-edituser-".$this->contentObj->data['uid']."-editorfield_".$col->getUid()."-confirmation').value=pw;document.getElementById('jwfeusermanager-edituser-".$this->contentObj->data['uid']."-editorfield_".$col->getUid()."').type='text';document.getElementById('jwfeusermanager-edituser-".$this->contentObj->data['uid']."-editorfield_".$col->getUid()."-confirmation').type='text';return false;\">Erzeugen</button>");
					}
					break;
				case EditorField::TYPE_IMAGE:
					$imgFile = null;
					//if ($user->getImage()->toArray()[0]->getUid() == 13877) {
						//print_r($user->getImage()->toArray()[0]);
						//print_r($user->getImage()->toArray()[0]->getUidLocal());
					//}
					try {
						if ($user != null && $user->getImage() && count($user->getImage()->toArray()) > 0) {
							$imgStor = $user->getImage();
							$imgRes = $imgStor->toArray()[0]->getOriginalResource();
							$imgFile = $imgRes->getOriginalFile();
						}
					} catch (\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $e) {
						
					}
					if ($imgFile != null) {
						$el = $page1->createElement("editorfield_".$col->getUid()."_image", 'ImageCrop');
                        $el->setProperty("imageUid", $imgFile->getUid()*1);
                        $el->setProperty("ceUid", $this->contentObj->data['uid']*1);
                        $el->setProperty("colUid", $col->getUid()*1);
                        
						$el->setLabel("Aktuelles Bild:");
						$el->setProperty("containerClassAttribute", "input imagecrop");
						
						$el = $page1->createElement("editorfield_".$col->getUid()."_crop", 'Hidden');
						$crop = $user->getImage()->toArray()[0]->getOriginalResource()->getProperty('crop');
						if (empty($crop)) $crop = self::DEFAULT_CROP;
						$el->setDefaultValue($crop);
						
						$el = $page1->createElement("editorfield_".$col->getUid()."_delete", 'Checkbox');
						$el->setLabel("Bild löschen");
						
						$el = $page1->createElement("editorfield_".$col->getUid()."_newimage", 'ImageUpload');
						$el->setProperty("saveToFileMount", $col->getImagePathProcessed());
						$el->setLabel("Bild ersetzen:");
					} else {
						$el = $page1->createElement("editorfield_".$col->getUid()."_noimage", 'StaticText');
						$el->setProperty("text", "Kein Bild vorhanden");
					
						$el = $page1->createElement("editorfield_".$col->getUid()."_newimage", 'ImageUpload');
						$el->setProperty("saveToFileMount", $col->getImagePathProcessed());
						$el->setLabel("Bild hochladen:");
					}
					$colsById["editorfield_".$col->getUid()."_newimage"] = $col;
					
					break;
				case EditorField::TYPE_ADDITIONAL_RICHTEXT:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'Html');
					$el->setProperty("content", $col->getContent());
					break;
				case EditorField::TYPE_SEPARATOR:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'Html');
					$el->setProperty("content", "<hr />");
					break;
				case EditorField::TYPE_DB_FIELD_READONLY:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'LabeledStaticText');
					$title = $col->getTitle();
					$el->setLabel($title.":");
                    if ($user != null) $text = $user->getFields()[$col->getUsableDbField()];
                    if ($col->getDbMode() == EditorField::MODE_DB_TIME) {
                        $text = date("H:i:s", $text);
                    } else if ($col->getDbMode() == EditorField::MODE_DB_DATE) {
                        $text = date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d', $text);
                    } else if ($col->getDbMode() == EditorField::MODE_DB_DATETIME) {
                        $text = date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d', $text)." ".date("H:i:s", $text);
                    }
					if ($user != null) $el->setProperty("text", $text);
					break;					
				case EditorField::TYPE_DELETE_USER:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'Checkbox');
					$el->setLabel("Benutzer löschen (kann nicht rückgängig gemacht werden)");
					$el->setDefaultValue(0);
					$colsById["editorfield_".$col->getUid()] = $col;
					break;		
				case EditorField::TYPE_USERGROUPS:
					$el = $page1->createElement("editorfield_".$col->getUid(), 'MultiCheckbox');
					$title = $col->getTitle();
					$el->setLabel($title.":");
					$groups = $this->userGroupRepository->findAll()->toArray();
					usort($groups, function ($a, $b) {
						return strcmp($a->getTitle(), $b->getTitle());
					});
					$options = array();
					foreach ($groups as $g) {
						$options[$g->getUid()] = $g->getTitle();
					}
					$selected = array();
					if ($user != null && $user->getUsergroup() && count($user->getUsergroup()->toArray()) > 0) {
						foreach ($user->getUsergroup()->toArray() as $g) {
							$selected[] = $g->getUid();
						}
					}
					$el->setProperty("options", $options);
					$el->setProperty("fluidAdditionalAttributes", array("size" => min(20, count($groups))));
					$el->setDefaultValue($selected);
					$colsById["editorfield_".$col->getUid()] = $col;
					break;
				case EditorField::TYPE_EMAIL:
					$el = $page1->createElement("editorfield_".$col->getUid(), $col->getEmailMode() == 0 ? 'Hidden' : 'Checkbox');
					if ($col->getEmailMode() == 0 || $col->getEmailMode() == 1 || $col->getEmailMode() == 3) {
						 $el->setDefaultValue(1);
					}
					$el->setLabel(empty($col->getEmailRecipient()) ? "E-Mail an neuen Benutzer senden" : "E-Mail an ".$col->getEmailRecipient()." senden");
					$colsById["editorfield_".$col->getUid()] = $col;
					if ($col->getEmailMode() == 3 || $col->getEmailMode() == 4) {
						$el = $page1->createElement("editorfield_".$col->getUid()."_bcc_label", "LabeledStaticText");
						$el->setLabel("\u{00A0}");
						$el->setProperty("text", "Kopie (BCC) an:");
						
						$el = $page1->createElement("editorfield_".$col->getUid()."_bcc", "Text");
						$el->setLabel("\u{00A0}");
						$el->addValidator(GeneralUtility::makeInstance(EmailAddressValidator::class));
					}
					break;
			}
			//$el->addValidator(GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator::class));
		}
		
		$form->setRenderingOption("submitButtonLabel", $this->languageService->sL("LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang.xlf:edituser.save"));
		
		$closureFinisher = GeneralUtility::makeInstance(\TYPO3\CMS\Form\Domain\Finishers\ClosureFinisher::class);
		$closureFinisher->setOption('closure', function($finisherContext) use (&$user, &$colsById) {
			$formRuntime = $finisherContext->getFormRuntime();
			
			$refIndex = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ReferenceIndex::class);
			
			if ($user == null) $user = GeneralUtility::makeInstance(\JwTue\FeUserManager\Domain\Model\FrontendUser::class);
			
			foreach ($formRuntime->getFormState()->getFormValues() as $key => $value) {
				if (!isset($colsById[$key])) continue;
				if ($colsById[$key]->getType() == EditorField::TYPE_DB_FIELD || $colsById[$key]->getType() == EditorField::TYPE_ADDITIONAL_ENTRIES) {
					if ($colsById[$key]->getDbMode() == EditorField::MODE_DB_DATE) {
						$value = strtotime($value)+12*60*60;
					} else if ($colsById[$key]->getDbMode() == EditorField::MODE_DB_TIME) {
						$value = strtotime($value);
					} else if ($colsById[$key]->getDbMode() == EditorField::MODE_DB_DATETIME) {
						$value = strtotime($value);
					} else if ($colsById[$key]->getDbMode() == EditorField::MODE_DB_MULTISELECT) {
                        $val = 0;
                        foreach ($value as $i) {
                            $val |= (1 << $i);
                        }
                        $value = $val;
                    } else if ($colsById[$key]->getDbMode() == EditorField::MODE_DB_OPTIONS) {
                        $value = 1 << $value;
                    }
					$kkey = $colsById[$key]->getUsableDbField();
					$setFunctionName = 'set'.ucfirst($kkey);
					if (method_exists($user, $setFunctionName)) {
						$user->$setFunctionName($value);
						//  print_r("\$user->$setFunctionName(".$value.")");die();
					}
				} else if ($colsById[$key]->getType() == EditorField::TYPE_PASSWORD) {
					$user->passwordBuffer = $value;
					$hashFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class);
					$objSalt = $hashFactory->getDefaultHashInstance("FE");
					if (is_object($objSalt)) {
							$value = $objSalt->getHashedPassword($value);
					}
					$user->setPassword($value);
				} else if ($colsById[$key]->getType() == EditorField::TYPE_USERGROUPS) {
					$groups = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
					foreach ($value as $gid) {
						$groups->attach($this->userGroupRepository->findByUid($gid));
					}
					$user->setUsergroup($groups);
					$refIndex->updateRefIndexTable("fe_users", $user->getUid());
					foreach ($groups->toArray() as $g) {
						$refIndex->updateRefIndexTable("fe_groups", $g->getUid());
					}
				} else if ($colsById[$key]->getType() == EditorField::TYPE_EMAIL) {
					if ($value) {						
						$mail = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);

						$content = $colsById[$key]->getEmailContent();
						$content = str_replace("%firstname%", $user->getFirstName(), $content);
						$content = str_replace("%lastname%", $user->getLastName(), $content);
						$content = str_replace("%username%", $user->getUsername(), $content);
						$content = str_replace("%password%", $user->passwordBuffer, $content);
						
						$mail->setFrom($from = \TYPO3\CMS\Core\Utility\MailUtility::getSystemFrom())
							->setTo(empty($colsById[$key]->getEmailRecipient()) ? 
								[$user->getEmail() => $user->getFirstName()." ".$user->getLastName()] : 
								$colsById[$key]->getEmailRecipient())
							->setSubject($colsById[$key]->getEmailSubject())
							->html($content);
							
						if (isset($formRuntime->getFormState()->getFormValues()[$key."_bcc"]) && !empty($formRuntime->getFormState()->getFormValues()[$key."_bcc"])) {
							$mail->setBcc($formRuntime->getFormState()->getFormValues()[$key."_bcc"]);
						}
						$mail->send();
					}
				} else if ($colsById[$key]->getType() == EditorField::TYPE_DELETE_USER) {
					if ($formRuntime->getFormState()->getFormValues()[$key]) {
						$imgStor = $user->getImage();
						if ($imgStor) {
							foreach ($imgStor->toArray() as $img) {
								try {
									$imgFile = $img->getOriginalResource()->getOriginalFile();
									$imgFile->getStorage()->deleteFile($imgFile);
								} catch (\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $e) {

								}
							}
						}
						$this->userRepository->remove($user);

						// Den Vorschlaghammer instanzieren / aus der Kiste kramen
						// Mit dem Vorschlaghammer in die Datenbank speichern / Nägel mit Köpfen machen
						$persistenceManager = $this->persistenceManager;
						$persistenceManager->persistAll();
						
						$this->cacheService->clearPageCache($colsById[$key]->getRedirectPage());
						
						$finisherContext->cancel();
						
						$typolinkConfiguration = [
							'parameter' => $colsById[$key]->getRedirectPage()
						];
						$redirectUri = $GLOBALS['TSFE']->cObj->typoLink_URL($typolinkConfiguration);
						$redirectUri = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl((string)$redirectUri);
												
						$response = new \TYPO3\CMS\Core\Http\RedirectResponse($redirectUri, 303);
						throw new \TYPO3\CMS\Core\Http\PropagateResponseException($response, 1477070964);
					}
				} else if ($colsById[$key]->getType() == EditorField::TYPE_IMAGE) {
					$kkey = substr($key, 0, -1*strlen("_newimage"));
							
					$imgStor = $user->getImage();
						
					$connPool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
					$conn = $connPool->getConnectionForTable('sys_file_reference');
					$queryBuilder = $conn->createQueryBuilder();
							
					if ($imgStor && count($imgStor->toArray()) > 0) {
						$imgRes = array();
						$imgFile = array();
						for ($i = 0; $i < count($imgStor->toArray()); $i++) {
							$imgRes[$i] = $imgStor->toArray()[$i]->getOriginalResource();
							$imgFile[$i] = $imgRes[$i]->getOriginalFile();
						}
						$crop = $formRuntime->getFormState()->getFormValues()[$kkey."_crop"];	
						
						if (($formRuntime->getFormState()->getFormValues()[$kkey."_delete"]) ||
						($formRuntime->getFormState()->getFormValues()[$kkey."_newimage"] != null)) {
							for ($i = 0; $i < count($imgFile); $i++) {
								$imgFile[$i]->getStorage()->deleteFile($imgFile[$i]);
								
								$refIndex->updateRefIndexTable("sys_file", $imgFile[$i]->getUid());
								$refIndex->updateRefIndexTable("fe_users", $user->getUid());
								$refIndex->updateRefIndexTable("sys_file_reference", $imgRes[$i]->getUid());
							}
							$emptyStor = GeneralUtility::makeInstance(ObjectStorage::class);
							$user->setImage($emptyStor);
						} else {	
							$queryBuilder->update('sys_file_reference')
							->where(
								$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($imgRes[0]->getUid()))
							)->set("crop", $crop)->executeStatement();
							
							$refIndex->updateRefIndexTable("sys_file", $imgFile[0]->getUid());
							$refIndex->updateRefIndexTable("fe_users", $user->getUid());
							$refIndex->updateRefIndexTable("sys_file_reference", $imgRes[0]->getUid());
						}
					}
					if ($formRuntime->getFormState()->getFormValues()[$kkey."_newimage"] != null) {
						//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($colsById[$key]);
                        //						\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($formRuntime->getFormState()->getFormValues()[$kkey."_newimage"]);
						$newfile = $formRuntime->getFormState()->getFormValues()[$kkey."_newimage"];
						if ($newfile instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
							$newfile = $newfile->getOriginalResource();
						}
						// EXT:image_autoresize hat die Signal/Slot-API mit TYPO3 v12 abgelegt.
						// Die frühere Fassung prüfte auf Hooks\FileUploadHook, instanziierte aber
						// Slots\FileUpload — existierte die eine Klasse ohne die andere, war das ein
						// Fatal Error. Deshalb hier auf genau die Klasse prüfen, die auch benutzt wird.
						if (class_exists(\Causal\ImageAutoresize\Slots\FileUpload::class)) {
							$slot = GeneralUtility::makeInstance(\Causal\ImageAutoresize\Slots\FileUpload::class);
							$slot->postFileReplace($newfile->getOriginalFile(), null);
						}

						$folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($colsById[$key]->getImagePathProcessed());

						$filename = $colsById[$key]->getImageFilename() == 1 ? $user->getUsername() : $user->getUid();
						// array_pop() erwartet eine Referenz — der frühere Aufruf
						// array_pop(explode(...)) ist unter PHP 8 ein Fatal Error.
						$pathSegments = explode("/", $newfile->getForLocalProcessing(false));
						$oldfilename = array_pop($pathSegments);
						$parts = explode(".", $oldfilename);
						$ext = "";
						if (count($parts) > 1) {
							$ext = ".".array_pop($parts);
						}
						$finalFile = $folder->getStorage()->moveFile($newfile->getOriginalFile(), $folder, $filename.$ext);
						
						$imgStor = GeneralUtility::makeInstance(ObjectStorage::class);
						$imgRef = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Domain\Model\FileReference::class);
						$imgRefRef = new \TYPO3\CMS\Core\Resource\FileReference(array(
							"pid" => $user->getPid(),
							"tstamp" => time(),
							"crdate" => time(),
							"uid_local" => $finalFile->getUid(),
							"uid_foreign" => $user->getUid(),
							"tablenames" => "fe_users",
							"fieldname" => "image",
							"sorting_foreign" => 1,
							"table_local" => "sys_file",
							"crop" => self::DEFAULT_CROP
						));
						$imgRef->setOriginalResource($imgRefRef);
						$imgStor->attach($imgRef);
						$user->setImage($imgStor);
						
						//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($imgRefRef);
						//die("2");
						
						$imgRef = $user->getImage()->toArray()[0]->getOriginalResource();
						/*$crop = '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":null}}';
						
						$queryBuilder->update('sys_file_reference')
						->where(
							$queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($imgRef->getUid()))
						)->set("crop", $crop)->execute();*/
						
							/*		
						$arrayNewData = array(
							"pid" => $user->getPid(),
							"tstamp" => time(),
							"crdate" => time(),
							"uid_local" => $finalFile->getUid(),
							"uid_foreign" => $user->getUid(),
							"tablenames" => "fe_users",
							"fieldname" => "image",
							"sorting_foreign" => 1,
							"table_local" => "sys_file",
							"crop" => '{"default":{"cropArea":{"x":0,"y":0,"width":1,"height":1},"selectedRatio":"NaN","focusArea":null}}'
						);
						//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($arrayNewData);
						$queryBuilder->insert('sys_file_reference')->values($arrayNewData);
						$queryBuilder->execute();
						
						$newRefId = $conn->lastInsertId("sys_file_reference");*/
						
						$refIndex->updateRefIndexTable("sys_file", $finalFile->getUid());
						$refIndex->updateRefIndexTable("fe_users", $user->getUid());
						$refIndex->updateRefIndexTable("sys_file_reference", $imgRef->getUid());
						
						//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($queryBuilder->getSQL());
                        
					}
					//die();
				}
			}
			if ($this->settings['setLastupdated']) {
				$user->setTxjwfeusermanagerLastupdated(time());
			}
			if ($this->settings['mode'] == 1) {
				$this->userRepository->add($user);
			} else {
				$this->userRepository->update($user);
			}
			
			// Den Vorschlaghammer instanzieren / aus der Kiste kramen
			// Mit dem Vorschlaghammer in die Datenbank speichern / Nägel mit Köpfen machen
			$persistenceManager = $this->persistenceManager;
			$persistenceManager->persistAll();
			
			$clearPages = array_filter(explode(",", $this->settings['clearCachePages']));

			foreach ($clearPages as $page) {
				$this->cacheService->clearPageCache($page);
			}
			
			// Den Vorschlaghammer instanzieren / aus der Kiste kramen
			// Mit dem Vorschlaghammer in die Datenbank speichern / Nägel mit Köpfen machen
			/*$persistenceManager = $this->persistenceManager;
			$persistenceManager->persistAll();*/
			 
			
			//print_r($user->getTxjwfeusermanagerLastupdated());
			//die();
			/*try {
				$this->userRepository->update($user);
				echo "1";
			} catch (Exception $e) {
				echo "2";
				print_r($e->getMessage());
			}
			die("2");*/
		});
		$form->addFinisher($closureFinisher);
		/*$confirmationFinisher = GeneralUtility::makeInstance(\TYPO3\CMS\Form\Domain\Finishers\ConfirmationFinisher::class);
		$confirmationFinisher->setOptions([
			'message' => 'saved',
		]);
		$form->addFinisher($confirmationFinisher);*/
		if ($user != null) {
			$form->createFinisher('Redirect', [
				'pageUid' => $GLOBALS['TSFE']->page['uid'],
				'additionalParameters' => "user=".$user->getUid(),
			]);
		} else {
			$form->createFinisher('Redirect', [
				'pageUid' => $GLOBALS['TSFE']->page['uid'],
			]);
		}

		/*foreach ($form->getRenderablesRecursively() as $renderable) {
			if (method_exists($renderable, "onBuildingFinished")) {
				$renderable->onBuildingFinished();
			}
		}*/
		
		$fr = $form->bind($this->request);
				
		//$this->view->assign("form", $form);

		return "<div class=\"tx-jwfeusermanager-edituser\">".$fr->render()."</div>";
	}

    private function getLanguageService(
        ServerRequestInterface $request,
    ): LanguageService {
        return $this->languageServiceFactory->createFromSiteLanguage(
            $request->getAttribute('language')
            ?? $request->getAttribute('site')->getDefaultLanguage(),
        );
    }
}
