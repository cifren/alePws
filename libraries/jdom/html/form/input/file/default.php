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

class JDomHtmlFormInputFileDefault extends JDomHtmlFormInputFile
{
	protected $size;
	protected $maxSize;
	protected $actions;
	protected $allowedExtensions;
	protected $editable;
	protected $preview;
	protected $editor;
	protected $dataValue_changed;
	protected $dataValue_editor;
	protected $buttons;
	protected $params;
	protected $editorHeight;
	protected $editorWidth;
	protected $rows;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 * 	@indirect	: Indirect file access (bool)
	 * 	@domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 *
	 *	@size		: Input Size
	 *
	 */
	function __construct($args)
	{

		parent::__construct($args);
		$this->arg('size'				, null, $args, '32');
		$this->arg('maxSize'		, null, $args);
		$this->arg('allowedExtensions'	, null, $args);
		$this->arg('editor'				, null, $args); 
		$this->arg('dataValue_changed'	, null, $args,0); 
		$this->arg('dataValue_editor'	, null, $args,''); 
		$this->arg('buttons'			, null, $args); 
		$this->arg('params'				, null, $args); 
		$this->arg('editorHeight'		, null, $args); 
		$this->arg('editorWidth'		, null, $args); 
		$this->arg('rows'				, null, $args); 
		$this->arg('editable'			, null, $args, false); 
		$this->arg('preview'			, null, $args, false); 
		

		// Only load once
		static $loadedEditableJS;
		if ($this->editable AND empty($loadedEditableJS)){
			$doc = JFactory::getDocument();
			$script = "
				function loadFileToTextarea(Obj, rawEditorContainer){
					var inputFileId = jQuery(Obj).attr('data-target'),
						textareaId = inputFileId +'-raw_editor',
						textareaContainer = jQuery(Obj).closest('.control, fieldset, form').find('#'+ inputFileId +'-raw_editor_container').first(),					
						textarea = textareaContainer.find('textarea'),
						dataFile = textarea.attr('data-file');
					
					if(typeof window['dataFiles_loaded'] == 'undefined'){
						window['dataFiles_loaded'] = {};
					}
					var dataFile = textarea.attr('data-file');
					if(dataFile != ''){
						if(typeof window['dataFiles_loaded'][textareaId] == 'undefined'){
							// load data
							jQuery.ajax({
								type: 'GET',
								url: dataFile,
								beforeSend: function(){
									jQuery(Obj).after('<span class=\"loading\"><img src=\"". JURI::root( true ) ."/libraries/jdom/assets/ajax/images/spinner.gif\"/> ". JText::_("PLG_JDOM_LOADING") ."</span>');
								},
								complete: function(){
									jQuery(Obj).parent().find('.loading').remove();
								},
								success: function(response){
									textarea.val(response);
									textarea.html(response);
									window['dataFiles_loaded'][textareaId] = response;
									textareaContainer.collapse('toggle');
								},
								error: function(response){
									textarea.val('');
									textarea.html('');
									textareaContainer.collapse('toggle');								
								}
							});

							textarea.one('change keyup',function(e){
								textarea.closest('fieldset').find('#'+ inputFileId +'-raw_changed').val(1);
								textarea.off('change keyup');								
							});
						} else {
							textarea.val(window['dataFiles_loaded'][textareaId]);
							textarea.html(window['dataFiles_loaded'][textareaId]);
							textareaContainer.collapse('toggle');
						}
					} else {
						textareaContainer.collapse('toggle');
					}
				}
			";
			$doc->addScriptDeclaration($script);
			
			$loadedEditableJS = true;
		}
	
		
	}
	function build()
	{
		if ($this->useFramework('bootstrap')){
			return $this->buildBootstrap();
		}
		
		return $this->buildLegacy();
	}
	
