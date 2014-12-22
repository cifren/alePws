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
* @addon		Fly table description list
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


class JDomHtmlObjectTable extends JDomHtmlObject
{
	var $domClass = 'table table-striped table-bordered table-condensed ';	
	var $assetName = 'table';
	var $allLabels = array();
	var $cid;
	var $indirect;
	var $extraOptions;
	
	function __construct($args)
	{
		parent::__construct($args);
		$this->arg('cid'		, null, $args);
		$this->arg('indirect'	, null, $args, true);
		$this->arg('extraOptions'	, null, $args, array());
		
		$this->attachCss[] = 'table.css';
	}
	
	function build()
	{
		$rows = array();
		$html = '';
		foreach($this->fieldset as $field){
			$fieldName = $field->fieldname;
			if(isset($this->fieldsToRender) AND !in_array($fieldName,$this->fieldsToRender)){
				continue;
			}
			$fType = strtolower($field->type);
			if($fType == 'hidden' OR $fType == 'ckhidden'){
				continue;
			}

			// check ACL
			$canView = true;
			if($this->form instanceof JForm){
				$fiCanView = $this->form->getFieldAttribute($fieldName,'canView',null,$field->group);
				
				if($fiCanView AND class_exists('ByGiroHelper') AND !(isset($this->extraOptions['viewAll']) AND $this->extraOptions['viewAll'])){
					$canView = ByGiroHelper::canAccess($fiCanView);
				}
			}
			
			if(!$canView){
				continue;
			}
		
			$jdomOptions = array();
			if(isset($this->dataObject->id)){
				$jdomOptions = array_merge($jdomOptions,array(
					'cid' => $this->dataObject->id
				));
			}
			
			if($this->cid){
				$jdomOptions = array_merge($jdomOptions,array(
					'cid' => $this->cid
				));
			}

			if($fType == 'ckfile' OR $fType == 'ckmedia'){
				if(isset($this->indirect)){
					$jdomOptions = array_merge($jdomOptions,array(
						'indirect' => $this->indirect
					));
				}			
			}
			
			if(isset($this->dataObject->$fieldName)){
				$field->__set('value',$this->dataObject->$fieldName);
			}
			
			$field->jdomOptions = array_merge($field->jdomOptions,$jdomOptions);
			$field_output = $field->value;
			// check method getOutput exists
			if(method_exists($field,'getOutput')){
				$field_output = $field->getOutput($this->tmplEngine);					
			} else if(is_array($field->value)){
				// check method getAllLabels exists
				if(method_exists($field,'getAllLabels')){
					$this->allLabels = $field->getAllLabels();
				}
				
				$field_output = $this->outputArray($field_output);
			}

			$field->jdomOptions = array_merge($field->jdomOptions,$jdomOptions);
			$fakeFields = array('meter');
			if(in_array($fType,$fakeFields)){
				$field_output = $field->getOutput($this->tmplEngine);
			}
			
			$row = '<tr class="'. $fieldName .'_cln">';
			$field->markup = 'span';
			switch($fType){
				case 'ckspacer':
				case 'Spacer':
					$row .= '<td colspan="2">'. html_entity_decode($field->label) .'</td>';
					break;
				
				default:
					$row .= '<th>'. $field->label .'</th>';
					$row .= '<td>'. $field_output .'</td>';
					break;
			}			
			$row .= '</tr>';
			
			$rows[$fieldName] = $row;
		}

		// sort based on fieldsToRender
		if(count($this->fieldsToRender) > 0){
			$newOrder = array();
			foreach($this->fieldsToRender as $fi){
				if(isset($rows[$fi])){
					$newOrder[$fi] = $rows[$fi];
				}
			}
			$rows = $newOrder;
		}

		$html = '<table <%CLASS%><%SELECTORS%>>'. implode('',$rows) .'</table>';		
		return $html;
	}
	
	public function outputArray($list){
		$output = '<ol>';

		foreach($list as $k => $val){
			$li = '<li>';
			if(!is_numeric($k)){
				$li .= $k .': ';
			}
			
			if(is_string($val)){
				if(isset($this->allLabels[$val])){
					$val = $this->allLabels[$val];
				}
			} else if(is_object($val) OR is_array($val)){
				$val = $this->outputArray($val);
			} else {
				continue;
			}
			
			$li .= $val .'</li>';
			$output .= $li;
		}
		
		$output .= '</ol>';
		return $output;
	}
}