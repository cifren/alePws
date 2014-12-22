/*
 *  Project: jQuery RadioToBtn
 *  Description: convert simple and ugly radio inputs into nice bootstrap radio groups (twitter bootstrap 2.3.2 required)
 *  Author: Girolamo Tomaselli http://bygiro.com
 *  License: GNU General Public License
 */

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "radioToBtn",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {	
		text: {
			
		},
		
	};

	var trigger = function(event, data) {
		
		var evt = $.Event(event),
			callback = this.options.events[event];

		if(!(data instanceof Array)){
			data = [data];
		}
		data.unshift(this);
		$(document).triggerHandler(evt, data);
		this.options.$element.triggerHandler(evt, data);
		
		if (typeof callback == 'function') {
			return callback.call(this, data);
		} else {
			return true;
		}
	},
	
	checkSelected = function(btn){
		var btnContainer = btn.parent();
		var mainContainer = btnContainer.parent();
		
		var input = mainContainer.find('input#' + btn.attr('for')),
		name = input.attr('name'),
		inputChecked = null;
		
		mainContainer
			.find('input[type="radio"][name="'+ name +'"]')
			.each(function(){
				// find the input checked
				if ($(this).prop('checked') || $(this).attr('checked')){
					inputChecked = $(this);
					return false;
				}
			});
		
		// remove the active status to all the btns
		btnContainer.find('span[for]').each(function(){
			$(this).removeClass('active');
			var activeClass = $(this).attr('data-active') || 'btn-success';
			$(this).removeClass(activeClass);
		});
		
		if(inputChecked){
			var id = inputChecked.attr('id'),
			label = mainContainer.find('label[for="'+ id +'"]'),
			btn = btnContainer.find('span[for="'+ id +'"]'),
			activeClass = btn.attr('data-active') || 'btn-success';
			
			inputChecked.trigger('click');
			label.trigger('click');
			btn.addClass('active '+ activeClass);		
		}
	
	};
	
    // The actual plugin constructor
    var Plugin = function ( element ) {
        /*
         * Plugin instantiation
         */
			
		this.options = $.extend( {}, defaults );
    };

	Plugin.prototype = {
		init: function ( options ) {
			$.extend( this.options, options);

			var firstEle = this.elements.first(),
			parent = firstEle.parent();
			
			// destroy previous instances
			this.destroy();
			
			// create the btn group HTML
			var newButtons = [],failed = false;
			this.elements.each(function(){
				var id = $(this).attr('id'),
					rel = $(this).attr('rel'),
					options = null,
					label = $(this).parent().find('label[for="'+ id +'"]');
				
				if(!id || label.length == 0){
					failed = true;
					return false;
				}
				
				var btn = {
					'for': id,
					text: label.html()
				};
				
				if (typeof(rel) != 'undefined'){
					var options = jQuery.parseJSON(rel);					
					$.extend(btn,options);
				}
				
				newButtons.push(btn);
			});
			
			if(failed) return;
			
			// buildHTML and prepend to the first input
			var html = '';

			for(var i=0;i<newButtons.length;i++){
				var btn = newButtons[i],
				activeClass = btn['active'] || '';
				if(activeClass == '' && btn['color']){
					activeClass = 'btn-'+ btn['color'];
				}
				html += '<span for="'+ btn['for'] +'" data-active="'+ activeClass +'" class="radioToBtn-btn btn '+ (btn['class'] || 'btn-default') +'">'+ btn['text'] +'</span>';
				
				// add class to input and label
				parent.find('#'+ btn['for'] +', label[for="'+ btn['for'] +'"]').addClass('radioToBtn-hidden');
			}
			
			html = '<span class="radioToBtn-btn-container btn-group">'+ html +'</span>';
			html = $(html);
		
			this.elements.first().before(html);
			// add events handlers
			html.on('click','span[for]', function() {
				var mainContainer = $(this).parent().parent();
				mainContainer.find('label[for="'+ $(this).attr('for') +'"]').trigger('click');
				
				checkSelected($(this));
			});
			
			checkSelected(html.find('span[for]').first());
		},
	
		destroy: function () {
			var firstEle = this.elements.first(),
			parent = firstEle.parent();
			
			// find btn containers AND remove
			parent.find('.radioToBtn-btn-container.btn-group .radioToBtn-btn').each(function(){
				var id = $(this).attr('for');
				parent.find('#'+ id +', label[for="'+ id +'"]').removeClass('radioToBtn-hidden');
			});
			parent.find('.radioToBtn-btn-container.btn-group').remove();
		}
    }

    /*
     * Plugin wrapper, preventing against multiple instantiations and
     * allowing any public function to be called via the jQuery plugin,
     * e.g. $(element).pluginName('functionName', arg1, arg2, ...)
     */
    $.fn[ pluginName ] = function ( arg ) {

      var args, instance;

		if(this.length <= 0){
			return this;
		}
		
		var target = this.find('input[type="radio"]');		
		
		// group elements based on NAME attribute
		var targetGroups = {};		
		target.each(function(){
			var name = $(this).attr('name');
			
			if(typeof targetGroups[name] == 'undefined'){
				targetGroups[name] = $();
			}
			targetGroups[name] = targetGroups[name].add($(this));
		});
		
		$.each(targetGroups,function(i,v){
			instance = new Plugin( this );
			
			instance.elements = this;
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
			
		return this;
    };

}(jQuery, window, document));