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

if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
if(!defined('BR')) define("BR", "<br />");
if(!defined('LN')) define("LN", "\n");


/*
 * JDom Framework is an abstraction between your component and the HTML renderer (of your choice)
 *
 * 	Rewrite inside the element classes files you want to change, or override them (see below)
 * 	Using JDom in your component, you'll be able to upgrade all your component DOM possibilities in seconds...
 *
 *  See documentation at www.j-cook.pro
 *
 *
 *	OVERRIDES :
 * 	You can place the files you want to override wherever you prefers see the $searches array;
 *
 *	in the app site client	ie : components/com_mycomponent/dom/html/form/input/select.php
 * 	in the template			ie : templates/my_template/html/com_mycomponent/dom/html/form/input/select.php
 *  in the template view	ie : templates/my_template/html/com_mycomponent/my_view/dom/html/form/input/select.php
 *	and more ...
 *
 *	The search array defines the order of priority for overriding
 *
 *  JDom is 100% compatible for all Joomla! versions since 1.5
 *
 */
class JDom extends JObject
{
	var $path;
	var $options;
	var $app;
	var $plugin;

	var $_pathSite = JPATH_SITE;
	var $_uriJdom;
	
	protected $args;
	protected $fallback;
	
	protected static $loaded = array();
	public $extension;
	
	
	/*
	* Define the priority order to search the classes
	* TODO : Comment some lines, or change order depending on how you want to use this functionnality.
	* see : searchFile()
	*/
	protected $searches = array(
			'fork',			// 	Files on the fork directory as priority
			'template',		// 	Files on the root directory of the template
			'client',		//	Files on the component root directory -> Search in the current client side (front or back)
			'back',			//	Files on the BACK component root directory (Administrator client)
	);
	
