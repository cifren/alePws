<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleRules extends JdomClassFormRule
{
	/**
	 * Method to test the value.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		// Get the possible field actions and the ones posted to validate them.
		$fieldActions = self::getFieldActions($element);
		$valueActions = self::getValueActions($value);

		// Make sure that all posted actions are in the list of possible actions for the field.
		foreach ($valueActions as $action)
		{
			if (!in_array($action, $fieldActions))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the list of permission action names from the form field value.
	 *
	 * @param   mixed  $value  The form field value to validate.
	 *
	 * @return  array  A list of permission action names from the form field value.
	 *
	 * @since   11.1
	 */
	protected function getValueActions($value)
	{
		$actions = array();

		// Iterate over the asset actions and add to the actions.
		foreach ((array) $value as $name => $rules)
		{
			$actions[] = $name;
		}

		return $actions;
	}

	/**
	 * Method to get the list of possible permission action names for the form field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the
	 *                                      form field object.
	 *
	 * @return  array   A list of permission action names from the form field element definition.
	 *
	 * @since   11.1
	 */
	protected function getFieldActions(SimpleXMLElement $element)
	{
		$actions = array();

		// Initialise some field attributes.
		$section = $element['section'] ? (string) $element['section'] : '';
		$component = $element['component'] ? (string) $element['component'] : '';

		// Get the asset actions for the element.
		$elActions = self::getAclActions($component, $section);

		// Iterate over the asset actions and add to the actions.
		foreach ($elActions as $item)
		{
			$actions[] = $item->name;
		}

		// Iterate over the children and add to the actions.
		foreach ($element->children() as $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (string) $el['name'];
			}
		}

		return $actions;
	}
	
	/**
	 * Method to return a list of actions for which permissions can be set given a component and section.
	 *
	 * @param	string	$component	The component from which to retrieve the actions.
	 * @param	string	$section	The name of the section within the component from which to retrieve the actions.
	 *
	 * @return	array	List of actions available for the given component and section.
	 * @since	11.1
	 */
	function getAclActions($component, $section = 'component')
	{
		$getAll = $section == '*';
		if(strpos($section, ',') !== false){
			$section = explode(',',$section);
		}
		
		if(!is_array($section)){
			$section = array($section);
		}
		
		$actions = array();

		if (is_file(JPATH_ADMINISTRATOR.'/components/'.$component.'/access.xml')) {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/'.$component.'/access.xml');

			foreach ($xml->children() as $child)
			{
				$sectionTitle = (string)$child['title'];
				$sectionTitle = !empty($sectionTitle) ? $sectionTitle : (string) $child['name'];
				if ($getAll OR in_array((string) $child['name'],$section)) {
					foreach ($child->children() as $action) {
						$actions[] = (object) array(
							'name' => (string) $action['name'],
							'title' => (string) $action['title'],
							'description' => (string) $action['description'],
							'section' => (string) $child['name'],
							'sectionTitle' => $sectionTitle,
							'sectionDescription' => (string) $child['description']
						);
					}
				}
			}
		}

		return $actions;
	}	
}
