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
class JFormFieldCkrepeatable extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckrepeatable';

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
				'form' => ByGiroHelper::getSubForm($this->element),
				'isMaster' => true,
				'showCounter' => $this->getOption('showCounter') ? true : false,
				'sortable' => $this->getOption('sortable') ? true : false,
				'cloneable' => $this->getOption('cloneable') ? true : false,
				'fieldsToRender' => $this->getOption('fieldsToRender'),
				'formControl' => $this->getOption('formControl'),
				'formGroup' => $this->getOption('formGroup'),
				'layout' => $this->getOption('layout'),
				'max' => $this->getOption('limit'),
				'debug' => $this->getOption('debug') ? true : false,
			);
				
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
	
		$this->input = JDom::_('html.form.input.repeatable', $this->fieldOptions);
			
		return parent::getInput();
	}

	public function getLabel()
	{
		$extraLabel = array();
		$max = $this->getOption('limit');
		
		if(!empty($max)){
			$extraLabel[] = 'max: '. $max ;
		}
		
		$this->__set('labelclass','jtags');
		$label = parent::getLabel();
		if(!empty($extraLabel)){
			$label = $label . '<span class="jtags_info">('. implode(' - ', $extraLabel) .')</span>';
		}
		
		return $label;
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