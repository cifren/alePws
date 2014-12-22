/*
* @project		tags manager jQuery plugin
* @version		0.0.1
* @package		jForms
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

;(function( $ ) {	
    var methods = {
        init : function(options){

			return this.each(function(){					
				var self = this;
				var $this = $(this);
				var opts = $this.data('tgMan');
 
				if(typeof(opts) == 'undefined') {
					var thisId = $this.attr('id');
					
					var defaults = {
						capitalizeFirstLetter: ($this.attr('data-capitalize-first-letter') == 'false') ? false : true,
						max: ($this.attr('data-max') > 0) ? $this.attr('data-max') : 0,
						separator: ($this.attr('data-separator') != '') ? $this.attr('data-separator') : ',',
						typeahead: true,
						sourceTagsClass: 'tgMan',
						typeaheadSourceFunction: function(query, process){ return jQuery('#'+ $this.attr('id')).tagsManagerByGiro('sourceTags') },
						validation: function(tag){ return true; },
						fakeInputId: 'FAKE-' + thisId
					};
					opts = $.extend({}, defaults, options);
 
					$this.data('tgMan', opts);
				} else {
					opts = $.extend({}, opts, options);
				}
 				
				if($this.length == 0 || !$this.is('input[type="text"], textarea')){
					return $this;
				}
				
				/* hide original input field */
				$this.hide().addClass('tgMan');
				
				/* check new FAKE input id */
				if(opts.fakeInputId == 'FAKE-' || opts.fakeInputId == '' || $('#'+ opts.fakeInputId).length > 1){
					var randomId = _generateRandom();
					while($('#FAKE-'+ randomId).length > 0){
						randomId = _generateRandom();
					}
					opts.fakeInputId = 'FAKE-'+ randomId;
				}

				var FAKEinput;
				
				/* tgMan already has been actived on input */
				if($this.next().hasClass('tgMan-fake-input')){
					/* check it has ID */
					var nextId = $this.next().attr('id');
					if(nextId == ''){
						$this.next().attr('id',opts.fakeInputId);
					} else {
						opts.fakeInputId = nextId;
					}
					
					FAKEinput = $this.next();
				} else {
					/* add a FAKE input */
					var FAKEinput = jQuery('<input type="text" id="'+ opts.fakeInputId +'" class="tgMan-fake-input"/>');
					$this.after(FAKEinput);
				}
				
				if($this.prev().hasClass('tags-container')){
					// empty the tags container
					$this.prev().html('');
				} else {
					/* add a TAGS container */
					$this.before('<span class="tags-container"></span>');
				}
				
				/* get current input tags */
				var inputTags = $this.val();
				inputTags = inputTags.split(opts.separator);

				var tagsList = new Array();
				$this.data('tgMan-tagsList', tagsList);
				
				$.each(inputTags, function(index,value){
					methods.addTag.call(self,value);
				});
				
				tagsList = $this.data('tgMan-tagsList');
				
				/* rebuild the input value with the cleaned tags */
				var newTagsString = tagsList.join(opts.separator);
				$this.val(newTagsString).attr('value',newTagsString);

				if(opts.typeahead){
					var inputTypeahead = FAKEinput.typeahead({
							source: opts.typeaheadSourceFunction,
							updater: function (item) {
								methods.addTag.call(self,item);
								return;
							}							
						});
				}
				
				FAKEinput.on('keydown', function (e) {
					var inputVal = FAKEinput.val();
					
					switch(e.which){
						case 13: /* enter */
						case 9: /* tab */
							if(!(FAKEinput.next().hasClass('typeahead') && FAKEinput.next().is(':visible')) || !opts.typeahead){
								e.preventDefault();
								if(inputVal != ''){
									methods.addTag.call(self,inputVal);
								}
							}
						break;		
					}
				});
				
				FAKEinput.on('blur', function () {
					var inputVal = FAKEinput.val();
					
					if(!(FAKEinput.next().hasClass('typeahead') && FAKEinput.next().is(':visible')) || !opts.typeahead){
						if (inputVal != '') {
							methods.addTag.call(self,inputVal);
						}
					}
				});
			});
        },
        addTag : function (rawtag){
			var self = this;
			var $this = $(this);
			opts = $this.data('tgMan');
			
			var splitted = [];
			if(typeof rawtag == 'string'){
				splitted = rawtag.split(opts.separator);
			} else if(typeof rawtag == 'array'){
				splitted = rawtag;
			}
			
			if(splitted.length > 1){
				$.each(splitted,function(i,v){
					methods.addTag.call(self,v);
				});
				return;
			}
			
			var tag = $.trim(rawtag);
			
			tagsList = $this.data('tgMan-tagsList');

			/* check max number of tags */
			if (opts.max > 0 && tagsList.length >= opts.max) return;
			
			/* tag exists */
			if (!tag || tag.length <= 0) return;
						
			if (opts.capitalizeFirstLetter) {
				tag = tag.charAt(0).toUpperCase() + tag.slice(1).toLowerCase();
			}

			fakeInput = $this.parent().find('#'+ opts.fakeInputId);
			/* tag is not already in the list */
			if($.inArray(tag,tagsList) >= 0){
				fakeInput.val('').attr('value','');
				return;
			}
			
			/* validate the tag */
			if (typeof opts.validation == 'function' && !opts.validation(tag)){	return; };
			
			var escaped = $("<span></span>").text(tag).html();
			var html = '<span tag="'+ escaped +'" class="tm-tag">';
			html += '<span>' + escaped + '</span>';
			html += '<a href="#" onclick="jQuery(this).closest(\'.tags-container\').parent().find(\'#'+ $this.attr('id') +'\').tagsManagerByGiro(\'removeTag\',\''+ escaped +'\'); return false;" class="tm-tag-remove">&times;</a></span>';
			var $el = $(html);
			$this.prev().append($el);
			
			/* add tag to the tagsList */
			tagsList.push(tag);
			
			/* checkmax */
			if(opts.max > 0 && tagsList.length >= opts.max){
				fakeInput.hide();
			}
			
			/* remove the tag from the FAKE input */
			fakeInput.val('').attr('value','');
			
			var newTagsString = tagsList.join(opts.separator);
			$this.val(newTagsString).attr('value',newTagsString).trigger('keyup');
			$this.data('tgMan-tagsList', tagsList);
		},
		removeTag : function (tag){
			var self = this;
			var $this = $(this);
			opts = $this.data('tgMan');
			
			var splitted = [];
			if(typeof tag == 'string'){
				splitted = tag.split(opts.separator);
			} else if(typeof tag == 'array'){
				splitted = tag;
			}
			
			if(splitted.length > 1){
				$.each(splitted,function(i,v){
					methods.removeTag.call(self,v);
				});
				return;
			}
			
			tagsList = $this.data('tgMan-tagsList');
			/* get last tags if none is provided */
			if(typeof tag == 'undefined' || tag.length <= 0){
				var tag = tagsList[tagsList.length-1];
			}
			
			if(typeof tag == 'undefined' || tag.length <= 0){
				return;
			}
			
			/* remove tag span */
			$this.prev().find('[tag="'+ tag +'"]').remove();
			
			/* remove tag from list and update input field */			
			tagsList.splice( $.inArray(tag, tagsList), 1 );
			
			fakeInput = $this.parent().find('#'+ opts.fakeInputId);
			/* checkmax */
			if(opts.max > 0 && tagsList.length < opts.max){
				fakeInput.show();
			}			
			
			var newTagsString = tagsList.join(opts.separator);
			$this.val(newTagsString).attr('value',newTagsString).trigger('keyup');
			$this.data('tgMan-tagsList', tagsList);			
		},
		tagsList: function(){
			var tags = $(this).data('tgMan-tagsList');
			return tags;
		},
		sourceTags: function(tagsClass){
			var self = this;
			var $this = $(this);
			opts = $this.data('tgMan');
			
			if(typeof tagsClass == 'undefined'){
				tagsClass = opts.sourceTagsClass;
			}
			var inputTags = [];
			jQuery('body').find('.'+ tagsClass).each(function(){
				var thisTags = $(this).tagsManagerByGiro('tagsList');
				
				if(typeof thisTags != 'undefined' && thisTags.length > 0){
					inputTags = $.merge(inputTags, thisTags);
				}
			});
			
			var tags = _unique(inputTags);
			return (tags.length > 0) ? tags : null;
		}
	}



    $.fn.tagsManagerByGiro = function(method_or_options) {
        if ( methods[method_or_options] ) {
            return methods[ method_or_options ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method_or_options === 'object' || ! method_or_options ) {
            // Default to "init"
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method_or_options );
        }
    };

/* utility functions */	
	function _generateRandom(){
		var randomId, alfabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		for (var i = 0; i < 5; i++){
			randomId += alfabet.charAt(Math.floor(Math.random() * alfabet.length));
		}

		return randomId;
	}
	
	function _unique(list) {
		var result = [];
		$.each(list, function(i, e) {
			if ($.inArray(e, result) == -1) result.push(e);
		});
		return result;
	}	
}( jQuery ));