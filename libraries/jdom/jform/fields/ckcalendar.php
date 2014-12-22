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
class JFormFieldCkcalendar extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckcalendar';

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
				'format' => $this->getOption('format'),
				'filter' => $this->getOption('filter'),
				'selectors' => $this->getOption('selectors'),
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null)
			);
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.calendar', $this->fieldOptions);

		return parent::getInput();
	}



}