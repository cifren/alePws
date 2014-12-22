<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryPostmaxsize extends JDomFrameworkJquery
{	

	var $assetName = 'postmaxsize';
	
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
		$script = '
		jQuery(document).ready(function(){
			var options = {
				max_input_vars: '. ini_get('max_input_vars') .', // usually it is 1000 - 0 (ZERO) to disable it
				post_max_size: '. $this->return_bytes(ini_get('post_max_size')) .', // in bytes. usually it is 108MB - 0 (ZERO) to disable it
				max_file_uploads: '. ini_get('max_file_uploads') .', // usually it is 20 - 0 (ZERO) to disable it
				upload_max_filesize: '. $this->return_bytes(ini_get('upload_max_filesize')) .', // in bytes. usually it is 100MB - 0 (ZERO) to disable it
				message_1: 65, // the percentage to be reached to send the advice to the user
				message_2: 80, // the percentage to be reached to send the advice to the user
				message_3: 95, // the percentage to be reached to send the advice to the user

				text: {
					message_1: "'. JText::_("JDOM_POSTMAXSIZE_MESSAGE_1") .'",
					message_2: "'. JText::_("JDOM_POSTMAXSIZE_MESSAGE_2") .'",
					message_3: "'. JText::_("JDOM_POSTMAXSIZE_MESSAGE_3") .'"
				}
			};
			jQuery("form").each(function(){
				jQuery(this).postMaxSize(options);
			});
		});
		';
		$doc->addScriptDeclaration($script);
		$this->attachJs[] = 'jquery.postmaxsize.js';
			
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
	
	function return_bytes($val) {
		 $val = trim($val);
		 $last = strtolower($val[strlen($val)-1]);
		 switch($last) {
			  // The 'G' modifier is available since PHP 5.1.0
			  case 'g':
					$val *= 1024;
			  case 'm':
					$val *= 1024;
			  case 'k':
					$val *= 1024;
		 }

		 return $val;
	}
}