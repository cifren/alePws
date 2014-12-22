<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryTagmanager extends JDomFrameworkJquery
{	

	var $assetName = 'tagmanager';
	
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
		//Requires jQuery
		JDom::_('framework.jquery');
		
		$doc = JFactory::getDocument();
		
		// timepicker manager files needed
		$this->attachJs[] = 'tgman.js';
		$this->attachCss[] = 'tgman.css';			

		$script = "jQuery('document').ready(function(){
				jQuery('input[tgman]').tagsManagerByGiro();			
			});";
		$doc->addScriptDeclaration($script);
			
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
	
	function buildJs()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
}