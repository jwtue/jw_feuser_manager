<?php
namespace JwTue\FeUserManager\Domain\Model;


/**
 * @package JwTue\FeUserManager
 */
class EditorField extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    const TYPE_DB_FIELD = 0;
    const TYPE_PASSWORD = 1;
    const TYPE_IMAGE = 2;	
    const TYPE_ADDITIONAL_RICHTEXT = 3;
    const TYPE_SEPARATOR = 4;
    const TYPE_DB_FIELD_READONLY = 5;
    const TYPE_DELETE_USER = 6;
    const TYPE_USERGROUPS = 7;
    const TYPE_EMAIL = 8;
    const TYPE_ADDITIONAL_ENTRIES = 9;
    
    const MODE_DB_TEXT = 0;
    const MODE_DB_TEXT_MULTILINE = 1;
    const MODE_DB_EMAIL = 2;
    const MODE_DB_BOOLEAN = 3;
    const MODE_DB_DATE = 4;
    const MODE_DB_TIME = 5;
    const MODE_DB_DATETIME = 6;
    const MODE_DB_MULTISELECT = 7;
    const MODE_DB_OPTIONS = 8;
    
    /**
    * @var boolean
    */
    protected $hidden;
	
    /**
     * @var string
     */
    protected $title = '';
	
    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var string
     */
    protected $selectoptionEntries = '';

    /**
     * @var string
     */
    protected $dbField = '';

    /**
     * @var string
     */
    protected $dbMode = '';

    /**
     * @var boolean
     */
    protected $required = '';
    /**
     * @var boolean
     */
    protected $passwordGenerator = '';
	
    /**
     * @var string
     */
    protected $imagePath = '';
	
    /**
     * @var string
     */
    protected $imageFilename = '';

    /**
     * @var int
     */
    protected $redirectPage = '';
	
    /**
     * @var string
     */
	protected $emailMode = '';
	
    /**
     * @var string
     */
	protected $emailRecipient = '';
	
    /**
     * @var string
     */
	protected $emailSubject = '';
	
    /**
     * @var string
     */
	protected $emailContent = '';
 
    /**
    * @return boolean $hidden
    */
    public function getHidden() {
        return $this->hidden;
    }

    /**
    * @return boolean $hidden
    */
    public function isHidden() {
        return $this->getHidden();
    }

    /**
    * @param boolean $hidden
    * @return void
    */
    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }
	
    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->type;
    }

    /**
     * @param int $type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getSelectoptionEntries()
    {
        return $this->selectoptionEntries;
    }
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setSelectoptionEntries($selectoptionEntries)
    {
        $this->selectoptionEntries = $selectoptionEntries;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getDbField()
    {
        return $this->dbField;
    }
	
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setDbField($dbField)
    {
        $this->dbField = $dbField;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getUsableDbField()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($this->dbField);
    }

    /**
     * Returns field configuration as xml string
     *
     * @return string
     */
    public function getDbMode()
    {
        return $this->dbMode;
    }
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setDbMode($dbMode)
    {
        $this->dbMode = $dbMode;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return boolean
     */
    public function getPasswordGenerator()
    {
        return $this->passwordGenerator;
    }
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setPasswordGenerator($passwordGenerator)
    {
        $this->passwordGenerator = $passwordGenerator;
    }

    /**
     * Returns field configuration as xml string
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }
    /**
     * Set field configuration as xml string
     *
     * @param string $configuration xml string
     * @return void
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * Returns path for image files
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }
	
    /**
     * Set path for image files
     *
     * @param string $path for image files
     * @return void
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Returns page for redirecting
     *
     * @return int
     */
    public function getRedirectPage()
    {
        return $this->redirectPage;
    }
	
    /**
     * Set page for redirecting
     *
     * @param int $redirectPage
     * @return void
     */
    public function setRedirectPage($redirectPage)
    {
        $this->redirectPage = $redirectPage;
    }
	
    public function getImagePathProcessed()
    {
		//"t3://folder?storage=2&identifier=%2Fabteilung%2Fbilder_ea_stadtmitte%2F"
		if (substr($this->imagePath, 0, strlen("t3://folder?")) == "t3://folder?") {
			$parts = explode("&", substr($this->imagePath, strlen("t3://folder?")));
			$storage = null;
			$identifier = null;
			foreach ($parts as $k) {
				if (substr($k, 0, strlen("storage=")) == "storage=") {
					$storage = substr($k, strlen("storage="));
				} else if (substr($k, 0, strlen("identifier=")) == "identifier=") {
					$identifier = urldecode(substr($k, strlen("identifier=")));
				}
			}
			return /*"file:".*/$storage.":".$identifier;
		}
		if (substr($this->imagePath, 0, strlen("file:")) == "file:") {
			return substr($this->imagePath, strlen("file:"));
		}
        return $this->imagePath;
    }

    /**
     * Returns how to build the filename for uploaded images (0: uid, 1: username)
     *
     * @return string
     */
    public function getImageFilename()
    {
        return $this->imageFilename;
    }
	
    /**
     * Set filename for image files
     *
     * @param string $filename for uploaded images (0: uid, 1: username)
     * @return void
     */
    public function setImageFilename($imageFilename)
    {
        $this->imageFilename = $imageFilename;
    }

    public function getEmailMode()
    {
        return $this->emailMode;
    }
    public function setEmailMode($emailMode)
    {
        $this->emailMode = $emailMode;
    }
    public function getEmailRecipient()
    {
        return $this->emailRecipient;
    }
    public function setEmailRecipient($emailRecipient)
    {
        $this->emailRecipient = $emailRecipient;
    }
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;
    }
    public function getEmailContent()
    {
        return $this->emailContent;
    }
    public function setEmailContent($emailContent)
    {
        $this->emailContent = $emailContent;
    }
}