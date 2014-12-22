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
class JFormRuleFile extends JdomClassFormRule
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
	protected $handler = 'file';

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
		static $instance;
		
		if($instance){
			$this->handler .= '_'. $instance;
		}
		$instance++;
		
		$this->regex = '';
		$allowedExtensionsText = '*.*';
		if (isset($fieldNode['allowedExtensions']))
		{
			$allowedExtensions = (string)$fieldNode['allowedExtensions'];

			$allowedExtensionsText = str_replace("|", ", ", $allowedExtensions);
			$this->setRegex($fieldNode);
		}

		$maxSize = 'null';
		if(isset($fieldNode['maxSize']) AND $size = floatval((string)$fieldNode['maxSize'])){
			$maxSize = intval($size * 1024 * 1024);
		}
		
		if (isset($fieldNode['msg-incorrect']))
			$alertText = LI_PREFIX . JText::_($fieldNode['msg-incorrect']);
		else
			$alertText = LI_PREFIX . JText::sprintf('PLG_JDOM_ERROR_ALLOWED_FILES', $allowedExtensionsText);

		$alertTextSize = LI_PREFIX . JText::sprintf('PLG_JDOM_ERROR_TOO_BIG_FILE_BYTES_MAX_ALLOWED_SIZE_BYTES', '<filesize>', self::bytesToString($maxSize));
		
		$values = array(
			'func' => 'function(field, rules, i, options){
				var maxSize = '. $maxSize .';
				
				// checkSize
				if(maxSize){
					// get the input file
					var id = field.attr("id");
					id = id.replace("-view","");
					var inputField = field.parent().find("#"+ id).get(0);
					
					if(inputField.files && inputField.files[0]){
						if(maxSize < inputField.files[0].size){
							var alertText = "'. addslashes($alertTextSize) .'".replace("<filesize>",bytesToString(inputField.files[0].size));						
							options.allrules["'. $this->handler .'"].alertText = alertText;
							return false;
						}
					}
				}
				
				// test allowed extensions
				var pattern = new RegExp("' . $this->regex . '", \'i\');
				if (!pattern.test(field.val())){					
					options.allrules["'. $this->handler .'"].alertText = "'. addslashes($alertText) .'";
					return false;
				}
				
				return true;
			}'
		);

		$json = JdomHtmlValidator::jsonFromArray($values);
		return "{" . LN . $json . LN . "}";
	}

	
	public function setRegex($fieldNode)
	{
		//Remove eventual '*.'
		$allowedExtensions = str_replace(array(",","*."," "), array("|",""), (string)$fieldNode['allowedExtensions']);

		$this->regex = '\.(' . $allowedExtensions . ')$';
	}
	
	public static function bytesToString($bytes)
	{
		$suffix = "";
		$units = array('K', 'M', 'G', 'T');

		$i = 0;
		while ($bytes >= 1024)
		{
			$bytes = $bytes / 1024;
			$suffix = $units[$i];
			$i++;
		}

		return round($bytes, 2) . $suffix;
	}
}