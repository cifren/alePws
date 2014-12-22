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
class JFormFieldCkeditor extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckeditor';

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
		$buttons = ($this->getOption('buttons') === 'true') ? true : false;
		$repeatable = ($this->getOption('repeatable') === 'true') ? true : false;

		$thisOpts = array(
				'height' => $this->getOption('height'),
				'width' => $this->getOption('width'),
				'rows' => $this->getOption('rows'),
				'editor' => $this->getOption('editor'),
				'params' => $this->getOption('params'),
				'dataFile' => $this->getOption('dataFile'),
				'repeatable' => $repeatable,
				'buttons' => $buttons
			);

		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
		$this->input = JDom::_('html.form.input.editor', $this->fieldOptions);

		
		return parent::getInput();
	}



}