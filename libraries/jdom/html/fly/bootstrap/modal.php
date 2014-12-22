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
* @addon		Fly bootstrap modal
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


class JDomHtmlFlyBootstrapModal extends JDomHtmlFlyBootstrap
{
	var $domClass = '';
	var $domId = '';
	var $title = '';
	var $body = '';
	var $footer;
	var $selectors = '';

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		$footer = '<a class="btn btn-cancel" data-dismiss="modal" aria-hidden="true">'. JText::_("JCANCEL") .'</a>'
			.	'<a class="btn btn-primary btn-apply">'. JText::_("PLG_JDOM_APPLY") .'</a>';
			
		$this->arg('footer'		, null, $args, $footer);
		$this->arg('title'		, null, $args);
		$this->arg('body'		, null, $args);
		$this->arg('domId'		, null, $args);
		$this->arg('domClass'	, null, $args);
		$this->arg('selectors'	, null, $args);
	}

	function build()
	{
		$this->domClass .= ' modal hide fade';
		
		$header = '';
		if(!empty($this->title)){
			$header = '
		  <div class="modal-header">
			<a href="#" class="close" onclick="return false;" data-dismiss="modal" aria-hidden="true">&times;</a>
			<h3 id="'. $this->domId .'_label">'. $this->title .'</h3>
		  </div>			
			';
		}
		
		$footer = '';
		if(!empty($this->footer)){
			$footer = '<div class="modal-footer">'. $this->footer .'</div>';
		}
		
		$html =
		'<div id="'. $this->domId .'" <%CLASS%> <%SELECTORS%> tabindex="-1" aria-labelledby="'. $this->domId .'_label" role="dialog" aria-hidden="true">
		  '. $header .'
		  <div style="height: 80%; overflow-y: auto;" class="modal-body">'. $this->body .'</div>  
		  '. $footer .'
		</div>';
		
		return $html;
	}

}