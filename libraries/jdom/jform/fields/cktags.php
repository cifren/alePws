<?php
/**
* (¯`·.¸¸.-> °º★ вүgιяσ.cσм ★º° <-.¸¸.·´¯)
* @version		2.5
* @package		Cook Self Service
* @subpackage	JDom
* @license		GNU General Public License
* @author		Jocelyn HUARD
*
* @added by		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined('_JEXEC') or die('Restricted access');



/**
* Form field for Jextrafields.
*
* @package	Jextrafields
* @subpackage	Form
*/
class JFormFieldCktags extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'cktags';

	/**
	* Method to get the field input markup.
	*
	* @access	public
	*
	* @return	string	The field input markup.
	*
	* @since	11.1
	*/
	public function getInput()
	{
		$this->setCommonProperties();
		
		$thisOpts = array(
				'min' => $this->getOption('mintags') ?: $this->getOption('min'),
				'max' => $this->getOption('maxtags') ?: $this->getOption('max'),
				'separator' => $this->getOption('separator'),
				'capitalizeFirstLetter' => $this->getOption('capitalizeFirstLetter'),
			);
			
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.tags', $this->fieldOptions);

		return parent::getInput();
	}

	public function getLabel()
	{
		$extraLabel = array();
		$min = $this->getOption('mintags') ?: $this->getOption('min');
		$max = $this->getOption('maxtags') ?: $this->getOption('max');
		
		if($min > 0){
			$extraLabel[] = 'min: '. $min ;
		}

		if($max > 0){
			$extraLabel[] = 'max: '. $max ;
		}
		
		$this->__set('labelclass','jtags');
		$label = parent::getLabel();
		if(!empty($extraLabel)){
			$label = $label . '<span class="jtags_info">('. implode(' - ', $extraLabel) .')</span>';
		}
		
		return $label;

	}

	public function getOutput($tmplEngine = null)
	{
		$html = '';
		if(!isset($this->value)){
			return $html;
		}
		
		$separator = $this->getOption('separator',',');
		$value = explode(',',$this->value);
		
		$fieldName = $this->getOption('name');
		
		$html = '';
		switch($tmplEngine){
			case 'doT':
				$tmpl = JDom::_('html.label', array(
							'content' => "'+ tmp_val[k].trim() +'",
							'color' => 'info'
						));
			
				$html = '{{ var value = "",tmp_val = "";
							if(typeof it.'. $fieldName .' != null && typeof it.'. $fieldName .' != "undefined"){
								tmp_val = it.'. $fieldName .';
								tmp_val = tmp_val.split("'. $separator .'");
								
								for(var k=0;k < tmp_val.length;k++){
									value += \''. $tmpl .'\';
								}}
								{{ } }}
							{{ } }}							
							{{=value || ""}}
						';
				break;
				
			default:
				$html = array();
				foreach($value as $v){
					$html[] = JDom::_('html.label', array(
							'content' => trim($v),
							'color' => 'info'
						));			
				}
				
				$html = implode(' ',$html);
				break;
		}
		
		return $html;
	}
}
