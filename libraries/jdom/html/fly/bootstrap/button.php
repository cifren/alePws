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
* @addon		Fly bootstrap button
* @author		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomHtmlFlyBootstrapButton extends JDomHtmlFlyBootstrap
{
	var $domClass = '';
	var $label = '';
	var $icon = '';
	var $selectors;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
			
		$this->arg('label'		, null, $args);
		$this->arg('icon'		, null, $args);
		$this->arg('selectors'	, null, $args);
		$this->arg('domClass'	, null, $args);
	}

	function build()
	{
		$this->domClass .= ' btn';
		$html = '<span <%CLASS%> <%SELECTORS%>><i class="'. $this->icon .'"></i> '. JText::_( $this->label ) .'</span>';

		return $html;
	}

}