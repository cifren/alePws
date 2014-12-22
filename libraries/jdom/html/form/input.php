<?php
/**                               ______________________________________________
*                          o O   |                                              |
*                 (((((  o      <     JDom Class - Cook Self Service library    |
*                ( o o )         |______________________________________________|
* --------oOOO-----(_)-----OOOo---------------------------------- www.j-cook.pro --- +
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JDomHtmlFormInput extends JDomHtmlForm
{
	var $level = 3;				//Namespace position
	var $fallback = 'text';		//Used for default

	protected $dataKey;
	protected $dataObject;
	protected $dataValue;
	protected $domId;
	protected $domName;
	protected $formControl;
	protected $formGroup;
	protected $prefix_id;
	protected $required;
	protected $readonly;
	protected $disabled;
	protected $hidden;
	protected $validatorHandler;
	protected $dateFormat;
	protected $validatorRegex;
	protected $validatorMsgInfo;
	protected $validatorMsgIncorrect;
	protected $validatorMsgRequired;
	protected $validatorInsensitive = false;
	protected $validatorInvert = false;
	protected $validatorModifiers = '';
	protected $title;
	protected $placeholder;
	protected $type;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domId		: HTML id (DOM)  default=dataKey
	 *  @domName	: HTML name (DOM)  default=dataKey
	 *  @formControl: Form control (JForms)
	 *  @formGroup	: Form group (JForms)
	 *
	 * Validator
	 *  @required	: If the field is required
	 *  @validatorHandler 		: Validator alias
	 *  @dateFormat				: Date format to convert in regex
	 *  @validatorRegex			: Validation regex
	 *  @validatorMsgInfo 		: Introdution message
	 *  @validatorMsgIncorrect 	: Error message
	 *  @validatorMsgRequired 	: Required error message
	 *  @placeholder			: Placeholder
	 *  @type					: Input type (default: 'text') can be 'file'
	 *
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('dataKey'	, null, $args);
		$this->arg('dataObject'	, null, $args);
		$key = $this->dataKey;
		$this->arg('dataValue'	, null, $args, (($this->dataObject && $key)?(isset($this->dataObject->$key)?$this->dataObject->$key:null):null));
		$this->arg('domId'		, null, $args);
		$this->arg('domName'	, null, $args);
		$this->arg('formControl', null, $args);
		$this->arg('formGroup', null, $args);
		$this->arg('prefix_id', null, $args);
		$this->arg('required' 				, null, $args, false);
		$this->arg('readonly' 				, null, $args, false);	
		$this->arg('disabled' 				, null, $args, false);	
		$this->arg('validatorHandler' 		, null, $args);
		$this->arg('dateFormat' 			, null, $args);
		$this->arg('validatorRegex' 		, null, $args);
		$this->arg('validatorMsgInfo' 		, null, $args);
		$this->arg('validatorMsgIncorrect' 	, null, $args, "PLG_JDOM_VALIDATOR_INCORRECT");
		$this->arg('validatorMsgRequired' 	, null, $args, "PLG_JDOM_VALIDATOR_REQUIRED");
		$this->arg('placeholder' 			, null, $args);
		$this->arg('title' 			, null, $args);
		
		$this->arg('hidden' 		, null, $args);
		$this->arg('type' 			, null, $args, 'text');
		
		

		if (isset($this->dateFormat))
		{
			$this->validatorRegex = $this->strftime2regex($this->dateFormat);
		}

		if (isset($this->validatorRegex))
		{
			//Last char is a 'i' modifier
			if (substr(strrev($this->validatorRegex), 0, 1) == 'i')
			{
				$this->validatorRegex = substr($this->validatorRegex, 0, strlen($this->validatorRegex) - 1);
				$this->validatorInsensitive = true;
				$this->validatorModifiers = 'i';
			}

			//Trim slashes
			$this->validatorRegex = trim($this->validatorRegex, "/");

		}


		if ($this->required)
			$this->addClass('required');

		if ($this->validatorHandler)
			$this->addClass('validate-' . $this->validatorHandler);

/*
		if ($this->placeholder)
			$this->addSelector('placeholder', $this->JText($this->placeholder));
	
		if ($this->title)
			$this->addSelector('title', $this->title);
*/		
			
		if (!$this->domId OR $this->domId == '')
			$this->domId = $this->getInputId();
			
		if (!$this->domName OR $this->domName == '')
			$this->domName = $this->getInputName();		
	}

	function addValidatorHandler($regex= null, $handler = null)
	{
		if (!isset($this->validatorHandler))
			return;

		if (!$jsRule = self::getJsonRule())
			return;

		$script = 'jQuery.validationEngineLanguage.allRules.' . $this->validatorHandler . ' = ' . $jsRule .';';
		
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}

	function reformateRegexForJS($regex)
	{
		$regex = preg_replace("/\\\\/", "\\\\\\\\", $regex);
		$regex = preg_replace("/\\\\s/", " ", $regex);
		$regex = preg_replace("/\\\\d/", "[0-9]", $regex);
		return $regex;
	}

	function buildValidatorMessage()
	{
		if (isset($this->validatorMsgInfo))
			$this->loadScriptPromptInfo($this->getInputId(), $this->JText($this->validatorMsgInfo));

	}

	//DEPRECATED
	function buildValidatorIcon()
	{
		
	}

	protected function parseVars($vars)
	{	
		$value = '';
		if(!is_array($this->dataValue) AND !is_object($this->dataValue)){
			$value = htmlspecialchars($this->dataValue, ENT_COMPAT, 'UTF-8');
		}
		
		return parent::parseVars(array_merge(array(
			'DOM_ID'		=> $this->domId,
			'INPUT_NAME'		=> $this->domName,
			'STYLE'			=> $this->buildDomStyles(),
			'CLASS'			=> $this->buildDomClass(),		//With attrib name
			'CLASSES'		=> $this->getDomClass(),		// Only classes
			'SELECTORS'		=> $this->buildSelectors(),
			'VALUE'			=> $value,
			'MESSAGE' 		=> $this->buildValidatorMessage(),
			'VALIDOR_ICON' 	=> $this->buildValidatorIcon(),
			'JSON_REL' 		=> htmlspecialchars($this->jsonArgs(), ENT_COMPAT, 'UTF-8'),
		), $vars));
	}


	function buildJS()
	{
		$this->addValidatorHandler();
	}


	//jQuery Validator

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
					'el.validationEngine("showPrompt", "' . addslashes($message) . '", "pass", false);' .
				'});';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}

	/**
	* Get the JSON object rule for the validator.
	*
	* @access	public static
	* @param	JXMLElement	$fieldNode	The XML field node.
	* @param	JFormRule	$rule	The validator rule.
	*
	* @return	string	JSON string.
	*/
	public function getJsonRule()
	{
		if (!isset($this->validatorRegex))
			return;

		//Escape quotes now because escapes has been automaticaly removed.
		$regex = str_replace('"', '\"', $this->validatorRegex);

		//reformate Regex for javascript
		$jsRegex = $this->reformateRegexForJS($regex);

		$values = array(
			"#regex" => 'new RegExp("' . $jsRegex . '", \'' . $this->validatorModifiers . '\')',
			"alertText" => LI_PREFIX . addslashes(JText::_($this->validatorMsgIncorrect))
		);


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
			}

			$entries[] = '"'. $key. '" : '. $q. $value. $q;
		}

		return implode(',' .LN, $entries);
	}




}
