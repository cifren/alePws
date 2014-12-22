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


class JDomHtmlFormInputCheckbox extends JDomHtmlFormInput
{
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *	@inputValue		: Input value
	 *	@inputLabel		: Label for input
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->arg('inputValue'		, 1, $args);
		$this->arg('inputLabel'		, null, $args);	
	}

	function build()
	{
		$html = '';

		
		$inputVal = 1;
		if($this->inputValue != '' AND is_string($this->inputValue)){
			$inputVal = htmlspecialchars($this->inputValue, ENT_COMPAT, 'UTF-8');
		}

		$checked = ($this->dataValue !== null AND $this->dataValue == $inputVal);
		
		$html =	'<input type="checkbox" id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			.	' value="'. $inputVal .'"'
			.	($checked?' checked="checked"':'')
			.	'/>';
			
		if(isset($this->inputLabel) AND $this->inputLabel != ''){
			$html .= '<label for="<%DOM_ID%>">'. $this->inputLabel .'</label>';
		}
			
		return $html;
	}
}
