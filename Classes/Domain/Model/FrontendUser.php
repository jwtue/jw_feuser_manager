<?php
namespace JwTue\FeUserManager\Domain\Model;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * An extended frontend user with more attributes
 * @package JwTue\FeUserManager\Domain\Model
 */
class FrontendUser extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    private $user_table = "fe_user";
    private $userid_column = "uid";

	public $passwordBuffer = null;
	
    /**
     * Number of mobilephone
     *
     * @var string
     */
    protected $mobilephone;

    /**
     * Number of business phone
     *
     * @var string
     */
    protected $phoneBusiness;
		
    /**
     * Date of birth
     *
     * @var int
     */
    protected $dateOfBirth;
	
    /**
     * @var string
     */
	protected $firstnameLastname;
    /**
     * @var string
     */
	protected $lastnameFirstname;
    /**
     * @var string
     */
	protected $fullAddress;
    /**
     * @var int
     */	
	protected $txJwfrontendusermanagerLastupdated;
    
    /**
     * @var string
     */
	protected $txJwfrontendusermanagerAdditionalText1;
    
    /**
     * @var string
     */
	protected $txJwfrontendusermanagerAdditionalText2;
    
    /**
     * @var string
     */
	protected $txJwfrontendusermanagerAdditionalText3;
    
    /**
     * @var string
     */
	protected $txJwfrontendusermanagerAdditionalText4;
    
    /**
     * @var string
     */
	protected $txJwfrontendusermanagerAdditionalText5;
    
    /**
     * @var boolean
     */
	protected $txJwfrontendusermanagerAdditionalBoolean1;
    
    /**
     * @var boolean
     */
	protected $txJwfrontendusermanagerAdditionalBoolean2;
    
    /**
     * @var boolean
     */
	protected $txJwfrontendusermanagerAdditionalBoolean3;
    
    /**
     * @var boolean
     */
	protected $txJwfrontendusermanagerAdditionalBoolean4;
    
    /**
     * @var boolean
     */
	protected $txJwfrontendusermanagerAdditionalBoolean5;
    
    /**
     * @var int
     */
	protected $txJwfrontendusermanagerAdditionalBitfield1;
    
    /**
     * @var int
     */
	protected $txJwfrontendusermanagerAdditionalBitfield2;
    
    /**
     * @var int
     */
	protected $txJwfrontendusermanagerAdditionalBitfield3;
    
    /**
     * @var int
     */
	protected $txJwfrontendusermanagerAdditionalBitfield4;
    
    /**
     * @var int
     */
	protected $txJwfrontendusermanagerAdditionalBitfield5;
	
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $imageFal = null;

    /**
     * Sets the image value
     *
     * @api
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $imageFal
     */
    public function setImageFal(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $imageFal)
    {
        $this->imageFal = $imageFal;
    }

    /**
     * Gets the image value
     *
     * @api
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    public function getImageFal()
    {
        return $this->imageFal;
    }

    /**
     * @return string
     */
	public function getFirstnameLastname() {
		return $this->firstnameLastname;
	}
    /**
     * @return string
     */
	public function getLastnameFirstname() {
		return $this->lastnameFirstname;
	}
    /**
     * @return string
     */
	public function getFullAddress() {
		return $this->fullAddress;
	}
	
	public function addArtificialFields() {
		$this->firstnameLastname = $this->firstName." ".$this->lastName;
		$this->lastnameFirstname = $this->lastName.", ".$this->firstName;
		$this->fullAddress = trim($this->address."\n".$this->zip." ".$this->city);
	}
	
    /**
     * Getter for mobilphone
     *
     * @return string
     */
    public function getMobilephone()
    {
        return $this->mobilephone;
    }

    /**
     * Setter for mobilphone
     *
     * @param string $mobilephone
     * @return void
     */
    public function setMobilephone($mobilephone)
    {
        $this->mobilephone = $mobilephone;
    }
	
    /**
     * Getter for date of birth
     *
     * @return int
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Setter for date of birth
     *
     * @param int $dateOfBirth
     * @return void
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }
	
    /**
     * Getter for lastupdated
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerLastupdated()
    {
        return $this->txJwfrontendusermanagerLastupdated;
    }

    /**
     * Setter for lastupdated
     *
     * @param int $txJwfrontendusermanagerLastupdated
     * @return void
     */
    public function setTxJwfrontendusermanagerLastupdated($txJwfrontendusermanagerLastupdated)
    {
        $this->txJwfrontendusermanagerLastupdated = $txJwfrontendusermanagerLastupdated;
    }
	
    /**
     * Getter for business phone
     *
     * @return string
     */
    public function getPhoneBusiness()
    {
        return $this->phoneBusiness;
    }

    /**
     * Setter for business phone
     *
     * @param string $phoneBusiness
     * @return void
     */
    public function setPhoneBusiness($phoneBusiness)
    {
        $this->phoneBusiness = $phoneBusiness;
    }
	
    /**
     * Getter for additional text 1
     *
     * @return string
     */
    public function getTxJwfrontendusermanagerAdditionalText1()
    {
        return $this->txJwfrontendusermanagerAdditionalText1;
    }

    /**
     * Setter for additional text 1
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalText1($text)
    {
        $this->txJwfrontendusermanagerAdditionalText1 = $text;
    }
	
    /**
     * Getter for additional text 2
     *
     * @return string
     */
    public function getTxJwfrontendusermanagerAdditionalText2()
    {
        return $this->txJwfrontendusermanagerAdditionalText2;
    }

    /**
     * Setter for additional text 2
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalText2($text)
    {
        $this->txJwfrontendusermanagerAdditionalText2 = $text;
    }
	
    /**
     * Getter for additional text 3
     *
     * @return string
     */
    public function getTxJwfrontendusermanagerAdditionalText3()
    {
        return $this->txJwfrontendusermanagerAdditionalText3;
    }

    /**
     * Setter for additional text 3
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalText3($text)
    {
        $this->txJwfrontendusermanagerAdditionalText3 = $text;
    }
	
    /**
     * Getter for additional text 4
     *
     * @return string
     */
    public function getTxJwfrontendusermanagerAdditionalText4()
    {
        return $this->txJwfrontendusermanagerAdditionalText4;
    }

    /**
     * Setter for additional text 4
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalText4($text)
    {
        $this->txJwfrontendusermanagerAdditionalText4 = $text;
    }
	
    /**
     * Getter for additional text 5
     *
     * @return string
     */
    public function getTxJwfrontendusermanagerAdditionalText5()
    {
        return $this->txJwfrontendusermanagerAdditionalText5;
    }

    /**
     * Setter for additional text 5
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalText5($text)
    {
        $this->txJwfrontendusermanagerAdditionalText5 = $text;
    }
	
    /**
     * Getter for additional boolean 1
     *
     * @return boolean
     */
    public function getTxJwfrontendusermanagerAdditionalBoolean1()
    {
        return $this->txJwfrontendusermanagerAdditionalBoolean1;
    }

    /**
     * Setter for additional boolean 1
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBoolean1($value)
    {
        $this->txJwfrontendusermanagerAdditionalBoolean1 = $value;
    }
	
    /**
     * Getter for additional boolean 2
     *
     * @return boolean
     */
    public function getTxJwfrontendusermanagerAdditionalBoolean2()
    {
        return $this->txJwfrontendusermanagerAdditionalBoolean2;
    }

    /**
     * Setter for additional boolean 2
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBoolean2($value)
    {
        $this->txJwfrontendusermanagerAdditionalBoolean2 = $value;
    }
	
    /**
     * Getter for additional boolean 3
     *
     * @return boolean
     */
    public function getTxJwfrontendusermanagerAdditionalBoolean3()
    {
        return $this->txJwfrontendusermanagerAdditionalBoolean3;
    }

    /**
     * Setter for additional boolean 3
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBoolean3($value)
    {
        $this->txJwfrontendusermanagerAdditionalBoolean3 = $value;
    }
	
    /**
     * Getter for additional boolean 4
     *
     * @return boolean
     */
    public function getTxJwfrontendusermanagerAdditionalBoolean4()
    {
        return $this->txJwfrontendusermanagerAdditionalBoolean4;
    }

    /**
     * Setter for additional boolean 4
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBoolean4($value)
    {
        $this->txJwfrontendusermanagerAdditionalBoolean4 = $value;
    }
	
    /**
     * Getter for additional boolean 5
     *
     * @return boolean
     */
    public function getTxJwfrontendusermanagerAdditionalBoolean5()
    {
        return $this->txJwfrontendusermanagerAdditionalBoolean5;
    }

    /**
     * Setter for additional boolean 5
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBoolean5($value)
    {
        $this->txJwfrontendusermanagerAdditionalBoolean5 = $value;
    }
	
    /**
     * Getter for additional bitfield 1
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerAdditionalBitfield1()
    {
        return $this->txJwfrontendusermanagerAdditionalBitfield1;
    }

    /**
     * Setter for additional bitfield 1
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBitfield1($value)
    {
        $this->txJwfrontendusermanagerAdditionalBitfield1 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerAdditionalBitfield2()
    {
        return $this->txJwfrontendusermanagerAdditionalBitfield2;
    }

    /**
     * Setter for additional bitfield 2
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBitfield2($value)
    {
        $this->txJwfrontendusermanagerAdditionalBitfield2 = $value;
    }
	
    /**
     * Getter for additional bitfield 3
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerAdditionalBitfield3()
    {
        return $this->txJwfrontendusermanagerAdditionalBitfield3;
    }

    /**
     * Setter for additional bitfield 3
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBitfield3($value)
    {
        $this->txJwfrontendusermanagerAdditionalBitfield3 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerAdditionalBitfield4()
    {
        return $this->txJwfrontendusermanagerAdditionalBitfield4;
    }

    /**
     * Setter for additional bitfield 4
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBitfield4($value)
    {
        $this->txJwfrontendusermanagerAdditionalBitfield4 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfrontendusermanagerAdditionalBitfield5()
    {
        return $this->txJwfrontendusermanagerAdditionalBitfield5;
    }

    /**
     * Setter for additional bitfield 5
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfrontendusermanagerAdditionalBitfield5($value)
    {
        $this->txJwfrontendusermanagerAdditionalBitfield5 = $value;
    }
	
	public function getFields($filter = null)
	{
		$output = array();
		foreach (get_object_vars($this) as $k => $v) {
			if (!isset($output[$k]) && ($filter == null || in_array($k, $filter))) {
				$output[$k] = $v;
			}
		}
		
        $query = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->user_table);
        $query->select("*")->from($this->user_table)->where($query->expr()->eq($this->userid_column, $query->createNamedParameter($this->uid, Connection::PARAM_INT)));
		$row = $query->executeQuery()->fetchAssociative();
		foreach ($row as $k => $v) {
			$k = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($k);
			if ($filter == null || in_array($k, $filter)) {
				$output[$k] = $v;
			}
		}
		
		return $output;
	}

	public static function getFieldNames() {
		$output = array();
		/*$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('`COLUMN_NAME`', '`INFORMATION_SCHEMA`.`COLUMNS`', "`TABLE_NAME`='fe_users'");
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
			$output[] = $row[0];
		}*/
		return $output;
	}
}
