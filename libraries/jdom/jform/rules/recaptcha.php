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
* Form validator rule for Jdom.
*
* @package	Jdom
* @subpackage	Form
*/
class JFormRuleRecaptcha extends JdomClassFormRule
{

	const RECAPTCHA_API_SERVER = "http://www.google.com/recaptcha/api";
	const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
	const RECAPTCHA_VERIFY_SERVER = "www.google.com";

	protected $private_key = '';
	
	/**
	* Indicates that this class contains special methods (ie: get()).
	*
	* @var boolean
	*/
	public $extended = true;

	/**
	* Unique name for this rule.
	*
	* @var string
	*/
	protected $handler = 'recaptcha';

	/**
	* Method to test the field.
	*
	* @access	public
	* @param	SimpleXMLElement	$element	The JXMLElement object representing the <field /> tag for the form field object.
	* @param	mixed	$value	The form field value to validate.
	* @param	string	$group	The field name group control value. This acts as as an array container for the field.
	* @param	JRegistry	$input	An optional JRegistry object with the entire data set to validate against the entire form.
	* @param	JForm	$form	The form object for which the field is being tested.
	*
	* @return	boolean	True if the value is valid, false otherwise.
	*
	* @since	11.1
	*/
	public function test(SimpleXMLElement &$element, &$value, $group = null, JRegistry $input = null, JForm &$form = null)
	{		
		$jinput = JFactory::getApplication()->input;
		$value = $jinput->get('recaptcha_response_field', null, 'STRING');
		
		$version = new JVersion();
		$plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
		
		// Joomla! 1.6 - 1.7 - 2.5
		if (version_compare($version->RELEASE, '2.5', '<=')){
			jimport( 'joomla.html.parameter' );
			$params = new JParameter($plugin->params);
		} else {
			$params = new JRegistry($plugin->params);
		}
		
		$this->public_key = trim($params->get('public_key', ''));
		$this->private_key = trim($params->get('private_key', ''));
		
		$elePrivKey = (string)$element['privkey'];
		$elePubKey = (string)$element['pubkey'];
		
		if($elePrivKey != '' AND $elePubKey != ''){
			$this->public_key = $elePubKey;
			$this->private_key = $elePrivKey;
		}
		
		if(!$this->onCheckAnswer($value)){
			return false;
		}

		return true;
	}


	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  *
	  * @return  True if the answer is correct, false otherwise
	  *
	  * @since  2.5
	  */
	public function onCheckAnswer($code)
	{
		$input      = JFactory::getApplication()->input;
		$privatekey = $this->private_key;
		$remoteip   = $input->server->get('REMOTE_ADDR', '', 'string');
		$challenge  = $input->get('recaptcha_challenge_field', '', 'string');
		$response   = $input->get('recaptcha_response_field', '', 'string');

		// Check for Private Key
		if (empty($privatekey))
		{
			$msg = JText::_('JDOM_RECAPTCHA_ERROR_NO_PRIVATE_KEY');
			JError::raiseWarning(1201, $msg);
			return false;
		}

		// Check for IP
		if (empty($remoteip))
		{
			$msg = JText::_('JDOM_RECAPTCHA_ERROR_NO_IP');
			JError::raiseWarning(1201, $msg);
			return false;
		}

		// Discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0)
		{
			$msg = JText::_('JDOM_RECAPTCHA_ERROR_EMPTY_SOLUTION');
			JError::raiseWarning(1201, $msg);
			return false;
		}

		$response = $this->_recaptcha_http_post(
			self::RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
			array(
				'privatekey' => $privatekey,
				'remoteip'   => $remoteip,
				'challenge'  => $challenge,
				'response'   => $response
			)
		);

		$answers = explode("\n", $response[1]);

		if (trim($answers[0]) === 'true'){
			return true;
		} else {
			// @todo use exceptions here
			$msg = JText::_('JDOM_RECAPTCHA_ERROR_' . strtoupper(str_replace('-', '_', $answers[1])));
			JError::raiseWarning(1201, $msg);
			return false;
		}
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param   array  $data  Array of string elements to be encoded
	 *
	 * @return  string  Encoded request
	 *
	 * @since  2.5
	 */
	private function _recaptcha_qsencode($data)
	{
		$req = "";

		foreach ($data as $key => $value)
		{
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req = rtrim($req, '&');

		return $req;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server.
	 *
	 * @param   string  $host
	 * @param   string  $path
	 * @param   array   $data
	 * @param   int     $port
	 *
	 * @return  array   Response
	 *
	 * @since  2.5
	 */
	private function _recaptcha_http_post($host, $path, $data, $port = 80)
	{
		$req = $this->_recaptcha_qsencode($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';

		if (($fs = @fsockopen($host, $port, $errno, $errstr, 10)) == false )
		{
			die('Could not open socket');
		}

		fwrite($fs, $http_request);

		while (!feof($fs))
		{
			// One TCP-IP packet
			$response .= fgets($fs, 1160);
		}

		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}
}