<?php
/*
* @version		0.0.1
* @package		jCarosello
* @subpackage	
* @copyright	Copyright (C) 2013 Girolamo Tomaselli All rights reserved.
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License version 2 or later;
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('JcaroselloClassFormField'))
	require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jcarosello' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'loader.php');


/**
* Form field for Jcarosello.
*
* @package	Jcarosello
* @subpackage	Form
*/
class JFormFieldCkgmapeditor extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckgmapeditor';

	/**
	* Method to get the field input markup.
	*
	* @access	public
	*
	* @return	string	The field input markup.
	*
	* @since	11.1
	*/
	public function getInput()
	{
		$this->setCommonProperties();

		$this->input = JDom::_('html.form.input.mapeditor', $this->fieldOptions);
		
		return parent::getInput();
	}


}