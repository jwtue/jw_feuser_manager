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
	protected $txjwfeusermanagerLastupdated;
    
    /**
     * @var string
     */
	protected $txjwfeusermanagerAdditionalText1;
    
    /**
     * @var string
     */
	protected $txjwfeusermanagerAdditionalText2;
    
    /**
     * @var string
     */
	protected $txjwfeusermanagerAdditionalText3;
    
    /**
     * @var string
     */
	protected $txjwfeusermanagerAdditionalText4;
    
    /**
     * @var string
     */
	protected $txjwfeusermanagerAdditionalText5;
    
    /**
     * @var boolean
     */
	protected $txjwfeusermanagerAdditionalBoolean1;
    
    /**
     * @var boolean
     */
	protected $txjwfeusermanagerAdditionalBoolean2;
    
    /**
     * @var boolean
     */
	protected $txjwfeusermanagerAdditionalBoolean3;
    
    /**
     * @var boolean
     */
	protected $txjwfeusermanagerAdditionalBoolean4;
    
    /**
     * @var boolean
     */
	protected $txjwfeusermanagerAdditionalBoolean5;
    
    /**
     * @var int
     */
	protected $txjwfeusermanagerAdditionalBitfield1;
    
    /**
     * @var int
     */
	protected $txjwfeusermanagerAdditionalBitfield2;
    
    /**
     * @var int
     */
	protected $txjwfeusermanagerAdditionalBitfield3;
    
    /**
     * @var int
     */
	protected $txjwfeusermanagerAdditionalBitfield4;
    
    /**
     * @var int
     */
	protected $txjwfeusermanagerAdditionalBitfield5;
	
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
    public function getTxjwfeusermanagerLastupdated()
    {
        return $this->txjwfeusermanagerLastupdated;
    }

    /**
     * Setter for lastupdated
     *
     * @param int $txjwfeusermanagerLastupdated
     * @return void
     */
    public function setTxjwfeusermanagerLastupdated($txjwfeusermanagerLastupdated)
    {
        $this->txjwfeusermanagerLastupdated = $txjwfeusermanagerLastupdated;
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
    public function getTxjwfeusermanagerAdditionalText1()
    {
        return $this->txjwfeusermanagerAdditionalText1;
    }

    /**
     * Setter for additional text 1
     *
     * @param string $text
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalText1($text)
    {
        $this->txjwfeusermanagerAdditionalText1 = $text;
    }
	
    /**
     * Getter for additional text 2
     *
     * @return string
     */
    public function getTxjwfeusermanagerAdditionalText2()
    {
        return $this->txjwfeusermanagerAdditionalText2;
    }

    /**
     * Setter for additional text 2
     *
     * @param string $text
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalText2($text)
    {
        $this->txjwfeusermanagerAdditionalText2 = $text;
    }
	
    /**
     * Getter for additional text 3
     *
     * @return string
     */
    public function getTxjwfeusermanagerAdditionalText3()
    {
        return $this->txjwfeusermanagerAdditionalText3;
    }

    /**
     * Setter for additional text 3
     *
     * @param string $text
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalText3($text)
    {
        $this->txjwfeusermanagerAdditionalText3 = $text;
    }
	
    /**
     * Getter for additional text 4
     *
     * @return string
     */
    public function getTxjwfeusermanagerAdditionalText4()
    {
        return $this->txjwfeusermanagerAdditionalText4;
    }

    /**
     * Setter for additional text 4
     *
     * @param string $text
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalText4($text)
    {
        $this->txjwfeusermanagerAdditionalText4 = $text;
    }
	
    /**
     * Getter for additional text 5
     *
     * @return string
     */
    public function getTxjwfeusermanagerAdditionalText5()
    {
        return $this->txjwfeusermanagerAdditionalText5;
    }

    /**
     * Setter for additional text 5
     *
     * @param string $text
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalText5($text)
    {
        $this->txjwfeusermanagerAdditionalText5 = $text;
    }
	
    /**
     * Getter for additional boolean 1
     *
     * @return boolean
     */
    public function getTxjwfeusermanagerAdditionalBoolean1()
    {
        return $this->txjwfeusermanagerAdditionalBoolean1;
    }

    /**
     * Setter for additional boolean 1
     *
     * @param boolean $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBoolean1($value)
    {
        $this->txjwfeusermanagerAdditionalBoolean1 = $value;
    }
	
    /**
     * Getter for additional boolean 2
     *
     * @return boolean
     */
    public function getTxjwfeusermanagerAdditionalBoolean2()
    {
        return $this->txjwfeusermanagerAdditionalBoolean2;
    }

    /**
     * Setter for additional boolean 2
     *
     * @param boolean $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBoolean2($value)
    {
        $this->txjwfeusermanagerAdditionalBoolean2 = $value;
    }
	
    /**
     * Getter for additional boolean 3
     *
     * @return boolean
     */
    public function getTxjwfeusermanagerAdditionalBoolean3()
    {
        return $this->txjwfeusermanagerAdditionalBoolean3;
    }

    /**
     * Setter for additional boolean 3
     *
     * @param boolean $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBoolean3($value)
    {
        $this->txjwfeusermanagerAdditionalBoolean3 = $value;
    }
	
    /**
     * Getter for additional boolean 4
     *
     * @return boolean
     */
    public function getTxjwfeusermanagerAdditionalBoolean4()
    {
        return $this->txjwfeusermanagerAdditionalBoolean4;
    }

    /**
     * Setter for additional boolean 4
     *
     * @param boolean $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBoolean4($value)
    {
        $this->txjwfeusermanagerAdditionalBoolean4 = $value;
    }
	
    /**
     * Getter for additional boolean 5
     *
     * @return boolean
     */
    public function getTxjwfeusermanagerAdditionalBoolean5()
    {
        return $this->txjwfeusermanagerAdditionalBoolean5;
    }

    /**
     * Setter for additional boolean 5
     *
     * @param boolean $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBoolean5($value)
    {
        $this->txjwfeusermanagerAdditionalBoolean5 = $value;
    }
	
    /**
     * Getter for additional bitfield 1
     *
     * @return int
     */
    public function getTxjwfeusermanagerAdditionalBitfield1()
    {
        return $this->txjwfeusermanagerAdditionalBitfield1;
    }

    /**
     * Setter for additional bitfield 1
     *
     * @param int $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBitfield1($value)
    {
        $this->txjwfeusermanagerAdditionalBitfield1 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxjwfeusermanagerAdditionalBitfield2()
    {
        return $this->txjwfeusermanagerAdditionalBitfield2;
    }

    /**
     * Setter for additional bitfield 2
     *
     * @param int $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBitfield2($value)
    {
        $this->txjwfeusermanagerAdditionalBitfield2 = $value;
    }
	
    /**
     * Getter for additional bitfield 3
     *
     * @return int
     */
    public function getTxjwfeusermanagerAdditionalBitfield3()
    {
        return $this->txjwfeusermanagerAdditionalBitfield3;
    }

    /**
     * Setter for additional bitfield 3
     *
     * @param int $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBitfield3($value)
    {
        $this->txjwfeusermanagerAdditionalBitfield3 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxjwfeusermanagerAdditionalBitfield4()
    {
        return $this->txjwfeusermanagerAdditionalBitfield4;
    }

    /**
     * Setter for additional bitfield 4
     *
     * @param int $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBitfield4($value)
    {
        $this->txjwfeusermanagerAdditionalBitfield4 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxjwfeusermanagerAdditionalBitfield5()
    {
        return $this->txjwfeusermanagerAdditionalBitfield5;
    }

    /**
     * Setter for additional bitfield 5
     *
     * @param int $value
     * @return void
     */
    public function setTxjwfeusermanagerAdditionalBitfield5($value)
    {
        $this->txjwfeusermanagerAdditionalBitfield5 = $value;
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
