<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryGeolocation extends JDomFrameworkJquery
{	

	var $assetName = 'geolocation';
	
	var $attachJs = array();
	var $attachCss = array();
	var $success;
	var $timedout;
	var $error;
	var $addOnReady;
	
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
		
		$this->arg('success'	, null, $args, 'function(){}');
		$this->arg('timedout'	, null, $args, 'function(){}');
		$this->arg('error'		, null, $args, 'function(){}');
		$this->arg('addOnReady'	, null, $args, false);
	}
	
	function build()
	{	
		// Only load once
		if (empty(static::$loaded[__METHOD__])){
			$this->attachJs[] = 'jquery.geolocation.js';
			static::$loaded[__METHOD__] = true;
		}
		
		$hash = MD5($this->success . $this->timedout . $this->error);
		// Only load once
		if (!empty(static::$loaded[$hash])){
			return;
		}
		$doc = JFactory::getDocument();
		
		$script = "
		jQuery(document).ready(function(){
			jQuery.geolocation.get({
				success: ". $this->success .",
				timedout: ". $this->timedout .",
				error: ". $this->error ."
			});
		});";
		
		if($this->addOnReady){
			$doc->addScriptDeclaration($script);
		}
		
		static::$loaded[$hash] = true;		
	}
	
	function buildCss()
	{

	}
	
	function buildJs()
	{

	}
}