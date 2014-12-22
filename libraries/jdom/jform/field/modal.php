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
class JdomClassFormFieldModal extends JFormField
{
	/**
	* The modal height in pixels
	*
	* @var integer
	*/
	protected $height = 450;

	/**
	* The modal width in pixels
	*
	* @var integer
	*/
	protected $width = 600;

	/**
	* Method to get the field input markup.
	*
	* @access	protected
	*
	* @return	string	The field input markup.
	*
	* @since	11.1
	*/
	public function getInput()
	{
		//When Bootstrap is used in native, modal picker is instancied with JDom
		$version = new JVersion();
		if ($version->isCompatible('3.0'))
		{			
			$html =  JDom::_('html.form.input.select.modalpicker', array(
				'dataKey' => $this->id,
				'domName' => $this->name,
				'dataValue' => $this->value,
				'nullLabel' => $this->_nullLabel,
				'title' => $this->_title,

				'width' => $this->width,
				'height' => $this->height,

				'route' => array(
					'option' => $this->_option,
					'view' => $this->_view,
					'layout' => 'modal',
					'object' => $this->id

				),
		
				'tasks' => $this->getTasks(),

			));

			return $html;	
		}


	
		// Legacy not using JDom

		JHtml::_('behavior.modal', 'a.modal');

		//Instance vars
		$labelKey = $this->_labelKey;
		$table = $this->_table;
		$label = JText::_($this->_nullLabel);


		// Build the script.
		$script = array();
		$script[] = '	function jSelectItem(id, title, object) {';
		$script[] = '		document.id(object + "_id").value = id;';
		$script[] = '		document.id(object + "_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		$link 	= 'index.php?option=' . $this->_option . '&amp;view=' . $this->_view . '&amp;layout=modal&amp;tmpl=component&amp;object=' . $this->id;

		$title = $this->_title;
		if (empty($title)) {
			$title = $label;
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current item display field.
		$html	= array();
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="25" />';
		$html[] = '</div>';

		// The item select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="modal" title="'.$label.'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: ' . $this->width . ', y: ' . $this->height . '}}">'.JText::_('JSELECT').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';




		$scriptReset = "document.id('" . $this->id . "_id').value = '';";
		$scriptReset .= "document.id('" . $this->id . "_name').value = '" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "';";
		
		// The clear button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="" title="'. htmlspecialchars(JText::_('JCLEAR'), ENT_QUOTES, 'UTF-8').'"  onclick="'. htmlspecialchars($scriptReset, ENT_QUOTES, 'UTF-8') . '">'
					.JText::_('JCLEAR').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';


		$tasks = $this->getTasks();
		if ($tasks && count($tasks))
		foreach($tasks as $taskName => $task)
		{

			$label = (isset($task['label'])?JText::_($task['label']):'');
			$desc = (isset($task['description'])?JText::_($task['description']):$label);
			$jsCommand = (isset($task['jsCommand'])?$task['jsCommand']:null);
			$icon = (isset($task['icon'])?$task['icon']:$taskName);
		
		
			$html[] = '<div class="button2-left">';
			$html[] = '  <div class="blank">';
			$html[] = '	<a class="" title="'.$label.'" ' 
					. ($jsCommand?' onclick="'.htmlspecialchars($jsCommand, ENT_QUOTES, 'UTF-8').'"':'')
					. '>' . JText::_($label).'</a>';
			$html[] = '  </div>';
			$html[] = '</div>';
				
		}

		// The active item id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}

	/**
	* Method to extend the buttons in the picker.
	*
	* @access	protected
	*
	* @return	array	An array of tasks
	*
	* @since	Cook 2.5.8
	*/
	protected function getTasks()
	{
		return array();

	}


}
