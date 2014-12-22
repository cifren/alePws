<?php
/**                               ______________________________________________
*                          o O   |                                              |
*                 (((((  o      <     JDom Class - Cook Self Service library    |
*                ( o o )         |______________________________________________|
* --------oOOO-----(_)-----OOOo---------------------------------- www.j-cook.pro --- +
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JDomHtmlFormInputDatetimepicker extends JDomHtmlFormInput
{

	var $separator;
	var $style;
	var $mode;
	var $minView;
	var $maxView;
	var $startView;
	var $min;
	var $max;
	var $filter;
	var $firstDay;
	var $timeFormat;
	var $minuteStep;
	var $showDaysNotInMonth;
	var $showDisabledTimes;
	var $today;
	var $calendars;
	var $format;
	var $uiFormat;
	var $autoclose;
	var $specialDates;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *
	 *	@format	: Date Format
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('separator'		, null, $args, " | "); // Is used for joining separate dates in multiple mode and first/last dates in range mode
		$this->arg('style'			, null, $args, 'popup'); // inline / popup
		$this->arg('mode'			, null, $args, 'single'); // single/multiple/range
		$this->arg('minView'		, null, $args, 'times'); // times/days/months/years
		$this->arg('maxView'		, null, $args, 'years'); // times/days/months/years
		$this->arg('startView'		, null, $args, 'days');	//view: days/months/years
		$this->arg('min'			, null, $args, null); //min: null/object/string
		$this->arg('max'			, null, $args, null); //max: null/object/string
		$this->arg('firstDay'		, null, $args, 0); // 0 -> 6 // sunday to saturday
		$this->arg('timeFormat'		, null, $args, 24); // 12 / 24 // 12 (AM-PM) or 24 hours
		$this->arg('minuteStep'		, null, $args, 5);
		$this->arg('showDaysNotInMonth'		, null, $args, false);
		$this->arg('showDisabledTimes'		, null, $args, false);
		$this->arg('today'			, null, $args, null);
		$this->arg('calendars'		, null, $args, 1);
		$this->arg('format'			, null, $args, "Y-m-d"); // Date format PHP standard supported
		$this->arg('uiFormat'		, null, $args, null); // Date format PHP standard supported
		$this->arg('autoclose'					, null, $args, null); // hide_on_select
		$this->arg('specialDates'				, null, $args, null);
	
		$this->parameters = array(
			'separator',
			'style',
			'mode',
			'minView',
			'maxView',
			'startView',
			'min',
			'max',
			'firstDay',
			'timeFormat',
			'minuteStep',
			'showDaysNotInMonth',
			'showDisabledTimes',
			'today',
			'calendars',
			'format',
			'uiFormat',
			'autoclose',
			'specialDates'
		);
	}

	function build()
	{	
		static $jsLoaded;
		
		$dataValue = $this->formatDate($this->dataValue,$this->format);
		$uiValue = $this->formatDate($this->dataValue,$this->uiFormat);
		
		if($this->mode == 'multiple' AND $this->autoclose === null){
			$this->autoclose = 'false';
		}
	
		$inputId = $this->getInputId();

		foreach($this->parameters as $pa){
			if(!(!empty($this->$pa) OR $this->$pa === false)){
				continue;
			}
			$pieces = preg_split('/(?=[A-Z])/',$pa);

			$this->addSelector('data-'. implode('-',$pieces), $this->$pa);
		}
		
		$this->addSelector('data-ui-Target', '.uipicker');
		$this->addSelector('readonly', 'readonly');
		
		$this->addClass('ckdatetimepicker');
		if($this->mode == 'single'){
			$this->addClass('input-medium');
		} else {
			$this->addClass('input-xlarge');
		}
		
		$html =	'<div class="input-append date">'
			.	'<input id="<%DOM_ID%>" type="text" value="'. $dataValue .'" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>>';
			
	
		if(!empty($this->uiFormat) AND $this->uiFormat != $this->format){
			$html .= '<input id="<%DOM_ID%>_uipicker" class="uipicker input-medium fakeInput condRulesExcludeDisabled" disabled="disabled" type="text" value="'. $uiValue .'" <%STYLE%>>';
		}
		
		$html .= '<span class="add-on"><i style="cursor: pointer;" class="icomoon icon-calendar"></i></span>';
		
		if($this->mode == 'multiple'){
			$html .= '<span id="<%DOM_ID%>_uipicker" class="ckdatetimepicker_values dtp-values" <%STYLE%>></span>';
		}
		$html .=	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>'
			.	'</div>';
			
		
		if(!$jsLoaded){
			JDom::_('framework.jquery.datetimepicker');
			
			$doc = JFactory::getDocument();
		
			$script = '
			function dateTimePickerEnabler($obj){
				if(!($obj instanceof jQuery)){
					$obj = jQuery($obj);
				}
				
				$obj.find("input.ckdatetimepicker").each(function(){
					var that = this,
					opts = (typeof window.jQuery_datetimepicker != "undefined") ? window.jQuery_datetimepicker : {};
					
					jQuery(this).datetimepickerByGiro(opts);
					jQuery(this).parent().find("i.icon-calendar").parent().css("cursor","pointer").on("click",function(e){
						e.preventDefault();
						e.stopPropagation();						
						jQuery(that).trigger("click");
					});
				});
			}
			
			jQuery(document).ready(function(){				
				dateTimePickerEnabler("body");				
			});';
			
			$doc->addScriptDeclaration($script);			
			$jsLoaded = true;
		}
		
		return $html;		
	}

	
	public function formatDate($rawDate,$format){
		if((string)intval($rawDate) != $rawDate){
			$rawDate = strtotime($rawDate);
		}
		
		if(empty($rawDate)){
			return '';
		}
		
		try {
			jimport("joomla.utilities.date");
			$date = JFactory::getDate($rawDate);
			$formatedDate = $date->format($format);

			$config = JFactory::getConfig();
			// If a known filter is given use it.
			switch (strtoupper(($this->filter)))
			{
				case 'SERVER_UTC':
					// Convert a date to UTC based on the server timezone.
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($rawDate, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Format the date string.
					$formatedDate = $date->format($format, true);
					break;

				case 'USER_UTC':
					// Convert a date to UTC based on the user timezone.

					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($rawDate, 'UTC');
					$user = JFactory::getUser();
					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Format the date string.
					$formatedDate = $date->format($format, true);
					break;
			}
		} catch (Exception $e) {
			$formatedDate = "";
			// $errors[] = $e->getMessage();
		}
		
		return $formatedDate;
	}
}