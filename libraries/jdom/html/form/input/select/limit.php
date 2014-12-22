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


class JDomHtmlFormInputSelectLimit extends JDomHtmlFormInputSelect
{
	protected $pagination;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *  @pagination : Joomla pagination
	 * 
	 */
	function __construct($args)
	{
		parent::__construct($args);
		$this->arg('pagination', 	null, $args);
	}

	function build()
	{
		if (!$pagination = $this->pagination)
			return "<strong>Error</strong> : Pagination not found";
		
		
		$app = JFactory::getApplication();
		
		$list = array();
		// Make the option list.
		$limits = array(5,10,15,20,25,30,50,100);
		foreach($limits as $i){
			$list[] = array(
				'value' => (string)$i,
				'text' => $i
			);
		}
		$limit = $pagination->get('limit');
		
		if($limit > 0 AND !in_array($limit, $limits)){
			$lastStepTxt = $lastStep = $limit;
		} else {
			$lastStep = 0;
			$lastStepTxt = JText::_('JALL');
		}
		
		$list[] = array(
			'value' => (string)$lastStep,
			'text' => $lastStepTxt
		);
			
		$dataValue = (string)($pagination->get('viewall') ? 0 : $limit);

		$onChange = 'Joomla.submitform();';		
		if (!$app->isAdmin())
			$onChange = 'this.form.submit();';

		$html = JDom::_('html.form.input.select.combo', array_merge($this->options, array(
			'domClass' => 'input-mini ' . $this->options['domClass'],
			'size' => '1',
			'domId' => $pagination->prefix . 'limit',
		
		 // TODO investigate weird behaviour, multiple Joomla.submit if UNCOMMENTED
		/*	'selectors' => array(
				'onchange' => $onChange
			), */
			
			'list' => $list,
			'listKey' => 'value',
			'dataValue' => (string)$dataValue,
		
		)));

		return $html;
	}
}
