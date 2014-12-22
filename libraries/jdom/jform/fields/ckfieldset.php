<?php
/**
*
* @version		0.0.1
* @license		GNU General Public License
* @author		Girolamo Tomaselli - http://bygiro.com
*
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
* Form field for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormFieldCkfieldset extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckfieldset';

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
		
		$thisOpts = array(
				'fieldsToRender' => (trim($this->getOption('fieldsToRender'),',') != '') ? explode(',',trim($this->getOption('fieldsToRender'),',')) : array(),
				'form' => $this->form,
				'fieldsetName' => $this->getOption('fieldsetName'),
				'autoFilter' => $this->getOption('autoFilter') ? true : false,
				'isMaster' => $this->getOption('isMaster') ? true : false,
				'showCounter' => $this->getOption('showCounter') ? true : false,
				'showId' => $this->getOption('showId') ? true : false,
				'tmplEngine' => $this->getOption('tmplEngine'),
				'jListByGiro' => $this->getOption('jListByGiro') ? true : false,
				'listName' => $this->getOption('name'),
				'formGroup' => $this->getOption('name'),
				'disabledActions' => $this->getOption('disabledActions'),
				'maxItems' => $this->getOption('maxItems'),
				'enumList' => array(),
				'dataList' => $this->value
			);
		
		// check XML subitems fields
		$subForm = ByGiroHelper::getSubForm($this->element);
		if(!empty($subForm)){
			$thisOpts['form'] = $subForm;
		}
		
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
	
		$this->input = JDom::_('html.list.table', $this->fieldOptions);
			
		return parent::getInput();
	}


	public function getOutput($tmplEngine = null)
	{
		$html = '';
		if(!isset($this->value)){
			return $html;
		}
		
		$html = $this->getInput();
		
		return $html;
	}
}