	function buildBootstrap()
	{
		$isNew = (empty($this->dataValue));

		
		$html = '';
		
		//Create the input
		$htmlInput = $this->buildControlBootstrap();
			
		
		$htmlPreview = '';
		if(!$isNew){ 
			//Create the preview	
			$pickerStyle = "";
			if ($this->thumb)
				$pickerStyle = 'border:dashed 3px #ccc; padding:5px; margin:5px;display:inline-block';

			$htmlPreview = "<div style='" . $pickerStyle . "'>";
			$htmlPreview .= JDom::_("html.fly.file", $this->options);
			$htmlPreview .= "</div>";
		} 
		
		//Current value is important for the removing features features ()
		$htmlHiddenCurrent = JDom::_('html.form.input.hidden', array(
			'dataValue' => $this->dataValue,
			'dataKey' => $this->dataKey . '-current',
			'formControl' => $this->formControl,
			'formGroup' => $this->formGroup
		));
		
		//Store and send the 'remove' value
		$htmlHiddenRemove = JDom::_('html.form.input.hidden', array(
			'dataValue' => '',
			'dataKey' => $this->dataKey . '-remove',
			'formControl' => $this->formControl,
			'formGroup' => $this->formGroup
		));

		$html = '';
		
		$html .= $htmlPreview;
				
		// Group in a bootstrap appended input		
		$html .= '<div class="btn-group">';
		$html .= '<div class="input-prepend input-append">' .LN;	
		
		//Prepend		
		$htmlIconRemove = JDom::_('html.icon', array('icon' => 'icomoon-delete',));
		$idRemoveBtn = $this->getInputId('deletebtn');

		$removeList = $this->removeList();
		// Create the remove actions list	
		if (count($removeList) > 0 AND !empty($this->dataValue))
		{			
			$html .= '<a class="btn dropdown-toggle" id="' . $idRemoveBtn . '" data-toggle="dropdown">';
			$html .= $htmlIconRemove;
			$html .= '</a>' .LN;
		
			$html .= '<ul class="dropdown-menu removeList">' .LN;
			foreach($removeList as $item)
			{
				$icon = $item['icon'];
				$domItemIcon = JDom::getInstance('html.icon', array(
					'icon' => $icon,
					'styles' => array(
						'margin-left' => '1em',
						'float' => 'right'
					)
				));
				$itemIcon = $domItemIcon->output();
				
				$htmlLink =  $itemIcon . $item['text'];
				
				$html .= '<li data-input-target="'. $this->getInputId('remove') .'" data-task="'. $item['value'] .'" data-icon-task="'. $domItemIcon->getIconClass() .'">' .LN;				
				$html .=  '<a href="#" onclick="return false;">'. $htmlLink .'</a></li>' .LN;
			}
			$html .= '</ul>' .LN;
			
		}
	
		//File input
		$html .= $htmlInput .LN;	

		$html .= $htmlHiddenCurrent .LN;
		$html .= $htmlHiddenRemove .LN;
		
		//Close the control		
		$html .= '</div>' .LN;		
		$html .= '</div>' .LN;
		
		//MaxSize
		$html .= $this->buildMaxSize();
		
		//Extensions
		$html .= $this->buildFileExtensions();

		if($this->editable){
		
			$html .= JDom::_('html.form.input.hidden', array(
				'dataValue' => $this->dataValue_changed,
				'dataKey' => $this->dataKey . '-raw_changed',
				'formControl' => $this->formControl,
				'formGroup' => $this->formGroup
			));

			$defaults = array(
					'editor' => 'codemirror',
					'buttons' => false
				);
			
			$thisOpts = array_merge($defaults,$this->options);
			$thisOpts['dataKey'] = $this->dataKey . '-raw_editor';
			$thisOpts['height'] = $thisOpts['editorHeight'];
			$thisOpts['width'] = $thisOpts['editorWidth'];
			$thisOpts['domClass'] = '';
			$thisOpts['dataValue'] = $this->dataValue_editor;
			$thisOpts['dataFile'] = $this->dataValue;
			$thisOpts['repeatable'] = true;
			$thisOpts['selectors'] = array('data-editor' => $defaults['editor']);
		
			$textarea = JDom::_('html.form.input.editor', $thisOpts) .LN;
			
			$html .= '<div id="'. $this->domId . '-raw_editor_container" class="collapse">'. $textarea .'</div>';
		}

		return $html;
	}


