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
* Form field for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormFieldCkcombo extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckcombo';

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
		
		$groupByFields = explode(',',$this->getOption('groupBy'));
		$groupBy = array();
		foreach($groupByFields as $gFi){
			if(empty($gFi)){
				continue;
			}
			list($key,$val) = explode(':',$gFi,2);
			$groupBy[$key] = $val;
		}
		
		$thisOpts = array(
				'multiple' => $this->getOption('multiple') ? true : false,
				'groupBy' => $groupBy,
				'size' => $this->getOption('size', 1, 'int'),
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null),
				'ui' => $this->getOption('ui'),
				'ui_allowCustomValues' => $this->getOption('ui_allowCustomValues') ? true : false,				
				'ui_hideSelect' => $this->getOption('ui_hideSelect') ? true : false,				
				'ui_direction' => $this->getOption('ui_direction')
			);
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);

		$this->input = JDom::_('html.form.input.select', $this->fieldOptions);

		return parent::getInput();
	}



}