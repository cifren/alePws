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
class JFormFieldCkdatetimepicker extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckdatetimepicker';

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
				'separator' => $this->getOption('separator'),
				'style' => $this->getOption('style'),
				'mode' => $this->getOption('mode'),
				'minView' => $this->getOption('minView'),
				'maxView' => $this->getOption('maxView'),
				'startView' => $this->getOption('startView'),
				'min' => $this->getOption('startDate'),
				'max' => $this->getOption('endDate'),
				'firstDay' => $this->getOption('firstDay'),
				'timeFormat' => $this->getOption('timeFormat'),
				'minuteStep' => $this->getOption('minuteStep'),
				'showDaysNotInMonth' => $this->getOption('showDaysNotInMonth'),
				'showDisabledTimes' => $this->getOption('showDisabledTimes'),
				'specialDates' => json_decode(htmlspecialchars_decode($this->getOption('specialDates'),ENT_COMPAT)),
				'today' => $this->getOption('today'),
				'calendars' => $this->getOption('calendars'),
				'format' => $this->getOption('format'),
				'uiFormat' => $this->getOption('uiFormat'),
				'autoclose' => $this->getOption('autoclose'),
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null)
			);
		
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
		
		if($this->fieldOptions['dataValue'] == 'now'){
			$this->fieldOptions['dataValue'] = date($this->fieldOptions['dateFormat'],time());
		}
		
		$this->input = JDom::_('html.form.input.datetimepicker', $this->fieldOptions);

		return parent::getInput();
	}



}