	//Some framworks are invasive, you can activate them manually see registerFrameworks()
	public $frameworks = array();  //can contain : bootstrap, icomoon, chosen
	
	
	/*
	 * Constuctor
	 *
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 */
	public function __construct($args = null)
	{
		$this->arg('namespace'	, 0, $args);
		$this->arg('options'	, 1, $args);

		$this->args = $args;
		
		CkJLoader::registerPrefix('JDom', dirname(__FILE__));
		CkJLoader::discover('JDom', dirname(__FILE__));

		$this->app = JFactory::getApplication();
		
		$this->registerFrameworks();
		
		// load some params (if any) from JDOM plugin (if exists)
		// Get the plugin
		$plugin = JPluginHelper::getPlugin('system', 'jdom');
		if(!empty($plugin)){
			$params = new JRegistry;
			$params->loadString($plugin->params);
			$plugin->params = $params;
			$this->plugin = $plugin;
		}
	}

	
	protected function registerFrameworks()
	{
		$version = new JVersion;

		//Compatible with all frameworks
		if ($version->isCompatible('3.0') || ($this->app->isAdmin()))
		{
			$this->registerFramework('bootstrap');
			//$this->registerFramework('icomoon');	//Conflicts with bootstrap
			$this->registerFramework('chosen');
		}
		
		
		if ($this->app->isSite())
		{
			$this->registerFramework('bootstrap');
			$this->registerFramework('icomoon');		
			$this->registerFramework('chosen');
		}
		
	}


	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		
		$this->$property = $value;
		$this->options[$property] = $value;
		return $previous;
	}
	
	
	
	protected function loadClassFile($namespace = null)
	{
		if (!$namespace)
			$namespace = $this->namespace;
		
		$parts = explode('.', $namespace);
		$currentParts = array();
		$className = 'JDom';
		foreach($parts as $part)
		{
			$currentParts[] = $part;
			$className .= ucfirst($part);

			//Load all the parent classes
			if (!$this->includeFile(implode(DS, $currentParts) . '.php', $className))
				return false; //Not found

		}
		return $className;
	}

	protected function includeFile($relativeName, $className)
	{
		$file = $this->searchFile($relativeName);

		//Not founded
		if (!$file)
			return false;

		CkJLoader::register($className, $file);

		return true;
	}

	protected function searchFile($relativeName)
	{
		$extension = $this->getExtension();

		foreach($this->searches as $search)
		{
			switch($search)
			{
				case 'fork';
					$path = JPATH_ADMINISTRATOR .DS. 'components' .DS. $extension .DS. 'fork';
					break;

				case 'template';
					$tmpl = $this->app->getTemplate();
					$path = JPATH_SITE .DS. 'templates' .DS. $tmpl .DS. 'html';
					break;

				case 'client';
					$path = JPATH_COMPONENT;
					break;

				case 'back';
					$path = JPATH_ADMINISTRATOR .DS. 'components' .DS. $extension;
					break;

				default:
					$path = $search;		//Custom path
					break;
			}

			$path = $path .DS. 'dom' .DS. $relativeName;

			if (file_exists($path))
				return $path;
		}

		//Last Fallback : call a children file from the JDom called Class (First instanced)
		if (!file_exists($path))
		{
			$classFile = __FILE__;
			if (preg_match("/.+dom\.php$/", $classFile))
			{
				$classRoot = substr($classFile, 0, strlen($classFile) - 8);
				$path = $classRoot .DS. $relativeName;

				if (file_exists($path))
					return $path;
			}
		}
		return null;
	}


	public static function getInstance($namespace = null, $options = null)
	{
		$app = JFactory::getApplication();
		if (!isset($app->dom))
			$app->dom = new JDom();

		$dom = $app->dom;
		
		if ($namespace)
		{
			$className = $dom->loadClassFile($namespace);
			if (!class_exists($className))
				return null;
		
			$class = new $className(array($namespace, $options));
			return $class;
		}
		
		return $dom;
	}
	
	protected function getClassInstance()
	{
		$className = $this->loadClassFile();
		
		if (!class_exists($className))
			return null;
		
		$class = new $className($this->args);

		//Load the fallback class
		if (!method_exists($class, 'build'))
		{
			
			$fallback = $class->fallback;
			if (!empty($fallback))
			{
				$className .= ucfirst($fallback);
				$namespace = $this->namespace .'.'. $fallback;
				$this->loadClassFile($namespace);
				
				if (!class_exists($className))
					return $class;
				
				$class = new $className($this->args);
			}
		
		}
		
		if (!method_exists($class, 'build'))
		{
			exit($this->error('build() function not found.'));
		}
		
		return $class;
	}
	
	public static function _()
	{
		$dom = self::getInstance();
		$args = func_get_args();
		
		$dom->set('args', $args);
		
		return $dom->render($args);
	}
	
	public function __()
	{
		$args = func_get_args();
		$this->set('args', $args);
		
		return $this->render($args);
	}
	
	protected function error($msg, $icon = null)
	{
		$html = '<strong>JDom Error</strong> : ' . $msg;
		return $html;
	}
	
	public function render($args = array())
	{
		//Get the namespace
		if (empty($args[0]))
			return $this->error('Namespace is undefined');
		
		$this->namespace = $namespace = $args[0];
		
		$class = $this->getClassInstance();
		if (!$class)
			return $this->error('Not found : <strong>' . $namespace . '</strong>');
		
		$class->loadOptions();
		
		//load the extension name
		$this->getExtension();
				
		return $class->output();
	}
	
	public function output()
	{
		//ACL Access
		if (!$this->access())
			return '';	//Not authorizated
		
		//HTML
		$html = $this->build();
	
	
		//EMBED LINK
		if (method_exists($this, 'embedLink'))
			$html = $this->embedLink($html);
		
		//Assets implementations
		$this->implementAssets();
		
		if ($this->isAjax())
			$this->ajaxHeader($html);	//Embed javascript and CSS in case of Ajax call
	
		//Parser
		$html = $this->parse($html);   //Was Recursive ?
		
		return $html;
	}
	
	public function registerFramework($framework)
	{
		$this->frameworks[$framework] = true;		
	}
	
	protected function useFramework($framework)
	{
		$dom = JDom::getInstance();
		if (in_array($framework, array_keys($dom->frameworks)))
			return true;
	}

	
	
	protected function loadOptions()
	{
		if (!$this->args)
			return;
		
		$options = array();
		if (!empty($this->args[1]))
			$options = $this->args[1];
		
		$this->options = $options;
	}
	
	protected function arg($name, $i = null, $args = array(), $fallback = null)
	{
		@$currentValue = $this->$name;		
		$optionValue = $this->getOption($name);

		if ($optionValue !== null){
			$newVal = $this->options[$name];
		} else if (($i !== null) && (count($args) > $i)){
			if ($args[$i] !== null){
				$newVal = $args[$i];
			}
		}
		
		if (!isset($newVal) && ($fallback !== null)){
			$newVal = $fallback;
		}
		
		if(!isset($newVal)){
			return;
		}
		
		if ($optionValue){
			$this->options[$name] = $newVal;
		}
		
		switch($name){
			case 'selectors':
				$this->addSelectors($newVal);
				$newVal = $this->selectors;
				break;
		}
		
		$this->$name = $newVal;
	}

	protected function isArg($varname)
	{
		if (isset($this->$varname) || (is_array($this->options) && (in_array($varname, array_keys($this->options)))))
			return true;
		else
			return false;
	}

	public function getOption($name)
	{
		if ($name == 'options')
			return;
		if (!is_array($this->options))
			return;
		
		if (!(in_array($name, array_keys($this->options))))
			return;
		
		if (!isset($this->options[$name]))
			return;
		
		return $this->options[$name];
	}

	public function getExtension()
	{
		$dom = JDom::getInstance();
		if ($extension = $dom->get('extension'))
			return $extension;
		
		$extension = $this->getOption('extension');
		if (!$extension)
		{
			$jinput = new JInput;
			$extension = $jinput->get('option', null, 'CMD');
		}
		
		$dom->set('extension', $extension);

		return $extension;
	}
	
	function getComponentHelper($comAlias = '')
	{
		if(empty($comAlias)){
			$comAlias = substr($this->getExtension(), 4);
		}
		$helperClass = ucfirst($comAlias) . 'Helper';
		
		if (!class_exists($helperClass))
		{
			echo('Class <strong>' . $helperClass . '<strong> not found');
			return;
		}
		return new $helperClass;
	}
	
	public function isAjax()
	{
		$jinput = new JInput;
		$layout = $jinput->get('layout', null, 'CMD');
		if ($layout == 'ajax')
			return true;
		
		return false;
	}
	
	
