<?php
/*
 * package		Repeatable field
 * @copyright	Copyright (C) 2014 Girolamo Tomaselli All rights reserved.
 * @email		girotomaselli@gmail.com
 * @website		http://bygiro.com
 * @license		GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JDomHtmlFormInputRepeatable extends JDomHtmlFormInput
{
	static $loaded;
	static $sortableLoaded;
	
	var $debug;
	var $btnActions;
	var $fieldContainer;
	var $form;
	var $fields;
	var $fieldNames;
	var $sortable;
	var $confirmationOnDelete;
	var $showCounter;
	var $fieldsToRender;
	var $formControl;
	var $formGroup;
	var $max;
	var $layout;
	var $list;
		
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('form'				, null, $args, null);
		$this->arg('showCounter'	, null, $args, false);
		$this->arg('sortable'		, null, $args, false);
		$this->arg('confirmationOnDelete' , null, $args, true);
		$this->arg('isMaster'		, null, $args, false);
		$this->arg('layout'			, null, $args, 'table'); // table, collapsible, modal
		$this->arg('fieldsToRender', null, $args);
		$this->arg('formGroup'		, null, $args);
		$this->arg('formControl'	, null, $args, 'jform');
		$this->arg('min'				, null, $args, 0);
		$this->arg('max'				, null, $args, 999);
		$this->arg('debug'			, null, $args, false);
		$this->arg('list'		, null, $args);
		
		// add table.css
		$this->addStyleSheet('assets/table/css/table.css');
		
		if(!empty($this->fieldsToRender) AND is_string($this->fieldsToRender)){
			$this->fieldsToRender = array_map('trim', explode(',',$this->fieldsToRender));
		}
		
		
		// add actions
		$actions = array();
		
		// NEW
		$act = new stdClass;
		$act->label = 'JTOOLBAR_NEW';
		$act->icon = 'icomoon icon-plus-2 icon-white';
		$act->domClass = $this->domId .'_edit btn-new btn btn-success btn-mini';
		$act->selectors = array(
		//	'title' => JText::_('JACTION_NEW')
		);
		$actions['new'] = $act;

		// edit
		$act = new stdClass;
		$act->label = '';
		$act->icon = 'icomoon icon-pencil icon-white';
		$act->domClass = $this->domId .'_edit btn btn-success btn-mini hasTooltip';
		$act->selectors = array(
		//	'title' => JText::_('JACTION_EDIT')
		);		
		$actions['edit'] = $act;
		
		// copy
		$act = new stdClass;
		$act->label = '';
		$act->icon = 'icomoon icon-save-copy icon-white';
		$act->domClass = $this->domId .'_clone btn btn-warning btn-mini hasTooltip';
		$act->selectors = array(
		//	'title' => JText::_('JACTION_COPY')
		);		
		$actions['copy'] = $act;
		
		
		// DELETE
		$act = new stdClass;
		$act->label = '';
		$act->icon = 'icomoon icon-remove icon-white';
		$act->domClass = $this->domId .'_delete btn btn-danger btn-mini hasTooltip';
		$act->selectors = array(
		//	'title' => JText::_('JACTION_DELETE')
		);
		$actions['delete'] = $act;
		
		$this->btnActions  = $actions;
	}
	
	public function build()
	{
	
		$html = array();
		$this->fieldContainer = $this->domId . '_container';

		if(!($this->form instanceof JForm)){
			return '';
		}

		$value = $this->dataValue;
		if(strpos($value,'{{=it.') !== false){
			// we have DOT variable;
			$value = "{{var value = escapeHtml(it.". $this->dataKey .");}}{{=value}}";
		} else {
			$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		
		$fsets = $this->form->getFieldsets();		
		if(empty($fsets)) return '';

		// test
		if($this->debug){
			$html[] = '<span onclick="console.log(jQuery(this).next().next().cloner(\'getData\', true));" class="btn btn-mini">GET</span>';
			$html[] = '<span data-vallo="'. $value .'" onclick="var data = jQuery(this).data(\'vallo\');jQuery(this).next().cloner(\'setData\',data);" class="btn btn-mini">SET</span>';
		}

		$class = '';
		if($this->sortable){
			$class = 'repeatable_sortable';
		}		

		$header_cels = array();
		$td_cels = array();
		$itemForm = '';
		foreach($fsets as $fset){
			$fields = $this->form->getFieldset($fset->name);
			if(empty($fields)) continue;
			
			$multilanguage = !empty($fset->multilanguage) ? true : false;
			
			// render fields in a block
			$fsetData = JDom::_('html.form.fieldset', array(
						'fieldset' => $fields,
						'multilanguage' => $multilanguage,
						'formControl' => '',
						'formGroup' => '',
						'format' => 'array',
						'form' => $this->form,
						'enumList' => $this->list
					));			
	
			foreach($fsetData as $key => $field){
				// skip
				$skipTypes = array('ckspacer','ckordering');
				if(in_array($field['field']->type,$skipTypes)) continue;
				
				$header_cels[$key] = '<th class="'. $key .'_cln">'. $field['field']->getLabel() .'</th>';
				if($this->layout != 'plain'){
					$td_cels[$key] = '<td class="'. $key .'_cln" data-cloner-field="'. $key .'"></td>';
				} else {
					$td_cels[$key] = '<td class="'. $key .'_cln">'. $field['field']->getInput() .'</td>';
				}
			}
			
			if(!empty($this->fieldsToRender)){
				$newOrderHeader = array();
				$newOrderTds = array();
				foreach($this->fieldsToRender as $fi){
					if(isset($header_cels[$fi])){
						$newOrderHeader[$fi] = $header_cels[$fi];
						$newOrderTds[$fi] = $td_cels[$fi];
					}
				}
				$header_cels = $newOrderHeader;
				$td_cels = $newOrderTds;
			} else if($this->layout != 'plain'){
				$header_cels = array_slice($header_cels, 0, 4);
				$td_cels = array_slice($td_cels, 0, 4);
			}
			
			if($this->layout != 'plain'){
				$itemForm .= JDom::_('html.form.fieldset', array(
							'fieldset' => $fields,
							'multilanguage' => $multilanguage,
							'formControl' => '',
							'formGroup' => '',
							'form' => $this->form,
							'prefix_id' => $this->dataKey . '_'
						));
			}
		}
		
		if(empty($header_cels)) return '';

		// security lock delete
		$security = '
			<ul class="actions_options">
				<li><input autocomplete="off" type="checkbox" checked="checked" class="security_lock_delete" id="security_lock_delete_'. $this->domId .'" /></li>
				<li><label for="security_lock_delete_'. $this->domId .'">'. JText::_("PLG_JDOM_SECURITY_LOCK_DELETE") .'</label></li>
				<li><i class="icomoon icon-help hasTooltip" data-placement="right" title="'. JText::_("PLG_JDOM_SECURITY_LOCK_DELETE_DESC") .'"></i></li>
			</ul>';
				
		$act = $this->btnActions['new'];
		$th_actions = '<th>'.
			JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'selectors' => $act->selectors,
						'icon' => $act->icon,
						'label' => $act->label
					))
		.	$security
		.'</th>';
		
		array_unshift($header_cels,$th_actions);
		
		$header = '<tr>'. implode('',$header_cels) .'</tr>';
				
		// add extras		
		$counter = $this->showCounter ? '<span data-cloner-index="1"></span>' : '';
		
		
		$sortControls = $this->sortable ? '<span class="sortable-handler" >'
					.	'<i class="icon-menu icomoon '. $this->domId .'_handle"></i>'
					.	'</span>' : '';
		
		$buttons = '';
		unset($act);
		foreach($this->btnActions as $k => $act){
			if($k == 'new') continue;
			
			if($this->layout == 'plain' AND $k == 'edit') continue;
			
			$buttons .= JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'selectors' => $act->selectors,
						'icon' => $act->icon,
						'label' => $act->label
					));		
		}
		
		$cloneControls = '<div class="btn-group">'. $buttons .'</div>';
			
		array_unshift($td_cels,'<td class="actions">'. $counter . $sortControls . $cloneControls .'<div class="itemform_repeatable '. $this->domId .'_editForm">'. $itemForm .'</div></td>');
		
		$row = '<tr class="'. $this->domId .'_toclone" data-cloner-id="0">'. implode('',$td_cels) .'</tr>';
		$clonerOpts = '
			data-max="'. $this->max .'" 
			data-clone-this=".'. $this->domId .'_toclone" 
			data-items-container=".'. $this->domId .'_items" 
			data-group="'. $this->dataKey .'" 
			data-confirmation-on-delete="'. $this->confirmationOnDelete .'" 
			data-edit-form=".'. $this->domId .'_editForm" 
			data-edit-button=".'. $this->domId .'_edit" 
			data-clone-button=".'. $this->domId .'_clone" 
			data-delete-button=".'. $this->domId .'_delete" 
		';
		
		if($this->sortable){
			$clonerOpts .= 'data-sortable-list="true" ';
		}
		
		switch($this->layout){
				
			case 'default':
			case 'plain':
			default:
				$html[] = '<table id="' . $this->fieldContainer . '" '. $clonerOpts .' class="clonerContainer  table table-striped table-bordered table-condensed table-hover" data-target-field="#'. $this->domId .'" data-cloner-group="'. $this->dataKey .'">';
				$html[] = '	<thead>'. $header .'</thead>';
				$html[] = '	<tbody class="'. $this->domId .'_items">';
				$html[] = 		$row;
				$html[] = '	</tbody>';
				$html[] = '</table>';
				break;
		}
		
		if($this->isMaster){
			$html[] = '<input type="hidden" id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%> value="' . $value . '" />';
		}
		
		// add needed JS
		$this->init();
		
		return implode("\n", $html);
	}
	
	function init()
	{	
		$doc = JFactory::getDocument();
		$root = JURI::root(true);

		// Only load once
		if($this->sortable){
			if (empty(static::$sortableLoaded[__METHOD__])){

				JDom::_('framework.jquery.sortable');
				
				$script = "			
				sortableEnabler = function(target){
					if(!(target instanceof jQuery)){
						target = jQuery(target);
					}
					
					if(target.length <= 0) return;
					
					var sortablePlaceholder,
					targetId = target.attr('data-target-field');
					
					if(!targetId) return;
					targetId = targetId.replace('#','');
				
					if(target.is('table')){
						sortablePlaceholder = '<tr class=\"sortablePlaceholder\"/>';
					} else if(target.is('ul,ol')){
						sortablePlaceholder = '<li class=\"sortablePlaceholder\"></li>';
					} else {
						sortablePlaceholder = '<div class=\"sortablePlaceholder\"></div>';
					}
					
					target.sortable({
					//	containerSelector: '#'+ selector ,
						handle: 'i.icon-menu.'+ targetId +'_handle',
						nested: false,
						itemPath: '> .'+ targetId +'_items',
						itemSelector: '.'+ targetId +'_toclone',
						placeholder: sortablePlaceholder,
						delay: 10,
						onDrop: function (item, container, _super) {
							var cloner = item.closest('[data-cloner-hash]');
							if(cloner.data('plugin_cloner')){
								cloner.cloner('setClonerIds',true);
							}
							_super(item, container);
						}
					});
				}
				";
				
				$doc->addScriptDeclaration($script);
				
				static::$sortableLoaded[__METHOD__] = true;
			}
		}
		
		// Only load once
		if (empty(static::$loaded[__METHOD__])){

			JDom::_('framework.jquery.cloner');
			JDom::_('framework.jquery.multiselect');
			JDom::_('framework.jquery.condrules');
			
			$script = "			
			var delay = (function(){
			  var timer = 0;
			  return function(callback, ms){
				clearTimeout (timer);
				timer = setTimeout(callback, ms);
			  };
			})();			

			function pluginsEnabler(obj){
				// enable sortable
				var cloner = obj.first().closest('[data-cloner-hash]');
				if(cloner.attr('data-sortable-list')){
					sortableEnabler(cloner);
				}
			
				obj.each(function(i){
					var v = jQuery(this);
					
					// enable btn radio group
					v.radioToBtn();

					// color picker enabler
					if(typeof colorpickerEnabler == 'function'){
						colorpickerEnabler(v);
					}

					// tags manager
					if(typeof jQuery.fn.tagsManagerByGiro != 'undefined'){
						v.find('input[tgman]').tagsManagerByGiro();
					}

					// enable multiselect					
					if(typeof jQuery.fn.multiselectByGiro != 'undefined'){
						v.find('select.multiselect').not('.select-many').each(function(i){
							if(jQuery(this).data('plugin_multiselectByGiro')){
								jQuery(this).multiselectByGiro('destroy');
								jQuery(this).multiselectByGiro();
							} else {
								var dParent = jQuery(this).parent();
								dParent.find('.multiselect-container').remove();
								jQuery(this).show();
								jQuery(this).multiselectByGiro();
							}
						});
					}
					
					// enable tooltips
					if(typeof jQuery.fn.tooltip != 'undefined'){
						v.find('.hasTooltip').tooltip({'html': true,'container': 'body'});
					}
				});
				
				// conditional rules
				if(typeof jQuery.fn.condRules == 'function'){
					obj.condRules();
				}
				
				// timepicker
				if(typeof jQuery.fn.timepickerByGiro == 'function'){
					obj.find('.timepickerByGiro').each(function(){
						jQuery(this).timepickerByGiro('destroy');
						jQuery(this).timepickerByGiro();
					});
				}

				// datetimepicker
				if(typeof dateTimePickerEnabler == 'function'){
					dateTimePickerEnabler(obj);
				}
				
				if(typeof addressPickerEnabler == 'function'){
					addressPickerEnabler(obj);
				}
			}
			
			function clonerEnabler(obj){
				if(!(obj instanceof jQuery)){
					obj = jQuery(obj);
				}

				var clonerContainers = obj.find('.clonerContainer');
				if(typeof jQuery.fn.chosen != 'undefined'){
					clonerContainers.find('select.chzn-done').each(function(){
						// remove chosen
						jQuery(this)
							.chosen('destroy')
							.removeClass('chzn-done')
							.val(jQuery(this).val());
					});
				}
				
				while( clonerContainers.length ) {			
					clonerContainers.each(function(){
						// firstly find nested cloner to be enabled
						if(jQuery(this).find('.clonerContainer:not([data-cloner-hash])').not(jQuery(this)).length == 0){					
							
							pluginsEnabler(jQuery(this));
							
							jQuery(this).cloner();
							
							if(jQuery(this).attr('data-sortable-list')){
								sortableEnabler(jQuery(this));
							}
							
							// clonerContainers.not(jQuery(this));
							clonerContainers = clonerContainers.not(jQuery(this));
						}
					});			
				}
			}
						
			jQuery(document).ready(function(){
				clonerEnabler('body');
				
				jQuery(document).on('cloner_after_clone',function(a,b,c,d,e,f){
					pluginsEnabler(d);
				});
				
				jQuery(document).on('cloner_after_clone cloner_after_delete cloner_set_cloner_ids',function(a,b,c,d,e,f){				
					// check post max size
					jQuery('form').each(function(){					
						if(jQuery(this).data( 'plugin_postMaxSize' )){
							jQuery(this).postMaxSize('check');
						}
					});
					
					delay(
						function(){
							var mainParentCloner = b.options.\$element.parents('[data-cloner-hash]').last();
							if(mainParentCloner.length == 0){
								mainParentCloner = b.options.\$element;
							}
							
							var data = mainParentCloner.cloner('getData',true),
								target = mainParentCloner.attr('data-target-field');
								
							if(target){
								jQuery(target).val(JSON.stringify(data));
							}
							
							// console.log(jQuery('form').find('input,select,textarea').length);
						},
						50
					);
				});
			});			
			";
			
			$doc->addScriptDeclaration($script);
			
			static::$loaded[__METHOD__] = true;
		}
	}
}
