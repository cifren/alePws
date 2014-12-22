/*
* @name			MultiRecaptcha jQuery plugin
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
	
    var pluginName = "multirecaptchaByGiro",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		imagesSources: {
				clean: 'https://developers.google.com/recaptcha/images/reCAPTCHA_Sample_Clean.png',
				red: 'https://developers.google.com/recaptcha/images/reCAPTCHA_Sample_Red.png',
				white: 'https://developers.google.com/recaptcha/images/reCAPTCHA_Sample_White.png',
				blackglass: 'https://developers.google.com/recaptcha/images/reCAPTCHA_Sample_Black.png'
			},
		pubkey: '',
		theme: 'clean',
		extraValidators: {
			'data-prompt-position':'bottomLeft:-10,-18'
			},
		validateClass: 'validate[required] required',
		text: {
			genericerror: "Sorry, something went wrong. Try again!",
			btnTxt: "Click me!",
		},
		
		// callbacks
		onInit: false,
		onTaskA: false,
		onTaskB: false
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
		
	taskA = function(e) {		
		var item = e.target;

		
		var id = (this.id != '') ? '_'+this.id : '';
		trigger.call(this,this.options.event_taskA + id, this.options.onTaskA);		
	},
	
	taskB = function(e) {
		var item = e.target;

		
		var id = (this.id != '') ? '_'+this.id : '';
		trigger.call(this,this.options.event_taskB + id, this.options.onTaskB);		
	},
	
	generateRandom = function(){
		var randomId ='',alfabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		for (var i = 0; i < 7; i++){
			randomId += alfabet.charAt(Math.floor(Math.random() * alfabet.length));
		}

		return randomId;
	},
	
	checkUniqueId = function(obj){
		var eleId = obj.attr('id');
		if(typeof eleId == 'undefined' || eleId == '' || $('#'+ eleId).length > 0){
			do {
				eleId = generateRandom();
			} while($('#'+ eleId).length > 0);
			
			obj.attr('id',eleId);
		}
		
		return eleId;
	},
	
	enableRecaptcha = function(form){
		var that = this,
			obj = form.find('.multirecaptcha');

		if(obj.hasClass('captchaEnabled') || obj.find('#recaptcha_area').length > 0){
			obj.addClass('captchaEnabled');
			return true;
		}
		
		var id = checkUniqueId(obj),
			pubkey = (obj.attr('data-multirecaptcha-pubkey') || this.options.pubkey),
			theme = (obj.attr('data-multirecaptcha-theme') || this.options.theme);
		
		if(pubkey == ''){
			return false;
		}
		
		// remove anything inside the container
		//obj.empty();
		
		if(typeof Recaptcha == 'undefined') return false;
		
		Recaptcha.create( pubkey, id, {
									theme: theme,
									lang: this.options.lang,
									custom_translations: this.options.custom_translations,
									tabindex: 0
								}
		);
		
		var k=0,ok=false;
		do{
			setTimeout(function(){
				var captchaInit = obj.find('#recaptcha_area').length;

				if(captchaInit > 0){
					ok = true;
					
					var inputField = obj.addClass('captchaEnabled').find('#recaptcha_response_field');
					inputField.addClass(that.options.validateClass);
					
					$.each(that.options.extraValidators,function(i,v){
						inputField.attr(i,v);
					});
				}
				
			}, 4000);
				
			k++;
		}while(k<5 && !ok);		
		
		if(ok){
			return true;
		} else {
			return false;
		}		
	},
	
	resetAllRecaptchas = function(elements){
		var that = this,
			imgSources = that.options.imagesSources;
			
		if(!(elements instanceof jQuery)){
			elements = $('form');
		}
		
		elements.each(function(i){
			var ele = this;
			$(this).find('.multirecaptcha')
				.not(':last')
				.remove();

			var captchaEle = $(this).find('.multirecaptcha').last();

			if(captchaEle.length <= 0){
				return;
			}

			// add FAKE Recaptcha
			var theme = (captchaEle.attr('data-multirecaptcha-theme') || that.options.theme),
				src = imgSources['red'];
				
			if(typeof imgSources[theme] != 'undefined' && imgSources[theme] != ''){
				src = imgSources[theme];
			}
			
			var fakeImg = '<img src="'+ src +'" />';
			
			captchaEle.html(fakeImg).removeClass('captchaEnabled '+ that.options.validateClass);
		});
	},
	
	attachHandlers = function(){
		var that = this;
		
		// attach handlers
		$('form').on('mouseover', function(){
			// remove handler from THIS form
			resetAllRecaptchas.call(that, $('form'));
			$('form').off('mouseover');
			attachHandlers.call(that);
			$(this).off('mouseover');
			enableRecaptcha.call(that,$(this));
		});	
	};

	
    // The actual plugin constructor
    var Plugin = function ( element ) {
        /*
         * Plugin instantiation
         */		
		
		this.others_opts = {
			// Events
			event_initialized: pluginName +'_initialized',
			event_taskA: pluginName +'_add',
			event_taskB: pluginName +'_remove',
			
			// Variables for cached values or use across multiple functions
			captchaContainer: null,
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

			$.extend( this.options, options, this.others_opts);			
			
			// detect if we have many recaptcha instances
			// check if we have just ONE captcha in the page
			var instances = $('.multirecaptcha, #recaptcha_area');
			if(instances.length <= 1){
				// enable the plugin then EXIT
				if($('#recaptcha_area').length > 0){
					return;
				}
				
				enableRecaptcha.call(that,$('.multirecaptcha').closest('form'));
				
				return;
			}
			
			// detect main instantiated recaptcha and assign class .multirecaptcha
			var Main = $('#recaptcha_area'),
				MainParent = Main.parent();
			
			if(Main.length > 0){
				MainParent.addClass('multirecaptcha captchaEnabled');
				
				var tableCaptcha = Main.find('#recaptcha_table'),
					tClasses = ['white','clean','blackglass','red'];
				
				for(var k=0;k<4;k++){
					if(tableCaptcha.hasClass( 'recaptcha_theme_' + tClasses[k])){
						if(MainParent.attr('data-multirecaptcha-theme') == ''){
							MainParent.attr('data-multirecaptcha-theme',tClasses[k]);
						}
						
						if(MainParent.attr('data-multirecaptcha-pubkey') == ''){
							MainParent.attr('data-multirecaptcha-pubkey',this.options.pubkey);
						}						
						break;
					}
				}				
			}
			
			// fix BAD HTML duplicated IDs
			$('.multirecaptcha').each(function(index){
				checkUniqueId($(this));
			});
			
			// remove multirecaptcha instances and reset (per form)
			resetAllRecaptchas.call(that, $('form'));		
			attachHandlers.call(that,$('form'));
			
			// initialization completed
			this.options.initialized = true;
			var id = (this.id != '') ? '_'+this.id : '';
			trigger.call(this,this.options.event_initialized + id, this.options.onInit);
        },	
		
        destroy: function () {
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

        // only allow the plugin to be instantiated once
        if (!( this.data( dataPlugin ) instanceof Plugin )) {

            // if no instance, create one
            this.data( dataPlugin, new Plugin( this ) );
        }

        instance = this.data( dataPlugin );

        instance.element = this;

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