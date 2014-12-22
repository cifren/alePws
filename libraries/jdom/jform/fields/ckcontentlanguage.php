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
class JFormFieldCkcontentlanguage extends JdomClassFormField
{
	/**
	* The form field type.
	*
	* @var string
	*/
	public $type = 'ckcontentlanguage';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $opts = array();
	
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
		
		$options = $this->getOptions();
		
		$multiple = $this->getOption('multiple');
		
		$type = 'select';
		if($multiple){
			$type = 'checkboxes';
		}
		
		$thisOpts = array(
				'list' => $options,
				'size' => $this->getOption('size', 1, 'int'),
				'submitEventName' => ($this->getOption('submit') === 'true'?'onchange':null)
			);
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts,$this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.'. $type, $this->fieldOptions);

		return parent::getInput();
	}

		
	protected function getOptions(){
		// Hash for caching
		$hash = md5($this->element);
		
		if (isset(static::$opts[$hash])){
			return static::$opts[$hash];
		}
			
		$options = parent::getOptions();
		$opt = new stdClass();
		$opt->text = JText::_('JALL');
		$opt->value = '*';
		$options[] = $opt;		
		
		$this->languages = $languages = JHtml::_('contentlanguage.existing');
		foreach ($languages as $opt)
		{
			$options[] = $opt;
		}

		static::$opts[$hash] = $options;
		
		return static::$opts[$hash];
	}
}