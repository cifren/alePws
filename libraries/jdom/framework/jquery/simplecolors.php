<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJquerySimplecolors extends JDomFrameworkJquery
{	

	var $assetName = 'minicolors';
	
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
		$doc = JFactory::getDocument();

		// addresspicker manager files needed
		$this->attachJs[] = 'jquery.simplecolors.js';
		$this->attachCss[] = 'jquery.simplecolors.css';

		$doc = JFactory::getDocument();		

		$script = "
				jQuery(document).ready(function (){
					jQuery('select.simplecolors').simplecolors();
				});		
		";
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