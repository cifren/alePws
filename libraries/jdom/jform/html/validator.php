<?php
/**
* (¯`·.¸¸.-> °º★ вүgιяσ.cσм ★º° <-.¸¸.·´¯)
* @version		0.3.8
* @package		jForms
* @subpackage	
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @MVC			basic MVC generated with Cook Self Service  V2.6.4 - www.j-cook.pro
* @license		GNU GPL v3 or later
*
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

@define("LI_PREFIX", '<span class="msg-prefix">• </span>');


/**
* Helper HTML
*
* @package	Jforms
* @subpackage	Validator
*/
class JdomHtmlValidator
{
	/**
	* Get the JSON object rule for the validator.
	*
	* @access	public static
	* @param	JXMLElement	$fieldNode	The XML field node.
	* @param	JFormRule	$rule	The validator rule.
	*
	* @return	string	JSON string.
	*/
	public static function getJsonRule($fieldNode, $rule)
	{
		if ($rule->regexJs)
			$regex = $rule->regexJs;
		else
		{
			//reformate Regex for javascript
			$regex = $rule->regex;
			$regex = preg_replace("/\\\\/", "\\", $regex);

			$regex = preg_replace("/\\\\s/", " ", $regex);
			$regex = preg_replace("/\\\\d/", "[0-9]", $regex);
		}

		$values = array(
		// -> Uses now the slashes notation
		//			"#regex" => 'new RegExp("' . $regex . '", \'' . $rule->modifiers . '\')'
			"#regex" => '/' . $regex . '/' . $rule->modifiers
		);

		if (isset($fieldNode['msg-incorrect']))
			$values["alertText"] = LI_PREFIX . JText::_($fieldNode['msg-incorrect']);
		else
			$values["alertText"] = LI_PREFIX . JText::_("PLG_JDOM_FORMVALIDATOR_INCORRECT_VALUE");

		$json = self::jsonFromArray($values);

		return "{" . LN . $json . LN . "}";
	}

	/**
	* Transform a recursive associative array in JSON string.
	*
	* @access	public static
	* @param	array	$values	Associative array only (can be recursive).
	*
	* @return	string	JSON string.
	*/
	public static function jsonFromArray($values)
	{
		$entries = array();
		foreach($values as $key => $value)
		{
			$q = "'";

			if (is_array($value))
			{
				// ** Recursivity **
				$value = "{" . LN . self::jsonFromArray($value) . LN . "}";
				$q = "";
			}
			else if (substr($key, 0, 1) == '#')
			{
				//Do not require quotes
				$key = substr($key, 1);
				$q = "";
			} else if($key == 'func'){
				$q = "";
			}

			$entries[] = '"'. $key. '" : '. $q. $value. $q;
		}

		return implode(',' .LN, $entries);
	}

	/**
	* Instance the language script for the validator, and the default validation
	* rules.
	*
	* @access	public static
	*
	* @return	void	
	* @return	void
	*/
	public static function loadLanguageScript()
	{
		static $loaded;
		
		if(!empty($loaded)){
			return;
		}
		
		$script = '(function($){' .
				'jQuery.fn.validationEngineLanguage = function(){' .
				'};' .
				'jQuery.validationEngineLanguage = {' .
				'newLang: function(){' .
				'jQuery.validationEngineLanguage.allRules = {' .LN;

		$baseRules = array();

		$baseRules["required"] = array(
			"regex"	=> "none",
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_FIELD_IS_REQUIRED")),
			"alertTextCheckboxMultiple" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_PLEASE_SELECT_AN_OPTION")),
			"alertTextCheckboxe" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_CHECKBOX_IS_REQUIRED")),
			"alertTextDateRange" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_BOTH_DATE_RANGE_FIELDS_ARE_REQUIRED"))

		);

		// Default handlers

		$baseRules["numeric"] = array(
			"#regex"	=> '/^[\-\+]?\d+$/',
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_IS_NOT_A_VALID_INTEGER")),
		);

		$baseRules["integer"] = array(
			"#regex"	=> '/^[\-\+]?\d+$/',
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_IS_NOT_A_VALID_INTEGER")),
		);


		$baseRules["username"] = array(
			"#regex"	=> '/^[A-Za-z0-9 ]{3,20}$/i',
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_IS_NOT_A_VALID_USERNAME") .' (min: 3, max: 20)'),
		);


		$baseRules["password"] = array(
			"#regex"	=> '/^\S[\S ]{2,98}\S$/',
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_IS_NOT_A_VALID_PASSWORD")),
		);

		$baseRules["email"] = array(
			"#regex"	=> '/^[a-zA-Z0-9._-]+(\+[a-zA-Z0-9._-]+)*@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/',
			"alertText" => LI_PREFIX . addslashes(JText::_("PLG_JDOM_FORMVALIDATOR_THIS_IS_NOT_A_VALID_EMAIL")),
		);


		/* TODO : You can add some rules here
		 * These rules are executed ONLY in client side (javascript)
		 * If you want both JS and PHP validation, create a rule file
		 */
		
		$script .= self::jsonFromArray($baseRules);

		$script .= LN. '};}};' .
				'jQuery.validationEngineLanguage.newLang();' .
				'})(jQuery);';


		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
		
		$loaded = true;
	}

