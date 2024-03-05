<?php
namespace JwTue\FeUserManager\Utility;

/**
 * Class Utility
 *
 * @package JwTue\FeUserManager\Utility
 */
class Helper
{
	/**
	 * The getFieldNames method is used to get the "fe_users" field names into the flexform of the plugin.
	 *
	 * @param	array		$config: The fields selected.
	 * @return	array		$config
	 */
	public function getFieldNames($config) {
		$fieldList = array();

		foreach ($GLOBALS['TCA']['fe_users']['columns'] as $key => $_){
			$label = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns'][$key]['label']);
			if (substr($label, -1) == ":") $label = substr($label, 0, -1);
			$fieldList[] = array($label, $key);
		}
		
		$firstname = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns']["first_name"]['label']);
		if (substr($firstname, -1) == ":") $firstname = substr($firstname, 0, -1);
		
		$lastname = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns']["last_name"]['label']);
		if (substr($lastname, -1) == ":") $lastname = substr($lastname, 0, -1);
		
		$address = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns']["address"]['label']);
		if (substr($address, -1) == ":") $address = substr($address, 0, -1);
		
		$zip = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns']["zip"]['label']);
		if (substr($zip, -1) == ":") $zip = substr($zip, 0, -1);
		
		$city = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns']["city"]['label']);
		if (substr($city, -1) == ":") $city = substr($city, 0, -1);
		
		$fieldList[] = array("___ Zusammengesetzte Felder ___", "");
		$fieldList[] = array($firstname." ".$lastname, "firstname_lastname");
		$fieldList[] = array($lastname.", ".$firstname, "lastname_firstname");
		$fieldList[] = array($address.", ".$zip." ".$city, "full_address");

		$config['items'] = array_merge($config['items'], $fieldList);

		return $config;
	}
	
	/**
	 * The getFieldNames method is used to get the "fe_users" field names into the flexform of the plugin.
	 *
	 * @param	array		$config: The fields selected.
	 * @return	array		$config
	 */
	public function getEditableFieldNames($config) {
		$fieldList = array();

		foreach ($GLOBALS['TCA']['fe_users']['columns'] as $key => $_){
			
			switch ($key) {
				case "image":
				//case "tx_jwfeusermanager_lastupdated":
					continue 2;
			}
			
			
			$label = $GLOBALS['LANG']->sL($GLOBALS['TCA']['fe_users']['columns'][$key]['label']);
			if (substr($label, -1) == ":") $label = substr($label, 0, -1);
			$fieldList[] = array($label, $key);
		}
		$config['items'] = array_merge($config['items'], $fieldList);
		
		
		return $config;
	}
	
	public function getFieldNamesArray() {
		$config = $this->getFieldNames(array('items' => array()));
		$out = array();
		foreach ($config['items'] as $v) {
			$out[$v[1]] = $v[0];
		}
		return $out;
	}
}
