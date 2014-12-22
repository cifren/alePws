<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkBootstrapBootalerts extends JDomFrameworkBootstrap
{	

	var $assetName = 'bootalerts';
	
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
		$this->attachJs[] = 'bootalerts.js';
		$this->attachCss[] = 'bootalerts.css';

		$doc = JFactory::getDocument();
		$script = '
			jQuery(document).ready(function(){
				jQuery("body").bootAlertsByGiro("addOpts",{
					text: {
						ok: "'. JText::_("JDOM_BOOTALERTS_OK") .'",
						cancel: "'. JText::_("JDOM_BOOTALERTS_CANCEL") .'",
						close: "'. JText::_("JDOM_BOOTALERTS_CLOSE") .'"
					}
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