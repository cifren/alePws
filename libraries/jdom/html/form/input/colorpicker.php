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


class JDomHtmlFormInputColorpicker extends JDomHtmlFormInput
{

	var $layout;
	
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
	 * 	@domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->arg('layout'		, 6, $args, 'full');
	}

	function build()
	{
		JDom::_('framework.jquery.colorpicker');
		
		$html = $this->buildControl();
		return $html;
	}
	
	function buildControl()
	{
		$id = $this->getInputId();
		$html = '';

		//Create the input
		$dom = JDom::getInstance('html.form.input.text', array_merge($this->options, array(
			'selectors' => array(
				'data-layout' => $this->layout
			)
		)));

		$dom->addClass('input-mini colorPicker-input');
		$htmlInput = $dom->output();
	
		//Create the color
		$htmlColor = JDom::_('html.fly.color', array_merge($this->options, array(
			'width' => 18,
			'height' => 18,
		)));
		

		$html = '';

		//Render the control
		if ($this->hidden)
			$html .= $htmlInput .LN; //Place the hidden input out of the control

			
		$html .= '<div class="btn-group colorPicker">' .LN;					
		$html .= '<div class="input-prepend input-append">' .LN;

		$html .= '<span class="add-on">' . $htmlColor . '</span>' .LN;
		
		if (!$this->hidden)
		{			
			//Input mini
			$html .= $htmlInput .LN;	
		}
		
		//Close the control		
		$html .= '</div>' .LN;
		$html .= '</div>' .LN;
		

		return $html;

	}

}