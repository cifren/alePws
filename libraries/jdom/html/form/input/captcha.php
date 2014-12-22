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
* @added by		Girolamo Tomaselli - http://bygiro.com
* @version		0.0.1
*
*             .oooO  Oooo.
*             (   )  (   )
* -------------\ (----) /----------------------------------------------------------- +
*               \_)  (_/
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomHtmlFormInputCaptcha extends JDomHtmlFormInput
{
	const RECAPTCHA_API_SERVER = "http://www.google.com/recaptcha/api";
	const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
	const RECAPTCHA_VERIFY_SERVER = "www.google.com";

	var $pubkey;
	var $privkey;
	var $theme;
	var $captcha_plugin;
	var $loadLanguageFile = true;
	var $assetName = 'captcha';
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;	


	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 * 	@size		: Input Size
	 * 
	 */
	function __construct($args)
	{
		parent::__construct($args);

		$this->arg('pubkey'		, 6, $args, '');
		$this->arg('privkey'		, 6, $args, '');
		$this->arg('theme'		, 6, $args, '');

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
		
		if($this->pubkey == '' OR $this->privkey == ''){
			$this->pubkey = trim($params->get('public_key', ''));
		}
		
		if($this->theme == ''){
			$this->theme = $params->get('theme', '');
		}
		
	}
	
	function build()
	{
		JDom::_('framework.jquery.multirecaptcha');
		$html = '';
		if(is_string($this->selectors)){
			$this->selectors .= ' data-multirecaptcha-pubkey="'. $this->pubkey .'"';
			$this->selectors .= ' data-multirecaptcha-theme="'. $this->theme .'"';
		} if(!is_array($this->selectors)){			
			$this->selectors = array();
		}
		
		if(is_array($this->selectors)){			
			$this->selectors['data-multirecaptcha-pubkey'] = $this->pubkey;
			$this->selectors['data-multirecaptcha-theme'] = $this->theme;
		}
		
		$this->domClass = str_replace('required','',$this->domClass) . ' multirecaptcha';
	
		//html code inside form tag
		$html .= '<div style="clear: both;"></div>';
		$html .= '<div <%STYLE%><%CLASS%><%SELECTORS%> id="<%DOM_ID%>"></div>';
	
		return $html;
	}
	
}