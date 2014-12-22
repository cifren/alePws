<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryGmapeditor extends JDomFrameworkJquery
{	

	var $assetName = 'gmapeditor';
	
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
		$doc = JFactory::getDocument();
		
		JDom::_('framework.google.drawingmap');
		JDom::_('framework.jquery');	
	
/*	
		$doc = JFactory::getDocument();		
		$script = 'var baseURL = "'. JURI::root() .'";';
		$script .= 'var baseMapURL = baseURL + "components/'. $this->getExtension() .'/map/";';
		$doc->addScriptDeclaration($script);
*/

/*
$doc = JFactory::getDocument();
$script = 'var baseURL = "'. JURI::root() .'";
var baseMapURL = baseURL + "components/com_jcarosello/map/";
jQuery(document).ready(function(){
	setMapfolder();
	jQuery("#jform_map").on("change",function(){
		setMapfolder();		
	});
});

function setMapfolder(){
	var mapVal = jQuery("#jform_map").val();
	if(mapVal != ""){
		baseMapURL = baseURL + "components/com_jcarosello/"+ mapVal +"/";
	}
}
';
$doc->addScriptDeclaration($script);
*/

		// addresspicker manager files needed
		$this->attachJs[] = 'gmapeditorbygiro.js';
		
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