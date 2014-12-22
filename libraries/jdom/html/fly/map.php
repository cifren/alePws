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


class JDomHtmlFlyMap extends JDomHtmlFly
{

	var $width;
	var $height;
	var $domId;
	var $gmap3_options;
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@domId		: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 *
	 *
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('width'			, null, $args, "100%");
		$this->arg('height'			, null, $args, "300px");
		$this->arg('domId'			, null, $args, "jdom_map");
		$this->arg('gmap3_options'		, null, $args, array());
	}

	function build()
	{
		JDom::_('framework.jquery.gmap3');
		
		$this->addStyle('width', $this->width);
		$this->addStyle('height', $this->height);
		$this->addClass('map_container');

		$html = '<div id="'. $this->domId .'" <%STYLE%><%CLASS%><%SELECTORS%>></div>';
		
		$options = $this->gmap3_options;
		if(!isset($options['map'])){
			$options['map'] = array(
				'center' => array(46.578498,2.457275),
				'zoom' => 5
			);
		}

		$data = str_replace("'","\'",$this->escapeJsonString(json_encode($options)));
		$gmap3_opts = "JSON.parse('". $data ."')";
		
		$html .= '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#'. $this->domId .'").gmap3('. $gmap3_opts .');
		});
		</script>
		';
				
		return $html;
	}

}