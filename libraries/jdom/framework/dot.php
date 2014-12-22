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

class JDomFrameworkDot extends JDomFramework
{

	var $assetName = 'dot';
	var $attachJs = array();
	protected static $loaded = array();	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *  @jdn		: Load from the jdn
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
		$doc = JFactory::getDocument();
		$this->attachJs[] = 'doT.js';

		/*
		$script = "";
		$doc->addScriptDeclaration($script);	
		*/
		
		static::$loaded[__METHOD__] = true;
	}

}