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


class JDomHtmlObject extends JDomHtml
{
	var $fallback = 'table';	//Used for default

	var $attachJs = array();
	var $attachCss = array();
	var $selectors;
	
	protected $form;
	protected $dataObject;
	protected $fieldset;
	protected $fieldsToRender;
	protected $actions;
	protected $tmplEngine;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 * 	@selectors	: selectors
	 * 	@jformFields: array of jform fields 
	 * 	@fieldsToRender: array of fields  to render
	 * 	@actions	: default array()
	 * 	@dataObject	: Object
	 * 	@tmplEngine	: JS Template Engine, default null
	 * 	@domClass	: CSS class
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('selectors'		, null, $args);
		$this->arg('fieldset'		, null, $args, false);
		$this->arg('fieldsetName'	, null, $args);
		$this->arg('form'			, null, $args);
		$this->arg('dataObject'		, null, $args);
		$this->arg('fieldsToRender'	, null, $args);
		$this->arg('actions'		, null, $args, array());
		$this->arg('tmplEngine'		, null, $args, 'doT');
		$this->arg('domClass'		, null, $args);


		if(!$this->fieldset AND $this->form){
			$this->fieldset = $this->form->getFieldset($this->fieldsetName);
		}
	}

	protected function parseVars($vars)
	{
		return parent::parseVars(array_merge(array(
			'STYLE'		=> $this->buildDomStyles(),
			'CLASS'			=> $this->buildDomClass(),		//With attrib name
			'SELECTORS'		=> $this->buildSelectors(),
		), $vars));
	}
}