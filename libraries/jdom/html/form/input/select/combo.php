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


class JDomHtmlFormInputSelectCombo extends JDomHtmlFormInputSelect
{
	var $canEmbed = true;
	
	var $ui_allowCustomValues = false;
	var $ui_hideSelect = false;
	var $ui_direction = 'vertical';
	
	protected $ui;
	protected $multiple;
	protected $valueKey;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 * 	@list		: Possibles values list (array of objects)
	 * 	@listKey	: ID key name of the list
	 * 	@labelKey	: Caption key name of the list
	 * 	@size		: Size in rows ( 0,null = combobox, > 0 = list)
	 * 	@nullLabel	: First choice label for value = '' (no null value if null)
	 * 	@groupBy	: Group values on key(s)  (Complex Array Struct)
	 * 	@domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 *
	 *
	 *	@ui			: rendering effect (User Interface). (possible values : 'chosen','multiselect')
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
		$this->arg('ui', 	null, $args);
		$this->arg('multiple', 	null, $args);
		$this->arg('valueKey', 	null, $args, $this->dataKey);
		$this->arg('ui_direction', 	null, $args, $this->ui_direction);
		$this->arg('ui_hideSelect', 	null, $args, $this->ui_hideSelect);
		$this->arg('ui_allowCustomValues', 	null, $args, $this->ui_allowCustomValues);
		
