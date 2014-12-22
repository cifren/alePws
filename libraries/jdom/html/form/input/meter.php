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


class JDomHtmlFormInputMeter extends JDomHtmlFormInput
{
	var $active = false;
	var $animated = true;
	var $color;
	
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

		$this->arg('active'	, 6, $args);
		$this->arg('animated', 6, $args);
		$this->arg('color'	, 6, $args);
		
	}
	
	function build()
	{
		// Initialize some field attributes.
		$width = !empty($this->dataValue) ? ' style="width:' . $this->dataValue . ';"' : '';
		
		
		$color = '';
		if(!empty($this->color)){
			$color = $this->color;
			if(strpos($this->color,'#') === false){
				$color = '#'. $color;
			}
			$color = ' background-color: ' . $color . ';';
		}
		
		$class = 'progress';
		$class .= $this->animated ? ' progress-striped' : '';
		$class .= $this->active ? ' active' : '';

		$value = (float) $this->dataValue;

		$html[] = '<div class="'. $class .'" >';
		$html[] = '		<div class="bar" style="font-style: italic; width: ' . strval($value) . '%;' . $color . '"></div>';
		$html[] = '</div>';
		$html[] = '<input type="hidden" id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%> value="'. $value .'" />';

		return implode('', $html);
	}

}