	function buildLegacy()
	{
		
		$html = '';
			

		//Create the input
		$htmlInput = $this->buildControlLegacy();
			
			
		//Create the preview	
		$pickerStyle = "";
		if ($this->thumb)
			$pickerStyle = 'border:dashed 3px #ccc; padding:5px; margin:5px;display:inline-block';

		$isNew = (empty($this->dataValue));

	$htmlPreview = '';
	if(!$isNew){ 
		$htmlPreview .= "<div style='" . $pickerStyle . "'>";
		$htmlPreview .= JDom::_("html.fly.file", $this->options);
		$htmlPreview .= "</div>";
	}	

		$removeList = $this->removeList();
	
		//Current value is important for the removing features features ()
		$htmlHiddenCurrent = JDom::_('html.form.input.hidden', array(
			'dataValue' => $this->dataValue,
			'domId' => $this->getInputId('current'),
			'domName' => $this->getInputName('current'),
			'formControl' => $this->formControl,
		));
		
		


		$btnId = $this->getInputId('btn-new');
		$btnCancel = $this->getInputId('btn-cancel');
		$uploadDivId = $this->getInputId('div-upload');


		if (!$isNew)
		{
			$htmlBtnNew = JDom::_("html.link.button.joomla", array(
				'link_js' => 'jQuery(\'#' . $uploadDivId . '\').show();jQuery(\'#' . $btnId . '\').hide();',
				'content' => JText::_('New upload'),
				'icon' => 'image',
				'styles' => array(),
				'domId' => $btnId

			));
			
			$htmlBtnCancel = JDom::_("html.link.button.joomla", array(
				'link_js' => 'jQuery(\'#' . $uploadDivId . '\').hide();jQuery(\'#' . $btnId . '\').show()',
				'content' => JText::_('Cancel'),
				'icon' => 'image',
				'styles' => array(),
				'domId' => $btnCancel

				));
		}
			

		$idRemove = $this->getInputId('remove');
		$nameRemove = $this->getInputName('remove');
		
		//Store and send the 'remove' value
		$htmlComboRemove = JDom::_('html.form.input.select.combo', array(
			'dataValue' => '',
			'dataKey' => $this->dataKey . '-remove',
			'formControl' => $this->formControl,
			'formGroup' => $this->formGroup,
			'list' => $this->removeList()
		));
		
		$html = '';
		
		//Hidden inputs in top of the control
		$html .= $htmlHiddenCurrent .LN;
		
		//File preview
		$html .= $htmlPreview;


		//Button : New upload
		$html .= $htmlBtnNew;
		
		//Upload div (hidden when image is already present)
		$html .= ''
			.	'<div id="' . $uploadDivId . '"'
			.	(!$isNew?' style="display:none;"':'')
			.	'>';

		$htmlIconRemove = JDom::_('html.icon', array(
			'icon' => 'delete pull-left',
		));



		
		$idRemoveBtn = $this->getInputId('deletebtn');

	
		//File input
		$html .= $htmlInput .LN;	
		
		
		//Button : cancel
		$html .= $htmlBtnCancel;
		
	//	$html .= '<div class="clearfix"></div>';
		//Removing actions
		$html .= $htmlIconRemove;
		$html .= $htmlComboRemove .LN;
		
		//MaxSize
		$html .= $this->buildMaxSize();
		
		//Extensions
		$html .= $this->buildFileExtensions();

	
		//Close the control
		$html .= '</div>' .LN;

		if($this->editable){		
			$html .= JDom::_('html.form.input.hidden', array(
				'dataValue' => $this->dataValue_changed,
				'dataKey' => $this->dataKey . '-raw_changed',
				'formControl' => $this->formControl,
				'formGroup' => $this->formGroup
			));

			$defaults = array(
					'editor' => 'codemirror',
					'buttons' => false
				);
			
			$thisOpts = array_merge($defaults,$this->options);
			$thisOpts['dataKey'] = $this->dataKey . '-raw_editor';
			$thisOpts['height'] = $thisOpts['editorHeight'];
			$thisOpts['width'] = $thisOpts['editorWidth'];
			$thisOpts['domClass'] = '';
			$thisOpts['dataValue'] = $this->dataValue_editor;
			$thisOpts['dataFile'] = $this->dataValue;
			$thisOpts['repeatable'] = true;
		
			$textarea = JDom::_('html.form.input.editor', $thisOpts) .LN;
			
			$html .= '<div id="'. $this->domId . '-raw_editor_container" class="collapse">'. $textarea .'</div>';
		}
		
		return $html;
	}