//DEPRECATED
	public function getView()
	{
		$view = $this->getOption('view');

		if (!$view)
		{
			$jinput = new JInput;
			$view = $jinput->get('view', null, 'CMD');
		}

		return $view;
	}

	protected function implementAssets()
	{
		//Javascript
		$this->buildJs();
		$this->attachJsFiles();

		//CSS
		$this->buildCss();
		$this->attachCssFiles();
	}

	public function buildJs()	{}
	
	protected function attachJsFiles()
	{
		//Javascript
		if (!isset($this->attachJs))
			return;

		$attachJs = $this->attachJs;

		if (!is_array($attachJs))
			$attachJs = array($attachJs);

		$fileBase = ""; // dom Root
		if (isset($this->assetName) && ($this->assetName != null))
			$fileBase = 'assets' .DS. $this->assetName .DS. 'js' .DS;
			
		foreach($attachJs as $jsFileName)
		{
			if (preg_match("/^http/", $jsFileName)){
				JFactory::getDocument()->addScript($jsFileName);
			} else {
				if(strpos($jsFileName,'assets') !== 0){ 
					$jsFileName = $fileBase . $jsFileName;
				}
				$this->addScript($jsFileName);
			}
		}
	}

	protected function addScript($assetPath = null)
	{
		if ((!$assetPath) && (!isset($this->assetName)))
			return;

		if ($assetPath)
			$relativeName = $assetPath;
		else
			return;

			
		$mode = '';
		if(isset($this->plugin)){
			$mode = $this->plugin->params->get('mode','development');
		}

		if($mode == 'production'){
			$originalName = $relativeName;
			$ext = pathinfo($relativeName, PATHINFO_EXTENSION);
			$relativeName = str_replace('.'. $ext,'.min.'.$ext ,$relativeName);
		}
			
		$jsFile = $this->searchFile($relativeName);
		
		if($mode == 'production' AND empty($jsFile)){
			$jsFile = $this->searchFile($originalName);
		}		
		
		if ($jsFile)
		{
			$jsFile = self::pathToUrl($jsFile);
			if (isset(self::$loaded[__METHOD__][$relativeName]))
				return;
			
			if ($this->isAjax())
			{
				$jsScript = LN . '<script type="text/javascript">'
				.	LN . 'jQuery.getScripts(' . json_encode(array($jsFile)). ', function(){});'
				.	LN . '</script>';
				
				echo $jsScript;
			}
			else
			{
				$doc = JFactory::getDocument();
				$doc->addScript($jsFile);				
			}
			
			
			self::$loaded[__METHOD__][$relativeName] = true;
		}

	}

	protected function addScriptInline($script, $embedReady = false, $embedFramework = 'jQuery')
	{
		if (isset(self::$loaded[__METHOD__][$script]))
			return;

		if ($embedFramework)
			$script = $this->jsEmbedFramework($script, $embedFramework);

		//Do not embed ajax. Handled by the Ajax class callback
		if ($embedReady && !$this->isAjax())
			$script = $this->jsEmbedReady($script);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		self::$loaded[__METHOD__][$script] = true;

	}
	
	protected function jsEmbedReady($script)
	{
		//Do not embed. Handled by the Ajax class callback
		if ($this->isAjax())
			return $script;
		
		$js = "jQuery('document').ready(function(){" . LN;
		$js .= $this->indent($script, 1);
		$js .= LN. "});";

		return $js;
	}
	
	protected function jsEmbedFramework($script, $embedFramework = 'jQuery')
	{

		$js = ';(function($){' . LN;
		$js .= $this->indent($script, 1);
		$js .= LN. "})($embedFramework);";

		return $js;
	}

	public function buildCss()	{}

	protected function attachCssFiles()
	{
		if (!isset($this->attachCss))
			return;

		$attachCss = $this->attachCss;

		if (!is_array($attachCss))
			$attachCss = array($attachCss);

		$fileBase = ""; // dom Root
		if (isset($this->assetName) && ($this->assetName != null))
			$fileBase = 'assets' .DS. $this->assetName .DS. 'css' .DS;


		foreach($attachCss as $cssFileName)
		{
			if(strpos($cssFileName,'assets') !== 0){
				$cssFileName = $fileBase . $cssFileName;
			}
			$this->addStyleSheet($cssFileName);
		}
	}



	protected function addStyleSheet($assetPath = null)
	{
		if ((!$assetPath) && (!isset($this->assetName)))
			return;

		if ($assetPath)
			$relativeName = $assetPath;
		else
		{
			$name = $this->assetName;
			$relativeName = 'assets' .DS. $name . DS. 'css' .DS . $name . '.css';
		}

		$mode = '';
		if(isset($this->plugin)){
			$mode = $this->plugin->params->get('mode','development');
		}

		if($mode == 'production'){
			$originalName = $relativeName;
			$ext = pathinfo($relativeName, PATHINFO_EXTENSION);
			$relativeName = str_replace('.'. $ext,'.min.'.$ext ,$relativeName);
		}
			
		$cssFile = $this->searchFile($relativeName);
		
		if($mode == 'production' AND empty($cssFile)){
			$cssFile = $this->searchFile($originalName);
		}
		
		if ($cssFile)
		{
			$cssFile = self::pathToUrl($cssFile);
			if (isset(self::$loaded[__METHOD__][$relativeName]))
				return;

			JFactory::getDocument()->addStyleSheet($cssFile);

			self::$loaded[__METHOD__][$relativeName] = true;
		}
	}

	protected function addStyleDeclaration($css)
	{
		if (isset(self::$loaded[__METHOD__][$css]))
				return;
	
		JFactory::getDocument()->addStyleDeclaration($css);
		self::$loaded[__METHOD__][$css] = true;
		
	}


	protected function ajaxHeader(&$html)
	{
		if (!$this->isAjax())
			return;
		
		$js = $this->ajaxCallbackOnLoad();
		$css = $this->ajaxAttachCss();
		$html = $css . $js . $html;
	}


	/**
	 * Embed the scripts inside a temporary function called after the domReady event
	 */
	protected function ajaxCallbackOnLoad()
	{
		if (!$this->isAjax())
			return;
	
		//Ajax token is the unique fallback function name
		$jinput = new JInput;
		$token = $jinput->get('token', null, 'CMD');
		if (!$token)
			return;
			
	// Get script declarations
		$scripts = array();		
		if (!empty(self::$loaded['JDom::addScriptInline']))
			foreach(self::$loaded['JDom::addScriptInline'] as $content => $foo)
				$scripts[] = $content;

		if (!count($scripts))
			return '';
		
		$jsScriptCallback =  'registerCallback("' . $token . '", function(){' 
			. implode(";\n", $scripts) 
			. '});';

		$jsScript = '<script type="text/javascript">'
			.	$jsScriptCallback
			. 	'</script>';
		
		return $jsScript;
	}


	protected function ajaxAttachCss()
	{
		$html = '';
		// Generate stylesheet links
		if (!empty(self::$loaded['JDom::addStyleSheet']))
			foreach (self::$loaded['JDom::addStyleSheet'] as $url => $foo)
			{
				$cssFile = $this->searchFile($url);
				if ($cssFile)
					$cssFile = self::pathToUrl($cssFile);
	
				$html .= '<link rel="stylesheet" href="' . $cssFile . '" type="text/css"/>';
			}

		return $html;
	}

	protected function assetsDir()
	{
		if (!$this->assetName)
			return;
		
		return dirname(__FILE__) .DS. 'assets' .DS. $this->assetName;
	}

	protected function assetFilePath($type, $name)
	{
		if (!$this->assetName)
			return;
		
		if (!in_array($type, array('js', 'css', 'images', 'fonts')))
			return;
		
		return $this->assetsDir() .DS. $type .DS. $name;
	}


	protected function jsonArgs($args = array())
	{
		return json_encode($args);
	}

	protected function indent($contents, $indent)
	{
		if (is_int($indent))
		{
			$indentStr = "";
			for($i = 0 ; $i < $indent ; $i++)
				$indentStr .= "	";
		}
		else
			$indentStr = $indent;

		$lines = explode("\n", $contents);
		$indentedLines = array();

		foreach($lines as $line)
		{
			if (trim($line) != "") //Don't indent line if empty
				$line = $indentStr . $line;

			$indentedLines[] =  $line;
		}

		return implode("\n", $indentedLines);
	}

	protected function parseVars($vars)
	{
		return array_merge(array(
		
		), $vars);
		
	}

	protected function parse($html)
	{
		$vars = $this->parseVars(array());

		if (is_string($html) AND isset($vars) AND count($vars)){
			foreach($vars as $key => $value)
			{
				//Escape $ char
				$value = str_replace("$", "\\$", $value);
				//Replace values
				$html = preg_replace("/<%" . strtoupper($key) . "%>/", $value, $html);
			}
		}

		return $html;
	}

	/*
	 * object	@object	: Object value source
	 * string 	@pattern : pattern composed by object keys:
	 * 					ie : "<%name%> <%surname%> <%_user_email%>" (DEPRECATED)
	 * 					ie : "{name} {surname} {_user_email}" (NEW FORMAT)
	 * 					note : theses values must be available in $object
	 */
	protected function parseKeys($object, $pattern)
	{
		if (is_array($pattern))
		{
			$namespace = $pattern[0];
			array_shift($pattern);
			$options['labelKey'] = null; // No recursivity

			$options = array_merge($this->options, $pattern);
			$labelKey = $options['labelKey'];

			$options['list'] = null;
			$options['dataValue'] = $this->parseKeys($object, $labelKey);

			return JDom::_($namespace, $options);
		}
		
		//Tags <% % > are deprecated use { } instead
		$tag1 = '[<,{]%?';
		$tag2 = '%?[>,}]';

		
		$matches = array();
		if (preg_match_all("/" . $tag1 . "([a-zA-Z0-9_]+:)?([a-zA-Z0-9_]+)" . $tag2 . "/", $pattern, $matches))
		{

			$label = $pattern;

			$index = 0;
			foreach($matches[0] as $match)
			{
				$key = $matches[2][$index];

				if ($type = $matches[1][$index])
				{
					//JDOM FLY DEFINE
					$type = substr($type, 0, strlen($type) - 1);

					$namespace = "html.fly." . $type;
					$options['dataValue'] = $this->parseKeys($object, $key);

					$value = JDom::_($namespace, $options);
				}
				else
				{
					$value = (isset($object->$key)?$object->$key:"");
				}
				$label = preg_replace("/" . $tag1 . "([a-zA-Z0-9_]+:)?" . $key . "" . $tag2 . "/", $value, $label);
				$index++;

			}

		}
		else
		{
			$key = $pattern;  //No patterns
			$label = (isset($object->$key)?$object->$key:"");
		}

		return $label;
	}


	/*
	 * Parse a string with JText
	 * Accepts a composed string ie : "[MY_FIRST_STRING], [MY_SECOND_STRING] : "
	 */
	protected function JText($text)
	{
		//Fix a little Joomla bug
		if ((strtolower($text) === 'true') || (strtolower($text) === 'false'))
			return $text;

		if (preg_match("/\[([A-Z0-9_]+)\]/", $text))
		{
			preg_match_all("/\[([A-Z0-9_]+)\]/", $text, $results);
			foreach($results[1] as $string)
			{
				$translated = JText::_($string);
				$text = preg_replace("/\[(" . $string . ")\]/", JText::_($string), $text);
			}
		}
		else
			$text = JText::_($text);

		return $text;

	}

	public function setPathSite($path)
	{
		JDom::getInstance()->_pathSite = $path;
	}

	public function getPathSite()
	{
		return JDom::getInstance()->_pathSite;
	}

	public function setUriJDomBase($uri)
	{
		JDom::getInstance()->_uriJdom = $uri;
	}
	
	public function getUriJDomBase()
	{
		return JDom::getInstance()->_uriJdom;
	}

    protected function pathToUrl($path, $raw = false)
    {
        $base = JDom::getInstance()->getPathSite();
        $uri = JDom::getInstance()->getUriJDomBase();
        
        // Convert eventual Windows directory separators
        if (DS == "\\")
        {
            $path = str_replace(DS, "/", $path);
            $base = str_replace(DS, "/", $base);            
        }

        // Reduce until the base
        $relUrl = $uri . substr($path, strlen($base));
 
        if ($raw)
            return $relUrl;

        // Return complete URL
        return JURI::root(true) . $relUrl;
    }

	protected function strftime2regex($format)
	{
		$d2 = "(\d{2})";
		$d4 = "([1-9]\d{3})";

		$patterns =
array(	"\\", 	"/", 	"#",	"!", 	"^", "$", "(", ")", "[", "]", "{", "}", "|", "?", "+", "*", ".",
		"%Y", 	"%y",	"%m",	"%d", 	"%H", 	"%M", 	"%S", 	" ");
		$replacements =
array(	"\\", "\/", 	"\#",	"\!", 	"\^", "$", "\(", "\)", "\[", "\]", "\{", "\}", "\|", "\?", "\+", "\*", "\.",
		$d4,	$d2,	$d2,	$d2,	$d2,	$d2,	$d2,	"\s");

		$regex = str_replace($patterns, $replacements, $format);

		return "/^" . $regex . "$/";
	}


	protected function jVersion($ver, $comp = '>=')
	{		
		jimport('joomla.version');
		$version = new JVersion();

		return version_compare($version->RELEASE, $ver, $comp);
	}

	protected function adminTemplate()
	{
		if ($this->jVersion('3.0'))
			return 'isis';
		else
			return 'bluestork';	
	}

	protected function systemImagesDir()
	{
		$dir = 'templates' .DS. $this->adminTemplate() .DS. 'images' .DS. 'admin';
		
		if ($this->app->isSite())
			$dir = "administrator" .DS . $dir;
		
		return $dir;
	}


	protected function extensionDir()
	{
		return JPATH_ADMINISTRATOR .DS. 'components' .DS. $this->getExtension();
	}

	protected function domUrl()
	{
		$url = self::pathToUrl($this->extensionDir() . '/dom');
		return $url;
	}

	protected function assetImage($imageName, $assetName = null)
	{
		if (!$assetName)
			return;

		$urlImage = self::domUrl().'/assets/'. $assetName . '/images/' . $imageName;

		return $urlImage;
	}

	protected function htmlAssetSpriteImage($urlImage, $d)
	{
		$image = "<div style='background-image: url(" . $urlImage . ");"
			.	"width:" . $d->w . "px;"
			.	"height:" . $d->h . "px;"
			.	"background-position:-" . $d->x . "px -" . $d->y . "px;'>"
			.	"</div>";

		return $image;
	}

	protected function accessTask($task)
	{
		$aclAccess = $this->getOption('aclAccess');

		if ($aclAccess)
			return $this->access();

		switch ($task)
		{
			case 'new':
				$access = 'core.create';
				break;

			case 'edit':
			case 'save':
			case 'apply':
				$access = 'core.edit';
				break;

			case 'publish':
			case 'unpublish':
			case 'trash':
			case 'default_it':
				$access = 'core.edit.state';
				break;

			case 'delete':
			case 'empty_trash':
				$access = 'core.delete';
				break;

			case 'config':
				$access = 'core.manage';
				break;

			default:
				return true;
				break;
		}

		return $this->access($access);
	}


	protected function getRoute($route, $target = null)
	{
		if (($target == 'modal') && empty($route['tmpl']))
			$route['tmpl'] = 'component';
			
		$jinput = $this->app->input;
						
		$vars = array_merge(array_keys($route), array('option', 'view', 'layout', 'task', 'cid[]', 'tmpl'));
		$followers = array('lang', 'Itemid', 'tmpl');

		$queryVars = array();

		foreach($vars as $var)
		{
			if (isset($route[$var]))
			{
				if (!empty($route[$var]))
					$queryVars[$var] = $route[$var];
			}
			else
			{
				$value = $jinput->get($var, null, 'STRING');
				if ($value !== null)
					$queryVars[$var] = $value;
			}
		}

		foreach($followers as $follower)
		{
			$value = $jinput->get($follower, null, 'CMD');
			if ($value !== null)
				$queryVars[$follower] = $value;
		}

		$parts = array();

		if (count($queryVars))
		foreach($queryVars as $key => $value)
			$parts[] = $key . '=' . $value;

		$url = JRoute::_("index.php?" . implode("&", $parts), false);

		return $url;			
		
	}

	protected function access($aclAccess = null)
	{
		if (!$aclAccess)
			$aclAccess = $this->getOption('aclAccess');

		if (!$aclAccess)
			return true;

		if (!is_array($aclAccess))
			$aclAccess = array($aclAccess);

		$aclAsset = $this->getOption('aclAsset');
		if (!$aclAsset)
			$aclAsset = $this->getExtension();

		$user 	= JFactory::getUser();

		$authorize = false;
		foreach($aclAccess as $acl)
		{
			$auth = $user->authorise($acl, $aclAsset);

			if ($auth)
				$authorize = true;
		}

		return $authorize;
	}


	function regexFromDateFormat($dateFormat)
	{
		$d2 = '[0-9]{2}';
		$d4 = '[1-9][0-9]{3}';

		$patterns = array(
			'\\','/','#','!','^','$','(',')','[',']','{','}','|','?','+','*','.',
			'%?Y','%?y','%?m','%?d', '%?H', '%?I', 'i', '%?l', '%?M', '%?S', ' '
		);
		
		$replacements = array(
			'\\\\', '\\/','\\#','\\!','\\^','\\$','\\(','\\)','\\[','\\]','\\{','\\}','\\|','\\?','\\+','\\*','\\.',
			$d4,$d2,$d2,$d2,$d2,$d2,$d2,$d2,$d2,$d2,'\s'	
		);

		return "^" . str_replace($patterns, $replacements, $dateFormat) . "$";
	}
	
	function legacyDateFormat($dateFormat)
	{
		$patterns = array(	
		'Y','y','m','d', 'H', 'i', 'l', 's');

		$replacements =	array(	
		'%Y','%y','%m','%d', '%H', '%M', '%l', '%S');

		return str_replace($patterns, $replacements, $dateFormat);

	}

	public static function escapeJsonString($value) {
		# list from www.json.org: (\b backspace, \f formfeed)    
		$escapers =     array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}	

	public static function safeAlias($str, $toCase = 'lower')
	{
		//ACCENTS
		$accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$str = str_replace($accents, $replacements, $str);

		//SPACES
		$str = preg_replace("/\s+/", "-", $str);

		//OTHER CHARACTERS
		$strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
					   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
					   "—", "–", ",", "<", ".", ">", "/", "?");
		$str = trim(str_replace($strip, "", strip_tags($str)));
		
		switch($toCase)
		{
			case 'lower':
				$str = strtolower($str);
				break;

			case 'upper':
				$str = strtoupper($str);
				break;

			case 'ucfirst':
				$str = ucfirst($str);
				break;

			case 'ucwords':
				$str = ucwords($str);
				break;

			default:
				break;

		}

		return $str;
	}
	
	function getLanguages(){
		static $languages;
		
		if(isset($language)){
			return $languages;
		}
		$db = JFactory::getDBO();
		
		$sql = "SELECT *, LOWER(REPLACE(lang_code,'-','')) as lang_tag  FROM #__languages WHERE published = 1";
		$db->setQuery(  $sql );
		$languages = $db->loadObjectList();

		foreach($languages as &$lang){
			$lang->postfix = $lang->lang_tag;
			if($lang->lang_tag != ''){
				$lang->postfix = '_'. $lang->lang_tag;
			}
			
			$lang->img_url = '';
			if($lang->lang_code != ''){
				$lang->img_url = JURI::root() .'media/mod_languages/images/'. $lang->image .'.gif';
			}
		}
		
		return $languages;
	}	
}
