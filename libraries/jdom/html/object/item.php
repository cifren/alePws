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
* @addon		Fly table item
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


class JDomHtmlListItem extends JDomHtmlList
{
	var $markup;

	function build()
	{
		$cels = array();		
		foreach($this->fieldset as $field){
			$fieldName = $field->fieldname;
			if(!in_array($fieldName,$this->fieldsToRender) AND count($this->fieldsToRender) > 0){
				continue;
			}
			
			if(isset($this->dataObject->$fieldName)){
				$rp = new ReflectionProperty($field,'value');
				if($rp->isProtected() AND $this->form instanceof JForm){
					try{
						@$this->form->setValue($fieldName,$field->group,$this->dataObject->$fieldName);
						$field = $this->form->getField($fieldName,$field->group,$this->dataObject->$fieldName);
					} catch (Exception $e){
						$error = $e->getMessage();
					}
				} else if($rp->isPublic()){
					$field->value = $this->dataObject->$fieldName;
				}
			}
			
			// check method getOutput exists
			if(method_exists($field,'getOutput')){
				$field_output = $field->getOutput($this->tmplEngine);
			} else {
				$fake_field = new JdomClassFormField();
				$field_output = $fake_field->getOutput($this->tmplEngine);
			}
			
			$cels[$fieldName] = '<'. $this->markup .' class="'. $this->domClass .' '. $fieldName .'_item">'. $field_output .'</'. $this->markup .'>';
		}

		// sort based on fieldsToRender
		if(count($this->fieldsToRender) > 0){
			$newOrder = array();
			foreach($this->fieldsToRender as $fi){
				if(isset($cels[$fi])){
					$newOrder[$fi] = $cels[$fi];
				}
			}
			$cels = $newOrder;
		}
	
		if(count($this->actions) > 0){
			$buttons = '<'. $this->markup .' class="actions_cln"><div class="btn-toolbar"><div class="btn-group">';
			foreach($this->actions as $act){
				$buttons .= JDom::_('html.fly.bootstrap.button', array(
						'domClass' => $act->domClass,
						'selectors' => $act->selectors,
						'icon' => $act->icon,
						'label' => $act->label
					));
			}
			$buttons .= '</div></div></'. $this->markup .'>';
			$cels[] = $buttons;
		}
		
		return implode('',$cels);
	}

}