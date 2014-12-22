<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkBootstrapTimepicker extends JDomFrameworkBootstrap
{	

	var $assetName = 'timepicker';
	
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
		
		// timepicker manager files needed
		$this->attachJs[] = 'timepicker.js';
		$this->attachCss[] = 'timepicker.css';			
		
		$doc = JFactory::getDocument();
		$script = '
			jQuery(document).ready(function(){
				jQuery(".timepickerByGiro").each(function(){
					jQuery(this).timepickerByGiro();
				});
			});
		';
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