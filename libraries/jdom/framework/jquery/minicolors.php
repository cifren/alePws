<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryMinicolors extends JDomFrameworkJquery
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
		$this->attachJs[] = 'jquery.minicolors.js';
		$this->attachCss[] = 'jquery.minicolors.css';

		$doc = JFactory::getDocument();		

		$script = "
			function colorpickerEnabler(jObj){
				if(typeof jObj == 'undefined'){
					jObj = jQuery('body');
				} else if(!(jObj instanceof jQuery)){
					jObj = jQuery(jObj);
				}
				
				jObj.find('.minicolors').each(function(){
					jQuery(this).minicolors({
						control: jQuery(this).attr('data-control') || 'hue',
						position: jQuery(this).attr('data-position') || 'right',
						theme: 'bootstrap'
					});
				});
			}
		
			jQuery(document).ready(function (){
				colorpickerEnabler(jQuery('body'));
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