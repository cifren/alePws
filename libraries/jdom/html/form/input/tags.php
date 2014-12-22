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


class JDomHtmlFormInputTags extends JDomHtmlFormInput
{
	var $size;
	var $min;
	var $max;
	var $sourcetags;
	var $separator;
	var $capitalizeFirstLetter;

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

		$this->arg('size'		, 6, $args, '32');
		$this->arg('min'		, null, $args);
		$this->arg('max'		, null, $args);
		$this->arg('sourcetags'	, null, $args);
		$this->arg('separator'	, null, $args, ',');
		$this->arg('capitalizeFirstLetter'	, null, $args, ',');
	}
	
	function build()
	{		
		JDom::_('framework.jquery.tagmanager');
		
		
		$html =	'<input tgman="'. $this->sourcetags .'" data-capitalize-first-letter="'. $this->capitalizeFirstLetter .'" data-separator="'. $this->separator .'" data-max="'. $this->max .'" data-min="'. $this->min .'" type="text" name="<%INPUT_NAME%>" id="<%DOM_ID%>" <%STYLE%><%CLASS%><%SELECTORS%>'
			.	' value="<%VALUE%>"'
			.	' size="' . $this->size . '"'
			.	'/>' .LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>';

		return $html;
	}

}