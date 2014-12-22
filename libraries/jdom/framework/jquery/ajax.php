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


class JDomFrameworkJqueryAjax extends JDomFrameworkJquery
{
	var $assetName = 'ajax';
	
	var $attachJs = array();
	var $attachCss = array();
	
	protected static $loaded = array();	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *  @lib		: Array - jQuery UI libraries
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
		
		JDom::_('framework.jquery');

		// language strings
	//	JText::script('');
			
		
		$this->attachJs[] = 'ajax.js';
		$this->attachCss[] = 'ajax.css';
		
		static::$loaded[__METHOD__] = true;
	}

}