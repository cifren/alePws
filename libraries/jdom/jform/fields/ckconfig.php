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
class JFormFieldCkconfig extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckconfig';

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
				'usergroupsEnabled' => ($this->getOption('usergroupsEnabled') == 'true') ? true : false
			);
		
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
	
		$this->input = JDom::_('html.form.input.config', $this->fieldOptions);
			
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