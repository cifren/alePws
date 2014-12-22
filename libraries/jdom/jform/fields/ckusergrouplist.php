<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Field to load a list of available users statuses
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.2
 */
class JFormFieldCkusergrouplist extends JdomClassFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'ckusergrouplist';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $options = array();

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.2
	 */
	protected function getOptions($flat = true)
	{
		// Hash for caching
		$hash = md5((string)$this->element . $flat);
		
		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id AS value')
				->select('a.title AS text')
				->select('COUNT(DISTINCT b.id) AS level')
				->from('#__usergroups as a')
				->join('LEFT', '#__usergroups  AS b ON a.lft > b.lft AND a.rgt < b.rgt')
				->group('a.id, a.title, a.lft, a.rgt')
				->order('a.lft ASC');
			$db->setQuery($query);			
		
			if ($options = $db->loadObjectList())
			{
				if(!$flat){
					foreach ($options as &$option)
					{
						$option->text = str_repeat('- ', $option->level) . $option->text;
					}
				}

				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}
		
		return static::$options[$hash];
	}
	
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
		static $groups;
		
		$this->setCommonProperties();
		
		$options = $this->getOptions(false);
		$multiple = $this->getOption('multiple');
		
		$type = 'select';
		if($multiple){
			$type = 'checkboxes';
			
			if(is_string($this->value)){
			
				$values = explode(',',str_replace(' ','',strtolower($this->value)));
				
				if(!$groups){
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('a.id')
						->select('LOWER(REPLACE(a.title, " ", "")) AS text')
						->select('a.parent_id')
						->select('COUNT(DISTINCT b.id) AS level')
						->from('#__usergroups as a')
						->join('LEFT', '#__usergroups  AS b ON a.lft > b.lft AND a.rgt < b.rgt')
						->group('a.id, a.title, a.lft, a.rgt')
						->order('a.lft ASC');
					$db->setQuery($query);			
				
					$groups = $db->loadObjectList();
					$groupsByTitle = self::groupArrayByValue($groups, 'text', false);
				}

				
				// check the accessgroups values are the IDS
				foreach($values as $k => $av){
					if(!is_numeric($av)){
						if(isset($groupsByTitle[$av])){
							$values[$k] = $groupsByTitle[$av]->id;
						} else {
							unset($values[$k]);
						}
					}
				}
				
				$this->value = $values;
			}
		}

		$thisOpts = array(
				'list' => $options,
				'size' => $this->getOption('size', 3, 'int'),
				'dataValue' => $this->value,
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null)
			);
			
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.'. $type, $this->fieldOptions);

		return parent::getInput();
	}

		
	public static function groupArrayByValue($array, $keyName, $multiple = true){
		if(!is_array($array)){
			$array = (array)$array;
		}
		
		$newArray = array();
		foreach($array as $key => $it){
			if(is_array($it)){
				if($multiple){
					$newArray[$it[$keyName]][$key] = $it;
				} else {
					$newArray[$it[$keyName]] = $it;
				}
			} elseif(is_object($it)){
				if($multiple){
					$newArray[$it->$keyName][$key] = $it;
				} else {
					$newArray[$it->$keyName] = $it;
				}
			}
		}

		return $newArray;
	}	
}
