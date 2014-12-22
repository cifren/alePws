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


class JDomHtmlForm extends JDomHtml
{
	var $fallback = 'input';	//Used for default

	protected $dataKey;
	protected $alias;
	protected $dataObject;
	protected $dataValue;



	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 */
	function __construct($args)
	{

		parent::__construct($args);

	}

	protected function getInputValue()
	{
		$value = $this->dataValue;
		
		// Integers optimization.
		if (is_int($value))
			return $value;
				
		// Protect the string
		if (is_string($value))
			return  htmlspecialchars($value, ENT_COMPAT, 'UTF-8');


		if (is_array($value))
		{
			//Empty array
			if (!count($value))
				return '';
			
			// Simple array of integer
			if (is_int($value[0]))
				return implode(',', $value);
			
			// Other type of array : Not handled in value markup property
			return '';
		}
		
		// JSon an object
		if (is_object($value))
			return json_encode($value);
	
		return '';
	}
	
	function getInputName($suffix = null)
	{
		$name =  $this->dataKey;		
		if($suffix){
			$name =  $this->dataKey .'-'. $suffix;
		}
		
		if($this->formControl != null OR $this->formGroup != null){
			$name = '[' . $name . ']';
			if ($this->formGroup != null){
				$group = preg_replace("/\.(?!([^\{\{]+)?\}\})/i",'||',$this->formGroup);
				$name = '[' . implode('][', explode('||', $group)) . ']'. $name;		
			}
			
			if ($this->formControl != null){
				$name = $this->formControl . $name;
			}
		}
		
		return $name;
	}

	function getInputId($suffix = null)
	{
		$id = $this->dataKey;
		if(isset($this->alias) AND $this->alias != ''){
			$id = $this->alias;
		}
		
		if (isset($this->formGroup) AND $this->formGroup != null)
			$id = preg_replace("/\.(?!([^\{\{]+)?\}\})/i",'_',$this->formGroup) .'_'. $id;
		
		if (isset($this->formControl) AND $this->formControl != null)
			$id = $this->formControl . '_' . $id;
		
		if ($suffix)
			$id .= '-'. $suffix;
			
		if (!empty($this->prefix_id))
			$id = $this->prefix_id . $id;
		
		return $id;
	}


}
