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
* @addon		Fly table list
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


class JDomHtmlListTable extends JDomHtmlList
{
	var $domClass = 'table table-striped table-bordered table-condensed table-hover ';	
	var $assetName = 'table';
	var $dndSortable = false;
	var $cpanel_opts = array();
	static $listLoaded;
	
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->attachCss[] = 'table.css';
		$this->arg('loadItemsByJs', null, $args, true);
	}
	
	function build()
	{
		static $allRepeatablesHtml;
		self::$allRepeatablesHtml =& $allRepeatablesHtml;
		
		$this->domClass .= $this->listName .'_tablelist';
		$header_cels = array();
		foreach($this->fieldset as $field){
			$field->markup = 'span';
			$field->getLabel();

			if(count($this->fieldsToRender) AND !in_array($field->fieldname,$this->fieldsToRender)){
				continue;
			}

			// detect DnD sortable
			if($field->type == 'ckordering' AND !$this->dndSortable){
				$this->dndSortable = true;
			}
			
			$extraClass = $jList_extra = '';
			
			if($this->jListByGiro){
				$this->cpanel_opts['sortableFields']['id'] = 'id';
				
				$isFilterable = $isSortable = $isSearchable = false;
				if(($this->form instanceof JForm)){
					$isFilterable = $this->form->getFieldAttribute($field->fieldname,'filterable',null,$field->group);
					$isSortable = $this->form->getFieldAttribute($field->fieldname,'sortable',null,$field->group);
					$isSearchable = $this->form->getFieldAttribute($field->fieldname,'searchable',null,$field->group);
				}
				
				if($isSortable){
					$this->cpanel_opts['sortableFields'][$field->fieldname] = $field->fieldname;
				
					$jList_extra = 'data-jlist-list-name="'. $this->listName .'_list" data-jlist-sort-by="'. $field->fieldname .'"';
					$extraClass = 'jListSort';
				}
				
				if($isSearchable){
					$this->cpanel_opts['searchableFields'][$field->fieldname] = $field->fieldname;
				}
				
				if($isFilterable){
					$this->cpanel_opts['filterableFields'][$field->fieldname] = $field->fieldname;
				}
			}
			
			$header_cels[$field->fieldname] = '<th class="'. $field->fieldname .'_cln '. $extraClass .'" '. $jList_extra .'>'. $field->getLabel() .'</th>';
		}
		
		// sort based on fieldsToRender
		if(!empty($this->fieldsToRender)){
			$newOrder = array();
			foreach($this->fieldsToRender as $fi){
				if(isset($header_cels[$fi])){
					$newOrder[$fi] = $header_cels[$fi];
				}
			}
			$header_cels = $newOrder;
		}

		if($this->showId){
			$extraClass = $jList_extra = '';
			if($this->jListByGiro){
				$jList_extra = 'data-jlist-list-name="'. $this->listName .'_list" data-jlist-sort-by="id"';
				$extraClass = 'jListSort';
			}
			
			$id_cln = '<th class="id_cln '. $extraClass .'" '. $jList_extra .'>ID</th>';
			array_unshift($header_cels,$id_cln);
		}
		
		if($this->showCounter){
			$counter_cln = '<th class="counter_cln">N°</th>';
			array_unshift($header_cels,$counter_cln);
		}

		$newBtn = '';
		if(!in_array('new',$this->disabledActions)){
			// NEW
			$act = new stdClass;
			$act->label = 'JTOOLBAR_NEW';
			$act->icon = 'icomoon icon-plus-2 icon-white';
			$act->domClass = 'btn-new btn btn-success btn-mini';
			$act->selectors = '';
			
			$newBtn = JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'selectors' => $act->selectors,
						'icon' => $act->icon,
						'label' => $act->label
					));		
		}

		$security = '';
		if(!in_array('delete',$this->disabledActions)){
			// REMOVE ALL
			$act = new stdClass;
			$act->label = 'JACTION_DELETE_ALL';
			$act->icon = 'icomoon icon-remove icon-white';
			$act->domClass = 'btn-deleteAll btn btn-danger btn-mini';
			$act->selectors = '';
			
			$deleteAllBtn = JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'selectors' => $act->selectors,
						'icon' => $act->icon,
						'label' => $act->label
					));
		
			// security lock delete
			$security = '
				<ul class="actions_options">
					<li><input autocomplete="off" type="checkbox" checked="checked" class="security_lock_delete_'. $this->masterListName .'" id="security_lock_delete_'. $this->listName .'_container" /></li>
					<li><label for="security_lock_delete_'. $this->listName .'_container">'. JText::_("PLG_JDOM_SECURITY_LOCK_DELETE") .'</label></li>
					<li><i class="icomoon icon-help hasTooltip" data-placement="left" title="'. JText::_("PLG_JDOM_SECURITY_LOCK_DELETE_DESC") .'"></i></li>
				</ul>';
		}
		
		$header_cels[] = '<th class="actions_cln">'. $newBtn .' '. $deleteAllBtn .' '. $security .'</th>';
		
		$header_cels = '<tr data-item-id="0">'. implode('',$header_cels) .'</tr>';

		// add items by PHP
		$rows = '';
		if(!$this->loadItemsByJs){
			foreach($this->dataList as $item){
				$rows .= $this->buildItem($item);
			}
		}
		
		$listName = $this->moveLevel($this->listName,-1);
		$html =
