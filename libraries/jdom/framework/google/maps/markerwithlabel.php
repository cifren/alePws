<?php
/**                               ______________________________________________
*                          o O   |                                              |
*                 (((((  o      <     JDom Class - Cook Self Service library    |
*                ( o o )         |______________________________________________|
* --------oOOO-----(_)-----OOOo---------------------------------- www.j-cook.pro --- +
* @version		2.6.1
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
* @addon		Google maps markerwithlabel API v3
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


class JDomFrameworkGoogleMapsMarkerwithlabel extends JDomFrameworkGoogleMaps
{	
	var $assetName = 'markerwithlabel';
	
	protected static $loaded = array();	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
	}
	
	function build()
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}
		
		JDom::_('framework.google.maps');		
		$this->attachJs[] = 'markerwithlabel.js';
		$this->attachCss[] = 'markerwithlabel.css';
		
		
		static::$loaded[__METHOD__] = true;
	}

}