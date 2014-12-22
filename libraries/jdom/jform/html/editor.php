<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JEditor class to handle WYSIWYG editors
 *
 * @package     Joomla.Libraries
 * @subpackage  Editor
 * @since       1.5
 */
class CkJEditor extends JEditor
{

	/**
	 * Constructor
	 *
	 * @param   string  $editor  The editor name
	 */
	public function __construct($editor = 'none')
	{
		parent::__construct($editor);
	}
	
	/**
	 * getScriptInitializer 
	 */
	public function getInitializerScript()
	{
		if (is_null(($this->_editor)))
		{
			return;
		}

		switch($this->_editor){
			case 'codemirror':
			
				break;
			
			default:
				$args['event'] = 'onInit';
				$result = $this->_editor->update($args);
				break;
		}
		
		return $result;
	}
	
	public function getDisplay($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{	
		$this->asset = $asset;
		$this->author = $author;
		$this->_CkLoadEditor($params);

		// Check whether editor is already loaded
		if (is_null(($this->_editor)))
		{
			return;
		}

		// Backwards compatibility. Width and height should be passed without a semicolon from now on.
		// If editor plugins need a unit like "px" for CSS styling, they need to take care of that
		$width = str_replace(';', '', $width);
		$height = str_replace(';', '', $height);

		$return = null;

		$args['name'] = $name;
		$args['content'] = $html;
		$args['width'] = $width;
		$args['height'] = $height;
		$args['col'] = $col;
		$args['row'] = $row;
		$args['buttons'] = $buttons;
		$args['id'] = $id ? $id : $name;
		$args['event'] = 'onDisplay';
		$args['params'] = $params;

		$results[] = $this->_editor->update($args);

		foreach ($results as $result)
		{
			if (trim($result))
			{
				$return .= $result;
			}
		}

		return $return;
	}
	
	public function _ckLoadEditor($config = array())
	{
		// Check whether editor is already loaded
		if (!is_null(($this->_editor)))
		{
			return false;
		}

		// Build the path to the needed editor plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/editors/' . $name . '.php';

		if (!is_file($path))
		{
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';

			if (!is_file($path))
			{
				JLog::add(JText::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), JLog::WARNING, 'jerror');
				return false;
			}
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = JPluginHelper::getPlugin('editors', $this->_name);
		
		if(empty($plugin)){
			return false;
		}
		
		$params = new JRegistry;
		$params->loadString($plugin->params);	
		$params->loadArray($config);
		
		$plugin->params = $params;
		
		// Build editor plugin classname
		$name = 'plgEditor' . $this->_name;

		if ($this->_editor = new $name($this, (array) $plugin))
		{
			// Load plugin parameters
			JPluginHelper::importPlugin('editors-xtd');
		}
		
		return true;
	}
}