	/**
	* Render a prompt information to guide the user.
	*
	* @access	public static
	* @param	string	$id	The input id.
	* @param	string	$message	The message to display
	*
	* @return	void	
	* @return	void
	*/
	public static function loadScriptPromptInfo($id, $message)
	{
		$script = 'jQuery(document).ready(function(){' .
					'var el = jQuery("#' . $id . '");' .
					'el.validationEngine("showPrompt", "' . $message . '", "pass", false);' .
				'});';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}

	/**
	* Render a javascript validator.
	*
	* @access	public static
	* @param	JXMLElement	$fieldNode	The XML field node.
	* @param	JFormRule	$rule	The validator rule.
	*
	* @return	string	Rendered html validator.
	*/
	public static function loadScripts($fieldNode, $rule)
	{
		static $rulesLoaded;	
		if(!is_array($rulesLoaded)){
			$rulesLoaded = array();
		}
		
		if (empty($rule) OR !property_exists($rule, 'handler') OR !$rule->handler){
			return;
		}
	
		if ($rule->regex){
			$jsRule = self::getJsonRule($fieldNode, $rule);
		}
		else
		{
			//Try to get the JS rule object from the
			try{
				$jsRule = $rule->getJsonRule($fieldNode);
			}
			catch (Exception $e){
				return;
			}
		}
	
		if (!$jsRule)
			return;

		$handler = $rule->handler;
		$script = 'jQuery.validationEngineLanguage.allRules.' . $handler . ' = ' . $jsRule;

		$hash = md5($jsRule);
		// avoid duplicates rules loaded
		if(isset($rulesLoaded[$hash])){
			return $rulesLoaded[$hash];
		}
		
		$rulesLoaded[$hash] = $handler;		
		
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
		
		return $handler;
	}

	/**
	* Instance js validators for a field.
	*
	* @access	public static
	* @param	JXMLElement	$element	The XML field node.
	* @param	string	$id	The input id.
	* @return	void
	*/
	public static function loadValidators($element, $id)
	{
		//Show the prompt information
		if (isset($element['msg-info']))
			self::loadScriptPromptInfo($id, JText::_($element['msg-info']));

		if (!isset($element['class']))
			return;

		$class = $element['class'];

		preg_match_all('/validate\[(.+)\]\s?/', $class, $matches);

		$validates = array();
		if (count($matches[1]))
			$validates = explode(",", $matches[1][0]);

		$required = (isset($element['required'])?$element['required']:in_array('required', $validates));

		//TODO : Test it in PHP + JS (When required comes from class)
		if($required)
			$element['required'] = true;



		$rules = array();
		if (isset($validates)){
			foreach($validates as $ruleType)
			{
				$ruleType = trim($ruleType);
				if($ruleType == ''){
					continue;
				}
				preg_match_all("/custom\[([a-zA-Z0-9]+)(_.+)?\]/", $ruleType, $matchesCustom);

				$mainrule = '';
				if (count($matchesCustom[1])){
					$mainrule = $matchesCustom[1][0];
				}
				
				$rule = JFormHelper::loadRuleType($mainrule);				
				$rules[$ruleType] = $rule;
			}
		}

		$newRules = self::render($element, $id, $rules);

		// get rule types
		$types = array_keys($newRules);

		// build the new field Class
		$validateClass = 'validate['. implode(',',$types) .'] ';
		
		if(isset($matches[0][0])){
			$class = str_replace($matches[0][0],$validateClass,$class);
		}
		
		return $class;
	}

	/**
	* Render a javascript validator.
	*
	* @access	public static
	* @param	JXMLElement	$fieldNode	The XML field node.
	* @param	string	$id	The input id.
	* @param	JFormRule	$rules	The validator rule(s). Accept array
	*
	* @return	string	Rendered html validator.
	*/
	public static function render($fieldNode, $id, $rules = null)
	{
		if (!is_array($rules)){
			$rules = array($rules);
		}
		
		$rulesCleaned = array();
		foreach($rules as $key => $rule){
			$newKey = self::loadScripts($fieldNode, $rule);		
			if($newKey){
				preg_match_all("/custom\[(.*?)\]/", $key, $matchesCustom);
				$key = str_replace($matchesCustom[0][0],'custom['. $newKey .']',$key);
			}
			$rulesCleaned[$key] = $rule;
		}
		
		return $rulesCleaned;
	}


}