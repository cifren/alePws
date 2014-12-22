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
* @addon		list
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


class JDomHtmlList extends JDomHtml
{
	var $fallback = 'table';	//Used for default
	var $listName;
	var $masterListName;

	var $attachJs = array();
	var $attachCss = array();
	var $selectors;
	var $jListByGiro;
	var $loadItemsByJs;
	var $showId;
	var $showCounter;
	var $autoFilter;
	var $multilanguage;
	var $repeatable;
	var $maxItems;
	var $editable;
	var $disabledActions;
	var $unique;
	var $markup;
	var $isMaster;
	var $formControl;
	var $formGroup;
	var $enumList = array();
	static $listLoaded = array();
	static $allRepeatablesHtml = array();
	
	protected $dataList;
	protected $fieldset;
	protected $form;
	protected $fieldsToRender;
	protected $actions;
	protected $tmplEngine;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 * 	@selectors	: selectors
	 * 	@listName	: default 'itemsList'
	 * 	@jformFields: array of jform fields 
	 * 	@fieldsToRender: array of fields  to render
	 * 	@actions	: default array()
	 * 	@tmplEngine	: JS Template Engine, default null
	 * 	@domClass	: CSS class
	 * 	@jListByGiro: default: false
	 * 	@repeatable	: default: false
	 * 	@editable	: default: false
	 * 	@dataList	: values list (array of objects)
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('listName'		, null, $args, 'itemsList');
		$this->arg('dataList'		, null, $args, array());
		$this->arg('enumList'		, null, $args, array());
		$this->arg('formGroup'		, null, $args);
		$this->arg('formControl'	, null, $args, 'jform');
		$this->arg('selectors'		, null, $args);
		$this->arg('fieldset'		, null, $args, false);
		$this->arg('fieldsetName'	, null, $args);
		$this->arg('form'			, null, $args);
		$this->arg('fieldsToRender'	, null, $args, array());
		$this->arg('actions'		, null, $args, array());
		$this->arg('disabledActions', null, $args, array());
		$this->arg('autoFilter'		, null, $args, false);
		$this->arg('markup'			, null, $args, 'td');
		$this->arg('tmplEngine'		, null, $args, 'doT');
		$this->arg('domClass'		, null, $args);
		$this->arg('isMaster'		, null, $args, false);
		$this->arg('showId'			, null, $args, false);
		$this->arg('showCounter'	, null, $args, false);
		$this->arg('multilanguage'	, null, $args, false);
		$this->arg('repeatable'		, null, $args, false);
		$this->arg('maxItems'		, null, $args, 5000);
		$this->arg('editable'		, null, $args, false);
		$this->arg('unique'			, null, $args, '');
		$this->arg('jListByGiro'	, null, $args, false);
		$this->arg('loadItemsByJs', null, $args, true);
		
		static $listLoaded;
		JDom::_('framework.jquery.postmaxsize');
		
		if(!is_array($listLoaded)){
			$listLoaded = array();
		}
		
		if(!is_array($this->dataList)){
			$this->dataList = (array)$this->dataList;
		}
		
		if($this->form instanceof JForm AND isset($this->fieldsetName)){			
			if(!$this->fieldset){
				$this->fieldset = $this->form->getFieldset($this->fieldsetName);
			}
			
			$fieldSets = $this->form->getFieldsets();
			$fset = $fieldSets[$this->fieldsetName];
			$this->multilanguage = (isset($fset->multilanguage) AND $fset->multilanguage != '') ? true : false;
			$this->repeatable = (isset($fset->repeatable) AND $fset->repeatable != '') ? true : false;
			$this->editable = (isset($fset->editable) AND $fset->editable != '') ? true : false;
			if(isset($fset->unique)){
				$this->unique = explode(',',$fset->unique);
			}
		}
		
		if(is_string($this->disabledActions)){
			$this->disabledActions = array_map('trim',explode(',',$this->disabledActions));
		}
		
		if($this->formGroup != ''){
			$this->listName = preg_replace("/\.(?!([^\{\{]+)?\}\})/i",'_',$this->formGroup);
		}

		
		// create masterListName
		if($this->masterListName == ''){
			$this->masterListName = preg_replace('/\{\{.*?\}\}/', '', $this->formGroup);
			$this->masterListName = str_replace('.','_',str_replace('..','.',trim($this->masterListName,'.')));
		}
	

