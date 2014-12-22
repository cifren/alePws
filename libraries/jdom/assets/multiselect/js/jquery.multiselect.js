/*
* @name			Multiselect jQuery plugin
* @version		0.0.1
* @package		jForms
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "multiselectByGiro",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		direction: 'vertical',  // possible values 'vertical' - 'horizontal',
		allowCustomValues: false,
		hideSelect: false,
		
		// callbacks
		onInit: false,
		onSelect: false,
		onRemove: false
	};

	var trigger = function(event, callback) {
		// for external use
		$(document).trigger(event);

		// for internal use
		this.options.$events.trigger(event);

		if ($.isFunction(callback)) {
			callback.call(this.element);
		}
	},
	
	addHTML = function(){
		var html = this.options.multi.html();
		this.options.single = $('<select>'+ html +'</select>');
		this.options.single.addClass(this.options.multi.attr('class')).addClass('select-many');
		
		if(this.options.direction == 'vertical'){
			var width = parseInt(this.options.multi.outerWidth());
		}
		var style = '';
		if(width > 0){
			style = 'style="max-width:'+ width +'px;"';
		}
		
		
		this.options.container = $('<span class="multiselect-container"/>');
		this.options.container.append(this.options.single);	

		if(this.options.allowCustomValues){
			this.options.inputCustom = $('<input autocomplete="off" type="text" size="10" value="" class="multiselect_custom_values"/>');
			this.options.container.append(this.options.inputCustom);
		}
		if(this.options.direction == 'vertical'){
			this.options.container.append('<br />');
		}
		
		this.options.list = $('<ul '+ style +' class="select-many-selected"></ul>');
		this.options.container.append(this.options.list);
		
		this.options.multi.after(this.options.container);
	},
	
	selectOption = function (option, TRIG_ELEMENT){
		if (!option.attr('value') || option.attr('value').trim() == '') return;		
		var item = $('<li></li>').text(option.text() +' '),
		val  = option.val(),
		TRIG_ELEMENT = typeof TRIG_ELEMENT != 'undefined' ? TRIG_ELEMENT : true;

		$.data(item[0], 'value', val);
		this.options.list.append(item);

		this.options.blank.attr('selected', false);
		var values = this.options.multi.val();

		if(typeof values == 'string') {
			values = [values];
		} else if(!$.isArray(values)){
			values = [];
		}
		
		values.push(val);
		if(TRIG_ELEMENT){
			this.options.multi.val(values).trigger('change');
		}
		
		this.options.single.find('option[value="' + val + '"]').attr('disabled', true).addClass('invalid').attr('selected', false).trigger('change');
		
		var id = (this.id != '') ? '_'+this.id : '';
		trigger.call(this,this.options.event_selected + id, this.options.onSelect);
	},
	
	handleCustomValue = function(e){
		var newVal = this.options.inputCustom.val();
		
		if(typeof newVal != 'undefined'){
			newVal = newVal.trim();
		}
		
		if(newVal != ''){
			// add option and select it
			if(this.options.multi.find('option[value="'+ newVal +'"]').length == 0){
				this.options.multi.append('<option value="'+ newVal +'">'+ newVal +'</option>');
			}
			
			var optSelected = this.options.single.find('option[value="'+ newVal +'"]');
			if(optSelected.length == 0){
				this.options.single.append('<option selected="selected" value="'+ newVal +'">'+ newVal +'</option>');
			} else {
				if(typeof optSelected.attr('disabled') == 'undefined' || optSelected.attr('disabled') == '' || !optSelected.prop('disabled')){
					optSelected.prop('selected',true).attr('selected','selected');
				}
			}
						
			this.options.single.trigger('change');
			this.options.inputCustom.val('');
		}
	},
	
	handleSingleChange = function(e) {
		var selectedOpt = this.options.single.find('option:selected');
		selectOption.call(this,selectedOpt);
	},
	
	handleItemClick = function(e) {
		var item = e.target;
		var val  = $.data(item, 'value');

		$(item).remove();
		var values = this.options.multi.val();
		if(typeof values == 'string') {
			values = [values];
		} else if(!$.isArray(values)){
			values = [];
		}
		values.splice(values.indexOf(val),1);
		if(values.length <= 0){
			this.options.multi.val('').trigger('change');
		} else {
			this.options.multi.val(values).trigger('change');
		}

		this.options.single.find('option[value="' + val + '"]').attr('disabled', false).removeClass('invalid');

		if (this.options.multi.find('option:selected').size() == 0) {
			this.options.blank.attr('selected', true);
		}
		
		var id = (this.id != '') ? '_'+this.id : '';
		trigger.call(this,this.options.event_removed + id, this.options.onRemove);		
	};

	
    // The actual plugin constructor
    var Plugin = function ( element ) {
        /*
         * Plugin instantiation
         */		
		
		this.others_opts = {
			// Events
			event_initialized: pluginName +'_initialized',
			event_removed: pluginName +'_removed',
			event_selected: pluginName +'_selected',
			
			// Variables for cached values or use across multiple functions
			single: null,
			multi: null,
			container: null,
			inputCustom: false,
			initialized: false,
			
			// Cached jQuery Object Variables
			$events: $('<a/>')		
		}
		
		this.options = $.extend( {}, defaults);
    };

    Plugin.prototype = {
        init: function ( options ) {
			var that = this;
			if(this.options.initialized){
				return;
			}

			// detect we have options in the element itself
			var elementOpts = this.element.data();
		
			$.extend( this.options, elementOpts, options, this.others_opts);

			this.id = this.element.attr('id');
			if(!this.element.is('select')){
				$.each(this.element.find('select'),function(){
					that.init(options);
				});
			}
			
			this.options.multi = this.element;
			this.options.multi.hide();
			
			this.options.blank = this.options.multi.find('option:first-child');

			// ensure that first option in the select is a blank one
			if (this.options.blank.length == 0 || this.options.blank.attr('value').trim() != '') {
				this.options.blank = $('<option value=""></option>');
				this.options.multi.prepend(this.options.blank);
			}
			this.options.blank.attr('selected', true);
						
			addHTML.call(that);
			
			// handle pre-selected options
			this.options.multi.find('option:selected').each(function() {
				selectOption.call(that,$(this));
			});

			// create observers
			var that = this;
			this.options.single.on('change',function(e){
				e.stopPropagation();
				handleSingleChange.call(that,e);
			});
			this.options.multi.on('change',function(e){
				e.stopPropagation();
				that.refresh();
			});
			this.options.list.on('click','li',function(e){
				e.stopPropagation();
				handleItemClick.call(that,e);
			});
			
			if(this.options.inputCustom){
				this.options.inputCustom.on('keypress',function(e){
					var keyCode = e.keyCode || e.which;
					switch(keyCode){
						case 13: /* enter */
						case 9: /* tab */
							e.preventDefault();
							handleCustomValue.call(that,e);
							break;
					}
				});
			}

			if(this.options.hideSelect){
				this.options.single.hide();
			}
			
			// initialization completed
			this.options.initialized = true;
			var id = (this.id != '') ? '_'+this.id : '';
			trigger.call(this,this.options.event_initialized + id, this.options.onInit);
        },
		
		refresh: function(){
			var that = this;
			this.options.list.html('');
			this.options.single.html(this.options.multi.html())
				.find('option[selected]')
				.attr('selected',false)
				.removeClass('invalid')
				.attr('disabled', false);
				
			this.options.multi.find('option:selected').each(function(){
				selectOption.call(that,$(this), false);
			});
		},		
		
        destroy: function () {
				this.options.initialized = false;
				this.options.container.remove();
				this.options.multi.show();
				
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

		// filter only select elements
		this.filter('select');
		
		if(this.length == 0){
			return this;
		}
		
		this.each(function(i){
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

				// call the method
				return instance[arg].apply( instance, args );

			} else {

				$.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);

			}		
		});
    };

}(jQuery, window, document));