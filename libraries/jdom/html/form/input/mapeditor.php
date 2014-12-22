<?php
/*
* @version		0.3.8
* @package		jForms
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomHtmlFormInputMapeditor extends JDomHtmlFormInput
{
	var $level = 4;			//Namespace position
	var $last = true;		//This class is last call

	var $cols;
	var $rows;
	var $languages;

	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 * 	@dataKey	: database field name
	 * 	@dataObject	: complete object row (stdClass or Array)
	 * 	@dataValue	: value  default = dataObject->dataKey
	 * 	@domID		: HTML id (DOM)  default=dataKey
	 *
	 *
	 * 	@cols		: Textarea width (in caracters)
	 * 	@rows		: Textarea height (in caracters)
	 * 	@domClass	: CSS class
	 * 	@selectors	: raw selectors (Array) ie: javascript events
	 */
	function __construct($args)
	{

		parent::__construct($args);

		$this->arg('cols'		, 6, $args, '32');
		$this->arg('rows'		, 7, $args, '4');
		$this->arg('domClass'	, 8, $args);
		$this->arg('selectors'	, 9, $args);

		JDom::_('framework.jquery.gmapeditor');
		JHtml::_('behavior.modal');
		
		$db = JFactory::getDBO();
		$sql = 'SELECT * FROM #__languages WHERE published = 1';
		$db->setQuery(  $sql );
		$this->languages = $db->loadObjectList();		
	}

	function build()
	{
		$js_vars = array();
		foreach($this->languages as $lang){
			$js_vars[] = "{name:'". $lang->title ."', code:'". strtolower(str_replace('-','', $lang->lang_code)) ."'}";
		}

		$lang = JFactory::getLanguage();				
		$lang_tag = strtolower(str_replace('-','', $lang->getTag()));
		
		$html = '<div style="width: 784px; height: 508px;" id="<%DOM_ID%>_map" target="<%DOM_ID%>" class="gmap_container span8"></div>'
			.	'<div id="<%DOM_ID%>_form" style="max-height:508px; overflow: auto;" class="gmap_form_container span4"></div>'
			.	'<div class="span4"><span class="btn btn-primary" onclick="clearMap(\'<%DOM_ID%>_map\'); return false;">Clear</span>'
			.	'<br /><textarea id="<%DOM_ID%>" name="<%INPUT_NAME%>"<%STYLE%><%CLASS%><%SELECTORS%>'
			.	' cols="' . $this->cols . '"'
			.	' rows="' . $this->rows . '"'
			.	'>'
			.	'<%VALUE%>'
			.	'</textarea>' .LN
			.	'<%VALIDOR_ICON%>'.LN
			.	'<%MESSAGE%></div>';

		$html .= '
<script type="text/javascript">
function clearMap(mapId){
	jQuery("#"+ mapId).gmapEditorByGiro("clear");
}

jQuery(document).ready(function(){
	// Basic options for our map
	var myOptions = {
		center: new google.maps.LatLng(0, 0),
		zoom: 2,
		minZoom: 2,
		streetViewControl: false,
		mapTypeControl: false,
		mapTypeControlOptions: {
			mapTypeIds: ["custom"]
		}
	};

	var baseURL = "'. JURI::root() .'";
	
	// Init the map and hook our custom map type to it
	var map = new google.maps.Map(document.getElementById("<%DOM_ID%>_map"), myOptions);
	map.mapTypes.set("custom", customMapType);
	map.setMapTypeId("custom");
	
	window["<%DOM_ID%>_map"] = map;
	
	var mapSaved = {};
	var data = jQuery("#<%DOM_ID%>").val();
    try {
		mapSaved = JSON.parse(data);
    } catch (e) {
        mapSaved.objects = [];
    }

    jQuery("#<%DOM_ID%>_map").gmapEditorByGiro({
		baseURL: baseURL,
		languages:['. implode(',', $js_vars) .'],
		current_lang:"'. $lang_tag .'",
		mapObj: map,
		data:{			
			objects: mapSaved.objects
		}
	});
    jQuery("#<%DOM_ID%>_map").gmapEditorByGiro("edit",true);
		
	google.maps.event.addListener(map, "resize", function (e) {
		checkBounds(map);
	});

	google.maps.event.addListener(map, "zoom_changed", function (e) {
		checkBounds(map);
	});
	
	google.maps.event.addListener(map, "center_changed", function (e) {
		checkBounds(map);
	});	
});
</script>
';
		return $html;
	}


}