		if ($this->multiple || $this->ui == 'multiselect'){
			$this->domName .= '[]';
			$this->multiple = true;
		}

	}

	function build()
	{
		
		if ($this->groupBy)
			$options = $this->buildOptionsGroup();
		else
			$options = $this->buildOptions();


		if ($this->ui == 'chosen')
		{
			JDom::_('framework.jquery.chosen');			
			$this->addClass('chzn-select');
		} else if($this->ui == 'multiselect'){
			JDom::_('framework.jquery.multiselect');
			$this->addClass('multiselect');
			
			if($this->ui_allowCustomValues){
				$this->addSelector('data-allow-custom-values', 'true');			
			}
			
			if($this->ui_hideSelect){			
				$this->addSelector('data-hide-select', 'true');
			}

			$this->addSelector('data-direction', $this->ui_direction);
		}

		$html =	'<select id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			. 	($this->multiple ?' multiple':'')
			.	((int)$this->size > 1?' size="' . (int)$this->size . '"':'') . '>' .LN
			.	$this->indent($this->buildDefault(), 1)
			.	$this->indent($options, 1)
			.	'</select>'.	LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>';


		// add options description
		if($this->list){
			JDom::_('framework.jquery.condrules');
			foreach($this->list as $item){
				$item = (object)$item;
				
				if(!empty($item->description)){
					$html .= ' <span class="option-description condRule[show,#<%DOM_ID%>,'. $item->{$this->listKey} .']">'. JText::_($item->description) .'</span>';
				}
			}
		}
	
		return $html;

	}
	
	function buildJs()
	{
		static $multiselectLoaded;
		
		if ($this->useFramework('chosen') && $this->ui == 'chosen')
		{
			$js = 'jQuery(".chzn-select").chosen({
				disable_search_threshold : 10,
				allow_single_deselect : true
			});';
			$this->addScriptInline($js, !$this->isAjax());
		} else if($this->ui == 'multiselect' AND !isset($multiselectLoaded)){
			$js = 'jQuery(".multiselect").multiselectByGiro();';
			$this->addScriptInline($js, !$this->isAjax());
			$multiselectLoaded = true;
		}
	}

	function buildDefault()
	{
		if (!$this->nullLabel)
			return '';

		$item = new stdClass();
		$item->id = '';
		$item->text = JText::_($this->nullLabel);

		return $this->buildOption($item, 'id', 'text');

	}

	function buildOptions()
	{
		$html =	'';
		
		if ($this->list){
			$isArrDataValue = is_array($this->dataValue);
			$vals = $this->dataValue;		
			foreach($this->list as $item){
				// check we have an UI multiselect with custom values
				if($this->ui == 'multiselect' AND $this->ui_allowCustomValues AND $isArrDataValue){
					$v = $item->{$this->listKey};
					// check missing options
					$found = array_search($v,$vals);
					if($found !== false){
						unset($vals[$found]);
					}					
				}
			
				$html .= $this->buildOption($item, $this->listKey, $this->labelKey);
			}
		
			// add missing values to the list
			if(is_array($vals) AND $this->ui_allowCustomValues){
				unset($v);
				foreach($vals as $v){
					if($v == '') continue;
					
					$fakeItem = (object)array(
						'value' => $v,
						'text' => $v
					);
					$html .= $this->buildOption($fakeItem, 'value', 'text');
				}
			}
		}

		return $html;

	}

	function buildOptionsGroup()
	{
		$indentStr = 		'&nbsp;&nbsp;&nbsp;';
		$indentStrGroup = '&nbsp;&nbsp;&nbsp;';


		$html =	'';

		$groupBy = array_reverse($this->groupBy);
		$group = array();

		$indent = 0;

		if ($this->list)
		foreach($this->list as $item)
		{
			// Close OPTGROUP
			foreach(array_reverse($groupBy) as $groupKey => $groupLabelKey)
			{
				if (isset($group[$groupKey]) && $group[$groupKey] != $item->$groupKey)
				{
					if ($group[$groupKey] != null)
					{
						$indent --;
						$html .= $this->indent('</optgroup>', $indent) .LN;
					}

				}
			}

			// Open OPTGROUP
			foreach($groupBy as $groupKey => $groupLabelKey)
			{
				if (!isset($group[$groupKey]) || $group[$groupKey] != $item->$groupKey)
				{

					$prefixGroup = str_repeat($indentStrGroup, $indent);

					$html .= $this->indent(
							'<optgroup label="'
							. $prefixGroup . htmlspecialchars($this->parseKeys($item, $groupLabelKey), ENT_COMPAT, 'UTF-8')
							. '">' .LN
							, $indent);

					$indent ++;
					$group[$groupKey] = $item->$groupKey;

				}
			}

			// build the OPTION
			$prefix = str_repeat($indentStr, $indent);
			$html .= $this->indent($this->buildOption($item, $this->listKey, $this->labelKey, $prefix), $indent);

		}

		//Close last GROUPS
		foreach(array_reverse($groupBy) as $groupKey => $groupLabelKey)
		{
			if (isset($group[$groupKey]) && $group[$groupKey] != null)
			{
				$indent --;
				$html .= $this->indent('</optgroup>', $indent) .LN;
			}
		}

		return $html;
	}

	function buildOption($item, $listKey, $labelKey, $prefix = '')
	{
		//If item is an array, convert it to an object
		if (!is_object($item))
			$item = JArrayHelper::toObject($item);

		if ($item === null)
			$item = new stdClass();
		
		
		if (!isset($item->$listKey))
			$item->$listKey = null;

		$selected = false;
		// In case of multi select
		if (is_array($this->dataValue) && count($this->dataValue))
		{
			// When a list is send as value : reduce array to the dataKey only.
			if (is_object($this->dataValue[0]))
			{
				$valueKey = $this->valueKey;
				$values = array();
				foreach($this->dataValue as $row)
				{
					$values[] = $row->$valueKey;
				}				
			}
			else
				$values = $this->dataValue;
			
			$selected = in_array(($item->$listKey), $values);
		}
		//Integer compatibility when possible
		else if (is_integer($this->dataValue)){
			$selected = ((int)$item->$listKey === $this->dataValue);
		} else {
			$selected = ($item->$listKey === $this->dataValue);
		}
		
		$class = '';
		if(!empty($item->class)){
			$class = $item->class .' ';
		}
		
		$extra = $icon = '';
		if(!empty($item->icon)){
			$icon = 'icomoon '. $item->icon .' ';
			$extra = ' ';
		}
		
			
		$html =	'<option class="'. $class . $icon .'" value="'
			.	htmlspecialchars($item->$listKey, ENT_COMPAT, 'UTF-8')
			. 	'"'
			.	($selected?' selected="selected"':'')
			.	'>' . $extra
			.	$prefix . $this->parseKeys($item, $labelKey)
			. 	'</option>'.LN;

		return $html;
	}

}