'<table id="'. $listName .'_container" <%CLASS%><%SELECTORS%>>
	<thead>
		'. $header_cels .'
	</thead>
	<tbody id="'. $listName .'_list" class="'. $this->masterListName .'_list">
		'. $rows .'
	</tbody>
</table>';
		
		static $counter;
		$counter++;
		// add dom elements needed for repeatable feature
		if($this->repeatable){
			$allRepeatablesHtml[$this->masterListName] = array();
			$allRepeatablesHtml[$this->masterListName]['isMaster'] = $this->isMaster;
			
			$allRepeatablesHtml[$this->masterListName]['status'] = 'open';			
			$allRepeatablesHtml[$this->masterListName]['html'] = $this->buildRepeatable();
			$allRepeatablesHtml[$this->masterListName]['status'] = 'closed';
		}

		if($allRepeatablesHtml[$this->masterListName]['isMaster'] AND $allRepeatablesHtml[$this->masterListName]['status'] == 'closed'){
			foreach($allRepeatablesHtml as $kk => $list_HTML){
				$html .= '<div class="'. $kk .'">'. $list_HTML['html'] .'</div>';
			}
			
			// reset the repeatable array
			$allRepeatablesHtml = array();
		}

		if($this->autoFilter AND !$this->jListByGiro){
			JDom::_('framework.jquery.filtertable');
			$html .= '
			<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("#'. $listName .'_container").filterTable({
					label: "'. JText::_('JDOM_FILTERTABLE_LABEL') .'",
					placeholder: "'. JText::_('JDOM_FILTERTABLE_SEARCH_THIS_TABLE') .'",
				});
			});
			</script>
			';
		}		
		
		return $html;
	}
	
	public function buildRepeatableDomHtml(){
		$html = '';
			
		// add forms container
		$html .= '<div style="display: none;" id="'. $this->masterListName .'_forms"></div>';

		
		// build the default item
		$defaultValues = new stdClass;
		$defaultValues->id = 0;
		
		// build the FAKE item for the output		
		$fakeItemOutput = new stdClass;
		$fakeItemOutput->id = '{{=it.id}}';
		
		// build the FAKE item for the form template		
		$fakeItemForm = new stdClass;
		$fakeItemForm->id = '{{=it.id}}';
		
		$formFieldsToRender = array();
		foreach($this->fieldset as $fi){		
			$fieldName = $fi->fieldname;
			$multilanguage = ($fi->getOption('multilanguage') != '') ? true : false;
			
			$def = $fi->getOption('default') ? $fi->getOption('default') : '';
			if(in_array(strtoupper($fi->getOption('filter')),array('INT','BOOL'))){
				$def = intVal($def);
			}
			
			$listOpts = strtolower($fi->getOption('list'));
			switch($listOpts){
				case 'self':				
					$fi->jdomOptions = array_merge($fi->jdomOptions,array('list' => $this->dataList));
					break;
				
				case 'parent':
					// to do
					break;
					
				default:
					break;
			}
			
			$ml_fieldnames = array();
			$ml_fieldnames[] = $fieldName;
			
			if($multilanguage OR $this->multilanguage){
				// get all languages
				$languages = ByGiroHelper::getInstalledLanguages();
			
				foreach($languages as $lang){
					$ml_fieldnames[] = $fieldName . $lang->postfix;
				}		
			}
			
			foreach($ml_fieldnames as $ml_fname){
				switch($fi->type){
					default:
						$fakeItemOutput->$ml_fname = '{{=it.'. $ml_fname .'}}';
						$fakeItemForm->$ml_fname = '{{=it.'. $ml_fname .'}}';
						$defaultValues->$ml_fname = $def;
						break;
				}
			}
		
			if($fi->type == 'ckordering' OR $fi->type == 'ckfieldset'){
				continue;
			}			
			$formFieldsToRender[$fieldName] = $fi->type;
		}
			
		// add form template
		$formHash = md5($this->formGroup . serialize($fakeItemOutput) . serialize($formFieldsToRender));
		$html .= $this->buildTmplForm($formFieldsToRender,$fakeItemForm, $formHash);	
		
		// add item template
		$itemHash = md5($this->formGroup . serialize($fakeItemOutput) . serialize($this->fieldsToRender));
		$html .= $this->buildTmplItem($fakeItemOutput, $itemHash);
		
		$unique = '';
		if($this->unique != ''){
			$unique = "unique: ['". implode("','",$this->unique) ."'],";
		}
		
		$script = "
			window['". $this->masterListName ."'] = {
			sample_item: JSON.parse('". str_replace("'","\'",$this->escapeJsonString(json_encode($defaultValues))) ."'),
			maxItems: ". $this->maxItems .",
			popup: '#fset_modal_form',
			". $unique ."
			tmpl_form: '". $formHash ."',
			tmpl_item: '". $itemHash ."'
			};
			";

		$this->addScriptInline($script);
			
		if($this->dndSortable){
			$html .= $this->buildOrdering();
		}

		return $html;
	}
	
	function buildItem($item, $tmplEngine = ''){
		static $counter;
		static $prevList;
	
		if($prevList == $this->listName){
			$counter++;
		} else {
			$prevList = $this->listName;
			$counter = 1;
		}
			
		$dataItem = '';
		if($tmplEngine == ''){
			$dataItem = 'data-item="'. htmlentities(json_encode($item)) .'"';
		}
	
		$html = '<tr class="'. $this->masterListName .'_item" data-item-id="'. $item->id .'" '. $dataItem .'>';
		
		if($this->showCounter){
			if($tmplEngine == 'doT'){
				$counter = '{{=it.list_counter || ""}}';
			}
			$html .= '<td class="counter_cln">'. $counter .'</td>';
		}
		
		if($this->showId){
			$html .= '<td class="id_cln">'. $item->id .'</td>';
		}
		
		$formGroup = trim($this->formGroup .'.{{=it.id}}','.');
		
		$html .= JDom::_('html.list.item', array(
				'domClass' => '',
				'fieldsToRender' => $this->fieldsToRender,
				'fieldset' => $this->fieldset,
				'form' => $this->form,
				'enumList' => $this->enumList,
				'dataObject' => $item,
				'actions' => $this->actions,
				'markup' => 'td',
				'formControl' => $this->formControl,
				'formGroup' => $formGroup,
				'tmplEngine' => $tmplEngine
			));
		$html .= '</tr>';
		
		return $html;
	}
	
	function buildRepeatable(){
		$html = '';
		$html .= $this->buildRepeatableDomHtml();

		$options = "
			var options = {};
		";
		
		// load items by JS
		if($this->loadItemsByJs){
			$items = array_values($this->dataList);
			// TO DO: check it!!!
			$options .= "
				options.values = JSON.parse('". str_replace("'","\'",$this->escapeJsonString(json_encode($items))) ."');				
			";
			
			// add previous data form (for example when a form data is not valid and the user come back to the edit page, to avoid to fill again the form)
			if(isset($this->form->context) AND !empty($this->form->context)){
				$previous_data = JFactory::getApplication()->getUserState($this->form->context, array());
				@$previous_data = $previous_data[$this->listName];
				if(!empty($previous_data)){
					$cleanPreviousData = array();
					foreach($previous_data as $k => $d){
						$d = (object)$d;
						$d->id = $k;
						$cleanPreviousData[] = $d;
					}
					
					$options .= "
						options.formValues = JSON.parse('". str_replace("'","\'",$this->escapeJsonString(json_encode($cleanPreviousData))) ."');				
					";
				}
			}
		}
		
		if($this->jListByGiro){	
			$options .= "options.jListByGiro = true;";
			$options .= "options.uniqueIndex = 'id';";
			$options .= "options.controlPanel = true;";
		
			foreach($this->cpanel_opts as $key => $opt){
				if(count($opt) > 0){
					$options .= "options.". $key ." = ". json_encode(array_values($opt)) .";";
				}
			}
			
			if(count($items) < 40){
				$options .= "options.pagination = false;";
			}
		}
		
		$script = $options;
		$script .= 'initList(jQuery(\'[data-repeatable="'. $this->masterListName .'"]\'),options);';

		$this->addScriptInline($script, true);
		
		return $html;
	}
	
	function buildTmplForm($formFieldsToRender,$fakeItemForm, $hash) {
		static $tmplForms;
		
		if(!isset($tmplForms)){
			$tmplForms = array();
		}
		
		$html = '';	
		if(isset($tmplForms[$hash])){
			return $html;
		}

		$formGroup = trim($this->formGroup .'.{{=it.id}}','.');
		$group = preg_replace("/\.(?!([^\{\{]+)?\}\})/i",'||',$formGroup);
		$remove_name = $this->formControl .'[' . implode('][', explode('||', $group)) . '][remove_item]';

		$html .= 
		'<script id="tmpl_'. $hash .'_form" type="text/x-dot-template">
				<fieldset data-item-id="{{=it.id}}" data-item-isnew="{{=it.isNew}}" class="fieldsform form-vertical row-fluid">
					<input type="hidden" class="remove_item" name="'. $remove_name .'" />
				'.
				
				JDom::_('html.form.fieldset', array(
						'fieldset' => $this->fieldset,
						'multilanguage' => $this->multilanguage,
						'fieldsToRender' => array_keys($formFieldsToRender),
						'formControl' => $this->formControl,
						'formGroup' => $formGroup,
						'form' => $this->form,
						'dataObject' => $fakeItemForm,
						'enumList' => $this->enumList,
						'tmplEngine' => $this->tmplEngine,
						'jdomOptions' => array('repeatable' => true)
					))

				.'</fieldset>
		</script>';
		
		$tmplForms[$hash] = $html;
		
		return $html;
	}
	
	function buildTmplItem($fakeItemOutput, $hash) {
		static $tmplItems;
		
		if(!isset($tmplItems)){
			$tmplItems = array();
		}		
		
		$html = '';
		if(isset($tmplItems[$hash])){
			return $html;
		}	
		
		$html = '';
		$html .=	
'<script id="tmpl_'. $hash .'_item" type="text/x-dot-template">
		'. $this->buildItem($fakeItemOutput, $this->tmplEngine) .'
</script>';	
		
		$tmplItems[$hash] = $html;
		
		return $html;
	}
	
	function buildOrdering(){
		static $orderings;

		$html = '';		
		if(isset($orderings[$this->masterListName])){
			return $html;
		}
	
		// add the hidden input for the list ordering
		$html .= '<input type="hidden" value="" id="'. $this->masterListName .'_list_ordering" name="'. $this->formControl .'['. $this->masterListName .'_list_ordering]" />';
	
		JDom::_('framework.jquery.sortable');
		$script = '
			jQuery(\'[data-repeatable="'. $this->masterListName .'"]\').sortable({
				containerSelector: "table",
				handle: "i.icon-menu",
				nested: true,
				itemPath: ".'. $this->masterListName .'_list",
				itemSelector: "tr",
				placeholder: \'<tr class="sortablePlaceholder"/>\',
				onDrop: function ($item, container, _super) {
					$item.removeClass("dragged").removeAttr("style");
					jQuery("body").removeClass("dragging");
					
					// update the ordering
					updateListOrdering(jQuery(\'[data-repeatable="'. $this->masterListName .'"]\'));
				}
			});			
		';

		$this->addScriptInline($script, true);
		
		$orderings[$this->masterListName] = true;
		return $html;
	}
	
	function buildJS(){

	}	
}