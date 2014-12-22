<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkBootstrapRadiotobtn extends JDomFrameworkBootstrap
{	

	var $assetName = 'radiotobtn';
	
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
		
		/* example arguments */
		$this->arg('options1'	, null, $args);
		$this->arg('options2'	, null, $args);		
	}
	
	function build()
	{	
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}
		
		JDom::_('framework.bootstrap');		
		
		// addresspicker manager files needed
		$this->attachJs[] = 'jquery.radiotobtn.js';
		$this->attachCss[] = 'jquery.radiotobtn.css';

		$doc = JFactory::getDocument();
		$script = '
			jQuery(document).ready(function(){
				jQuery("body").find(".radio.btn-group").radioToBtn();
			});
		';
		$doc->addScriptDeclaration($script);
		
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{
	
	}
	
	function buildJs()
	{
	
	}
}