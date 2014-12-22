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

// Detect if we have full UTF-8 and unicode PCRE support.
if (!defined('JCOMPAT_UNICODE_PROPERTIES'))
{
	define('JCOMPAT_UNICODE_PROPERTIES', (bool) @preg_match('/\pL/u', 'a'));
}

/**
* Form validator rule for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JdomClassFormRule
{

	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $regex;

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $modifiers;
	
	/**
	 * Method to test the value.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException if rule is invalid.
	 */
	public function test(SimpleXMLElement &$element, &$value, $group = null, JRegistry $input = null, JForm &$form = null)
	{
		// Common test : Required, Unique
		if (!self::testDefaults($element, $value, $group, $input, $form)){
			return false;
		}
		
		if(empty($value)){
			return true;
		}
		
		// build regex and modifiers if MISSING
		$this->setRegex($element);
		
		// Check for a valid regex.
		if (empty($this->regex))
		{
		//	throw new UnexpectedValueException(sprintf('%s has invalid regex.', get_class($this)));
			return true; 
		}

		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES)
		{
			$this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers . 'u';
		}

		// Test the value against the regular expression.
		if (preg_match(chr(1) . $this->regex . chr(1) . $this->modifiers, $value))
		{
			return true;
		}

		return false;
	}	
	
	public function setRegex($fieldNode)
	{		

	}
	
	/**
	* Proxy to access protected methods.
	*
	* @access	public
	* @param	string	$var	The name of the property.
	*
	* @return	mixed	The value of the property. Null if not in the list.
	*/
	public function __get($var)
	{
		if (in_array($var, array('regex', 'regexJs', 'modifiers', 'handler')))
			if (isset($this->$var))
				return $this->$var;
	}

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
		/* 	TODO : Fill the associative array below, or create a JSON string manually
		* 	Note : $this->regex must be null
		*/

		$values = array();

		$json = JdomHtmlValidator::jsonFromArray($values);
		return "{" . LN . $json . LN . "}";
	}

	/**
	* Method to test all common rules (Required, Unique).
	*
	* @access	public static
	* @param	JXMLElement	&$element	The JXMLElement object representing the <field /> tag for the form field object.
	* @param	mixed	$value	The form field value to validate.
	* @param	string	$group	The field name group control value. This acts as as an array container for the field.
	* @param	JRegistry	&$input	An optional JRegistry object with the entire data set to validate against the entire form.
	* @param	object	&$form	The form object for which the field is being tested.
	*
	* @return	boolean	True if the value is valid, false otherwise.
	*
	* @since	Cook V2.0
	*/
	public static function testDefaults(&$element, &$value, $group = null, &$input = null, &$form = null)
	{
		$generateByfield = (string)$element['autoGenerate'];
		
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] === 'true' || (string) $element['required'] == 'required');	
		if ($required && (empty($value) OR $value == ''))
		{
			if($generateByfield != ''){
				$data = (array)$input;
				
				if($group != ''){
					$value = ByGiroHelper::array_path_value($data, $group .'.'. $generateByfield);
				} else {				
					$value = $data[$generateByfield];
				}
					
				$value = ByGiroHelper::safeAlias($value);		
			} else {
				return false;
			}
		}
		
		// Check if we should test for uniqueness.
		$unique = ((string) $element['unique'] === 'true' || (string) $element['unique'] == 'unique');
		if ($unique)
		{
			$jinput = JFactory::getApplication()->input;

			$parts = explode(".", $form->getName());
			$extension = preg_replace("/com_/", "", $parts[0]);
			$table = JTable::getInstance($parts[1], $extension . 'Table', array());

			// Get the database object and a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$id =  $jinput->get('cid', 0, 'array');
			if (count($id))
				$id = $id[0];

			if (in_array($jinput->get('task'), array('save2copy')))
				$id = 0;

			if((string)$element['queryUnique'] == ''){
				// Build the query.
				$query->select('COUNT(*)');
				$query->from($table->getTableName());
				$query->where(qn($db, (string)$element['name']) . ' = ' . $db->quote($value));
				$query->where(qn($db, 'id') . '<>' . (int)$id);
			} else {
				$query = (string)$element['queryUnique'];
			}
			
			// Set and query the database.
			$db->setQuery($query);
			$duplicate = (bool) $db->loadResult();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				JError::raiseWarning(1201, $db->getErrorMsg());
				return false;
			}

			if($duplicate AND $group == '' AND $generateByfield != ''){		
				$counter = 0;
				$newvalue = $value;
				do{
					$counter++;
					$newvalue = $value . $counter;

					if((string)$element['queryUnique'] == ''){
						$query = "SELECT COUNT(*)"
							. " FROM " . qn($db, $table->getTableName())
							. " WHERE ". (string)$element['name'] ." = '". $newvalue ."' "
							. " AND id <> ". $id;
					} else {
						$query = (string)$element['queryUnique'];
					}
					
					$db->setQuery($query);
					$aliases = $db->loadResult();
				}while($aliases > 0);
				$duplicate = false;
				$value = $newvalue;				
			}
			
			if ($duplicate)
			{
				JError::raiseWarning(1201, JText::sprintf("PLG_JDOM_DUPLICATE_ENTRY_FOR", JText::_($element['label'])));
				return false;
			}
		}

		return true;
	}


}
