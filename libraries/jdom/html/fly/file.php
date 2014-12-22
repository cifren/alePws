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

class JDomHtmlFlyFile extends JDomHtmlFly
{
	var $fallback = 'path';		//Used for default
	var $allowWrapLink = false;	// Because this class in only a dispather
	
	protected $componentHelper;
	protected $comAlias;
	protected $indirect;
	protected $width;
	protected $height;
	protected $root;
	protected $attrs;

	protected $view;
	protected $cid;
	protected $listKey;
	
	protected $thumb;
	protected $fullRoot; 

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 *
	 *	@root		: Default folder (alias : ex [DIR_TABLE_FIELD]) -> Need a parser (Cook helper)
	 *	@width		: Thumb width
	 *	@height		: Thumb height
	 *	@attrs		: File attributes ('crop', 'fit', 'center', 'quality')
	 * 
	 *	@indirect	: Indirect File access
	 * 		indirect : access through controler to decode path
	 * 		direct 	: access through direct URL
	 * 		physical: retreive real file path (No preview, return string)
	 * 		index	: through database index : Filter item accesses to protect files
	 * 				Required for 'index' :
	 * 					@view 		: Table name
	 * 					@dataKey 	: Image path field
	 * 					@listKey 	: Dynamic Item id (with dataObject)
	 * 					@cid		: Static Item id
	 */
	function __construct($args)
	{

		parent::__construct($args);


		$this->arg('indirect'	, null, $args, 'indirect');
		$this->arg('root'		, null, $args);
		$this->arg('width'		, null, $args, 0);
		$this->arg('height'		, null, $args, 0);
		$this->arg('attrs'		, null, $args);

		$this->arg('listKey'	, null, $args, 'id');
		$this->arg('view'		, null, $args);
		$this->arg('cid'		, null, $args);
		$this->arg('fullRoot'	, null, $args, false); 
		$this->arg('comAlias'	, null, $args); 
		
		$this->componentHelper = $comHelper = $this->getComponentHelper($this->comAlias);
		
		if($comHelper AND method_exists($comHelper, 'getImgSizes')){
			$imgSizesLimit = $comHelper::getImgSizes();
			
			$width = min($this->width, $imgSizesLimit->maxWidth);
			$this->width = max($width, $imgSizesLimit->minWidth);
			
			$height = min($this->height, $imgSizesLimit->maxHeight);
			$this->height = max($width, $imgSizesLimit->minHeight);
		}
	
		$this->thumb = ($this->width || $this->height);

		if ($this->indirect === true)
			$this->indirect = 'indirect';
		else if ($this->indirect === false)
			$this->indirect = 'direct';

    	if (!is_array($this->attrs)){
    		$this->attrs = explode(",", $this->attrs);
		}

		$type = '';
		//Dispatcher
		switch($this->getFileExt())
		{
			case 'png':
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'bmp':
				$type = 'image';
				break;
				
			default:
				$type = 'path';
				break;

		}

		$this->fallback = $type;
		$this->buildHref($type);		
	}

	function getFileUrl($thumb = false, $link = false)
	{
		$helperClass = $this->componentHelper;		
		if (!$helperClass)
			return;

		if (($this->indirect != 'index') && empty($this->dataValue))
			return;
		
		$path = $this->dataValue;
		if (!preg_match("/\[.+\]/", $path) AND !preg_match("/\{\{.+\}\}/", $path)){
			$path = $this->root . $path;				
		}
		$path = trim(preg_replace("#/+#", "/", str_replace('\\','/',$path)),'/');

		// $link = false when creating the image thumb. 'download' not allowed in this case.
		// Then, pass a second time to eventually create the download URL	
		$options = array();
		if ($thumb)
			$options = array(
				'width' => $this->width,
				'height' => $this->height,
				'attrs' => $this->attrs,			
			);
		else if ($link)
		{
			$options = array(
				'content' => ($this->target == 'content'),			
				'download' => ($this->target == 'download')			
			);
		}
		
		if($this->fullRoot){
			$options['fullRoot'] = $this->fullRoot;
		}
		
		switch ($this->indirect)
		{
			case 'index':		// Indexed image url
				if ((!$cid = $this->cid) && $this->dataObject && ($listKey = $this->listKey))
					$cid = $this->dataObject->$listKey;
					
				if(method_exists($helperClass , 'getIndexedFile')){
					$url = $helperClass::getIndexedFile($this->view, $this->dataKey, $cid, $options);
				}
				break;
			
			case 'physical':	// Physical file on the drive (url is a path here)
			case 'direct':		// Direct url				
			case 'indirect':	// Indirect file access
			default:
				if(method_exists($helperClass , 'getFile')){
					$url = $helperClass::getFile($path, $this->indirect, $options);
				}
				
				if($this->indirect != 'physical' AND $this->indirect != 'direct'){
					// default - indirect
					if($this->cid){
						$url .= '&cid='. $this->cid;
					}
					
					if($this->fullRoot){
						$url = trim(str_replace(JURI::root(true),'',JURI::root()),'/') . $url;
					}
				} else {
					// physycal - direct
					
					if($this->fullRoot){
						$url = JURI::root() . $url;
					}				
					$url = preg_replace( '/\\\\+/', '/', $url);
				}
				break;
		}	
	
		// FIX: remove administrator from the URL
	//	$url = str_replace(array('administrator/','administrator\\'),'',$url);  TODO
		
		/* Uncomment to see the returned url */
		//echo('<pre>');print_r($url);echo('</pre>');

		return $url;
	}
	
	function getFileExt()
	{
		$path_parts = pathinfo($this->getFileUrl());
		$ext = isset($path_parts["extension"])?$path_parts["extension"]:'';

		return strtolower($ext);
	}
	
	function buildHref($type)
	{	
		if ($this->target == 'download')
			$this->target = 'download';
		else if ($this->preview == 'modal')
		{
			$this->handler = '';
			switch($type)
			{
				case 'image':
					$this->target = 'modal';
					break;

				case 'flash':
					$this->target = 'modal';
					$this->handler = 'iframe';
					break;

				default:
					$this->target = 'download';
					break;
			}

			$this->options['target'] = $this->target;
			$this->options['handler'] = $this->handler;

		}


		if (($this->href || $this->target) && (basename($this->dataValue) != ""))
		{
			if (!$this->href)
			{
				$this->href = $this->getFileUrl(false, true);
				$this->options['href'] = $this->href;
			}


		}
	}	
}