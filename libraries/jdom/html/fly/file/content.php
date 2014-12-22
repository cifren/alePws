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

class JDomHtmlFlyFileContent extends JDomHtmlFlyFile
{
	var $fallback = null;		//Used for default

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
	}


	function build()
	{
		if (!$this->dataValue)
			return;
		
		$helperClass = $this->componentHelper;		
		if (!$helperClass)
			return;

		$filePath = $this->dataValue;
		if(!method_exists($helperClass , 'getDirectory'))
			$filePath = $helperClass::getDirectory($this->dataValue);

		$content = file_get_contents(JPATH_SITE .DS. $filePath);
		
		if (!$content)
			return;


        return $content;
	}
}