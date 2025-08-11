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
use JwTue\FeUserManager\Utility\Helper;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

/**
 * File controller.
 *
 */
class ShowFeUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
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
		
	
	private function getTyposcriptConfiguration() {
        $conf = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
			$this->request->getControllerExtensionName());
		$svc = new \TYPO3\CMS\Core\TypoScript\TypoScriptService();
		$conf = $svc->convertTypoScriptArrayToPlainArray($conf);
		$conf = $conf['plugin']['tx_'.strtolower($this->request->getControllerExtensionName())];
		return $conf;
	}
	
	private function getFullTyposcriptConfiguration() {
        $conf = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
			$this->request->getControllerExtensionName());
		$svc = new \TYPO3\CMS\Core\TypoScript\TypoScriptService();
		$conf = $svc->convertTypoScriptArrayToPlainArray($conf);
		return $conf;
	}
		
	/**
	 * Allows the widget template root path to be overridden via the framework configuration,
	 * e.g. plugin.tx_extension.view.templateRootPaths
	 *
	 * @param $view
	 * @return void
	 */
	protected function setViewConfiguration($view)
	{
		// Template Path Override
		$extbaseFrameworkConfiguration = $this->getTyposcriptConfiguration();

		// set TemplateRootPaths
		$viewFunctionName = 'setTemplateRootPaths';
		if (method_exists($view, $viewFunctionName)) {
		   $setting = 'templateRootPaths';
		   $parameter = $this->getViewProperty($extbaseFrameworkConfiguration, $setting);
		   // no need to bother if there is nothing to set
		   if ($parameter) {
			   $view->$viewFunctionName($parameter);
		   }
		}

		// set LayoutRootPaths
		$viewFunctionName = 'setLayoutRootPaths';
		if (method_exists($view, $viewFunctionName)) {
		   $setting = 'layoutRootPaths';
		   $parameter = $this->getViewProperty($extbaseFrameworkConfiguration, $setting);
		   // no need to bother if there is nothing to set
		   if ($parameter) {
			   $view->$viewFunctionName($parameter);
		   }
		}

		// set PartialRootPaths
		$viewFunctionName = 'setPartialRootPaths';
		if (method_exists($view, $viewFunctionName)) {
		   $setting = 'partialRootPaths';
		   $parameter = $this->getViewProperty($extbaseFrameworkConfiguration, $setting);
		   // no need to bother if there is nothing to set
		   if ($parameter) {
			   $view->$viewFunctionName($parameter);
		   }
		}
	}

    /**
     * Initializes the view before invoking an action method.
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param $view The view to be initialized
     */
     protected function initializeView($view)
     {
		$view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
		$view->getRequest()->setControllerExtensionName($this->request->getControllerExtensionName());
		$view->getRequest()->setPluginName("ListOfUsers");
		$view->getRequest()->setControllerName("ShowFeUser");
		$view->setFormat('html');
		
		$this->setViewConfiguration($view);
		
		$view->assign("TSFE", $GLOBALS['TSFE']);
		$view->assign("page", $GLOBALS['TSFE']->page);
		
		$conf = $this->getFullTyposcriptConfiguration();
		$view->assign("fluidSettings", $conf['lib']['contentElement']['settings']);
		
		$view->assign("contentObject", $this->configurationManager->getContentObjectRenderer());
	    $this->view->assign("settings", $this->settings);
					
		$this->view = $view;
     }
	
    /**
     * Listing of files.
     *
     * @param int $filter Optional argument to list only users from group with given uid
	 * @param string $download The format to create a download, or null for default list
     * @return void|string
     */
    public function listAction($filter = 0, $download = null) : ResponseInterface {
		 
		//print_r($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']);
		//die();
		
		if (intval($this->request->getQueryParams()['user'])>0) {
			return new ForwardResponse("detail");
		}
		
		$filter = $this->request->getQueryParams()['filter'] ?? 0;
		$download = $this->request->getQueryParams()['download'] ?? null;
		
		$this->view->getRequest()->setControllerActionName("list");
		$this->view->setTemplate("List");

		$columns = explode(",", $this->settings['fields']);
		$csvColumns = explode(",", $this->settings['csvFields']);
		$pdfColumns = explode(",", $this->settings['pdfFields']);
		$csvColumns = array_filter($csvColumns);
		$pdfColumns = array_filter($pdfColumns);
		$columnTitles = array();
		$csvColumnTitles = array();
		$pdfColumnTitles = array();
				
		$this->languageService = $this->getLanguageService($this->request);
		
		$h = new \JwTue\FeUserManager\Utility\Helper();
		$colconfig = $h->getFieldNamesArray();
		
		foreach ($columns as $col) {
			$title = $colconfig[$col];
            if (substr($col, 0, strlen("tx_jwfeusermanager_additional_")) == "tx_jwfeusermanager_additional_") {
                $what = substr($col, strlen("tx_jwfeusermanager_additional_"));
                $what = str_replace("_", "", $what);
                if (!empty(trim($this->settings['additional_'.$what.'_title']))) {
                    $title = trim($this->settings['additional_'.$what.'_title']);
                }                
            }
			$columnTitles[] = $title;
		}
		foreach ($csvColumns as $col) {
			$title = $colconfig[$col];
            if (substr($col, 0, strlen("tx_jwfeusermanager_additional_")) == "tx_jwfeusermanager_additional_") {
                $what = substr($col, strlen("tx_jwfeusermanager_additional_"));
                $what = str_replace("_", "", $what);
                if (!empty(trim($this->settings['additional_'.$what.'_title']))) {
                    $title = trim($this->settings['additional_'.$what.'_title']);
                }                
            }
			$csvColumnTitles[] = $title;
		}
		foreach ($pdfColumns as $col) {
			$title = $colconfig[$col];
            if (substr($col, 0, strlen("tx_jwfeusermanager_additional_")) == "tx_jwfeusermanager_additional_") {
                $what = substr($col, strlen("tx_jwfeusermanager_additional_"));
                $what = str_replace("_", "", $what);
                if (!empty(trim($this->settings['additional_'.$what.'_title']))) {
                    $title = trim($this->settings['additional_'.$what.'_title']);
                }                
            }
			$pdfColumnTitles[] = $title;
		}
		
		$columns = array_map("\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase", $columns);
		$csvColumns = array_map("\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase", $csvColumns);
		$pdfColumns = array_map("\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase", $pdfColumns);
		
		$this->view->assign("columns", $columns);
		$this->view->assign("columnTitles", $columnTitles);		
		
		$usersOrig = $this->userRepository->findAll()->toArray();
					
		$usersUnfiltered = array();
		$showgroups = explode(",", $this->settings['groups']);
		if (empty($this->settings['groups'])) {
			$showgroups = array();
		}
		
		$groups = array();		 
		   
		foreach ($usersOrig as $u) {
		   $u->addArtificialFields();
		   
		   $or = false;
		   $and = true;
		   $groupids = array();
		   foreach ($u->getUsergroup()->toArray() as $g) {
			   $groupids[] = $g->getUid();
			   $groups[$g->getUid()] = $g->getTitle();
		   }
		   foreach ($showgroups as $g) {
			   if (in_array($g, $groupids)) {
				   $or = true;
			   } else {
				   $and = false;
			   }
		   }
		   
		   if ($this->settings['groupConjunction'] == "or") {
				if ($or) {
					$usersUnfiltered[] = $u;
				}
		   } else if ($this->settings['groupConjunction'] == "notor") {
				if (!$or) {
					$usersUnfiltered[] = $u;
				}				
		   } else if ($this->settings['groupConjunction'] == "and") {
				if ($and) {
					$usersUnfiltered[] = $u;
				}				
		   } else if ($this->settings['groupConjunction'] == "notand") {
				if (!$and) {
					$usersUnfiltered[] = $u;
				}				
		   } else {
				$usersUnfiltered[] = $u;
		   }
		}
		
		asort($groups);
		
		$groupFiltersShow = array_filter(explode(",", $this->settings['groupFilteringSelect']));
				
		$groupsFilter = array();
		foreach ($groupFiltersShow as $v) {
			$g = $this->userGroupRepository->findByUid($v);
			$groupsFilter[$v] = $g->getTitle();
		}
		asort($groupsFilter);
		$groupsFilter = array(0 => "Alle") + $groupsFilter;
				
		$this->view->assign("userGroups", $groupsFilter);
		
		$this->settings['orderBy'] = explode(",", $this->settings['orderBy']);
				
		$this->view->assign("settings", $this->settings);
		usort($usersUnfiltered, (function ($ob) {
		   return function ($a, $b) use ($ob) {
			   $cmp = 0;
			   foreach ($ob as $order) {
				   $order = "get".ucfirst($order);
				   $aa = $a->$order();
				   if (!is_string($aa)) $aa = "";
				   $aa = str_replace("ä", "ae", $aa);
				   $aa = str_replace("ö", "oe", $aa);
				   $aa = str_replace("ü", "ue", $aa);
				   $aa = str_replace("Ä", "Ae", $aa);
				   $aa = str_replace("Ö", "Oe", $aa);
				   $aa = str_replace("Ü", "Ue", $aa);
				   $aa = str_replace("ß", "ss", $aa);
				   $bb = $b->$order();
				   if (!is_string($bb)) $bb = "";
				   $bb = str_replace("ä", "ae", $bb);
				   $bb = str_replace("ö", "oe", $bb);
				   $bb = str_replace("ü", "ue", $bb);
				   $bb = str_replace("Ä", "Ae", $bb);
				   $bb = str_replace("Ö", "Oe", $bb);
				   $bb = str_replace("Ü", "Ue", $bb);
				   $bb = str_replace("ß", "ss", $bb);
				   $cmp = strcasecmp($aa, $bb);
				   if ($cmp != 0) return $cmp;
			   }
			   return $cmp;
		   };
		})($columns));
		
		$users = array();
		if ($filter == 0) {
			$users = $usersUnfiltered;
		} else {
			foreach ($usersUnfiltered as $u) {		
			   foreach ($u->getUsergroup()->toArray() as $g) {
				   if ($g->getUid() == $filter) {
						$users[] = $u;
						break 1;
				   }
			   }
			}
		}		
		
		$this->view->assign("fullUsers", $users);

		$extendedUsers = array();
		
		foreach ($users as $u) {
			$extendedUsers[] = $u->getFields($columns);
		}
		$this->view->assign("users", $extendedUsers);
		
		$this->view->assign("filter", $filter);
		
		$title = $this->settings['pdfTitle'];
		$subtitle = null;
		$filename = $this->settings['downloadFilename'];
		
		if ($filter != 0) {
			$filename .= " - ".$groups[$filter]." - ".date("Y-m-d");
			if ($this->settings['useGroupTitle']) {
				$subtitle = $groups[$filter];
			}
		} else {
			$filename .= " - ".date("Y-m-d");
		}
		
		if ($download == "csv") {
			$extendedUsers = array();
			foreach ($users as $u) {
				$extendedUsers[] = $u->getFields($csvColumns);
			}
			
			//header("Content-type: text/plain; charset=utf-8");
			header("Content-type: text/csv; charset=utf-8");
			header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($filename.".csv"));
			$skip = array();
			foreach ($csvColumns as $k => $c) {
				if ($c == "image") {
					unset($csvColumnTitles[$k]);
					$skip[] = $k;
				}
			}
			echo implode(",", $csvColumnTitles);
			foreach ($extendedUsers as $u) {
				$uu = array();
				foreach ($csvColumns as $k => $c) {
					if (!in_array($k, $skip)) {
						$value = $u[$c];
						if (strpos($value, ",") !== false || strpos($value, " ") !== false) {
							$value = "\"".str_replace("\"", "\"\"", $value)."\"";
						} else if ($c == "txJwfrontendusermanagerLastupdated" || $c == "lastlogin") {
							$value = date("c", $value);
						} else if ($c == "dateOfBirth") {
							$value = substr(date("c", $value), 0, 10);
						} else if ($c == "mobilephone" || $c == "phone" || $c == "phoneBusiness") {
							$value = \JwTue\FeUserManager\ViewHelper\Format\PhoneViewHelper::formatPhoneNumber($value, true);
						} else if (substr($c, 0, strlen("txJwfrontendusermanagerAdditionalBitfield")) == "txJwfrontendusermanagerAdditionalBitfield") {
                            $number = substr($c, strlen("txJwfrontendusermanagerAdditionalBitfield"));
                            $entrynames = explode("\n", $this->settings['additional_bitfield'.$number.'_entries']);
                            $entrynames = array_filter($entrynames, function($i) use ($value) { return (($value >> ($i+1)) & 1) != 0;}, ARRAY_FILTER_USE_KEY);
                            $value = "\"".implode(",", $entrynames)."\"";
                        }
						$uu[] = $value;
					}
				}
				echo "\n".implode(",", $uu);
			}
			die();
		} else if ($download == "pdf") {
						
			$skip = array();
			
			$columnwidth = array();			
			$skip = array();
			
			$marginTop = 10;
			$marginSide = 10;
			if ($this->settings['pdfOrientation'] == "P") {
				$marginTop *= 2;
			} else {
				$marginSide *= 2;
			}
			
			$pdf = new \TCPDF($this->settings['pdfOrientation'],"mm","A4");
			$pdf->setTitle($title);
			if ($subtitle != null) {
				$pdf->setTitle($title." ".json_decode('"\\u2013"')." ".$subtitle);
			}
			$pdf->SetPrintHeader(false);
			$pdf->SetPrintFooter(false);
			$pdf->SetMargins($marginSide, $marginTop, $marginSide);
			$pdf->SetAutoPageBreak(true, $marginTop);
			$pdf->AddPage();
			
			$height = 0;
			$ugroup = $this->userGroupRepository->findByUid($filter);
			if ($this->settings['useGroupLogo'] && $ugroup != null && $ugroup->getImage() != null && $ugroup->getImage()->count()) {
				$resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');        
                $fileReference = $ugroup->getImage()->toArray()[0]->getOriginalResource();
				
				$height = 20;
				if ($fileReference->getMimeType() == "image/svg+xml") {
					$svgfile = simplexml_load_file($fileReference->getForLocalProcessing(false));
					$viewbox = explode(" ", $svgfile['viewBox']);
					$size = array(
						$viewbox[2]-$viewbox[0],
						$viewbox[3]-$viewbox[1]
						);
					$width = $height*$size[0]/$size[1];
					// $pdf->setRasterizeVectorImages(true);
					$pdf->ImageSVG($fileReference->getForLocalProcessing(false), $pdf->getPageWidth()-$marginSide-$width, $marginTop, $width, $height);
				} else {
					$size = getimagesize($fileReference->getForLocalProcessing(false));
					$width = $height*$size[0]/$size[1];
					$pdf->Image($fileReference->getForLocalProcessing(false), $pdf->getPageWidth()-$marginSide-$width, $marginTop, $width, $height);
				}
				$height += 2;
			} else if (!empty($this->settings['pdfLogo'])) {
				$resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');        
                $fileReference = $resourceFactory->getFileReferenceObject($this->settings['pdfLogo']);
				
				$height = 20;
				if ($fileReference->getMimeType() == "image/svg+xml") {
					$svgfile = simplexml_load_file($fileReference->getForLocalProcessing(false));
					$viewbox = explode(" ", $svgfile['viewBox']);
					$size = array(
						$viewbox[2]-$viewbox[0],
						$viewbox[3]-$viewbox[1]
						);
					$width = $height*$size[0]/$size[1];
					// $pdf->setRasterizeVectorImages(true);
					$pdf->ImageSVG($fileReference->getForLocalProcessing(false), $pdf->getPageWidth()-$marginSide-$width, $marginTop, $width, $height);
				} else {
					$size = getimagesize($fileReference->getForLocalProcessing(false));
					$width = $height*$size[0]/$size[1];
					$pdf->Image($fileReference->getForLocalProcessing(false), $pdf->getPageWidth()-$marginSide-$width, $marginTop, $width, $height);
				}
				$height += 2;
			}
			
			$fontsize = $this->settings['pdfFontSize'];
			
			if ($subtitle != null) {
				$pdf->SetFont("Helvetica","B",$fontsize*2);
				$pdf->Cell(0, 0.6*$fontsize*2, $title);
				$height -= (0.6*$fontsize*2);
				$pdf->Ln();
				
				$pdf->SetFont("Helvetica","B",$fontsize*1.4);
				
				$pdf->Cell(0, 0.6*$fontsize*1.4, $subtitle);
				$pdf->Ln();
				$height -= (0.6*$fontsize*1.4);
					
				if ($height != 0) {
					$pdf->SetY($pdf->getY() + $height);
				}
			} else {
				$pdf->SetFont("Helvetica","B",$fontsize*2);
				if ($height != 0) {
					$pdf->SetY( $pdf->getY() + (($height-(0.6*$fontsize*2))/2));
				}
				$pdf->Cell(0, 0.6*$fontsize*2, $title);
				$pdf->Ln();
				if ($height != 0) {
					$pdf->SetY($pdf->getY() + (($height-(0.6*$fontsize*2))/2));
				}
			}
						
			foreach ($pdfColumns as $k => $c) {
				if ($c == "image") {
					$skip[] = $c;
				}
				$pdfColumnTitles[$c] = $pdfColumnTitles[$k];
				unset($pdfColumnTitles[$k]);
				$columnwidth[$c] = 0;
			}
									
			$pdf->SetFont("Helvetica","",$fontsize);
			
			foreach ($extendedUsers as $i => $u) {
				foreach ($pdfColumns as $k => $c) {
					if (in_array($c, $skip)) continue;
					if ($c == "telephone" || $c == "mobilephone" || $c == "phoneBusiness" ) {
						$u[$c] = \JwTue\FeUserManager\ViewHelper\Format\PhoneViewHelper::formatPhoneNumber($u[$c]);
						$extendedUsers[$i] = $u;
					}
					$value = $u[$c];
					$width = $pdf->getStringWidth($value);
					$columnwidth[$c] = max($columnwidth[$c], $width);
				}
			}
									
			$pdf->SetFont("Helvetica","B",$fontsize);
			
			foreach ($pdfColumnTitles as $k => $t) {
				if (in_array($k, $skip)) continue;
				if ($columnwidth[$k] == 0) continue;
				$width = $pdf->getStringWidth($t);
				$columnwidth[$k] = max($columnwidth[$k], $width);
			}
			
			$columnsum = array_sum($columnwidth);
			
			$pdf->SetFont("Helvetica","B",$fontsize);
			
			foreach ($pdfColumns as $k => $c) {
				if ($columnwidth[$c] == 0) continue;
				$widthfactor = $columnwidth[$c]/$columnsum;
				$pdf->Cell($widthfactor*($pdf->GetPageWidth()-($marginSide*2)),0.6*$fontsize,$pdfColumnTitles[$c],"TB");
			}
			$pdf->SetFont("Helvetica","",$fontsize);
			
			$pdf->SetFillColor(220);
				
			foreach ($extendedUsers as $i => $u) {
				$pdf->Ln();
				
				foreach ($pdfColumns as $k => $c) {
					$value = $u[$c];
					if ($columnwidth[$c] == 0) continue 1;
					$widthfactor = $columnwidth[$c]/$columnsum;
					$pdf->Cell($widthfactor*($pdf->GetPageWidth()-($marginSide*2)),0.6*$fontsize,$value,($i == 0 ? "T":""),0,"L",($i % 2 == 0));
				}
			}
			
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Cell(0, 7, $this->languageService->sL("LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang.xlf:listofusers_pdf.effective")." ".
			date($this->languageService->sL("LLL:EXT:jw_feuser_manager/Resources/Private/Language/locallang.xlf:listofusers_pdf.effective.format")), "", 0, "R");
						
			$pdf->Output($filename.'.pdf', "I");	
						
			die();
		} else if ($download == "email") {
			header("Content-type: text/plain;charset=utf-8");
						
			$rcpt = array();
			foreach ($users as $u) {
				$rcpt[] = "\"".$u->getLastnameFirstname()."\" <".$u->getEmail().">";
			}
			echo implode(", ", $rcpt);
			
			die();
		} else {
			foreach ($extendedUsers as $k => $uu) {
                for ($number = 1; $number <= 5; $number++) {
                    if (!empty($this->settings['additional_bitfield'.$number.'_entries']) && array_key_exists("txJwfrontendusermanagerAdditionalBitfield".$number, $uu)) {
                        $entrynames = explode("\n", $this->settings['additional_bitfield'.$number.'_entries']);
                        $entrynames = array_filter($entrynames, 
							function($i) use ($uu, $number) { return (($uu["txJwfrontendusermanagerAdditionalBitfield".$number] >> ($i+1)) & 1) != 0;},
							ARRAY_FILTER_USE_KEY);
                        $extendedUsers[$k]["txJwfrontendusermanagerAdditionalBitfield".$number] = array_values($entrynames);
                    }
                }
            }        
        }
        $this->view->assign("users", $extendedUsers);

		return $this->view->render();
	}
	
    /**
     * Listing of files.
     *
     * @param int $user Optional argument to list only users from group with given uid
     * @return void|string
     */
	public function detailAction($user = 0) {
		
		$user = $this->request->getQueryParams()['user'] ?? 0;
		
		$download = $this->request->getQueryParams()['download'] ?? null;
		
		$this->view->getRequest()->setControllerActionName("detail");
		$this->view->setTemplate("ShowFeUserDetail");
		
		$usr = $this->userRepository->findByUid($user);
		$usr->addArtificialFields();
		
		$this->view->assign("user", $usr);
		$this->view->assign("fullUser", $usr->getFields());
				
		$groups = array();
		foreach ($usr->getUsergroup()->toArray() as $g) {
			$groups[$g->getUid()] = $g->getTitle();
		}
		asort($groups);
		$this->view->assign("groups", $groups);
		
		if ($download == "vcf") {
			
			$vcf_folder = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
				\TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($this->request->getControllerExtensionName())
				) . 'Resources/Private/Library/vcard/';
			require_once($vcf_folder.'vCard.class.php');
			
			$vcard = new \vCard();
			
			$displayname = $usr->getName();
			if (empty($displayname)) $displayname = $usr->getFirstName()." ".$usr->getLastName();
			
			$data = array(
				'display_name' => $usr->getName(),
				'first_name' => $usr->getFirstName(),
				'last_name' => $usr->getLastName(),
			);
			
			$filename = str_replace(" ", "-", $usr->getFirstName()." ".$usr->getLastName());
			
			
			$columns = explode(",", $this->settings['fields']);
						
			$mapping = array(
				"telephone" => "home_tel",
				"email" => "email1",
				"phone_business" => "office_tel",
				"mobilephone" => "cell_tel",
				"date_of_birth" => "birthday",
				"address" => "home_address",
				"full_address" => "home_address",
				"fax" => "fax_tel",
				"title" => "title",
				"zip" => "home_postal_code",
				"city" => "home_city",
				"country" => "home_country",
                "company" => "company",
			);
			
			foreach ($columns as $col) {
				if ($col == "image") {					
					$photos = $usr->getImage() ? $usr->getImage()->toArray() : array();
					if (!empty($photos)) {
						$img = imagecreatefromstring(file_get_contents($photos[0]->getOriginalResource()->getForLocalProcessing(false)));
						$crop = json_decode($photos[0]->getOriginalResource()->getProperty("crop"), true);
						
						if (is_array($crop) && isset($crop["default"]) && isset($crop["default"]["cropArea"])) {
							$crop = $crop["default"]["cropArea"];
						} else {
							$crop = array(
								"height" => 1,
								"width" => 1,
								"x" => 0,
								"y" => 0
							);
						}
						
						$newImg = imagecreatetruecolor($crop["width"]*imagesx($img), $crop["height"]*imagesy($img));
						imagecopy($newImg, $img, 0, 0, $crop["x"]*imagesx($img), $crop["y"]*imagesy($img), $crop["width"]*imagesx($img), $crop["height"]*imagesy($img));
						
						ob_start();
						imagejpeg($newImg);
						$imageString = ob_get_clean();
						
						$data["photo"] = base64_encode($imageString);
					}
				} else if ($col == "date_of_birth") {
					$data["birthday"] = date("Y-m-d", $usr->getDateOfBirth());
				} else if (isset($mapping[$col])) {
					$getter = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($col);
					$getter = "get".ucfirst($getter);
					$data[$mapping[$col]] = $usr->$getter();
					if ($col == "telephone" || $col == "phone_business" || $col == "mobilephone" || $col == "fax") {
						$data[$mapping[$col]] = \JwTue\FeUserManager\ViewHelper\Format\PhoneViewHelper::formatPhoneNumber($data[$mapping[$col]], true);
					}
				}
			}
			$vcard->set("data", $data);
			$vcard->set("filename", $filename);
			
			$vcard->download();
			die();
		}
		
		return $this->view->render();
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