	/*
		if(count($this->fieldset)){
			$arr = array_values($this->fieldset);
			$firstField = array_shift($arr);
			$this->formGroup = trim($this->formGroup .'.'. $firstField->group,'.');
		}
		*/
		
		if(!isset($listLoaded[$this->listName])){
			$listLoaded[$this->listName] = array();
		} else {
			$base = $this->listName;
			$counter = 1;
			while(isset($listLoaded[$base])){
				$counter++;
				$base = $this->listName .'_'. $counter;
			}
			$this->listName = $base;
			$listLoaded[$this->listName] = array();
		}
		self::$listLoaded = $listLoaded;
		
		if($this->jListByGiro){
			JDom::_('framework.jquery.jlist');
		}
				
		if($this->repeatable){
			JDom::_('framework.dot');
			JDom::_('framework.jquery.repeatable');
			
			// check this is the first instance in the page
			if(count(self::$listLoaded) == 1){
				echo $this->buildPopup();
			}
			
			// add data attributes
			if(is_array($this->selectors)){
				$this->selectors['data-repeatable'] = $this->masterListName;
			} else {
				$this->selectors = (string)$this->selectors;
				$this->selectors .= ' data-repeatable="'. $this->masterListName .'"';
			}
			
			if($this->editable){
				// add actions
				$repeatableActions = array();
				
				// edit
				$act = new stdClass;
				$act->label = '';
				$act->icon = 'icomoon icon-pencil icon-white';
				$act->domClass = 'btn-edit btn btn-success btn-mini hasTooltip';
				$act->selectors = array(
					'title' => JText::_('JACTION_EDIT')
				);
				
				$repeatableActions['edit'] = $act;
				
				// copy
				$act = new stdClass;
				$act->label = '';
				$act->icon = 'icomoon icon-save-copy icon-white';
				$act->domClass = 'btn-copy btn btn-warning btn-mini hasTooltip';
				$act->selectors = array(
					'title' => JText::_('JACTION_COPY')
				);
				
				$repeatableActions['copy'] = $act;
				
				
				// DELETE
				JText::script("PLG_JDOM_ALERT_ASK_BEFORE_REMOVE");
				$act = new stdClass;
				$act->label = '';
				$act->icon = 'icomoon icon-remove icon-white';
				$act->domClass = 'btn-delete btn btn-danger btn-mini hasTooltip';
				$act->selectors = array(
					'title' => JText::_('JACTION_DELETE')
				);
				$repeatableActions['delete'] = $act;
				
				
				$this->actions = array_merge($this->actions,$repeatableActions);
				
				// remove unwanted actions
				$this->actions = array_diff_key($this->actions, (array)array_flip($this->disabledActions));
			}
		}		
	}

	function moveLevel($string,$step = 1){
		// find maxdeep level
		$maxLev = null;
		while(strpos($string,'pid'.$maxLev) !== false){
			$maxLev++;
		}

		if(!$maxLev){
			if($step > 0){
				$string = str_replace('{{=it.id}}','{{=it.pid}}',$string);
			} else {
				$string = str_replace('{{=it.pid}}','{{=it.id}}',$string);
			}
		} else {
			for($lv=$maxLev;$lv > 0;$lv--){
				$nextLev = $lv + $step;
				$string = str_replace('{{=it.pid'. $lv .'}}','{{=it.pid'. $nextLev .'}}',$string);
			}
			
			if($step > 0){
				$string = str_replace('{{=it.pid}}','{{=it.pid1}}',$string);
				$string = str_replace('{{=it.id}}','{{=it.pid}}',$string);
			} else {
				$string = str_replace('{{=it.pid}}','{{=it.id}}',$string);
				$string = str_replace('{{=it.pid1}}','{{=it.pid}}',$string);
			}
		}
		
		return $string;
	}

	// add bootstrap modal HTML
	function buildPopup(){		
		$html = '';
		$html .= JDom::_('html.fly.bootstrap.modal', array(
				'domId' => 'fset_modal_form',
				'domClass' => 'popupform formFieldsContainer',
				'selectors' => array(
					'data-backdrop'=> 'static'
					),
				'title' => JText::_("JACTION_EDIT")
			));
		
		return $html;
	}
	
	protected function parseVars($vars)
	{
		return parent::parseVars(array_merge(array(
			'STYLE'		=> $this->buildDomStyles(),
			'CLASS'			=> $this->buildDomClass(),		//With attrib name
			'SELECTORS'		=> $this->buildSelectors(),
		), $vars));
	}
}