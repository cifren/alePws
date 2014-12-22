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


class JDomHtmlFormInputEditorExtended extends JDomHtmlFormInputEditor
{

	var $dataFile;

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
		
		$this->arg('dataFile'	, null, $args);
		
		$this->editorInstance = new CkJEditor($this->editor);
		$this->editorInstance->set( 'toolbar', 'Default' );
	}

	
	function build()
	{
		$loaded = $this->initEditor($this->editor);	
		$html = '';
		
		$dataFileUrl = '';
		if($this->dataFile != ''){
			$options = array(
				'target' => 'content',
				'dataValue' => $this->dataFile
			);

			$dataFileUrl = JDom::_("html.fly.file.url", $options);
		}

		$html =	'<textarea id="<%DOM_ID%>" data-file="'. $dataFileUrl .'" data-editor="'. $this->editor .'" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			.	' cols="' . $this->cols . '"'
			.	' rows="' . $this->rows . '"'
			.	'>'
			.	'<%VALUE%>'
			.	'</textarea>' .LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>';		

		// fallback when editor is not loaded
		if(!$loaded){
			return $html;
		}

		$html .= $this->getButtons();
		
		return $html;
	}
}