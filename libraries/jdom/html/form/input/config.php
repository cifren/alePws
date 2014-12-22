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


class JDomHtmlFormInputConfig extends JDomHtmlFormInput
{
	var $isOutput;
	var $usergroupsEnabled;
	var $form;

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

		$this->arg('isOutput'				, 6, $args, false);
		$this->arg('usergroupsEnabled'	, 6, $args, true);
		$this->arg('form'						, 6, $args);
	}
	
	function build()
	{
		static $loadedOnce;
		
		if(empty($loadedOnce)){

		}
		
		JDom::_('framework.jquery.condrules');
		
		// Get the available user groups.
		$groups = ByGiroHelper::getUserGroups();

		// Prepare output
		$html = array();
		
		$html[] = '<div id="<%DOM_ID%>" <%STYLE%><%CLASS%><%SELECTORS%>>';

		
		$fieldsets = $this->form->getFieldsets();
			
		$tabs = array();
		unset($group);
		foreach ($groups as $group)
		{
			$tabContent = array();
			
			$tabContent[] = '<h3>'. $group->text .'</h3>';
			
			foreach($fieldsets as $fset){
				$fsetLabel = $fset->name;
				if(!empty($fset->label)){
					$fsetLabel = JText::_($fset->label);
				}
				
				$tabContent[] = '<h5>'. $fsetLabel .'</h5>';
				
				@$jdomOptions = array(
					'form' => $this->form,
					'fieldsetName' => $fset->name,
					'dataObject' => $this->dataValue->{$group->value},
					'formControl' => $this->formControl,
					'formGroup' => trim($this->formGroup .'.'. $this->dataKey .'.'. $group->value ,'.'),
					'format' => 'array'
				);
			
				$fsetFields = JDom::_('html.form.fieldset', $jdomOptions);
				
				unset($fi);
				foreach($fsetFields as $fi){
					$fName = $fi['field']->fieldname;
					$fiHtml = $fi['html'];
					
					if($group->value != 1){
						$checked = false;
						if(isset($this->dataValue->{$group->value})){
							if(isset($this->dataValue->{$group->value}->{$fName.'_inherit'})){
								@$checked = $this->dataValue->{$group->value}->{$fName.'_inherit'};
							}
						}
						$checked = $checked ? ' checked="checked"' : '';
						
						$fieldInheritId = $this->domId .'_'. $group->value .'_'. $fName .'_inherit';
						$fieldInheritName = $this->domName .'['. $group->value .']['. $fName .'_inherit]';
						
						$checkBox = '<span class="inherit_input"><input '. $checked .' type="checkbox" name="'. $fieldInheritName .'" value="1" id="'. $fieldInheritId .'" /> <label for="'. $fieldInheritId .'">'. JText::_('JLIB_RULES_INHERITED') .'</label></span>';
						
						$class = 'condRule[hide,#'. $fieldInheritId .',1]';
						
						$fiHtml = str_replace('<%input%>','<span class="'. $class .'"><%input%></span>',$fiHtml);					
						$fiHtml = str_replace('<%label%>','<%label%><br />'. $checkBox,$fiHtml);
					}
					
					$tabContent[] = ByGiroHelper::getHtmlField($fiHtml,$fi['field'],'input',$this->form);
				}
			}
			
			$tabs[] = array(
				'id' => $this->domId .'_'. $group->value,
				'name' => str_repeat('<span class="level">&ndash;</span> ', $group->level) . JText::_($group->text),
				'content' => implode(PHP_EOL,$tabContent)
			);
		}

		if(!empty($tabs)){
			$html[] = JDom::_('html.fly.bootstrap.tabs', array(
					'domId' => $this->domId .'_tabs',
					'domClass' => $this->domId .'_tabs',
					'tabs' => $tabs,
					'side' => 'left'
				));
		}
	
		return implode("\n", $html);
	}
}