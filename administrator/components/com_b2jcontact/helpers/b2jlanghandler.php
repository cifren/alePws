<?php defined('_JEXEC') or die('Restricted access');

/* ------------------------------------------------------------------------
 * Bang2Joom Contact for Joomla 3.0+
 * ------------------------------------------------------------------------
 * Copyright (C) 2011-2013 Bang2Joom. All Rights Reserved.
 * Fork of Fox Contact by Fox Joomla Extensions
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: Bang2Joom
 * Websites: http://www.bang2joom.com
  ------------------------------------------------------------------------
 */

class B2JLangHandler
	{
	protected $lang;
	protected $messages = array();

	function __construct()
	{
		$this->lang = JFactory::getLanguage();

		$this->check_partial();
		$this->check_missing();
	}


	public function HasMessages()
	{
		return (bool)count($this->messages);
	}


	public function GetMessages()
	{
		return $this->messages;
	}


	protected function check_partial()
	{
		if (intval(JText::_('COM_B2JCONTACT_PARTIAL')))
		{
			$this->messages[] = $this->lang->get("name") . "translation is still missing. Please consider to contribute by writing and sharing your own translation.<br/>";
		}
	}


	protected function check_missing()
	{
		$lg = JFactory::getLanguage();
		
		foreach ($lg->getKnownLanguages() as $l){
			$filename = JPATH_SITE . "/language/" . $l['tag'] . "/" . $l['tag'] . ".com_b2jcontact.ini";
			
			if (!file_exists($filename))
			{

				if(!$this->check_availability($l['tag'],$l['name'])){
					$this->messages[] = $l['name'] . " translation is still missing. Please consider to contribute by writing and sharing your own translation.<br/>";
				}
			}
		}
	}


	private function check_availability($lTag,$lName)
	{
		$filename = JPATH_ADMINISTRATOR . '/components/com_b2jcontact/b2jcontact.xml';
		$xml = JFactory::getXML($filename);

		if (!$xml)
		{
			return false;
		}else{
			foreach ($xml->languages->language as $l)
			{
				if (strpos((string)$l, $lTag) === 0)
				{
					//$this->messages = array();
					$this->messages[] = $lName . " translation has not been installed, but <strong>is available</strong>. To fix this problem simply install this extension once again, without uninstalling it.<br/>";
					return true;
					break;
				}
			}
		}
		return false;
	}

}

?>