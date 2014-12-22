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
* @addon		Fly bootstrap
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


class JDomHtmlFlyBootstrap extends JDomHtmlFly
{	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);

		JDom::_('framework.jquery');
		JDom::_('framework.jquery.noconflict');
		JDom::_('framework.bootstrap');

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