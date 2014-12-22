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


class JDomHtmlFormInputAddresspicker extends JDomHtmlFormInput
{
	var $size;
	var $targets_prefix;
	var $cssSelectorTarget;
	var $mode;
	var $onOpenModal;
	var $onCloseModal;
	var $onSuccess;
	var $onViewportChanged;
	var $geolocation;
	var $latitude;
	var $longitude;
	var $zoom;
	var $mapType;
	var $markerType;
	var $mapOptions;
	var $vendorsMap;
	var $distanceWidget;
	var $distanceWidgetRadius;

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

		$this->arg('size'		, 6, $args, '32');	
		$this->arg('cssSelectorTarget', null, $args, '');		
		$this->arg('targets_prefix', null, $args, '');		
		$this->arg('mode'				, null, $args, 'modal');	
		$this->arg('distanceWidget', null, $args, false);
		$this->arg('distanceWidgetRadius', null, $args, 30000);
		$this->arg('onOpenModal'	, null, $args, 'false');		
		$this->arg('onCloseModal'	, null, $args, 'false');		
		$this->arg('onSuccess'		, null, $args, 'false');		
		$this->arg('onViewportChanged'		, null, $args, 'false');		
		$this->arg('geolocation'	, null, $args, true);
		$this->arg('latitude'		, null, $args, 51.751724);
		$this->arg('longitude'		, null, $args, -1.255284);
		$this->arg('zoom'				, null, $args, 2);
		$this->arg('mapType'			, null, $args, 'ROADMAP');
		$this->arg('markerType'		, null, $args, 'labeled');
		$this->arg('vendorsMap'		, null, $args, 'null');
		$this->arg('mapOptions'		, null, $args, '{mapTypeId: google.maps.MapTypeId.ROADMAP}');
	}
	
	function build()
	{
		$doc = JFactory::getDocument();

		JDom::_('framework.jquery.addresspicker');

		$inputId = $this->getInputId();
		
		if($this->geolocation){					
			JDom::_('framework.jquery.geolocation');
		}
		
		

		$script = '
		function addressPickerEnabler($obj){
			if(!($obj instanceof jQuery)){
				$obj = jQuery($obj);
			}
			
			$obj.find("input.ckaddresspicker").each(function(){
				var that = this,
				opts = (typeof window.jQuery_addressPickerByGiro != "undefined") ? window.jQuery_addressPickerByGiro : {};
				
				jQuery(this).addressPickerByGiro(opts);

				// check map is inside a tab and NOT visible
				if(!jQuery(this).is(":visible")){
					var parentWiz = jQuery(this).closest(".step-pane"),
					parentTab = jQuery(this).closest(".tab-pane"),
					target = [];
					
					var delay = (function(){
						var timer = 0;
						return function(callback, ms){
								clearTimeout (timer);
								timer = setTimeout(callback, ms);
							};
					})();
					
					var mapInitialized = false,
					reInitMap = function(e){
						delay(function(){
							var id = jQuery(that).attr("id"),
							mapContainer = jQuery("body").find( "#"+ id +"_map_canvas" );
							
							if(!mapContainer.is(":visible")) return;
							if(mapInitialized) return;
							
							jQuery(that).addressPickerByGiro("resizeMap");
							jQuery("body").find( "#"+ id +"_search" ).trigger("click");
							mapInitialized = true;
						},300);					
					};
					
					if(parentWiz.length > 0){
						// it is wizard step
						target = jQuery("body").find(\'.wizard ul.steps li.step[data-target="#\'+ parentWiz.attr("id") +\'"]\');
					} else if(parentTab.length > 0) {
						// it is tab
						target = jQuery("body").find(\'.nav.nav-tabs li a[href="#\'+ parentTab.attr("id") +\'"]\');
					}

					if(target.length > 0){
						target.on("shown", reInitMap);
					}
				}
				
			});
		}
		
		jQuery(document).ready(function(){				
			addressPickerEnabler("body");				
		});';
		
		$doc->addScriptDeclaration($script);
		
		if($this->geolocation){
			$script = '
			var gelocationXsession = false;
			jQuery(document).ready(function(){
				jQuery("#'. $this->dataKey .'").on("click",function(){
					if(!gelocationXsession && jQuery(this).val() == ""){
						jQuery.geolocation.get({
							success: function(position){
								jQuery("#'. $this->dataKey .'").addressPickerByGiro("geocodeLookup","latLng",position.coords.latitude +","+ position.coords.longitude);
							}
						});
						gelocationXsession = true;
					}
				});
			});';
		//	$doc->addScriptDeclaration($script);
		}

		$this->parameters = array(
			'mode',
			'distanceWidget',
			'distanceWidgetRadius',
			'targets_prefix',
			'cssSelectorTarget',
			'onOpenModal',
			'onCloseModal',
			'onSuccess',
			'onViewportChanged',
			'latitude',
			'longitude',
			'zoom',
			'mapType',
			'markerType',
			'mapOptions',
			'vendorsMap'
		);		

		foreach($this->parameters as $pa){
			if(!(!empty($this->$pa) OR $this->$pa === false)){
				continue;
			}
			$pieces = preg_split('/(?=[A-Z])/',$pa);

			$this->addSelector('data-'. implode('-',$pieces), $this->$pa);
		}
		
		$this->addClass('ckaddresspicker');
		
		$html =	'<input type="text" id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			.	' value="<%VALUE%>"'
			.	' size="' . $this->size . '"'
			.	'/>' .LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%>';
			
		return $html;
	}

}