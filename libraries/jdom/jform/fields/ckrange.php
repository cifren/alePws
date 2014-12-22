<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('cknumber');

/**
 * Form Field class for the Joomla Platform.
 * Provides a horizontal scroll bar to specify a value in a range.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       3.2
 */
class JFormFieldCkrange extends JFormFieldCknumber
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'ckrange';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	public function getInput()
	{
		static $initialized;
		
		if(!$initialized){
			$doc = JFactory::getDocument();
			$script = "
			jQuery(document).ready(function(){
				jQuery('form').on('change','.range_input',function(){
					var value = jQuery(this).val();
					if(jQuery(this).next().hasClass('range_value')){
						jQuery(this).next().html(value);
					}
				});
			});
			";
			$doc->addScriptDeclaration($script);
			
			$style = "
			.range_value {
				margin-left: 5px;
				font-style: italic;
				color: green;
				font-size: 80%;
			}
			";
			$doc->addStyleDeclaration($style);			
			
			$initialized = true;
		}
		
		$this->class .= ' range_input';
		
		// Initialize some field attributes.
		$max      = !empty($this->max) ? ' max="' . $this->max . '"' : '';
		$min      = !empty($this->min) ? ' min="' . $this->min . '"' : '';
		$step     = !empty($this->step) ? ' step="' . $this->step . '"' : '';
		$class    = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly = $this->readonly ? ' readonly' : '';
		$disabled = $this->disabled ? ' disabled' : '';

		$autofocus = $this->autofocus ? ' autofocus' : '';

		$value = (float) $this->value;
		$value = empty($value) ? $this->min : $value;

		// Initialize JavaScript field attributes.
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		JDom::_('framework.jquery'); 
		JHtml::_('script', 'system/html5fallback.js', false, true);

		return '<input type="range" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $readonly
			. $onchange . $max . $step . $min . $autofocus . ' /><span class="range_value">'. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') .'</span>';
	}
	

	public function getOutput($tmplEngine = null)
	{
		$html = '';
		if(!isset($this->value)){
			return $html;
		}
		
		return $this->value;
	}
}
