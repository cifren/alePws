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
* Form validator rule for JDom.
*
* @package	JDom
* @subpackage	Form
*/
class JFormRuleTime extends JdomClassFormRule
{
	/**
	* Specifiate the default format.
	*
	* @var string
	*/
	protected $dateFormat = 'Y-m-d';

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
	protected $handler = 'time';

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
		if (!isset($fieldNode['format']))
			return;

		$timeFormat = $fieldNode['format'];		
		$regex = JdomHelperDates::strftime2regex($timeFormat, false, false);

		$values = array(
			"#regex" => 'new RegExp("' . $regex . '", \'i\')'
		);

		if (isset($fieldNode['msg-incorrect']))
			$values["alertText"] = LI_PREFIX . JText::_($fieldNode['msg-incorrect']);
		else
			$values["alertText"] = LI_PREFIX . JText::sprintf('TEST_ERROR_INCORRECT_FORMAT_EXPECTED', $timeFormat);

		$json = JdomHtmlValidator::jsonFromArray($values);
		return "{" . LN . $json . LN . "}";
	}

	/**
	* Method to test the field.
	*
	* @access	public
	* @param	SimpleXMLElement	$element	The JXMLElement object representing the <field /> tag for the form field object.
	* @param	mixed	$value	The form field value to validate.
	* @param	string	$group	The field name group control value. This acts as as an array container for the field.
	* @param	JRegistry	$input	An optional JRegistry object with the entire data set to validate against the entire form.
	* @param	JForm	$form	The form object for which the field is being tested.
	*
	* @return	boolean	True if the value is valid, false otherwise.
	*
	* @since	11.1
	*/
	public function test(SimpleXMLElement &$element, &$value, $group = null, JRegistry $input = null, JForm &$form = null)
	{
		$timeFormat = (string)$element['format'];

		//Convert the date format (strftime) in regular expression
		$this->regex = JdomHelperDates::strftime2regex($timeFormat, false, false);

		// Test the value against the regular expression.
		$test = parent::test($element, $value, $group, $input, $form);

		if (!$test){
			return false;
		}
		
		return true;
	}


}