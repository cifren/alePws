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


class JDomHtmlFormInputTimepicker extends JDomHtmlFormInput
{
	var $size;
	var $time;
	var $start;
	var $end;
	var $step;
	var $format;
	var $uiFormat;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 * 	@size		: Input Size
	 * 
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('size'		, null, $args, '32');
		$this->arg('time'		, null, $args, '8:00');
		$this->arg('start'		, null, $args, null);
		$this->arg('end'		, null, $args, null);
		$this->arg('step'		, null, $args, 5);
		$this->arg('format'	, null, $args, 'H:i'); // options H hours, i minutes, unix = unixtimestamp
		$this->arg('timeFormat'	, null, $args, 'H:i'); // options H hours, i minutes, unix = unixtimestamp
		$this->arg('uiFormat'	, null, $args, 'H:i'); // options H hours, i minutes, unix = unixtimestamp
	}
	
	function build()
	{
	
		JDom::_('framework.bootstrap.timepicker');
				
		$this->addClass('timepickerByGiro input-mini');
		$this->addSelector('data-start',$this->start);
		$this->addSelector('data-end',$this->end);
		$this->addSelector('data-time',$this->time);
		$this->addSelector('data-step',$this->step);
		$this->addSelector('data-format',$this->format);
		$this->addSelector('data-ui-format',$this->uiFormat);

		$input =	'<input type="text" id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			.	' value="<%VALUE%>"'
			.	' size="' . $this->size . '"'
			.	' />' .LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>';

		$html = '
  <div class="input-prepend">
    <span class="add-on"><i class="datetime icon-clock"></i></span>
    '. $input .'
  </div>';

		return $html;
	}

}