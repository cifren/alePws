<?php
/**
* (¯`·.¸¸.-> °º★ вүgιяσ.cσм ★º° <-.¸¸.·´¯)
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
* @added by		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined('_JEXEC') or die('Restricted access');



/**
* Form validator rule for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormRuleRegexp extends JdomClassFormRule
{
	/**
	* Indicates that this class contains special methods (ie: get()).
	*
	* @var boolean
	*/
	public $extended = true;

	/**
	* Unique name for this rule.
	*
	* @var string
	*/
	protected $handler = 'regexp';
	
	/**
	* Use this function to customize your own javascript rule.
	* $this->regex must be null if you want to customize here.
	*
	* @access	public
	* @param	JXMLElement	$fieldNode	The JXMLElement object representing the <field /> tag for the form field object.
	*
	* @return	string	A JSON string representing the javascript rules validation.
	*/
	public function getJsonRule($fieldNode)
	{
		// set regex and modifiers from the XML element
		$this->setRegex($fieldNode);
		
		$regex = $this->regex;
		if ($this->regexJs){
			$regex = $this->regexJs;
		}

		if(empty($regex)) return '';
		
		$values = array(
			"#regex" => '/' . $regex . '/' . $this->modifiers
		);

		if (isset($fieldNode['msg-incorrect']))
			$values["alertText"] = LI_PREFIX . JText::_($fieldNode['msg-incorrect']);
		else
			$values["alertText"] = LI_PREFIX . JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', JText::_($fieldNode['label']));

		$json = JdomHtmlValidator::jsonFromArray($values);
		return "{" . LN . $json . LN . "}";
	}	
	
	public function setRegex($fieldNode)
	{
		$this->regex = (string)$fieldNode['regexp'];
		$this->modifiers = (string)$fieldNode['regexpModifiers'];
	}	
}