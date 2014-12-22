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
 * Provides a meter to show value in a range.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       3.2
 */
class JFormFieldCkmeter extends JdomClassFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'ckmeter';


	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'active':
			case 'value':
			case 'animated':
			case 'color':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'value':
			case 'color':
				$this->$name = (string) $value;
				break;

			case 'active':
				$value = (string) $value;
				$this->$name = ($value === 'true' || $value === $name || $value === '1');
				break;

			case 'animated':
				$value = (string) $value;
				$this->$name = !($value === 'false' || $value === 'off' || $value === '0');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->value = isset($this->element['value']) ? (string) $this->element['value'] : 0;
			$this->color = isset($this->element['color']) ? (string) $this->element['color'] : '';

			$active       = (string) $this->element['active'];
			$this->active = ($active === 'true' || $active == 'on' || $active == '1');

			$animated       = (string) $this->element['animated'];
			$this->animated = !($animated === 'false' || $animated == 'off' || $animated == '0');
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	public function getInput()
	{
		$this->setCommonProperties();
		
		$thisOpts = array(
				'value' => $this->value,
				'active' => $this->active,
				'color' => $this->color,
				'animated' => $this->animated,
			);
			
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.meter', $this->fieldOptions);

		return parent::getInput();

	}
	
	public function getOutput($tmplEngine = null)
	{
		return $this->getInput();

	}
}
