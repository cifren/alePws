<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryMsg extends JDomFrameworkJquery
{	

	var $assetName = 'msg';
	
	var $attachJs = array();
	var $attachCss = array();
	
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
		JDom::_('framework.jquery.center');
		
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
				
		JText::script("PLG_JDOM_MSG_DEFAULT_MESSAGE");
		
		$base = JURI::root(true);		
		$msg = '<img class="loading_img" src="'. $base .'/libraries/jdom/assets/msg/img/spinner-big.gif"/><p class="loading_txt">'. JText::_("PLG_JDOM_MSG_DEFAULT_MESSAGE") .'</p>';
		
		$script = "
			window.jQuery_msg = {};
			window.jQuery_msg.z = 100000000;
			window.jQuery_msg.content = '". $msg ."';
			window.jQuery_msg.bgPath = '". $base ."/libraries/jdom/assets/msg/img/blank.gif';
			";
			
		// workaround to preload the message img
		$script .= "
			jQuery(document).ready(function(){
				jQuery('body').append('<div style=\"display: none;\">". $msg ."</div>');
			});
		";
		
		$doc->addScriptDeclaration($script);
		$this->attachJs[] = 'jquery.msg.js';
		$this->attachCss[] = 'jquery.msg.css';
		
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{

	}
	
	function buildJs()
	{

	}
}