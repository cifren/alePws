/*
 *  Project: jQuery Post Max Size plugin
 *  Description: it checks if the form  meets the Server side limits to post a form
 *  Author: Girolamo Tomaselli http://bygiro.com
 *  License: GNU General Public License
 */
 
// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "postMaxSize",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		max_input_vars: 1000, // usually it's 1000 - 0 (ZERO) to disable it
		post_max_size: 100000000, // in bytes. usually it's 108MB - 0 (ZERO) to disable it
		max_file_uploads: 20, // usually it's 20 - 0 (ZERO) to disable it
		upload_max_filesize: 100000000, // in bytes. usually it's 100MB - 0 (ZERO) to disable it
		message_1: 65, // the percentage to be reached to send the advice to the user
		message_2: 80, // the percentage to be reached to send the advice to the user
		message_3: 95, // the percentage to be reached to send the advice to the user
		
		text: {
			message_1: "You are reaching the max limit for this form to be processed, please save it, before to continue",
			message_2: "I strongly suggest you to save the form, before to continue your editing",
			message_3: "ATTENTION!! you may lose all the modifications on this form, you should immediately save the form!"
		},
		
		// callbacks
		onInit: false,
		onChecked: false,
		onMaxInputVarsReached: false,
		onMaxFileUploadsReached: false,
		onPostMaxSizeReached: false,
		onUploadMaxFilesizeReached: false,
	},
	
	trigger = function(event, data) {
		
		var evt = $.Event(event),
			callback = this.events[event];

		if(!(data instanceof Array)){
			data = [data];
		}
		data.unshift(this);
		$(document).triggerHandler(evt, data);
		this.element.triggerHandler(evt, data);
		
		if (typeof callback == 'function') {
			return callback.call(this, data);
		} else {
			return true;
		}
	},	
	
	removeEventsHandlers = function($elements){
		var that = this;

	},
	
	addEventsHandlers = function(){
		var that = this;
		
		that.element.find('input,select,textarea').on('keyup change click',function(event){
			var withDelay = 1200,
				doIt = false,
				eleType,
				self = $(this),
				tagName = self.get(0).tagName,
				type = self.attr('type');

			if(typeof type != 'undefined'){
				type = '_'+type;
			} else {
				type = '';
			}
			
			eleType = tagName + type;
			eleType = eleType.toLowerCase();
			
			switch(eleType){							
				case 'select':
				case 'input_hidden':
				case 'input_file':
					// onchange
					if(event.type == 'change'){
						doIt = true;
						withDelay = 50;
					}
					break;
					
				case 'input_radio':
				case 'input_checkbox':
					// onclick
					if(event.type == 'click'){
						doIt = true;
						withDelay = 50;
					}							
					break;

				case 'input_text':
				case 'textarea':						
				default:
					// onkeyup
					if(event.type == 'keyup'){
						doIt = true;
					}							
					break;
			}
		
			if(!doIt){
				return;
			}
			
			// check the form
			delay(
				function(){
					check.call(that);
				},
				withDelay
			);
		});
	},

	status = function(value,limit){
		var that = this,
		limits = {
			limit_1: (parseInt(that.options.message_1 * limit / 100) || 0),
			limit_2: (parseInt(that.options.message_2 * limit / 100) || 0),
			limit_3: (parseInt(that.options.message_3 * limit / 100) || 0)
		};
		
		for(var i=3;i>0;i--){
			if(limits['limit_'+ i] && value >= limits['limit_'+ i]){
				return i;
			}
		}
		
		return false;
	},
	
	check = function(){
		var that = this,
		error = false,message, vars = [],value,
		formElements = that.element.find('select,input,textarea'),
		clonerFormElements = $();
		that.element.find('[data-cloner-group]').each(function(){
			var target = jQuery(this).attr('data-target-field');
			if(target){
				clonerFormElements = clonerFormElements.add($(this).find('input,textarea,select').not($(target)));
			}
		});

		// do not count cloner inputs			
		formElements.not(clonerFormElements);
		
		var thingsToTest = ['max_input_vars','post_max_size','max_file_uploads','upload_max_filesize'];
		var formElementsArray = formElements.serializeArray();
		for(var t=0;t<thingsToTest.length;t++){
			var thing = thingsToTest[t];
			if(error || that.options[thing] <= 0) continue;
			
			switch(thing){
				case 'max_input_vars':
					value = formElementsArray.length;
					break;
					
				case 'post_max_size':
					value = getUTF8Length(JSON.stringify(formElementsArray));
					break;
					
				case 'max_file_uploads':
					value = formElements.filter(function(){
						if(!$(this).is("input[type='file']")) return false;				
						return this.value;
					}).length;
					break;
					
				case 'upload_max_filesize':
					value = 0;
					break;
			}
			
			if(value){
				error = status.call(that,value,that.options[thing]);
				vars = [thing,value,that.options[thing]];
			}
		}

		if(error){
			if(message != 'undefined'){
				message = typeof that.options.text['message_'+ error];
				if(message == 'function'){
					that.options.text['message_'+ error].apply(vars);
				} else if(message == 'string') {
					alert(that.options.text['message_'+ error]);
				}
			}
		}
		
		return !(error ? true : false);
	},
	
	getUTF8Length = function(string) {
		var utf8length = 0;
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utf8length++;
			} else if((c > 127) && (c < 2048)) {
				utf8length = utf8length+2;
			} else {
				utf8length = utf8length+3;
			}
		}
		return utf8length;
	},
	
	delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	  };
	})();
	
    // The actual plugin constructor
	var Plugin = function ( element ) {
		if(typeof element.length == 0){
			element = $('body');
		}
		this.element = element;		
		this.events = {};		
		this.events[pluginName +'_initialized'] = 'onInit';
		this.events[pluginName +'_checked'] = 'onChecked';
		this.events[pluginName +'_max_input_vars_reached'] = 'onMaxInputVarsReached';
		this.events[pluginName +'_max_file_uploads_reached'] = 'onMaxFileUploadsReached';
		this.events[pluginName +'_post_max_size_reached'] = 'onPostMaxSizeReached';
		this.events[pluginName +'_upload_max_filesize_reached'] = 'onUploadMaxFilesizeReached';
	};

	Plugin.prototype = {
		init: function (options) {
			// detect we have options in the element itself
			var elementOpts = this.element.data();		
			this.options = $.extend( defaults, elementOpts, options);		
		
			// add events handlers
			addEventsHandlers.call(this);
			
			// initialization completed	
			trigger.call(this,pluginName +'_initialized', []);
		},
		
		check: function(){
			var that = this;
			
			delay(
				function(){
					check.call(that);
				},
				300
			);
		},
		
		destroy: function () {			
			// remove handlers
			removeEventsHandlers.call(this);
		}
    }

	/*
	* Plugin wrapper, preventing against multiple instantiations and
	* allowing any public function to be called via the jQuery plugin,
	* e.g. $(element).pluginName('functionName', arg1, arg2, ...)
	*/
	$.fn[ pluginName ] = function ( arg ) {
		var args, instance;

		// only allow the plugin to be instantiated once
		if (!( $(this).data( dataPlugin ) instanceof Plugin )){

			// if no instance, create one
			$(this).data( dataPlugin, new Plugin( $(this) ) );
		}

		instance = $(this).data( dataPlugin );

		instance.element = $(this);
		
		// Is the first parameter an object (arg), or was omitted,
		// call Plugin.init( arg )
		if (typeof arg === 'undefined' || typeof arg === 'object') {

			if ( typeof instance['init'] === 'function' ) {
				instance.init( arg );
			}

		// checks that the requested public method exists
		} else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {

			// copy arguments & remove function name
			args = Array.prototype.slice.call( arguments, 1 );
			return instance[arg].apply( instance, args );
		} else {
			$.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);
		}
		
		return this;
	};

}(jQuery, window, document));