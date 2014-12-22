<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryAddresspicker extends JDomFrameworkJquery
{	

	var $assetName = 'addresspicker';
	
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
		
		JDom::_('framework.google.maps');
		JDom::_('framework.google.maps.styledmarker');
		JDom::_('framework.google.maps.markerwithlabel');
		
		// addresspicker manager files needed
		$this->attachJs[] = 'addresspicker.js';
		$this->attachCss[] = 'addresspicker.css';
		
		$doc = JFactory::getDocument();
		$script = 'window.jQuery_addressPickerByGiro = {
				text: {
					genericerror: "'. JText::_("JDOM_ADDRESSPICKER_GENERIC_ERROR") .'",
					cancel: "'. JText::_("JDOM_ADDRESSPICKER_CANCEL") .'",
					ok: "'. JText::_("JDOM_ADDRESSPICKER_OK") .'",
					edit: "'. JText::_("JDOM_ADDRESSPICKER_EDIT") .'",
					search: "'. JText::_("JDOM_ADDRESSPICKER_SEARCH") .'",
					you_are_here:"'. JText::_("JDOM_ADDRESSPICKER_YOU_ARE_HERE") .'",
					noresults: "'. JText::_("JDOM_ADDRESSPICKER_NORESULTS") .'",
					toofar: "'. JText::_("JDOM_ADDRESSPICKER_TOOFAR") .'",
					set_your_address: "'. JText::_("JDOM_ADDRESSPICKER_SET_YOUR_ADDRESS") .'",
					set_your_address_info: "'. JText::_("JDOM_ADDRESSPICKER_SET_YOUR_ADDRESS_INFO") .'"
				}
		}';
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