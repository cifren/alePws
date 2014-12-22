/*
* A jQuery Geolocation API Extended
*
* @author: Girolamo Tomaselli
* @url: http://bygiro.com
* @email: http://girotomaselli@gmail.com
* based on a FORK of https://github.com/manuelbieh/jQuery-Geolocation
* @published: 27-10-2013
* @version 0.0.1
* @license MIT
*/
/*
$('#getPositionButton').bind('click', function() {
    $.geolocation.get({win: alertMyPosition, fail: noLocation});
});

$('#watchPositionButton').bind('click', function() {
    // alertMyPosition is called each time the user's position changes
    myPosition = $.geolocation.watch({win: alertMyPosition}); 
});

$('#stopButton').bind('click', function() {
    $.geolocation.stop(myPosition);
});
*/

(function($){
	$.extend({
		geolocation:  {
			received: false, // reference for timeout callback
			timedout: false,
			location_timeout: null,
			
			watchIDs: [],

			get: function(o) {
				o = jQuery.geolocation.prepareOptions(o);
				if(typeof o.message != 'undefined'){
					$(o.message).addClass($.geolocation.browser()).show();
				}
				
				$.geolocation.getCurrentPosition(o.successCallback, o.errorCallback, o.timedoutCallback, o.options);
			},

			watch: function(o) {
				o = jQuery.geolocation.prepareOptions(o);
				return $.geolocation.watchPosition(o.successCallback, o.errorCallback, o.timedoutCallback, o.options);
			},

			stop: function(watchID) {
				if(typeof navigator.geolocation != 'undefined') {
					navigator.geolocation.clearWatch(watchID);
				}
			},

			getCurrentPosition: function(success, error, timedout, options) {				
				if(typeof navigator.geolocation != 'undefined') {
					$.geolocation.location_timeout = setTimeout(timedout,options.timeout);
					navigator.geolocation.getCurrentPosition(success, error, options);
				} else {
					errorcode = {NOT_SUPPORTED: true};
					error(errorcode);
				}
			},

			watchPosition: function(success, error, timedout, options) {
				if(typeof navigator.geolocation != 'undefined') {
					watchID = navigator.geolocation.watchPosition(success, error, options);
					$.geolocation.watchIDs.push(watchID);
					return watchID;
				} else {
					errorcode = {NOT_SUPPORTED: true};
					error(errorcode);
				}
			},

			clearWatch: function(watchID) {
				$.geolocation.stop(watchID);
			},

			prepareOptions: function(o) {				
				o.errorCallback = function(error){
						if(!error.NOT_SUPPORTED){
							$.geolocation.received = true;
						}
						clearTimeout($.geolocation.location_timeout);
						
						if(typeof o.message != 'undefined'){
							$(o.message).hide();
						}
						
						if(typeof o.error != 'function') {
							o.error = function(){}
						}
						o.error(error);
					}

				o.successCallback = function(position){
						$.geolocation.received = true;
						clearTimeout($.geolocation.location_timeout);
						
						if(typeof o.message != 'undefined'){
							$(o.message).hide();
						}
						
						if(typeof o.success != 'function') {
							o.success = function(){}
						}
						
						$.geolocation.position = position;
						o.success(position);
					}

				o.timedoutCallback = function(){
						$.geolocation.timedout = true;
						if(typeof o.message != 'undefined'){
							$(o.message).hide();
						}
							
						if(!$.geolocation.received){						
							if(typeof o.timedout != 'function') {
								o.timedout = function(){}
							}						
							o.timedout();
						}
					}
				
				if(typeof o.options == 'undefined' || o.options.length <= 0) {
					o.options = {
						highAccuracy: false,
						maximumAge: 30 * 1000, // 30 seconds
						timeout: 6 * 1000 // 30 seconds
					 }
				}

				return o;

			},
			
			stopAll: function() {
				$.each(jQuery.geolocation.watchIDs, function(key, value) {
					$.geolocation.stop(value);
				});
			},
			
			browser: function (){
				var N=navigator.appName, ua=navigator.userAgent, tem;
				var M=ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
				if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
				M=M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
				return M[0];
			}
		}
	});
})(jQuery);