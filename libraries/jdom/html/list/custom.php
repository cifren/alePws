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
* @addon		Fly custom list
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


class JDomHtmlListCustom extends JDomHtmlList
{
	var $domClass = 'table table-striped table-bordered table-condensed ';	
	var $assetName = 'custom';
	var $formItemId;
	
	static $listLoaded;
	
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->attachCss[] = 'custom.css';
		$this->arg('loadItemsByJs', null, $args, true);
		$this->arg('formItemId', null, $args, '');
	}
	
	function build()
	{
		$html = '';
		
				
		return $html;
	}

	function buildJS(){

	}	
}