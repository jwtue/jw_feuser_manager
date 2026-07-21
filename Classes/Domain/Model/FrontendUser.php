<?php
namespace JwTue\FeUserManager\Domain\Model;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An extended frontend user with more attributes
 * @package JwTue\FeUserManager\Domain\Model
 */
class FrontendUser extends AbstractFrontendUser
{

    // The table is called "fe_users" (plural). During the port this said "fe_user", which
    // would have triggered a Doctrine exception at runtime; v11 used the literal "fe_users".
    private $user_table = "fe_users";
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
	protected $txJwfeusermanagerLastupdated;
    
    /**
     * @var string
     */
	protected $txJwfeusermanagerAdditionalText1;
    
    /**
     * @var string
     */
	protected $txJwfeusermanagerAdditionalText2;
    
    /**
     * @var string
     */
	protected $txJwfeusermanagerAdditionalText3;
    
    /**
     * @var string
     */
	protected $txJwfeusermanagerAdditionalText4;
    
    /**
     * @var string
     */
	protected $txJwfeusermanagerAdditionalText5;
    
    /**
     * @var boolean
     */
	protected $txJwfeusermanagerAdditionalBoolean1;
    
    /**
     * @var boolean
     */
	protected $txJwfeusermanagerAdditionalBoolean2;
    
    /**
     * @var boolean
     */
	protected $txJwfeusermanagerAdditionalBoolean3;
    
    /**
     * @var boolean
     */
	protected $txJwfeusermanagerAdditionalBoolean4;
    
    /**
     * @var boolean
     */
	protected $txJwfeusermanagerAdditionalBoolean5;
    
    /**
     * @var int
     */
	protected $txJwfeusermanagerAdditionalBitfield1;
    
    /**
     * @var int
     */
	protected $txJwfeusermanagerAdditionalBitfield2;
    
    /**
     * @var int
     */
	protected $txJwfeusermanagerAdditionalBitfield3;
    
    /**
     * @var int
     */
	protected $txJwfeusermanagerAdditionalBitfield4;
    
    /**
     * @var int
     */
	protected $txJwfeusermanagerAdditionalBitfield5;
	
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
    public function getTxJwfeusermanagerLastupdated()
    {
        return $this->txJwfeusermanagerLastupdated;
    }

