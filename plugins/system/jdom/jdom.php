<?php
/**
 * @copyright	Copyright (C) 2013 Cook Self Service. All rights reserved.
 * @author		J. HUARD (http://j-cook.pro) - G. Tomaselli (http://bygiro.com)
 * @license     MIT License (MIT) AND GNU GPL v3 or later
 */
 
defined('_JEXEC') or die;

defined('DS') or define('DS',DIRECTORY_SEPARATOR);
defined('BR') or define("BR", "<br />");
defined('LN') or define("LN", "\n");

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM')) {
	define('JPATH_PLATFORM', JPATH_SITE . DS . 'libraries' . DS);
}

if(!defined('PATH_LIBRARY_JDOM'))  define('PATH_LIBRARY_JDOM', JPATH_SITE . DS . 'libraries' . DS . 'jdom');


if (!class_exists('CkJLoader')){
	require_once(PATH_LIBRARY_JDOM .DS. 'legacy' .DS. 'loader.php');
}



/**
 * Jdom plugin class.
 *
 * @package     Joomla.plugin
 * @subpackage  System.jdom
 */ 
class plgSystemJdom extends JPlugin
{
    public function onAfterInitialise()
    {
		// load plugin language file
		$language = JFactory::getLanguage();
		$language->load('plg_system_jdom', JPATH_ADMINISTRATOR);
	
		CkJLoader::register('JDom', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'dom.php');
		
		
		CkJLoader::register('JdomHelperDates', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'helpers' . DS . 'dates.php');
		CkJLoader::register('JdomHtmlValidator', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'jform' . DS . 'html' . DS . 'validator.php');
		CkJLoader::register('CkJEditor', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'jform' . DS . 'html' . DS . 'editor.php');
		
		// form field class
		CkJLoader::register('JdomClassFormField', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'jform' . DS . 'field.php');
		CkJLoader::register('JdomClassFormFieldModal', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'jform' . DS . 'field' . DS . 'modal.php');
		CkJLoader::register('JdomClassFormRule', JPATH_SITE . DS . 'libraries' . DS . 'jdom' . DS . 'jform' . DS . 'rule.php');
		
		CkJLoader::setup(false, false);	
    }
}