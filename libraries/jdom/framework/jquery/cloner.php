<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryCloner extends JDomFrameworkJquery
{	

	var $assetName = 'cloner';
	
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
		
		$this->attachJs[] = 'jquery.cloner.js';
		$this->attachCss[] = 'jquery.cloner.css';
		
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{

	}
	
	function buildJs()
	{

	}
}