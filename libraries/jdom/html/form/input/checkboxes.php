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


class JDomHtmlFormInputCheckboxes extends JDomHtmlFormInput
{

	var $domClass;
	var $selectors;

	protected $list;

	protected $listKey;
	protected $labelKey;


	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *	@cols		: numer of columns
	 *	@list		: list of items for checkboxes
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->arg('cols'		, 1, $args);
		$this->arg('layout'		, null, $args,'vertical');
		$this->arg('list'		, array(), $args);
		$this->arg('domClass'	, null, $args);
		$this->arg('selectors'	, null, $args);
		

		$this->arg('listKey'	, null, $args, 'value');
		$this->arg('labelKey'	, null, $args, 'text');


		$this->domId = $this->getInputId();
		$this->domName = $this->getInputName();
		
		static $loaded;
		
		if(!$loaded){
		
		$doc = JFactory::getDocument();
			$style = "
ul.checkboxes {
	list-style: none;
}

ul.checkboxes:not(.horizontal_layout) {
	display: inline-block;
}

ul.checkboxes.horizontal_layout li{
	display: inline-block;
}

ul.checkboxes.horizontal_layout li:not(:first-child){
	margin-left: 15px;
}

ul.checkboxes li label{
	display: inline;
	margin: 3px 0 0 5px;
	float: none;
}

ul.checkboxes li input{
	display: inline;
	margin: 0;
	height: auto;
}";
			$doc->addStyleDeclaration($style);
			$loaded = true;
		}
	}

	function build()
	{

		$output = '';
		switch(gettype($this->dataValue)){
			case'object':
				$dataValues = (array)$this->dataValue;
			break;
			
			case'string':
				$dataValues = explode(',',$this->dataValue);
			break;
			
			case'array':
				$dataValues = $this->dataValue;
			break;
			
			default:
				$dataValues = array($this->dataValue);
			break;
		}
		
		$listKey = $this->listKey;
		$labelKey = $this->labelKey;

		$checkboxes = array();
		foreach($this->list as $key => $opt){
			if(!is_array($opt)){
				$opt = (array)$opt;
			}
			$value = null;
			if(isset($opt[$listKey]) AND in_array($opt[$listKey], $dataValues)){
				$value = $opt[$listKey];
			}
			
			$inputVal = null;
			if(isset($opt[$listKey])){
				$inputVal = $opt[$listKey];
			}
			
			$inputLabel = null;
			if(isset($opt[$labelKey])){
				$inputLabel = $opt[$labelKey];
			}
	
			$checkboxes[] = JDom::_('html.form.input.checkbox', array_merge($this->options, array(
					'dataValue' => $value,
					'domId' => $this->domId .'_'. $key,
					'domName' => $this->domName .'[]',
					'inputValue' => $inputVal,
					'inputLabel' => $inputLabel
				))
			);
		}
		
		
		$cols = intVal($this->cols);
		
		$count_checkboxes = count($checkboxes);
		if(empty($count_checkboxes)){
			return '';
		}
		
		if($cols > 1){
			$itemsXcol = ceil($count_checkboxes / $cols);
		} else {
			$cols = 1;
			$itemsXcol = $count_checkboxes;
		}
		
		$layout = (!empty($this->layout) AND $this->layout == 'horizontal') ? 'horizontal_layout' : '';
		$output = '';
		$output .= '<ul class="checkboxes '. $layout .'">';
		$k=1;
		foreach($checkboxes as $checkbox){
			$output .= '<li>'. $checkbox .'</li>';
			
			if ($k % $itemsXcol == 0 AND $k < $count_checkboxes){
				$output .= '</ul><ul class="checkboxes '. $layout .'">';
			}
			$k++;
		}
		$output .= '</ul>';
			
		return '<div id="<%DOM_ID%>" class="input-container">'. $output .'</div>';
	}
}