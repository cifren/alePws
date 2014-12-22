<?php
/**                               ______________________________________________
*                          o O   |                                              |
*                 (((((  o      <     JDom Class - Cook Self Service library    |
*                ( o o )         |______________________________________________|
* --------oOOO-----(_)-----OOOo---------------------------------- www.j-cook.pro --- +
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomHtmlFormInputRules extends JDomHtmlFormInput
{
	var $isOutput;
	var $assetField;
	var $section;
	var $component;
	var $aclActions;
	var $assetId;
	var $itemAcl;
	static $assetRules;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 * 	@size		: Input Size
	 * 
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('size'			, 6, $args, '32');
		$this->arg('isOutput'	, 6, $args, false);
		$this->arg('assetField'	, 6, $args);
		$this->arg('section'		, 6, $args);
		$this->arg('component'	, 6, $args);
		$this->arg('aclActions'	, 6, $args,array());
		$this->arg('itemAcl'		, 6, $args,false);
	}
	
	function build()
	{
		static $loadedOnce;
		
		if(empty($loadedOnce)){
			$doc = JFactory::getDocument();
			
			$css = '
.permissions-sliders .chzn-container {
	position: absolute;
}

.permissions-sliders .input-small {
	width: 120px;
}

.permissions-sliders h3 {
	margin-bottom: 3px;
}
			';			
			$doc->addStyleDeclaration($css);
			
			$script = '
jQuery(document).ready(function(){
	jQuery(".permissions-sliders").find("ul.nav-tabs li:first-child a").trigger("click");
	jQuery(".permissions-sliders").find("ul.nav-tabs li:first-child").addClass("active");
	jQuery(".permissions-sliders").find(".tab-pane:first-child").addClass("active");
});
			';
			$doc->addScriptDeclaration($script);
		}
		
		// Initialise some field attributes.
		$section = $this->section;		
		$component = $this->component;
		$assetField = $this->assetField;

		// Get the actions for the asset.
		$actions = array_merge($this->aclActions,self::getAclActions($component, $section));

		$this->assetId = 1;
		if(empty($this->itemAcl)){
			// get rules from dataValue
			$assetRules	= new JRules;
			$assetRules->mergeCollection($this->dataValue);			
			$this->assetRules[$this->assetId] = $assetRules;		
		} else {
			// Get the explicit rules for this asset.
			if (strpos($section,'component') !== false)
			{
				// Need to find the asset id by the name of the component.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('`id`')
					->from('`#__assets`')
					->where('`name` = ' . $db->quote($component));
				$db->setQuery($query);
				$this->assetId = (int) $db->loadResult();
			}
			else
			{
				// Find the asset id of the content.
				// Note that for global configuration, com_config injects asset_id = 1 into the form.
				$this->assetId = $this->form->getValue($assetField);
			}

			// Full width format.

			// Get the rules for just this asset (non-recursive).
			$this->assetRules[$this->assetId] = JAccess::getAssetRules($this->assetId);
		}
	
		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Prepare output
		$html = array();

		if(!$this->isOutput){
			// Description
			$html[] = '<p class="rule-desc">' . JText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';
		}
		
		$this->addClass('permissions-sliders tabbable tabs-left');
		
		// Begin tabs
		$html[] = '<div id="<%DOM_ID%>" <%STYLE%><%CLASS%><%SELECTORS%>>';

		// Building tab nav
		$html[] = '<ul class="nav nav-tabs">';

		unset($group);
		foreach ($groups as $group)
		{
			// Initial Active Tab
			$active = "";

			if ($group->value == 1)
			{
				$active = "active";
			}

			$html[] = '<li class="' . $active . '">';
			$html[] = '<a href="#'. $this->domId .'-permission-' . $group->value . '" data-toggle="tab">';
			$html[] = str_repeat('<span class="level">&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</a>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		$html[] = '<div class="tab-content" style="min-height: 500px;">';

		// group actions by SECTION
		$actions = ByGiroHelper::groupArrayByValue($actions, 'section');
		
		// Start a row for each user group.
		unset($group);
		foreach ($groups as $group)
		{
			// Initial Active Pane
			$active = "";

			if ($group->value == 1)
			{
				$active = " active";
			}

			$html[] = '<div class="tab-pane' . $active . '" id="'. $this->domId .'-permission-' . $group->value . '">';
			
			// start section
			foreach($actions as $sectActions){
				$firstAction = $sectActions[0];
									
				$html[] = '<h3>'. $firstAction->sectionTitle .'</h3>';
				$html[] = $firstAction->sectionDescription;
						
						
				$html[] = '<table class="table table-striped">';
				$html[] = '<thead>';
				$html[] = '<tr>';

				$html[] = '<th class="actions">';
				$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_ACTION') . '</span>';
				$html[] = '</th>';

				if(!$this->isOutput){
					$html[] = '<th class="settings">';
					$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
					$html[] = '</th>';
				}
				
				// The calculated setting is not shown for the root group of global configuration.
				$canCalculateSettings = ($group->parent_id || !empty($component));

				if ($canCalculateSettings)
				{
					$html[] = '<th>';
					$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
					$html[] = '</th>';
				}

				$html[] = '</tr>';
				$html[] = '</thead>';
				$html[] = '<tbody>';

				foreach ($sectActions as $action)
				{
					$html[] = '<tr>';
					$html[] = '<td>';
					$html[] = '<label for="' . $this->domId . '_' . $firstAction->section . '_' . $action->name . '_' . $group->value . '" class="hasTooltip" title="'
						. htmlspecialchars(JText::_($action->title) . ' ' . JText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
					$html[] = JText::_($action->title);
					$html[] = '</label>';
					$html[] = '</td>';

					if(!$this->isOutput){
						$html[] = '<td headers="settings-th' .'_'. $firstAction->section . '_' . $group->value . '">';

						$html[] = '<select class="input-small" name="' . $this->domName . '[' . $action->name . '][' . $group->value . ']" id="' . $this->domId . '_' . $firstAction->section . '_' . $action->name
							. '_' . $group->value . '" title="'
							. JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

						$inheritedRule = $this->checkGroup($group->value, $action->name, $this->assetId);

						// Get the actual setting for the action for this group.
						$assetRule = $this->assetRules[$this->assetId]->allow($action->name, $group->value);

						// Build the dropdowns for the permissions sliders

						// The parent group has "Not Set", all children can rightly "Inherit" from that.
						$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
							. JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
						$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED')
							. '</option>';
						$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED')
							. '</option>';

						$html[] = '</select>&#160; ';

						// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
						if (($assetRule === true) && ($inheritedRule === false))
						{
							$html[] = JText::_('JLIB_RULES_CONFLICT');
						}

						$html[] = '</td>';
					}
					// Build the Calculated Settings column.
					// The inherited settings column is not displayed for the root group in global configuration.
					if ($canCalculateSettings)
					{
						$html[] = '<td>';

						// This is where we show the current effective settings considering currrent group, path and cascade.
						// Check whether this is a component or global. Change the text slightly.

						if ($this->checkGroup($group->value, 'core.admin', $this->assetId) !== true)
						{
							if ($inheritedRule === null)
							{
								$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === true)
							{
								$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === false)
							{
								if ($assetRule === false)
								{
									$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
								}
								else
								{
									$html[] = '<span class="label"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
										. '</span>';
								}
							}
						}
						elseif (!empty($component))
						{
							$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span>';
						}
						else
						{
							// Special handling for  groups that have global admin because they can't  be denied.
							// The admin rights can be changed.
							if ($action->name === 'core.admin')
							{
								$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === false)
							{
								// Other actions cannot be changed.
								$html[] = '<span class="label label-important"><i class="icon-lock icon-white"></i> '
									. JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span>';
							}
							else
							{
								$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
									. '</span>';
							}
						}

						$html[] = '</td>';
					}

					$html[] = '</tr>';
				}

				$html[] = '</tbody>';
				$html[] = '</table>';
			
			}
			// end section
			
			$html[] = '</div>';
		}

		$html[] = '</div></div>';

		if(!$this->isOutput){
			$html[] = '<div class="alert">';

				if (strpos($section,'component') !== false || empty($section))
				{
					$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
				}
				else
				{
					$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
				}

			$html[] = '</div>';
		}
		
		return implode("\n", $html);
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	protected function getUserGroups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id')
			->from('#__usergroups AS a')
			->join('LEFT', '`#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id, a.title, a.lft, a.rgt, a.parent_id')
			->order('a.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
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

		if(empty($component)) return $actions;
		
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
	
	/**
	 * Method to check if a group is authorised to perform an action, optionally on an asset.
	 *
	 * @param	integer	$groupId	The path to the group for which to check authorisation.
	 * @param	string	$action		The name of the action to authorise.
	 * @param	mixed	$asset		Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return	boolean	True if authorised.
	 * @since	11.1
	 */
	public function checkGroup($groupId, $action, $asset = null)
	{
		// Sanitize inputs.
		$groupId = (int) $groupId;
		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset  = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Get group path for group
		$groupPath = self::getGroupPath($groupId);

		// Default to the root asset node.
		if (empty($asset)) {
			$asset = 1;
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty($this->assetRules[$asset])){
			$this->assetRules[$asset] = JAccess::getAssetRules($asset, true);
		}

		return $this->assetRules[$asset]->allow($action, $groupPath);
	}
	
	/**
	 * Gets the parent groups that a leaf group belongs to in its branch back to the root of the tree
	 * (including the leaf group id).
	 *
	 * @param	mixed	$groupId	An integer or array of integers representing the identities to check.
	 *
	 * @return	mixed	True if allowed, false for an explicit deny, null for an implicit deny.
	 * @since	11.1
	 */
	public function getGroupPath($groupId)
	{
		static $groups, $paths;

		// Preload all groups
		if (empty($groups)) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true)
				->select('parent.id, parent.lft, parent.rgt')
				->from('#__usergroups AS parent')
				->order('parent.lft');
			$db->setQuery($query);
			$groups = $db->loadObjectList('id');
		}

		// Make sure groupId is valid
		if (!array_key_exists($groupId, $groups))
		{
			return array();
		}

		// Get parent groups and leaf group
		if (!isset($paths[$groupId])) {
			$paths[$groupId] = array();
			foreach($groups as $group) {
				if ($group->lft <= $groups[$groupId]->lft && $group->rgt >= $groups[$groupId]->rgt) {
					$paths[$groupId][] = $group->id;
				}
			}
		}

		return $paths[$groupId];
	}
}