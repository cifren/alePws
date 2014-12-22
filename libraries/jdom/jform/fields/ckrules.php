<?php
/**
* (¯`·.¸¸.-> °º★ вүgιяσ.cσм ★º° <-.¸¸.·´¯)
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
* @added by		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
* Form field for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormFieldCkrules extends JdomClassFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ckrules';

	/**
	 * The section.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $section;

	/**
	 * The component.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $component;

	/**
	 * The assetField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $assetField;

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
			case 'section':
			case 'component':
			case 'assetField':
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
			case 'section':
			case 'component':
			case 'assetField':
				$this->$name = (string) $value;
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
			$this->section    = $this->element['section'] ? (string) $this->element['section'] : '';
			$this->component  = $this->element['component'] ? (string) $this->element['component'] : '';
			$this->assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 * @todo:   Add access check.
	 */
	public function getInput($isOutput = false)
	{
		$this->setCommonProperties();
		
		$aclActions = $this->getAclActions();

		$thisOpts = array(
				'assetField' => $this->getOption('assetField') ?: $this->getOption('asset_field'),
				'component' => $this->getOption('component'),
				'section' => $this->getOption('section'),
				'aclActions' => $aclActions,
				'isOutput' => $isOutput,
			);
			
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);		
		$this->input = JDom::_('html.form.input.rules', $this->fieldOptions);

		return parent::getInput();
	}

	

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 * @todo:   Add access check.
	 */
	public function getOutput($tmplEngine = '')
	{
		return self::getInput(true, $tmplEngine);
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
	function getAclActions()
	{
		
		$actions = array();
		$children = (array)$this->element->children();
		if (empty($children)){
			return $actions;
		}
		
		foreach ($children as $child)
		{
			$sectionTitle = (string)$child['title'];
			$sectionTitle = !empty($sectionTitle) ? $sectionTitle : (string) $child['name'];
			
			foreach ($child->children() as $action) {
			if ($action->getName() != 'action') continue;
			
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

		return $actions;
	}
}
