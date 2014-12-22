/*
 *  Project: timepickerByGiro jQuery plugin
 *  Description: a lightweight timepicker based on bootstrap
 *  Author: Girolamo Tomaselli http://bygiro.com
 *  Dual licensed under the MIT and GPL licenses.
 */

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "timepickerByGiro",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		time: '',
		
		step: 1,
		
		start: null,
		
		end: null,
		
		format: 'H:i',
		
		uiFormat: 'H:i',
		
		// callbacks
		onChange: false,
		onInit: false
	};

	var trigger = function(event, callback, data) {
		
		var evt = $.Event(event);

		if(!(data instanceof Array)){
			data = [data];
		}
		data.unshift(this);
		$(document).triggerHandler(evt, data);
		this.options.$element.triggerHandler(evt, data);
		
		if ($.isFunction(callback)) {
			callback.call(this, data);
		}
	},
	
	preg_quote = function(str, delimiter) {
	  //  discuss at: http://phpjs.org/functions/preg_quote/
	  // original by: booeyOH
	  // improved by: Ates Goral (http://magnetiq.com)
	  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // improved by: Brett Zamir (http://brett-zamir.me)
	  // bugfixed by: Onno Marsman
	  //   example 1: preg_quote("$40");
	  //   returns 1: '\\$40'
	  //   example 2: preg_quote("*RRRING* Hello?");
	  //   returns 2: '\\*RRRING\\* Hello\\?'
	  //   example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
	  //   returns 3: '\\\\\\.\\+\\*\\?\\[\\^\\]\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:'

	  return String(str)
		.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
	},
	
	addHTML = function(){
		var that = this,
			config = this.options,
			html = '';

		this.options.$parent = config.$element.parent();
		if(!this.options.$parent.hasClass('input-append') && !this.options.$parent.hasClass('input-prepend')){
			this.options.$parent = config.$element.wrap('<div class="timepicker_container"></div>').parent();
		} else {
			this.options.$parent.addClass('timepicker_container');
		}
		
		// create a fake input text
		var cloneInput = this.options.$element.clone(true);
		cloneInput.attr('id','').attr('name','').addClass('fakeInput').attr('readonly','readonly').show();		
		this.options.$parent.append(cloneInput);
		
		this.options.$fake = this.options.$parent.find('.fakeInput');

		// add the popover
		html = '<div class="timepicker_popover"><span class="btn-close-popover">&times;</span>'
			+		'<table class="table">'
			+			'<tbody>'
			+				'<tr>'
			+					'<td class="hours">'
			+						'<i class="picknext icomoon icon-chevron-up"></i>'
            +						'<span class="clearfix"></span>'
            +						'<input type="text" class="input_micro" readonly>'
            +						'<span class="clearfix"></span>'
            +						'<i class="pickprevious icomoon icon-chevron-down"></i>'
			+					'</td>'
			+					'<td class="time_separator">:</td>'
			+					'<td class="minutes">'
			+						'<i class="picknext icomoon icon-chevron-up"></i>'
            +						'<span class="clearfix"></span>'
			+						'<input type="text" class="input_micro" readonly>'
            +						'<span class="clearfix"></span>'
            +						'<i class="pickprevious icomoon icon-chevron-down"></i>'
			+					'</td>'
			+				'</tr>'
			+			'</tbody>'
			+		'</table>'
			+	'</div>';		
		
		this.options.$parent.append(html);
	},
	
	updatePopover = function(move){
		var that = this,
			config = this.options,
			current = this.options.$element.val(),
			hours,minutes;
			
		if(!current){						
			current = this.options.time;
		}
		
		// convert to dateObj
		current = strToDate(current , config.format);
				

		// check time is valid
		current = checkTime.call(this,current, move);		
		
		updatePopTime.call(this,current);
	},
	
	updateInput = function(move){
		var that = this,
			config = this.options,
			current,inputVal,visibleVal,hours,minutes;

		hours = config.$parent.find('.timepicker_popover .hours input').val();
		minutes = config.$parent.find('.timepicker_popover .minutes input').val();
		
		// convert to dateObj	
		current = getDateTimeObj(hours,minutes);

		// check time is valid
		current = checkTime.call(this,current, move);		
		
		// convert to str
		inputVal = dateToStr(current,config.format);
		visibleVal = dateToStr(current,config.uiFormat);
		
		config.$element.val(inputVal).trigger('keyup');
		config.$fake.val(visibleVal);
	},
	
	getDateTimeObj = function(hours, minutes){		
		hours = parseInt(hours);
		minutes = parseInt(minutes);
		
		var hoursIsNumber = isNumber(hours);
		var minutesIsNumber = isNumber(minutes);
		
		if(!hoursIsNumber || !minutesIsNumber){
			var now = new Date();
		}
		
		hours = hoursIsNumber ? hours : now.getHours();
		minutes = minutesIsNumber ? minutes : now.getMinutes();
		return {h:hours,m:minutes};
	},
	
	updatePopTime = function(dateObj){
		var that = this,
			config = this.options,hours,minutes;
			
		hours = addZero(dateObj.h);
		minutes = addZero(dateObj.m);
		
		config.$parent.find('.timepicker_popover .hours input').val(hours);
		config.$parent.find('.timepicker_popover .minutes input').val(minutes);	
	},
	
	checkTime = function(timeToCheck, move){
		var that = this,
			config = this.options;
			
		if(move != '+' && move != '-'){
			move = null;
		} else {
			move = (move == '+') ? '+' : '-';
		}
		
		if(!config.start && !config.end){
			return timeToCheck;
		}
		
		// check start H
		if(timeToCheck.h < config.start.h){
			timeToCheck.h = config.start.h;
			if(move == '-'){
				timeToCheck.h = config.end.h;
			}
		}
		
		// check start M
		if(timeToCheck.h == config.start.h){
			if(timeToCheck.m < config.start.m){
				timeToCheck.m = config.start.m;
			}
		}
		
		// check end H
		if(timeToCheck.h > config.end.h){
			timeToCheck.h = config.end.h;
			if(move == '+'){
				timeToCheck.h = config.start.h;
			}
		}
		
		// check end M
		if(timeToCheck.h == config.end.h){
			if(timeToCheck.m > config.end.m){
				timeToCheck.m = config.end.m;
			}
		}
		
		return timeToCheck;
	},
	
	move = function(type, direction){
		var that = this,
			config = this.options,
			type = (type == 'hours') ? type : 'minutes',
			thisInput = config.$parent.find('.'+ type +' input'),
			currVal = parseInt(thisInput.val()),
			direction = (direction == '+') ? '+' : '-',
			step = config.step,
			limit = 60,current,hours,minutes;
			
		currVal = (currVal >= 0) ? currVal : 0;
		
		if(type == 'hours'){
			step = 1;
			limit = 24;
		}
		
		if(direction == '-'){
			currVal -= step;
		} else {
			currVal += step;
		}
		
		// check 24hrs OR 60 mins
		if(currVal >= limit){
			currVal = 0;
		} else if(currVal < 0){
			currVal = limit - step;
		}
		
		thisInput.val(currVal);
		
		updateInput.call(this,direction);
		updatePopover.call(this,direction);
		trigger.call(this,this.options.event_changed, this.options.onChange,[]);
	},
	
	addZero = function(currVal){
		if(currVal.toString().length == 1){
			currVal = '0'+ currVal.toString();
		}
		
		return currVal;
	},
	
	isNumber = function(n){
	  return !isNaN(parseFloat(n)) && isFinite(n);
	},
	
	strToRegex = function(str){
		// create regex from format
		str = str.replace(/h/gi, '%1%');
		str = str.replace(/i/gi, '%1%');			
		str = preg_quote(str, '/');
		return new RegExp(str.replace(/%1%/g,'([0-9]+)'), "i");	
	},
	
	strToDate = function(str,format){
		var obj,regex,matches,hours,minutes;
		
		if(typeof str != 'string' && !str){
			return getDateTimeObj();
		}
		
		if(format == 'U' && isNumber(str)){ /* assuming this is epoch unix time (seconds) */
			str = parseInt(str);
			
			// minutes
			minutes = str / 60;
			hours = Math.floor(minutes / 60);
			minutes = minutes - (hours * 60);
			
			if(hours > 23){
				multiple = Math.floor(hours / 24);
				hours = hours - (multiple * 24);
			}
			obj = {h:hours,m:minutes};
		} else {
			regex = strToRegex(format);
			if(typeof str.match == 'function'){
				matches = str.match(regex);
			}

			hours = 0;
			minutes = 0;
			if(matches && matches.length > 0){
				hours = matches[1];
				minutes = matches[2];
			}
			obj = getDateTimeObj(hours,minutes);
		}
		
		return obj;
	},
	
	dateToStr = function(dateObj,format){	
		if(format == 'U'){
			format = (dateObj.h * 3600) + (dateObj.m * 60);
		} else {
			format = format.replace(/h/i,addZero(dateObj.h));
			format = format.replace(/i/i,addZero(dateObj.m));
		}
		
		return format;
	},
		
	removeEventsHandlers = function($elements){
      this.element.parent().find('.pickprevious, .picknext').off();
		this.element.parent().off();		
	},
	
	addEventsHandlers = function(){
		var that = this,
			config = this.options;
			
		config.$parent.on('click',function(e){
			e.preventDefault();
			e.stopPropagation();

			jQuery(this).addClass('open');
		});
		
		config.$parent.find('.timepicker_popover .btn-close-popover').on('click',function(e){
			e.preventDefault();
			e.stopPropagation();
			
			jQuery(this)
				.closest('.timepicker_container')
				.removeClass('open');
		});
		
		config.$parent.find('.pickprevious, .picknext').on('click',function(e){
			e.preventDefault();
			e.stopPropagation();
			
			var direction = (jQuery(this).hasClass('picknext')) ? '+' : '-';
			if(jQuery(this).parent().hasClass('hours')){
				move.call(that,'hours',direction);
			} else {
				move.call(that,'minutes',direction);
			}
		});		
	};
	
    // The actual plugin constructor
    var Plugin = function ( element ) {
        /*
         * Plugin instantiation
         */		
		
		this.others_opts = {
			// Events
			event_changed: pluginName +'_changed',
			event_initialized: pluginName +'_initialized',
			
			initialized: false,
			
			// just creating a jQuery object, just in case
			$element: $(element)	
		}
				
		this.options = $.extend( {}, defaults );
    };

    Plugin.prototype = {
        init: function ( options ) {
		
			if(this.options.initialized){
				return;
			}
			this.options.$element = $(this.element);
			
			if(!this.options.$element.is('input[type="text"]')){
				return;
			}
			
			var ele = this.options.$element;

			// get options from data attributes
			var eleOpts = {},
			data = ele.data();
			for(var i in this.options){
				if(typeof data[i] != 'undefined'){
					eleOpts[i] = data[i];
				}
			}

			this.options = $.extend({}, this.options, options, eleOpts, this.others_opts );
			
			var config = this.options;
			config.step = parseInt(config.step);
			config.step = (config.step > 0 && config.step < 59) ? config.step : 1;

			if(typeof config.start == 'string' && config.start){
				// convert to dateObj
				config.start = strToDate(config.start, config.format);
			}
			
			if(typeof config.end == 'string' && config.end){
				config.end = strToDate(config.end, config.format);
			}
		
			this.options = config;

			// hide the original input
			ele.hide();
			
			addHTML.call(this);
			addEventsHandlers.call(this);
			
			updatePopover.call(this);
			
			updateInput.call(this);
			
			// initialization completed
			this.options.initialized = true;			
			trigger.call(this,this.options.event_initialized, this.options.onInit,[]);
        },
		
		strToDate : function(str,format){		
			return strToDate(str,format);
		},
		
		dateToStr : function(dateObj,format){		
			return dateToStr(dateObj,format);
		},
		
		destroy: function () {
		
			this.element.parent().find('.timepicker_popover').remove();
			this.element.parent().find('.fakeInput').remove();
			
			if(this.element.parent().hasClass('input-prepend')){
				this.element.parent().removeClass('timepicker_container');
			} else {
				this.element.parent().replaceWith(this.element.parent().contents);
			}
			
			// remove handlers
			removeEventsHandlers.call(this);
			
            // unset Plugin data instance
            this.element.data( dataPlugin, null );
		}
    }

    /*
     * Plugin wrapper, preventing against multiple instantiations and
     * allowing any public function to be called via the jQuery plugin,
     * e.g. $(element).pluginName('functionName', arg1, arg2, ...)
     */
    $.fn[ pluginName ] = function ( arg ) {

        var args, instance;

		if(this.length <= 0 && typeof arg != 'string'){
			return this;
		}
		
		
        // only allow the plugin to be instantiated once
        if (!( this.data( dataPlugin ) instanceof Plugin )) {

            // if no instance, create one
            this.data( dataPlugin, new Plugin( this ) );
        }
		
        instance = this.data( dataPlugin );

		if(typeof instance != 'undefined'){
			instance.element = this;
		} else {
			instance = new Plugin( this );
		}

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

            // call the method
            return instance[arg].apply( instance, args );
        } else {
            $.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);
        }
    };

}(jQuery, window, document));

jQuery(document).ready(function(){
	jQuery('body').on('click', function(){
		jQuery(this).find('.timepicker_container.open').each(function(){
			jQuery(this).removeClass('open');		
		});		
	});
});