    /**
     * Setter for lastupdated
     *
     * @param int $txJwfeusermanagerLastupdated
     * @return void
     */
    public function setTxJwfeusermanagerLastupdated($txJwfeusermanagerLastupdated)
    {
        $this->txJwfeusermanagerLastupdated = $txJwfeusermanagerLastupdated;
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
    public function getTxJwfeusermanagerAdditionalText1()
    {
        return $this->txJwfeusermanagerAdditionalText1;
    }

    /**
     * Setter for additional text 1
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalText1($text)
    {
        $this->txJwfeusermanagerAdditionalText1 = $text;
    }
	
    /**
     * Getter for additional text 2
     *
     * @return string
     */
    public function getTxJwfeusermanagerAdditionalText2()
    {
        return $this->txJwfeusermanagerAdditionalText2;
    }

    /**
     * Setter for additional text 2
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalText2($text)
    {
        $this->txJwfeusermanagerAdditionalText2 = $text;
    }
	
    /**
     * Getter for additional text 3
     *
     * @return string
     */
    public function getTxJwfeusermanagerAdditionalText3()
    {
        return $this->txJwfeusermanagerAdditionalText3;
    }

    /**
     * Setter for additional text 3
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalText3($text)
    {
        $this->txJwfeusermanagerAdditionalText3 = $text;
    }
	
    /**
     * Getter for additional text 4
     *
     * @return string
     */
    public function getTxJwfeusermanagerAdditionalText4()
    {
        return $this->txJwfeusermanagerAdditionalText4;
    }

    /**
     * Setter for additional text 4
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalText4($text)
    {
        $this->txJwfeusermanagerAdditionalText4 = $text;
    }
	
    /**
     * Getter for additional text 5
     *
     * @return string
     */
    public function getTxJwfeusermanagerAdditionalText5()
    {
        return $this->txJwfeusermanagerAdditionalText5;
    }

    /**
     * Setter for additional text 5
     *
     * @param string $text
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalText5($text)
    {
        $this->txJwfeusermanagerAdditionalText5 = $text;
    }
	
    /**
     * Getter for additional boolean 1
     *
     * @return boolean
     */
    public function getTxJwfeusermanagerAdditionalBoolean1()
    {
        return $this->txJwfeusermanagerAdditionalBoolean1;
    }

    /**
     * Setter for additional boolean 1
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBoolean1($value)
    {
        $this->txJwfeusermanagerAdditionalBoolean1 = $value;
    }
	
    /**
     * Getter for additional boolean 2
     *
     * @return boolean
     */
    public function getTxJwfeusermanagerAdditionalBoolean2()
    {
        return $this->txJwfeusermanagerAdditionalBoolean2;
    }

    /**
     * Setter for additional boolean 2
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBoolean2($value)
    {
        $this->txJwfeusermanagerAdditionalBoolean2 = $value;
    }
	
    /**
     * Getter for additional boolean 3
     *
     * @return boolean
     */
    public function getTxJwfeusermanagerAdditionalBoolean3()
    {
        return $this->txJwfeusermanagerAdditionalBoolean3;
    }

    /**
     * Setter for additional boolean 3
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBoolean3($value)
    {
        $this->txJwfeusermanagerAdditionalBoolean3 = $value;
    }
	
    /**
     * Getter for additional boolean 4
     *
     * @return boolean
     */
    public function getTxJwfeusermanagerAdditionalBoolean4()
    {
        return $this->txJwfeusermanagerAdditionalBoolean4;
    }

    /**
     * Setter for additional boolean 4
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBoolean4($value)
    {
        $this->txJwfeusermanagerAdditionalBoolean4 = $value;
    }
	
    /**
     * Getter for additional boolean 5
     *
     * @return boolean
     */
    public function getTxJwfeusermanagerAdditionalBoolean5()
    {
        return $this->txJwfeusermanagerAdditionalBoolean5;
    }

    /**
     * Setter for additional boolean 5
     *
     * @param boolean $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBoolean5($value)
    {
        $this->txJwfeusermanagerAdditionalBoolean5 = $value;
    }
	
    /**
     * Getter for additional bitfield 1
     *
     * @return int
     */
    public function getTxJwfeusermanagerAdditionalBitfield1()
    {
        return $this->txJwfeusermanagerAdditionalBitfield1;
    }

    /**
     * Setter for additional bitfield 1
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBitfield1($value)
    {
        $this->txJwfeusermanagerAdditionalBitfield1 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfeusermanagerAdditionalBitfield2()
    {
        return $this->txJwfeusermanagerAdditionalBitfield2;
    }

    /**
     * Setter for additional bitfield 2
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBitfield2($value)
    {
        $this->txJwfeusermanagerAdditionalBitfield2 = $value;
    }
	
    /**
     * Getter for additional bitfield 3
     *
     * @return int
     */
    public function getTxJwfeusermanagerAdditionalBitfield3()
    {
        return $this->txJwfeusermanagerAdditionalBitfield3;
    }

    /**
     * Setter for additional bitfield 3
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBitfield3($value)
    {
        $this->txJwfeusermanagerAdditionalBitfield3 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfeusermanagerAdditionalBitfield4()
    {
        return $this->txJwfeusermanagerAdditionalBitfield4;
    }

    /**
     * Setter for additional bitfield 4
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBitfield4($value)
    {
        $this->txJwfeusermanagerAdditionalBitfield4 = $value;
    }
	
    /**
     * Getter for additional bitfield 2
     *
     * @return int
     */
    public function getTxJwfeusermanagerAdditionalBitfield5()
    {
        return $this->txJwfeusermanagerAdditionalBitfield5;
    }

    /**
     * Setter for additional bitfield 5
     *
     * @param int $value
     * @return void
     */
    public function setTxJwfeusermanagerAdditionalBitfield5($value)
    {
        $this->txJwfeusermanagerAdditionalBitfield5 = $value;
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
