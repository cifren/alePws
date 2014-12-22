<?php
/** 
* @author		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
* @license		GNU General Public License
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
* Form field for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormFieldCkparentslist extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckparentslist';

	/**
	* Method to get the field input markup.
	*
	* @access	public
	*
	* @return	string	The field input markup.
	*
	* @since	11.1
	*/
	public function getInput()
	{
		$this->setCommonProperties();
		
		$options = parent::getOptions();
		$filterFunction = $this->element->__toString();
		$func = create_function('$list' , $filterFunction);		
		$options = $func($options);
		
		$groupBy = array();
		$groups = explode(',',$this->getOption('groupBy'));
		foreach($groups as $group){
			$group = explode(':',$group);
			if(count($group) != 2){
				continue;
			}
			
			$groupBy[$group[0]] = $group[1];
		}
		
		$thisOpts = array(
				'list' => $options,
				'groupBy' => $groupBy,
				'size' => $this->getOption('size', 3, 'int'),
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null)
			);
		
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.select', $this->fieldOptions);

		return parent::getInput();
	}



}