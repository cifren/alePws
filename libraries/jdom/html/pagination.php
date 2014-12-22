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

class JDomHtmlPagination extends JDomHtml
{
	var $pagination;
	var $showLimit;
	var $showCounter;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 * 	@pagination : Joomla pagination object
	 *	@showLimit	: show the selectbox to choose how many elements per page
	 *	@showCounter: show the current number of page and the total (ie: Page 1 of 4)
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('pagination'			, 2, $args);
		$this->arg('showLimit'			, 3, $args, true);
		$this->arg('showCounter'		, 4, $args, true);

	}

	function build()
	{
		$pagination = $this->pagination;

		$app = JFactory::getApplication();

		$list = array();		//ISSET() for warning prevention
		$list['prefix']			= (isset($pagination->prefix)?$pagination->prefix:null);
		$list['limit']			= $pagination->limit;
		$list['limitstart']		= $pagination->limitstart;
		$list['total']			= $pagination->total;
		$list['limitfield']		= $this->getLimitBox($pagination->limit, $pagination->prefix); 
		$list['pagescounter']	= $pagination->getPagesCounter();
		$list['pageslinks']		= $pagination->getPagesLinks();

		$chromePath	= JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';
		if (file_exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('pagination_list_footer')) {
				return pagination_list_footer($list);
			}
		}

		$html = "<div class=\"list-footer-pagination\">\n";

		if ($this->showLimit)
		{
			$langDisplayNum = $this->JText('JGLOBAL_DISPLAY_NUM');			
			$html .= "\n<div class=\"limit\">". $langDisplayNum .$list['limitfield']."</div>";
		}

		$html .= $list['pageslinks'];

		if ($this->showCounter)
			$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

		$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}


	/**
	 * Creates a dropdown box for selecting how many records to show per page.
	 *
	 * @return  string  The HTML for the limit # input box.
	 *
	 * @since   11.1
	 */
	public function getLimitBox($limit = null, $prefix = null)
	{
		$app = JFactory::getApplication();
		
		// Initialise variables.
		$options = array();

		// Make the option list.
		$limits = array(5,10,15,20,25,30,50,100);
		foreach($limits as $st){
			$options[] = JHtml::_('select.option', "$st");
		}
		
		if($limit > 0 AND !in_array($limit, $limits)){			
			$options[] = JHtml::_('select.option', $limit);
		} else {
			$options[] = JHtml::_('select.option', '0', JText::_('JALL'));
		}
		$selected = $limit;

		// Build the select list.
		if ($app->isAdmin())
		{
			$html = JHtml::_(
				'select.genericlist',
				$options,
				$prefix . 'limit',
				'class="inputbox" size="1" onchange="Joomla.submitform();"',
				'value',
				'text',
				$selected
			);
		}
		else
		{
			$html = JHtml::_(
				'select.genericlist',
				$options,
				$prefix . 'limit',
				'class="inputbox" size="1" onchange="this.form.submit()"',
				'value',
				'text',
				$selected
			);
		}
		return $html;
	}
		

}