<?php
/**
* (¯`·.¸¸.-> °º★ вүgιяσ.cσм ★º° <-.¸¸.·´¯)
* @version		0.0.1
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
class JFormFieldCkcheckboxes extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckcheckboxes';

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
		// explode comma separated values
		$this->__set('default',explode(',',trim($this->default,',')));
		if(!is_array($this->value) OR $this->value == ''){
			$this->__set('value',$this->default);
		}
		
		$this->setCommonProperties();
		$thisOpts = array(
				'layout' => $this->getOption('layout')
			);
			
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);

		$this->input = JDom::_('html.form.input.checkboxes', $this->fieldOptions);
		
		return parent::getInput();
	}



}