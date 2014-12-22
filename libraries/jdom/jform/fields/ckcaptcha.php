<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       2.5
 */
class JFormFieldCkcaptcha extends JdomClassFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'ckcaptcha';


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
				'theme' => $this->getOption('theme'),
				'pubkey' => $this->getOption('pubkey'),
				'privkey' => $this->getOption('privkey'),
				'required' => 'true',
				'selectors' => $this->getOption('selectors')
			);
		$this->fieldOptions = array_merge($this->fieldOptions,$thisOpts, $this->jdomOptions);
		
		$this->input = JDom::_('html.form.input.captcha', $this->fieldOptions);

		return parent::getInput();
	}

}
