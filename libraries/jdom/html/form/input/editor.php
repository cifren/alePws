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


class JDomHtmlFormInputEditor extends JDomHtmlFormInput
{
	var $fallback = 'default';

	var $cols;
	var $rows;
	var $width;
	var $height;
	var $editor;
	var $params;
	var $repeatable;
	var $editorInstance;
	var $hash;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *
	 * 	@cols		: Textarea width (in caracters)
	 * 	@rows		: Textarea height (in caracters)
	 * 	@width		: Textarea width (in px)
	 * 	@height		: Textarea height (in px)
	 * 	@editor		: Editor name (for example, 'tinymce'). If null then the current editor will be returned
	 * 	@domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 */
	function __construct($args)
	{

		parent::__construct($args);
		
		$this->arg('cols'		, null, $args, '32');
		$this->arg('rows'		, null, $args, '4');
		$this->arg('width'		, null, $args, '100%');
		$this->arg('height'		, null, $args, $this->rows * 20);
		$this->arg('editor'		, null, $args);
		$this->arg('params'		, null, $args);
		$this->arg('buttons'	, null, $args, false);
		$this->arg('domClass'	, null, $args);
		$this->arg('repeatable'	, null, $args);
		$this->arg('selectors'	, null, $args);
		
		$this->supportedEditors = array('tinymce','jce','codemirror');
		if($this->repeatable){
			$this->fallback = 'extended';
		}

		if($this->fallback == 'extended'){
			if(!$this->editor OR $this->editor == '' OR !in_array($this->editor,$this->supportedEditors)){
				$this->editor = 'tinymce';
			}		
		}
		
		if(is_string($this->buttons) AND strpos($this->buttons,',') !== false){
			$this->buttons = explode(',',$this->buttons);
		} else if($this->buttons !== false){
			$this->buttons = true;
		}
		
		if(is_string($this->params)){
			$this->params = json_decode($this->params);
		}		
	}
	
	function initEditor(){
		$this->hash = $hash = MD5(json_encode($this->params) . $this->editor);
		// Only load once
		if (isset(static::$loaded[__METHOD__ . $hash]))
		{
			return static::$loaded[__METHOD__ . $hash];
		}
		$doc = JFactory::getDocument();

		if(!$this->editorInstance->_ckLoadEditor($this->params)){
			return static::$loaded[__METHOD__ . $hash] = false;
		}
		$sc = $this->editorInstance->getInitializerScript();
	
		// add script
		preg_match_all('/src="([^"]*)"/i',$sc, $matches);
		foreach($matches[1] as $v){
			$doc->addScript($v);
		}
	
		// add stylesheets
		preg_match_all('/href="([^"]*)"/i',$sc, $matches);
		foreach($matches[1] as $v){
			$doc->addStyleSheet($v);
		}
		
		$initializer = '';
		$script = '';
		switch($this->editor){
			case 'codemirror':
				$tgFunc = $this->getScripts();
				$tgFunc = str_replace("'". $this->domId ."'",'"'. $this->domId .'"',$tgFunc);
				$tgFunc = str_replace('"'. $this->domId .'"','textareaId',$tgFunc);
				
				$params = $this->params;
				
				// set extra options
				$extra = '';

				$extraOptions = array('extraKeys','hintOptions');
				
				foreach($extraOptions as $extO){
					if(isset($params->$extO)){
						$extra .="
							if(typeof ". $params->$extO ." != 'undefined'){
								var ". $extO ." = Joomla.editors.instances[textareaId].getOption('". $extO ."');
								if(jQuery.isPlainObject(". $extO .")){
									". $extO ." = jQuery.extend({},". $extO .",". $params->$extO .");
								} else {
									". $extO ." = ". $params->$extO .";
								}
								Joomla.editors.instances[textareaId].setOption('". $extO ."',". $extO .");
							}
						";
					}
				}

				$script .= 'function codemirrorToggler'. $this->hash .'(domObj){
					var btnClicked = jQuery(domObj),
						textareaId = btnClicked.attr("data-target"),
						textarea = btnClicked.closest(".controls, fieldset, form").find("#"+ textareaId);
						
					var inputFileId = textareaId.split("-");
					inputFileId.pop();
					inputFileId = inputFileId.join("-");
					
					var enabler = function(){
							if(typeof Joomla.editors.instances[textareaId] != "undefined"){
								Joomla.editors.instances[textareaId].toTextArea();
								delete Joomla.editors.instances[textareaId];
							} else {				
								'. $tgFunc .'
								
								'. $extra .'
								// add the events handler
								Joomla.editors.instances[textareaId].on("change",function(){
									textarea.closest("fieldset").find("#"+ inputFileId +"-raw_changed").val(1);
									Joomla.editors.instances[textareaId].off("change");
								});
							}
						}
					
					enabler();				
				}';
				break;
				
			case 'tinymce':	
				if(strpos($sc,'tinyMCE.init({') !== false){
					$startsAt = strpos($sc, "tinyMCE.init({") + strlen("tinyMCE.init({");
					$caller = 'tinyMCE';
				} else if(strpos($sc,'tinymce.init({') !== false){
					$startsAt = strpos($sc, "tinymce.init({") + strlen("tinymce.init({");
					$caller = 'tinymce';
				} else {
					break;
				}
				
				$endsAt = strpos($sc, "})", $startsAt);
				$result = substr($sc, $startsAt, $endsAt - $startsAt);	
				$script .= 'tinyMCE.settings = {'. $result .'}; ';
				
				$result = str_replace('"textarea.mce_editable"','selectorCss',$result);
				$initializer = 'function(selectorCss){					
				/*	'. $caller .'.init({'. $result .'});	*/		
				}';
				
				
				break;
				
