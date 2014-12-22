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


class JDomHtmlFormLabel extends JDomHtmlForm
{
	public $domId;
	public $label;
	public $markup;
	public $required;
	public $description;

	protected static $loaded = array();
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 * 	@domID		: database field name
	 * 	@label		: label caption (JText)
	 *  @domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('dataKey'	, null, $args);
		$this->arg('dataObject'	, null, $args);
		$this->arg('required'	, null, $args, false);
		$this->arg('description', null, $args, '');
		$this->arg('domId'		, null, $args);
		$this->arg('label'		, null, $args);
		$this->arg('domClass'	, null, $args);
		$this->arg('selectors'	, null, $args);
		$this->arg('formControl', null, $args);
		$this->arg('formGroup', null, $args);
		$this->arg('markup', null, $args, 'label');
		$this->arg('prefix_id', null, $args, '');

		if (!$this->domId)
			$this->domId = $this->getInputId();
			
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}
		
		$script = 'jQuery(".hasTooltip").tooltip({"html": true});';
		$this->addScriptInline($script, true);		
		
		
		static::$loaded[__METHOD__] = true;			
		
	}

	function build()
	{
		$required = '';
		if($this->required){
			$required = '<span class="star"> *</span>';
		}
		
		if($this->description != ''){
			$this->domClass .= ' hasTooltip';
			$description = JText::_($this->description);

			$this->addSelector('title',$description);
			$this->addSelector('data-container','body');
		}
		
		$for = '';
		if($this->markup == 'label'){
			$for = 'for="<%DOM_ID%>"';
		}
		
		$html = '<'. $this->markup .' id="<%DOM_ID%>_label" '. $for .' <%CLASS%><%SELECTORS%>>'
			.	$this->JText($this->label) . $required
			.	'</'. $this->markup .'>';
		return $html;
	}
		
	protected function parseVars($vars)
	{
		return parent::parseVars(array_merge(array(
			'DOM_ID'	=> $this->domId,
			'STYLE'		=> $this->buildDomStyles(),
			'CLASS'		=> $this->buildDomClass(),		//With attrib name
			'SELECTORS'	=> $this->buildSelectors(),
		), $vars));
	}
	
	function buildJS()
	{

	}	
}