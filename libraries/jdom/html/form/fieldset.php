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
* @addon		form fieldset
* @author		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomHtmlFormFieldset extends JDomHtmlForm
{
	var $listName = 'itemsList';
	var $layout;
	var $enumList;
	var $format;
	var $renderInput;
	var $renderOutput;
	var $dataObject;
	var $jdomOptions = array();
	var $formGroup = null;
	var $formControl = null;
	var $prefix_id = '';

	var $attachJs = array();
	var $attachCss = array();
	
	protected $form;
	protected $fieldset;
	protected $fieldsToRender;
	protected $fieldsetName;
	protected $actions = array();
	protected $tmplEngine = null;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 * 	@listName	: default 'items'
	 * 	@markup		: default 'div'
	 * 	@dataObject	: object to override field value
	 * 	@formControl: Override the fieldset formControl
	 * 	@formGroup	: Override the fieldset formGroup
	 * 	@jformFields: array of jform fields 
	 * 	@fieldsToRender: array of fields  to render
	 * 	@actions	: default array()
	 * 	@tmplEngine	: JS Template Engine, default null
	 * 	@domClass	: CSS class
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('listName'		, null, $args, 'itemsList');
		$this->arg('layout'			, null, $args); // possible values: 'table' , 'div'
		$this->arg('enumList'		, null, $args);
		$this->arg('format'			, null, $args, 'html'); // possible values: 'array' , 'html'
		$this->arg('renderInput'	, null, $args, true);
		$this->arg('renderOutput'	, null, $args, false);
		$this->arg('dataObject'		, null, $args, array());
		$this->arg('formGroup'		, null, $args);
		$this->arg('formControl'	, null, $args, 'jform');
		$this->arg('prefix_id'		, null, $args, '');
		$this->arg('fieldset'		, null, $args);
		$this->arg('multilanguage'	, null, $args, false);
		$this->arg('form'				, null, $args);
		$this->arg('fieldsToRender', null, $args, array());
		$this->arg('jdomOptions'	, null, $args, array());
		$this->arg('actions'			, null, $args, array());
		$this->arg('tmplEngine'		, null, $args, null);
		$this->arg('domClass'		, null, $args);
		$this->arg('fieldsetName'	, null, $args);
		
		if(empty($this->layout)){
			$this->layout = 'div';
		}
		
		if($this->form instanceof JForm AND isset($this->fieldsetName)){			
			if(!$this->fieldset){
				$this->fieldset = $this->form->getFieldset($this->fieldsetName);
			}
			
			$fieldSets = $this->form->getFieldsets();
			$fset = $fieldSets[$this->fieldsetName];
			$multilanguage = (isset($fset->multilanguage) AND $fset->multilanguage != '') ? true : false;
		}
		
		if(isset($multilanguage)){
			$this->multilanguage = $multilanguage;
		}
	}


	function build()
	{	
		$output = '';
		if($this->multilanguage){
			$languages = $this->getLanguages();
			
			$fake_lang = (object)array(
				'postfix' => '',
				'lang_code' => '',
				'img_url' => '',
				'lang_tag' => '',			
			);
			$fake_lang->title = JText::_("JDEFAULT");
			array_unshift($languages,$fake_lang);
			
			$ml_forms = array();
			foreach($languages as $lang){
				$title = $lang->title;
				if($lang->img_url != ''){
					$title .= ' <img src="'. $lang->img_url .'" />';
				}
			
				$opts = array();
				$opts['multilanguage'] = false;
				
				if($lang->lang_tag != ''){
					$opts['required'] = false;
				}

				$opts = array_merge($this->jdomOptions, $opts);
				
				$tb = array(
					'name' => $title,
					'content' => $this->renderFieldset($lang->postfix, $opts)
				);
			
				$ml_forms[] = (object)$tb;
			}
			
			$output = JDom::_('html.fly.bootstrap.tabs', array(
					'side' => 'top',
					'tabs' => $ml_forms,
					'domClass' => 'ml_fset_tabs'
				));			
		} else {
			$output = $this->renderFieldset('', $this->jdomOptions);
		}
		
		return $output;
	}

	function renderFieldset($postfix = '', $jdomOptions = array()){
		$item = (object)$this->dataObject;
		$cels = array();
	
		// JOOMLA repeatable FIX
		/*
		$subFieldsRepeatable = array();
		foreach($this->fieldset as $field){
			if(strtolower($field->type) == 'ckrepeatable'){			
				$subFieldsRepeatable = array_merge($subFieldsRepeatable,$field->getSubFields());
			}
		}
		*/
	
		$doWeHaveTabs = false;
		unset($field);
		foreach($this->fieldset as $field){			
			if(!empty($this->fieldsToRender) AND !in_array($field->fieldname,$this->fieldsToRender)){
				continue;
			}
			
			if(!empty($this->form->isCustom) AND !empty($this->form->preform) AND (!empty($field->showinpreform) AND $field->showinpreform === 'false')){
				continue;
			}
			
			/*
			if(!empty($subFieldsRepeatable[$field->id]) AND $subFieldsRepeatable[$field->id]->group == $field->group){
				continue;
			}
			*/
			
			$html = '';
			
			if($this->formGroup === null){
				$this->formGroup = $field->group;
			}

			$fieldName = $field->fieldname;
			//$fValue = ByGiroHelper::array_path_value($item, $this->formGroup .'.'. $fieldName);
			@$fValue = $item->$fieldName;

			if($fValue !== null){
				try{
					$field->value = $fValue;
				} catch (Exception $e){
					$error = $e->getMessage();
				}
				
				// following code, not working when there are more than 1 field with same name and same group
				if(isset($error) AND $this->form instanceof JForm){
					try{
						@$this->form->setValue($fieldName,$field->group,$fValue);
						$field = $this->form->getField($fieldName,$field->group,$fValue);				
					} catch (Exception $e){
						$error = $e->getMessage();
					}
				}
			}

			$field->jdomOptions = (array)$field->jdomOptions;			
			if(!empty($fValue)){
				@$field->jdomOptions = array_merge($field->jdomOptions, array(
						'dataValue' => $fValue,
							));
			}
			
			if(!empty($this->enumList[$fieldName])){
				@$field->jdomOptions = array_merge($field->jdomOptions, array(
						'list' => $this->enumList[$fieldName],
							)
						);
			}
			
			@$field->jdomOptions = array_merge($field->jdomOptions, array(
					'dataObject' => $item,
					'formControl' => $this->formControl,
					'formGroup' => $this->formGroup,
					'prefix_id' => $this->prefix_id
						)
					);

			if(count($jdomOptions)){
				@$field->jdomOptions = array_merge($field->jdomOptions, $jdomOptions);
			}

			if ($field->hidden)
			{
				$html .= $field->getInputI($postfix);
				continue;
			}
			unset($error);
			
			$groupName = '';
			$containerClass = '';
			$classes = '';
			$canView = '';
			$canEdit = '';
			if($this->form instanceof JForm){
				$groupName = $this->form->getFieldAttribute($field->fieldname,'groupName',null,$field->group);
				$containerClass = $this->form->getFieldAttribute($field->fieldname,'containerClass',null,$field->group);
				$classes = $this->form->getFieldAttribute($field->fieldname,'class',null,$field->group);
				$canView = $this->form->getFieldAttribute($field->fieldname,'canView',null,$field->group);
				$canEdit = $this->form->getFieldAttribute($field->fieldname,'canEdit',null,$field->group);
			}

			//Check ACL
		    if ((method_exists($field, 'canView')) && !$field->canView()){
		    	continue;
			}
			
			// check ACL
			if($canView AND class_exists('ByGiroHelper')){
				$canView = ByGiroHelper::canAccess($canView);
			} else {
				$canView = true;
			}
			
			if(isset($this->jdomOptions['canEdit'])){
				$canEdit = $this->jdomOptions['canEdit'];
			} else if($canEdit AND class_exists('ByGiroHelper')){
				$canEdit = ByGiroHelper::canAccess($canEdit);
			} else {
				$canEdit = true;
			}

			if(!$canView){
				continue;
			}
			
			if(!$canEdit){
				try{
					$field->disabled = 1;
				} catch (Exception $e){
					$error = $e->getMessage();
				}
				
				$selectors = array();
				if(isset($field->jdomOptions['selectors'])){
					$selectors = $field->jdomOptions['selectors'];
				}
				if(is_array($selectors)){
					$selectors['disabled'] = 'disabled';
				} else {
					$selectors .= ' disabled="disabled"';
				}
				
				
				$field->jdomOptions = array_merge($field->jdomOptions, array(
					'selectors' => $selectors
						));
			}

			// is it a doT element?
			$isdoT = false;
			if(is_string($field->value) AND strpos($field->value,'{{=it.')!== false){
				$isdoT = true;
			}
			
			if($field->type == 'ckfieldset'){
				$field->jdomOptions = array_merge($field->jdomOptions, array(
						'formGroup' => trim($this->formGroup .'.'. $field->fieldname)
							));
			}

			if($field->type == 'ckfile' AND $this->tmplEngine == 'doT'){
				$field->jdomOptions['dataValue_changed'] = "{{=it['". $field->fieldname ."-raw_changed'] || 0}}";
				$field->jdomOptions['dataValue_editor'] = "{{=it['". $field->fieldname ."-raw_editor'] || ''}}";
			}
			
			$selectors = (($field->type == 'Editor' OR $field->type == 'Rules') ? ' style="clear: both; margin: 0;"' : '');


			if(isset($field->jdomOptions['containerClass'])){
				$containerClass = $field->jdomOptions['containerClass'];
			}
			
			if(isset($field->jdomOptions['groupName'])){
				$groupName = $field->jdomOptions['groupName'];
			}

			if(!$doWeHaveTabs AND !empty($groupName)) $doWeHaveTabs = true;
			
			if(isset($field->inputSelector)){
				$selectors .= ' '. $field->inputSelector;
			}
			
			$labelSelector = '';
			if(isset($field->labelSelector)){
				$labelSelector = $field->labelSelector;
			}
			
			$label = '<%label%>';
			$input = '<%input%>';
			$spacers = array('ckspacer','spacer');
			$hiddens = array('ckhidden','hidden');
			$type = strtolower($field->type);

			if($this->format != 'array'){
				if (method_exists($field, 'getLabel')){
					$reflection = new ReflectionMethod($field, 'getLabel');
					if (!$reflection->isPublic()) {
						if($this->form instanceof JForm){
							$label = $this->form->getLabel($field->fieldname);
						} else {
							$label = $field->label;
						}
					} else {
						$label = $field->getLabel();
					}
				}
				
				if($this->renderInput){
					if (!method_exists($field, 'getInputI')){
						if($this->form instanceof JForm){
							$input = $this->form->getInput($field->fieldname);
						} else {
							$input = $field->input;
						}
					} else {
						$input = $field->getInputI($postfix);
					}
				}

				if($this->renderOutput){
					if (method_exists($field, 'getOutput')){
						$htmlValue = $field->getOutput();
					} else {
						$htmlValue = (!empty($field->jdomOptions['dataValue'])) ? $field->jdomOptions['dataValue'] : $field->value;
					}
					
					if(!$this->renderInput){
						$input = $htmlValue;
						
						$toSkip = array('ckcaptcha');
						if(in_array($type,$toSkip)){
							continue;
						}
					}
				}
			}
						
			if($this->layout == 'table'){
				if(in_array($type,$hiddens)){
					$containerClass .= ' '. $classes;
					$html .= '<tbody style="display: none;"><tr class="field-' . $field->id . $field->responsive .' '. $containerClass .'"><td colspan="2">';
					$html .= $input;
					$html .= '</td></tr></tbody>';				
				} else if(in_array($type,$spacers)){
					$containerClass .= ' '. $classes;
					$html .= '<tr class="field-' . $field->id . $field->responsive .' '. $containerClass .'"><td colspan="2">';
					$html .= $label;
					$html .= '</td></tr>';
				} else {
					$html .= '<tr class="field-' . $field->id . $field->responsive . ' '. $containerClass .'">';
					$html .= '<th class="" '. $labelSelector .' >' 
							. $label
							. '</th>';
					$html .= '<td style="min-width: 300px;" class=""' . $selectors . '>'
							. $input
							. '</td>';
					$html .= '</tr>';
				}			
			} else {
				if(in_array($type,$hiddens)){
					$containerClass .= ' '. $classes;
					$html .= '<div style="display: none;" class="control-group field-' . $field->id . $field->responsive .' '. $containerClass .'">';
					$html .= $input;
					$html .= '</div>';				
				} else if(in_array($type,$spacers)){
					$containerClass .= ' '. $classes;
					$html .= '<div class="control-group field-' . $field->id . $field->responsive .' '. $containerClass .'">';
					$html .= $label;
					$html .= '</div>';
				} else {
					$html .= '<div class="control-group field-' . $field->id . $field->responsive . ' '. $containerClass .'">';
					$html .= '<div class="control-label" '. $labelSelector .' >' 
							. $label
							. '</div>';
					$html .= '<div class="controls"' . $selectors . '>'
							. $input
							. '</div>';
					$html .= '</div>';
				}
			}
		
			if($this->format == 'array'){
				$xml = array();
				if(method_exists($field,'getXML')){
					$ele = $field->getXML();
					$xml = current($ele->attributes());
				}
				
				$cels[$field->fieldname] = array(
					'html' => $html,
					'field' => $field,
					'xml' => $xml,
					'inputSelectors' => $selectors,
					'labelSelectors' => $labelSelector,
					'containerClass' => $containerClass,
					'groupName' 		=> $groupName,
					'fieldsetName' => $this->fieldsetName
				);
			} else {
				$cels[$field->fieldname] = array(
					'groupName' => $groupName,
					'html' => $html
				);
			}
		}
		
		// sort based on fieldsToRender
		if(!empty($this->fieldsToRender)){
			$newOrder = array();
			foreach($this->fieldsToRender as $fi){
				if(isset($cels[$fi])){
					$newOrder[$fi] = $cels[$fi];
				}
			}
			$cels = $newOrder;
		}
	
		$jf_actions = '';
		if(count($this->actions) > 0){		
			$buttons = '';
			foreach($this->actions as $act){
				$buttons .= JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'extra' => $act->extra,
						'icon' => $act->icon,
						'label' => $act->label
					));
			}
			
			$jf_actions = '<div class="actions_cln control-group btn-toolbar"><div class="btn-group">'
				.	$buttons
				.	'</div></div>';
			
			if($this->format == 'array'){
				$cels['jf_actions'] = array(
					'html' => $jf_actions,
					'input' => $buttons
				);
			}
		}
		

		if($this->format != 'array'){
			if($doWeHaveTabs){
				$cels = ByGiroHelper::groupArrayByValue($cels, 'groupName');
				
				$orphans = array();
				$tabs = array();
				unset($tb);
				foreach($cels as $k => $tb){
				
					$html = '';	
					unset($cel);
					foreach($tb as $cel){
						$html .= $cel['html'];
					}
					
					if(empty($k)){
						$orphans[] = $html;
					} else {
						if($isdoT){
							$rand = '_{{=it.id}}';
						} else {
							$rand = '_'. ByGiroHelper::generateRandomString(10);
						}
					
						$tabs[] = array(
							'id' => $k  . $rand,
							'name' => JText::_($k),
							'content' => $html
						);
					}
				}

				$html = '';
				if(!empty($tabs)){
					$html .= JDom::_('html.fly.bootstrap.tabs', array(
							'domId' => $this->fieldsetName .'_tabs',
							'domClass' => $this->fieldsetName .'_tabs',
							'tabs' => $tabs
						));
				}
				
				$html .= implode('',$orphans);				
				$cels = $html . $jf_actions;
			} else {
				$html = '';
				unset($cel);
				foreach($cels as $cel){
					$html .= $cel['html'];
				}
				$cels = $html . $jf_actions;
			}
		}

		return $cels;	
	}
}