			case 'jce':
				$initializer = 'function(selectorCss){				
					var ids = [];
					jQuery(selectorCss).each(function(){
						ids.push(jQuery(this).attr("id"));

						// add buttons
					});
					
					var customSettings = {
						editor_selector: "editor_jce",
						mode: "exact",
						elements: ids.join(",")
					};
					
					var settings = jQuery.extend({},WFEditor.settings,customSettings);
					WFEditor.init(settings);
					
					// fix for JCE multiple toggle buttons - remove the extra buttons
					jQuery(".form-widget").each(function(){
						jQuery(this)
							.find(".wf_editor_toggle")
							.not(":first-child")
							.remove();
					});
				}';
				break;
		}
	
		$script .= "if(typeof editors_repeatable != 'object') {editors_repeatable = {};}\n";
		
		if($initializer != ''){
			$script .= "editors_repeatable.". $this->editor ." = ". $initializer;
		}
		$doc->addScriptDeclaration($script);
		
		return static::$loaded[__METHOD__ . $hash] = $hash;	
	}

	function getButtons(){
		$buttons = '';

		switch($this->editor){
			case 'codemirror':
				$onclick = "codemirrorToggler". $this->hash ."(this);";
				break;
				
			case 'tinymce':
				$onclick = "tinyMCE.execCommand('mceToggleEditor', false, '". $this->domId ."');";	
				break;
				
			case 'jce':
				$onclick = "tinyMCE.execCommand('mceToggleEditor', false, '". $this->domId ."');";	
				break;
		}

		if($this->buttons !== false){
			$buttons .= $this->getButtons();
		}
		
		// toggler
		$buttons .= '
		<div class="toggle-editor btn-toolbar pull-right clearfix">
			<div class="btn-group">
				<span class="btn"
					data-target="'. $this->domId .'" onclick="'. $onclick .'"
					title="'. JText::_('PLG_JDOM_BUTTON_TOGGLE_EDITOR') .'"
				>
					<i class="icomoon icon-eye"></i> '. JText::_('PLG_JDOM_BUTTON_TOGGLE_EDITOR') .'
				</span>
			</div>
		</div>';
		
		return $buttons;
	}
	
	function getHtmlTags($html, $tag='*', $by_attr = '', $attr_value = '',$onlyContent = false){
		$result = array();		
		
		$dom = new domDocument;
		@$dom->loadHTML($html);
		$selector = new DOMXPath($dom);

		if(!($selector instanceof DOMXPath)){
			return $result;
		}
		
		if($by_attr != '' AND $attr_value != ''){
			$query = sprintf('//%s[@%s="%s"]', $tag, $by_attr, $attr_value);
		} else if($by_attr != ''){
			$query = sprintf('//%s[@%s]', $tag, $by_attr);
		} else {
			$query = '//'. $tag;
		}	

		$list = $selector->query($query);

		foreach($list as $ele) {
			if($onlyContent){
				$result[] = $ele->textContent;
			} else {
				$result[] = $dom->saveXml($ele);
			}
		}
		
		return $result;
	}
	
	function getHTMLButtons(){
		$result = '';
		
		if(!($this->editorInstance instanceof CkJEditor)){
			return '';
		}
		
		$output = $this->editorInstance->getDisplay($this->getInputName(), $this->dataValue, $this->width, $this->height, $this->cols, $this->rows, $this->buttons, $this->domId, $this->params);
		$matches = $this->getHtmlTags($output,'div','id','editor-xtd-buttons');
		$result = $matches[0];
		
		return $result;
	}
	
	function getScripts(){
		$result = '';
		
		if(!($this->editorInstance instanceof CkJEditor)){
			return '';
		}		
		
		$output = $this->editorInstance->getDisplay($this->getInputName(), $this->dataValue, $this->width, $this->height, $this->cols, $this->rows, $this->buttons, $this->domId, $this->params);
		$matches = $this->getHtmlTags($output,'script','type','text/javascript',true);
		$result = $matches[0];
		
		return $result;
	}
}
