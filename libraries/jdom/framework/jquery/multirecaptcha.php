<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkJqueryMultirecaptcha extends JDomFrameworkJquery
{	
	const RECAPTCHA_API_SERVER = "http://www.google.com/recaptcha/api";
	const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
	const RECAPTCHA_VERIFY_SERVER = "www.google.com";

	var $assetName = 'multirecaptcha';
	
	var $privkey;
	var $pubkey;
	var $theme;
	var $defaultSettings;
	var $attachJs = array();
	var $attachCss = array();
	
	protected static $loaded = array();	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		/* example arguments */
		$this->arg('pubkey'	, null, $args);
		$this->arg('privkey'	, null, $args);
		$this->arg('theme'	, null, $args);

		$this->defaultSettings = new stdClass;
	}
	
	function build()
	{	
		// Only load once
		if (!empty(static::$loaded[__METHOD__])){
			return;
		}
		
		$version = new JVersion();
		
		if($this->pubkey == '' OR $this->privkey == '' OR $this->theme == ''){
			$plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
			
			// Joomla! 1.6 - 1.7 - 2.5
			if (version_compare($version->RELEASE, '2.5', '<=')){
				jimport( 'joomla.html.parameter' );
				$params = new JParameter($plugin->params);
			} else {
				$params = new JRegistry($plugin->params);
			}		
		}
		
		if($this->pubkey == '' OR $this->privkey == '' ){
			$this->pubkey = trim($params->get('public_key', ''));
		}
		
		if($this->theme == ''){
			$this->theme = $params->get('theme', '');
		}
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		JDom::_('framework.jquery');		

		$server = self::RECAPTCHA_API_SERVER;
		if((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION')){
			$server = self::RECAPTCHA_API_SECURE_SERVER;
		}

		JHtml::_('script', $server . '/js/recaptcha_ajax.js');
				
		$this->defaultSettings->text = (object)array(
									'btnTxt' => JText::_("JDOM_RECAPTCHA_CLICK_ME")
								);
		$this->defaultSettings->pubkey = $this->pubkey;
		$this->defaultSettings->theme = $this->theme;
		
		// add language settings
		$this->_getLanguage();
		
		$defaultSettings = str_replace("'","\'",$this->escapeJsonString(json_encode($this->defaultSettings)));
		$script = "window.jQuery_multirecaptchaByGiro = JSON.parse('". $defaultSettings ."');";
		$script .= 'jQuery(document).multirecaptchaByGiro(window.jQuery_multirecaptchaByGiro);';
		$this->addScriptInLine($script, true);
		
		// addresspicker manager files needed
		$this->attachJs[] = 'jquery.multirecaptcha.js';
		$this->attachCss[] = 'multirecaptcha.css';
		
		static::$loaded[__METHOD__] = true;
	}
	
	function buildCss()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
	
	function buildJs()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
	
	/**
	 * Get the language tag or a custom translation
	 *
	 * @return  string
	 *
	 * @since  2.5
	 */
	private function _getLanguage()
	{
		$language = JFactory::getLanguage();

		$tag = explode('-', $language->getTag());
		$tag = $tag[0];
		$available = array('en', 'pt', 'fr', 'de', 'nl', 'ru', 'es', 'tr');

		if (in_array($tag, $available))
		{
			$this->defaultSettings->lang = $tag;
			return;
		}

		// If the default language is not available, let's search for a custom translation
		$custom_translations = new stdClass;
		
		$custom_translations->instructions_visual = JText::_('JDOM_RECAPTCHA_INSTRUCTIONS_VISUAL');
		$custom_translations->instructions_audio = JText::_('JDOM_RECAPTCHA_INSTRUCTIONS_AUDIO');
		$custom_translations->play_again = JText::_('JDOM_RECAPTCHA_PLAY_AGAIN');
		$custom_translations->cant_hear_this = JText::_('JDOM_RECAPTCHA_CANT_HEAR_THIS');
		$custom_translations->visual_challenge = JText::_('JDOM_RECAPTCHA_VISUAL_CHALLENGE');
		$custom_translations->audio_challenge = JText::_('JDOM_RECAPTCHA_AUDIO_CHALLENGE');
		$custom_translations->refresh_btn = JText::_('JDOM_RECAPTCHA_REFRESH_BTN');
		$custom_translations->help_btn = JText::_('JDOM_RECAPTCHA_HELP_BTN');
		$custom_translations->incorrect_try_again = JText::_('JDOM_RECAPTCHA_INCORRECT_TRY_AGAIN');

		
		$this->defaultSettings->custom_translations = $custom_translations;
		$this->defaultSettings->lang = $tag;
	}	
}