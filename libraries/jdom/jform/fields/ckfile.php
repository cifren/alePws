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
* Form field for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormFieldCkfile extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckfile';

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
				'height' => $this->getOption('height'),
				'indirect' => ($this->getOption('indirect','') === 'true') ? true : false,
				'attrs' => $this->getOption('attrs'), // it's a string like this 'fit,crop,center,quality:80,color:ffffff,format:jpg,opacity:100'
				'actions' => explode(',',$this->getOption('actions', null)),
				'preview' => (($this->getOption('preview') === 'true') ? true : false),
				'target' => $this->getOption('target'),
				'editable' => $this->getOption('editable'),
				'maxSize' => $this->getOption('maxSize'),
				'root' => $this->getOption('root'),
				'view' => $this->getOption('view'),
				'width' => $this->getOption('width'),
				'comAlias' => $this->getOption('comAlias'),
				
				// editor attributes
				'editorHeight' => $this->getOption('editorHeight'),
				'editorWidth' => $this->getOption('editorWidth'),
				'rows' => $this->getOption('rows'),
				'editor' => $this->getOption('editor'),
				'params' => $this->getOption('params'),
				'buttons' => $this->getOption('buttons')
			);
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.file', $this->fieldOptions);

		return parent::getInput();
	}



}