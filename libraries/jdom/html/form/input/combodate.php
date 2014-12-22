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

class JDomHtmlFormInputCombodate extends JDomHtmlFormInput
{
	protected $dateFormat;
	protected $uiFormat;
	protected $filter;

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
	 *	@dateFormat	: Date Format
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('dateFormat'	, null, $args, "Y-m-d");
		$this->arg('uiFormat'	, null, $args, "Y-m-d");
		$this->arg('filter'		, null, $args);

	}

	function build()
	{
		$dateFormat = $this->dateFormat;

		//JDate::toFormat() is deprecated. CONVERT Legacy Joomla Format
		//Minutes : ‰M > i
		$dateFormat = str_replace("%M", "i", $dateFormat);
		//remove the %
		$dateFormat = str_replace("%", "", $dateFormat);
	
	
		$formatedDate = $this->dataValue;

		if ($this->dataValue
		&& ($this->dataValue != "0000-00-00")
		&& ($this->dataValue != "00:00:00")
		&& ($this->dataValue != "0000-00-00 00:00:00"))
		{
			jimport("joomla.utilities.date");

			$date = JFactory::getDate($this->dataValue);
			$formatedDate = $date->format($dateFormat);

			$config = JFactory::getConfig();
			// If a known filter is given use it.
			switch (strtoupper(($this->filter)))
			{
				case 'SERVER_UTC':
					// Convert a date to UTC based on the server timezone.
					if (intval($this->dataValue))
					{
						// Get a date object based on the correct timezone.
						$date = JFactory::getDate($this->dataValue, 'UTC');
						$date->setTimezone(new DateTimeZone($config->get('offset')));

						// Format the date string.
						$formatedDate = $date->format($dateFormat, true);
					}
					break;

				case 'USER_UTC':
					// Convert a date to UTC based on the user timezone.
					if (intval($this->dataValue))
					{
						// Get a date object based on the correct timezone.
						$date = JFactory::getDate($this->dataValue, 'UTC');
						$user = JFactory::getUser();
						$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

						// Format the date string.
						$formatedDate = $date->format($dateFormat, true);
					}
					break;
			}
		}
		else {
			$formatedDate = "";
		}

		// accepted datetime elements
		$datetime_elements = array(
			'd', // Day of the month, 2 digits with leading zeros: 01 to 31
			'm', // Numeric representation of a month, with leading zeros: 01 through 12
			'M', // A short textual representation of a month, three letters: Jan through Dec
			'F', // A full textual representation of a month, such as January or March: January through December
			'Y', // A full numeric representation of a year, 4 digits: 1999 or 2003
			'h', // 12-hour format of an hour with leading zeros: 01 through 12
			'H', // 24-hour format of an hour with leading zeros: 00 through 23
			'i', // Minutes with leading zeros: 00 to 59
			's' // Seconds, with leading zeros: 00 to 59
		);
		
		// find date time elements
		$elements = array();
		foreach($datetime_elements as $ele){
			$elePos = strpos($this->uiFormat, $ele);
			if($elePos !== false){
				$elements[$elePos] = $ele;
			}
		}		
		ksort($elements);

		//Create the hidden input
		$dom = JDom::getInstance('html.form.input.hidden', array_merge($this->options, array(
			'dataValue' => $formatedDate
		)));
		$dom->addClass('input-medium');
		$htmlInput = $dom->output();
		
		$lists = $this->getLists($ele);
		$opt = $this->options;
		// create the other combo for dates
		foreach($elements as $ele){		
			//Create the hidden input
			$dom = JDom::getInstance('html.form.input.select', array_merge($this->options, array(
						'domId' => $opt['domId'] .'_'. $ele,
						'domName' => '',
						'domClass' => 'combodate',
						'dataValue' => (string)$this->value,
						'list' => $lists[$ele],
						'size' => 1
					)));	
			$htmlInput .= $dom->output();		
		}	
		
		$html = '';
		$html .= '<div class="btn-group">';
		
		$html .= '<div class="input-append">' .LN;
		$html .= $htmlInput .LN;
		$html .= '</div>' .LN;

		$html .= '</div>';

		return $html;		
	}
	
	function getLists($type){
		
		$lists = array();
		$months = array('JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER');
		
		$lists["d"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_DAY");
		$lists["d"][] = $opt;
		for($i=0; $i <= 31;$i++){		
			$opt = new stdClass();
			$opt->id = $i;
			$opt->text = $i;		
			if($i < 10){
				$opt->id = '0'.$i;
				$opt->text = '0'.$i;
			}
			
			$lists["d"][] = $opt;
		}
		
		$lists["m"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_MONTH");
		$lists["m"][] = $opt;
		for($i=0; $i <= 12;$i++){		
			$opt = new stdClass();
			$opt->id = $i;
			$opt->text = $i;		
			if($i < 10){
				$opt->id = '0'.$i;
				$opt->text = '0'.$i;
			}
			
			$lists["m"][] = $opt;
		}		
		
		$lists["M"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_MONTH");
		$lists["M"][] = $opt;		
		for($i=0; $i < 12;$i++){		
			$opt = new stdClass();
			$opt->id = $i +1;
			$opt->text = JText::_('JDOM_'. mb_substr($months[$i], 0, 3));		
			if($i < 10){
				$opt->id = '0'.($i +1);
			}
			
			$lists["M"][] = $opt;
		}	
		
		$lists["F"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_MONTH");
		$lists["F"][] = $opt;
		for($i=0; $i < 12;$i++){		
			$opt = new stdClass();
			$opt->id = $i +1;
			$opt->text = JText::_('JDOM_'. $months[$i]);		
			if($i < 10){
				$opt->id = '0'.($i +1);
			}
			
			$lists["F"][] = $opt;
		}
		
		$lists["Y"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_YEAR");
		$lists["Y"][] = $opt;
		for($i=1920; $i <= 2020;$i++){		
			$opt = new stdClass();
			$opt->id = $i;
			$opt->text = $i;		
		
			$lists["Y"][] = $opt;
		}
		
		$lists["h"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_HOUR");
		$lists["h"][] = $opt;
		for($i=0; $i < 12;$i++){		
			$opt = new stdClass();
			$opt->id = $i +1;
			$opt->text = ($i +1) . 'am';		
			if($i < 10){
				$opt->id = '0'.($i +1);
				$opt->text = '0'.($i +1) . 'am';
			}
			
			$lists["h"][] = $opt;
		}
		for($i=0; $i < 12;$i++){		
			$opt = new stdClass();
			$opt->id = $i +1;
			$opt->text = ($i +1) . 'pm';		
			if($i < 10){
				$opt->id = '0'.($i +1);
				$opt->text = '0'.($i +1) . 'pm';
			}
			
			$lists["h"][] = $opt;
		}
		
		$lists["H"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_HOUR");
		$lists["H"][] = $opt;
		for($i=0; $i <= 24;$i++){		
			$opt = new stdClass();
			$opt->id = $i;
			$opt->text = $i;		
			if($i < 10){
				$opt->id = '0'.$i;
				$opt->text = '0'.$i;
			}
			
			$lists["H"][] = $opt;
		}	
		
		$lists["i"] = array();
		$lists["s"] = array();
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_MINUTE");
		$lists["i"][] = $opt;
		$opt = new stdClass();
		$opt->id = '';
		$opt->text = JText::_("JDOM_SELECT_SECOND");
		$lists["s"][] = $opt;
		for($i=0; $i < 60;$i+=5){		
			$opt = new stdClass();
			$opt->id = $i;
			$opt->text = $i;		
			if($i < 10){
				$opt->id = '0'.$i;
				$opt->text = '0'.$i;
			}

			$lists["i"][] = $opt;
			$lists["s"][] = $opt;
		}
		
		return $lists;
	}
/*
	function buildJs()
	{
		$config = array();
		if ($this->submitEventName == 'onchange')
		{
			$jsEvent = $this->getSubmitAction();

			$config['onClose'] = "function(cal){if(cal.dateClicked){"
			. $jsEvent
			. "}cal.hide();}";
		}
				
		$jsonConfig = "";
		foreach($config as $key => $quotedValue)
			$jsonConfig .= "," . $key . ":" . $quotedValue;
	
		// Load the calendar behavior
		JHtml::_('behavior.calendar');
		JHtml::_('behavior.tooltip');
		
		$jsonConfig .= ",firstDay: " . JFactory::getLanguage()->getFirstDay();
		
		$id = $this->getInputId();
		$format = $this->legacyDateFormat($this->dateFormat);
		
		$js = 'window.addEvent(\'domready\', function() {if($("' . $id . '")) Calendar.setup({
					// Id of the input field
					inputField: "' . $id . '",
					// Format of the input field
					ifFormat: "' . $format . '",
					// Trigger for the calendar (button ID)
					button: "' . $id . '-btn",
					// Alignment (defaults to "Bl")
					align: "Tl",
					singleClick: true' . $jsonConfig . '
					});});';
					
		$this->addScriptInline($js, true);
	}
	*/
}