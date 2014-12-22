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

class JDomHtmlFlyFileImage extends JDomHtmlFlyFile
{
	var $fallback = null;		//Used for default


	protected $alt;
	protected $title;
	protected $frame;
	protected $altKey;
	protected $titleKey;


	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 *	@indirect	: Indirect File access
	 *	@root		: Default folder (alias : ex [DIR_TABLE_FIELD]) -> Need a parser (Cook helper)
	 *	@width		: Thumb width
	 *	@height		: Thumb height
	 *	@preview	: Preview type
	 *	@href		: Link on the file
	 *	@target		: Target of the link  ('download', '_blank', 'modal', ...)
	 *
	 *	@alt		: Meta alt
	 *  @frame		: Using a frame to secure the image overflow
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
		$this->arg('alt'	, null, $args);
		$this->arg('title'	, null, $args);
		$this->arg('frame'	, null, $args, true);
		$this->arg('altKey'	, null, $args);
		$this->arg('titleKey'	, null, $args);


		if (!empty($this->altKey))	
			$this->alt = $this->parseKeys($this->dataObject, $this->altKey);
			
		if (!empty($this->titleKey))	
			$this->title = $this->parseKeys($this->dataObject, $this->titleKey);
	}


	function build()
	{

        $pos = $this->imageInfos();

        $thumbUrl = $this->getFileUrl(true);


		// Cannot show the image, only the file path
		if ($this->indirect == 'physical')
			return $thumbUrl;
		
        $imgStyle = $this->getStyles();
       
	   	if (empty($thumbUrl))
			return;
		

        $html = '<img src="' . $thumbUrl . '"'
            .   ($this->title?' title="' . htmlspecialchars($this->title , ENT_COMPAT, 'UTF-8') .'"' : '')
            .   ($this->alt?' alt="' . htmlspecialchars($this->alt , ENT_COMPAT, 'UTF-8') .'"' : '')
            .   ' style="'
            .       ($pos->margin['top']?'margin-top:' . (int)$pos->margin['top'] . 'px;':'')
            .       ($pos->margin['bottom']?'margin-bottom:' . (int)$pos->margin['bottom'] . 'px;':'')
            .       ($pos->margin['left']?'margin-left:' . (int)$pos->margin['left'] . 'px;':'')
            .       ($pos->margin['right']?'margin-right:' . (int)$pos->margin['right'] . 'px;':'')
            .   '"'
            .   ((int)$pos->width?' width="' . (int)$pos->width . 'px" ':'')
            .   ((int)$pos->height?' height="' . (int)$pos->height . 'px" ':'')
            .   '/>';

            
		if ($this->frame && $pos->wrapWidth && $pos->wrapHeight)
		{
			$html = "\n" . '<div class="img-zone" style="width:' . $pos->wrapWidth . 'px;height:' . $pos->wrapHeight . 'px; overflow:hidden;'
            .   'display:inline-block;' . $imgStyle . '"'
            .   '>'
            
            . $html
            
			. '</div>';
		}

		if($this->href != ''){
			$options = array(
				'content' => $html,
				'domClass' => '',
				'href' => $this->href
			);
			
			$options = array_merge($this->options, $options);
			
			$html = JDom::_('html.link', $options);
		}
		
        return $html;
	}

	function imageInfos()
	{
		if (!$this->dataValue)
			return;
		
		$helperClass = $this->componentHelper;		
		if (!$helperClass)
			return;

		$path = $this->dataValue;
		if($this->dataValue[0] != '['){
			$path = $this->root . $this->dataValue;
		}
		
		$options = array(
			'width' => $this->width,
			'height' => $this->height,
			'attrs' => $this->attrs
		);

		if(method_exists($helperClass , 'getImageInfos')){
			$info = $helperClass::getImageInfos($path, $options);
		}
		
		if (!$info)	return;

		$margin = array(
			'top' => 0,
			'bottom' => 0,
			'left' => 0,
			'right' => 0,
		);

		$pos = new stdClass();

		$pos->width = (isset($info->w)?$info->w:null);
		$pos->height = (isset($info->h)?$info->h:null);

		if (!is_array($this->attrs) || (!in_array('fit', $this->attrs)))
		{
			if (isset($info->imagesize))
			{
				$pos->width = min($pos->width, $info->imagesize->width);
				$pos->height = min($pos->height, $info->imagesize->height);
			}
		}

		if (isset($info->resize) && isset($info->imagesize))
		{
			$w = $info->imagesize->width;
			$h = $info->imagesize->height;

		
			if ($this->attrs && in_array('center', $this->attrs))
			{
				if ($info->w != $info->widthCanvas)
				{
					$hzMarg = $info->widthCanvas - $pos->width;
					$margin['left'] = round($hzMarg/2);
					$margin['right'] = $hzMarg - $margin['left'];
				}


				if ($info->h != $info->heightCanvas)
				{
					$vtMarg = $info->heightCanvas - $pos->height;
					$margin['top'] = round($vtMarg/2);
					$margin['bottom'] = $vtMarg - $margin['top'];
				}

			}

		}

		$info->widthCanvas = @(int)$info->widthCanvas;
		$info->heightCanvas = @(int)$info->heightCanvas;

		$pos->wrapWidth = !empty($info->widthCanvas)? $info->widthCanvas:null;
		$pos->wrapHeight = !empty($info->heightCanvas)? $info->heightCanvas:null;
		$pos->margin = $margin;
		$pos->scale = isset($info->scale)?$info->scale:null;

		return $pos;
	}

}
