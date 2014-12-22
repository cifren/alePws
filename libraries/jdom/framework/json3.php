<?php

/*
* @version		0.3.8
* @package		jForms
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JDomFrameworkJson3 extends JDomFramework
{

	var $assetName = 'json3';
	
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
		
		$this->attachJs = 'json3.js';
		
		static::$loaded[__METHOD__] = true;
	}

}