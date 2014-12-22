<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryColorpicker extends JDomFrameworkJquery
{	

	var $assetName = 'colorpicker';
	
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
		$this->attachJs[] = 'jquery.colorpicker.js';
		$this->attachCss[] = 'jquery.colorpicker.css';

		$doc = JFactory::getDocument();		

		$script = "
			function colorpickerEnabler(jObj){
				if(typeof jObj == 'undefined'){
					jObj = jQuery('body');
				} else if(!(jObj instanceof jQuery)){
					jObj = jQuery(jObj);
				}
				jObj.find('.colorPicker-input').each(function(i){
					var picker = jQuery(this),
						layout = picker.attr('data-layout') || 'full',
						container = picker.parent(),
						target = container.find('.fly-color');

					picker.colpick({
						layout:layout,
						submit:0,
						onChange:function(hsb,hex,rgb,el,bySetColor) {
							target.css('background-color','#'+hex);
							// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
							if(!bySetColor) jQuery(el).val('#'+ hex).trigger('keyup');
						}
					}).on('keyup',function(){
						jQuery(this).colpickSetColor(this.value);
					});
					
					// trigger click on color preview
					jQuery(this).closest('.colorPicker').on('click',function(){
						picker.colpickShow();					
					});
				});
			}
			
			jQuery(document).ready(function (){
				colorpickerEnabler();
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