<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryDatetimepicker extends JDomFrameworkJquery
{	

	var $assetName = 'datetimepicker';
	
	var $attachJs = array();
	var $attachCss = array();
	
	protected static $loaded = array();	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
	}
	
	function build()
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}
		JDom::_('framework.jquery.extrajs');
		$doc = JFactory::getDocument();
		
		$this->attachJs[] = 'jquery.datetimepicker.js';
		$this->attachCss[] = 'datetimepicker.css';
		
		$script = "window.jQuery_datetimepicker = {
			text : {
				prev : '&#9664;',
				next : '&#9654;',
				select_a_date: 'Select a date',
				select_a_date_range: 'Select a date range',
				today: 'Today',
				close: 'Close',
				ok: 'Ok',
				days		: [
					'". JText::_("MONDAY") ."',
					'". JText::_("TUESDAY") ."',
					'". JText::_("WEDNESDAY") ."',
					'". JText::_("THURSDAY") ."',
					'". JText::_("FRIDAY") ."',
					'". JText::_("SATURDAY") ."',
					'". JText::_("SUNDAY") ."',
				],
				daysShort	: [
					'". JText::_("MON") ."',
					'". JText::_("TUE") ."',
					'". JText::_("WED") ."',
					'". JText::_("THU") ."',
					'". JText::_("FRI") ."',
					'". JText::_("SAT") ."',
					'". JText::_("SUN") ."',
				],
				daysMin	: [
					'". substr(JText::_("MON"),0,2) ."',
					'". substr(JText::_("TUE"),0,2) ."',
					'". substr(JText::_("WED"),0,2) ."',
					'". substr(JText::_("THU"),0,2) ."',
					'". substr(JText::_("FRI"),0,2) ."',
					'". substr(JText::_("SAT"),0,2) ."',
					'". substr(JText::_("SUN"),0,2) ."',
				],
				months	: [
					'". JText::_("JANUARY") ."',
					'". JText::_("FEBRUARY") ."',
					'". JText::_("MARCH") ."',
					'". JText::_("APRIL") ."',
					'". JText::_("MAY") ."',
					'". JText::_("JUNE") ."',
					'". JText::_("JULY") ."',
					'". JText::_("AUGUST") ."',
					'". JText::_("SEPTEMBER") ."',
					'". JText::_("OCTOBER") ."',
					'". JText::_("NOVEMBER") ."',
					'". JText::_("DECEMBER") ."'
				],
				monthsShort	: [
					'". JText::_("JANUARY_SHORT") ."',
					'". JText::_("FEBRUARY_SHORT") ."',
					'". JText::_("MARCH_SHORT") ."',
					'". JText::_("APRIL_SHORT") ."',
					'". JText::_("MAY_SHORT") ."',
					'". JText::_("JUNE_SHORT") ."',
					'". JText::_("JULY_SHORT") ."',
					'". JText::_("AUGUST_SHORT") ."',
					'". JText::_("SEPTEMBER_SHORT") ."',
					'". JText::_("OCTOBER_SHORT") ."',
					'". JText::_("NOVEMBER_SHORT") ."',
					'". JText::_("DECEMBER_SHORT") ."'
				],
			}
		}";
		$doc->addScriptDeclaration($script);
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{

	}
	
	function buildJs()
	{

	}
}