	function buildMaxSize()
	{
		$html = '';
		
		if (isset($this->maxSize))
		{
			$html .= '<span class="maxupload_info">Max '. $this->maxSize .' MB</span>';
		}
		return $html;
		
	}
	function buildFileExtensions()
	{
		$html = '';

		if (isset($this->allowedExtensions))
		{
			$html .= '<br/>(' . preg_replace("/\|/", ', ', $this->allowedExtensions) . ')';

		}
		return $html;
		
	}
	
	function buildControlBootstrap()
	{
		$html = '';
		$id = $this->getInputId();
		$idView = $this->getInputId('view');
		
		//Create hidden input (file)
		$onchange = "jQuery(this).closest('div').find('#" . $idView . "').val(jQuery(this).val()); if(typeof jQuery.fn.validationEngine != 'undefined'){jQuery(this).closest('div').find('#" . $idView . "').validationEngine('validate');}";
		$htmlInputHidden = '<input onChange="'. $onchange .'" type="file" id="<%DOM_ID%>" name="<%INPUT_NAME%>" ' 
			. ' style="display:none;"'
			.	' value="<%VALUE%>"'
			.	'/>' .LN;	
		
		//Create a visible text field (stylable)
		$onFocus = 'jQuery(this).closest(\'div\').find(\'input[id=\"'. $id .'\"]\').trigger(\'click\');';
		$dom = JDom::getInstance('html.form.input.text', array(
			'dataValue' => $this->dataValue, //Uncomment if you want to prefill the input with current value
			'dataKey' => $this->dataKey . '-view',
			'formControl' => $this->formControl,
			'formGroup' => $this->formGroup,
			'selectors' => array('onFocus' => $onFocus, 'readonly'=>'')			
		));

		$dom->addClass('input-large');
		$dom->addClass($this->domClass);

		//for cross compatibility (remove margin and float)
		$dom->addClass('inputfix');
		if ($this->hidden)
			$dom->addStyle('display', 'none');

		$htmlInputView = $dom->output();

		// Create the upload button
		$htmlIconBrowse = JDom::_('html.icon', array(
			'icon' => 'icomoon-folder-open',
		));

		//Create the button to trigger the input
		$htmlButtonBrowse = JDom::_('html.link.button', array(
			'content' => $htmlIconBrowse,
			'domClass' => 'btn buttonfix',
			
			'link_js' => 'jQuery(\'input[id=\"'. $id .'\"]\').trigger("click");',
			
		));
		
		$htmlButtonEdit = '';
		if($this->editable){
			// Create the upload button
			$htmlIconEdit = JDom::_('html.icon', array(
				'icon' => 'iconmoon icon-pencil',
			));

			$js = "loadFileToTextarea(this,'". $id ."-raw_editor_container');";
			//Create the button to trigger the input
			$htmlButtonEdit = JDom::_('html.link.button', array(
				'content' => $htmlIconEdit,
				'domClass' => 'btn buttonfix loadFile',
				'selectors' => array('data-target'=> $id),
				'link_js' => $js,
			));
		}
		
		// Original file input is hidden
		$html .= $htmlInputHidden;

		//Visible input
		$html .= $htmlInputView;

		//Browse button
		$html .= $htmlButtonBrowse;
		
		//Edit button
		$html .= $htmlButtonEdit;

		return $html;
	}
	
	function buildControlLegacy()
	{
		$html = '<input type="file" id="<%DOM_ID%>" name="<%INPUT_NAME%>" ' 
			.	' value="<%VALUE%>"'
			.	'/>' .LN;	

		if($this->editable){
			$js = "loadFileToTextarea(this);";
			$html .= '<span data-target="'. $id .'" class="loadFile" onclick="'. $js .'">'. JText::_("PLG_JDOM_EDIT") .'</span>';
		}
		
		return $html;
	}


	function buildJs()
	{
	
	}

}
