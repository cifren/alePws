/*
 *  Project: jQuery Cloner
 *  Description: form cloner with unlimited levels
 *  Author: Girolamo Tomaselli http://bygiro.com
 *  License: GNU General Public License
 */

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "cloner",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,
	defaults = {
		group: '',
		
	   // the maximum number of cloned elements
		max: 999, //setting it to a high number, by default
		
		// a valid jQuery selector for the element to be cloned
		cloneThis: '.toclone',
		itemsContainer: '.items_container',
		
		// should the jQuery data with the object be cloned, as well
		dataClone: true,

		// should it be a deep clone
		deepClone: true,
		
		// a valid jQuery selector for the element that triggers cloning
		// must be a child of the cloneThis selector
		editButton: '.edit',
		editForm: '.editForm',
		
		// a valid jQuery selector for the element that triggers cloning
		// must be a child of the cloneThis selector
		cloneButton: '.clone',

		// a valid jQuery selector for the element that triggers removal
		// must be a child of the cloneThis selector
		deleteButton: '.delete',

		// the position the clone will be inserted
		// relative to the original element
		clonePosition: 'after', // or 'before'
				
		// ask confirmation on delete
		confirmationOnDelete: false,
		
		enableJSONfields: true,
		
		targetField: null,
		// data items
		data: [],

		// unique identifier for plugin configuration
		hash: false,
		
		text: {
			are_you_sure_to_delete: 'Are you sure to delete it?'
		},
		
		// callbacks
		onMaxReached: false,
		onBeforeDelete: false,
		onAfterDelete: false,
		onClone: false,
		onBeforeEdit: false,
		onAfterEdit: false,
		onSetClonerIds: false,
		onInit: false
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
	
	removeEventsHandlers = function($elements){
		var that = this,
			config = this.options;

        if(config.$element.find(config.editButton).length > 0){
            config.$element.off('click', config.editButton);
        }
		  
        if(config.$element.find(config.cloneButton).length > 0){
            config.$element.off('click', config.cloneButton);
        }
		
        if(config.$element.find(config.deleteButton).length > 0){
            config.$element.off('click', config.deleteButton);
        }
		
        if(config.$element.find('input,select,textarea').length > 0){
            config.$element.off('click change keyup', 'input,select,textarea');
        }
		
	},
	
	addEventsHandlers = function(){
		var that = this,
			config = this.options;
	
		config.$element.on('click',config.editButton,function(event){
			event.preventDefault();
			event.stopPropagation();
			
			that.edit($(this));
		});
		
		config.$element.on('click',config.cloneButton,function(event){
			event.preventDefault();
			event.stopPropagation();
			
			that.clone($(this));
		});
		
		var securityLock = config.$element.find('.security_lock_delete').first();
		config.$element.on('click',config.deleteButton,function(event){
			event.preventDefault();
			event.stopPropagation();
			
			// check security lock			
			var r = true;
			if(securityLock.prop('checked') || securityLock.is(':checked')){
				if(config.confirmationOnDelete){
					r = confirm(config.text.are_you_sure_to_delete);
				}
			}
			
			if(r){
				that.delete($(this));
			}
		});

		if(config.enableJSONfields && config.targetField){
			var updateJSONfield = function(withDelay){
				delay(function(){
					var data = that.getData(true);
					config.$element.parent().find(config.targetField).val(JSON.stringify(data));
					
				},withDelay);
			};
			
			config.$element.on('cloner_after_clone cloner_after_delete',function(e){
				updateJSONfield.call(this,50);
			});
			
			config.$element.on('change click keyup','input,select,textarea',function(e){			
				var that = this;
				var eleName = jQuery(this).attr('name'),
					eventType,tagName = jQuery(this).get(0).tagName,
					type = jQuery(this).attr('type');

				if(typeof type != 'undefined'){
					type = '_'+type;
				} else {
					type = '';
				}
				
				var eleType = tagName + type,
					eleType = eleType.toLowerCase(),
					withDelay = 20;
					
				switch(eleType){
					case 'select':
					case 'input_hidden':
					case 'input_file':
						// onchange
						eventType = 'change';
						break;
						
					case 'input_radio':
					case 'input_checkbox':
						// onclick
						eventType = 'click';			
						break;

					case 'input_text':
					case 'textarea':						
					default:
						// onkeyup
						eventType = 'keyup';
						withDelay = 200;
						break;
				}
				
				if(e.type != eventType){
					return;
				}
				
				var item = jQuery(that).closest('[data-cloner-id]');
				if(item.length > 0){
					setDataClonerFields(item);
				}
				
				updateJSONfield.call(that,withDelay);
			});
		}
		
	},

	delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	  };
	})(),
	
	setClonerIds = function(deep){
		var that = this,
			list_counter = 0,
			config = this.options,
			groupName = buildRootName(config.$element),
			items = config.$element.find(config.cloneThis),
			deep = (typeof deep != 'undefined' && deep) ? true : false;			
		
		items.each(function(i){
			$(this).attr('data-cloner-id',list_counter);
				
			// rebuildIndex
			$(this).replaceWith(rebuildIndex($(this),list_counter,groupName));

			if(deep){
				$(this).find('[data-cloner-hash]').each(function(){
					if(typeof $(this).data('plugin_cloner') == 'undefined'){
						return true;
					}
					
					$(this).cloner('setClonerIds');					
				});
			}
			list_counter++;
		});
		
		trigger.call(that,pluginName +'_set_cloner_ids');
	},	
	
	buildRootName = function($obj){
		var route = buildRoutePath($obj),
			rootName = route.shift();
		
		if(route.length > 0){
			rootName += '['+ route.join('][') +']';
		}
		
		return rootName;
	},
	
	setDataClonerFields = function($newIt, obj){
		var thisLevel = $newIt.parents('[data-cloner-hash]').length;
		
		if(!($newIt instanceof jQuery)){
			return;
		}
		
		if(typeof obj == 'undefined'){
			obj = getDataItem($newIt,false,true,true);
		}

		$newIt.find('[data-cloner-field]').each(function(){
			var field = $(this).attr('data-cloner-field'),
			level = $(this).parents('[data-cloner-hash]').length,
			val = obj[field] || '';
			
			if(obj[field] instanceof Array && $.isPlainObject(obj[field][0])){
				return true;
			}
			
			// check we have select list
			if(obj[field] instanceof Array){
				val = obj[field].join(', ');
			}			
			
			if(level != thisLevel){
				return true;
			}
			
			$(this).html(val);
		});	
	},
	
	rebuildIndex = function($newIt, list_counter, rootName, obj) {
		var thisLevel = $newIt.parents('[data-cloner-hash]').length;

		setDataClonerFields($newIt,obj);
		
		if(typeof list_counter == 'undefined' || !isNumber(list_counter)){
			var list_counter = parseInt($newIt.closest('[data-cloner-id]').attr('data-cloner-id'));
			if(!isNumber(list_counter)){
				list_counter = 0;
			}
		}
		var cloner_id = $newIt.attr('data-cloner-id');
		if(typeof cloner_id != 'undefined' && cloner_id != ''){
			$newIt.attr('data-cloner-id',list_counter);
		}
		
		// fix index / name / IDs
		$newIt.find('[id],[for],[href^=#],[name],[data-cloner-index]').each(function() {
			var level = $(this).parents('[data-cloner-hash]').length;
			
			if(level != thisLevel){
				return true;
			}
			
			var attrs = ['id','for','href','name','data-cloner-index'];
			for(var x=0;x<attrs.length;x++){
				var at = attrs[x];
				var id = $(this).attr(at);
				
				if(typeof id == 'undefined' || id == ''){
					continue;
				}
				
				// get original at
				var originalAt = $(this).attr('data-original-'+ at);
				
				switch (at){
					case 'id':
					case 'for':
					case 'href':
						var newId = originalAt;
						if(!originalAt){
							// save original ATT for future processing
							$(this).attr('data-original-'+ at,id);
							newId = id;
						}
							
						$(this).attr(at, newId +'_'+ list_counter);
					break;
					
					case 'name':
						if(typeof rootName != 'undefined' && rootName !== ''){
							var fName = '['+ getFieldName(id) +']';
							if(id.indexOf('[]') >= 0){
								fName = fName +'[]';
							}
							id = rootName +'['+ list_counter +']'+ fName;
						} else {						
							var matches = id.match(/^(.*)\[(\d)\](.*)/i);
							
							// if there was a number
							if (matches && matches.length === 4) {
								// just take the string part
								// add the new number to it
								id = matches[1] +'['+ list_counter +']'+ matches[3];
							}
						}
						
						$(this).attr(at,id);
						break;
					
					case 'data-cloner-index':
						$(this).attr(at,list_counter +1);
						$(this).html(list_counter +1);
						break;
				}
			}
		});

		return $newIt;
	},	
	
	executeTask = function($obj,task){
		var config,
			that,
			result = false,
			parent_cloner = $obj.closest('[data-cloner-hash]');
			
		if(typeof parent_cloner.data('plugin_cloner') == 'undefined'){
			return false;
		}
		
		that = parent_cloner.data('plugin_cloner');
		config = that.options;
				
		// detect element index
		var itemIndex = $obj.attr('data-item-index');
		if(!itemIndex){
			var item = $obj.closest(config.cloneThis);
			if(item.length > 0){
				itemIndex = config.$element.find(config.cloneThis).index(item);
			}
		}
		
		switch(task){
			case 'edit':
				// no need to trigger the 'change'
				edit.call(that,itemIndex);
				break;
				
			case 'clone':
				result = clone.call(that,itemIndex);
				break;
				
			case 'remove':
				result = remove.call(that,itemIndex);
				break;
		}
		
		return result;
	},
	
	createHash = function(){		
		var classes = this.options.$element.attr('class'),
			hash_string = classes + this.options.$element.attr('id') + this.options.cloneThis + this.options.editButton + this.options.cloneButton + this.options.deleteButton,
			hash = Math.abs(hash_string.hashCode());
			this.options.hash = hash;
			
		return hash;
	},

	isNumber = function(n){
	  return !isNaN(parseFloat(n)) && isFinite(n);
	},

	array_path_value = function (obj, path, value, task) {
		var path = (path instanceof Array) ? path : path.split('.'),
			parent = obj,
			task = (typeof task == 'undefined') ? 'get' : task;

		// clean path
		path = path.filter(function(n){ return (n != undefined && n != '') });
		
		for (var i = 0; i < path.length -1; i++) {			
			if(typeof parent[path[i]] == 'undefined'){
				if(task == 'set'){
					if(isNumber(path[i])){
						parent[path[i]] = [];
					} else {
						parent[path[i]] = {};
					}
				} else {
					return null;
				}
			}
			parent = parent[path[i]];
		}

		if(task == 'set'){			
			parent[path[path.length-1]] = value;
		} if(task == 'remove'){
			delete parent[path[path.length-1]];
		} else {
			return parent[path[path.length-1]];
		}
	},
	
	getFieldName = function(fieldName){
		if(fieldName.indexOf('[') >= 0){
			var groups = fieldName.split('['),
			fieldName = groups.pop().replace(']', '');
		
			if(typeof fieldName == 'undefined' || fieldName == ''){
				fieldName = groups.pop().replace(']', '');
			}
		}
		
		return fieldName;
	},

	buildRoutePath = function($obj){
	
		var routePath = [];
		
		$obj.parents('[data-cloner-group],[data-cloner-id]').add($obj).each(function(){
			var index = $(this).parents('[data-cloner-group],[data-cloner-id]').length,
				group = $(this).attr('data-cloner-group'),
				id = $(this).attr('data-cloner-id');
			
			if(typeof group != 'undefined' && group !== ''){
				routePath[index] = group;
			} else if(typeof id != 'undefined' && id !== ''){
				routePath[index] = id;
			}
		});

		return routePath;
	},

	getDataItem = function($obj,deep,clean,getLabel){
		var data;
		clean = (typeof clean != 'undefined') ? clean : true;
		getLabel = (typeof getLabel != 'undefined') ? getLabel : false;
			
		data = $obj.serializeObject(clean,getLabel);
		var root = buildRoutePath($obj);
		
		if(deep){
			$obj.find('[data-cloner-hash]').each(function(){
				var thisLevel = $(this).parents('[data-cloner-hash]').length,
					group = $(this).attr('data-cloner-group');
				
				if(typeof group == 'undefined' || group == ''){
					return true;
				}
				var route = buildRoutePath($(this));
				
				var thisData = [];
				$(this).find('[data-cloner-id]').each(function(){
					var level = $(this).parents('[data-cloner-hash]').length;

					if(level != (thisLevel +1)){
						return true;
					}
					var itData = getDataItem($(this),true);
					
					if($.isPlainObject(itData) && !$.isEmptyObject(itData)){
						thisData.push(itData);
					}
				});
				
				var root_str = root.join('.'),
					route_str = route.join('.');
					
				route = (route_str.indexOf(root_str) == 0) ? route_str.replace(root_str,'').split('.') : route;
				
				var task = 'set';
				if(thisData.length == 0){
					task = 'remove';
				}

				array_path_value(data, route, thisData, task);
			});
		}

		return data;
	},
	
	setFieldsValues = function($obj,it){		
		$obj.find('select,input,textarea').each(function(index){
			var $this = $(this),
				name = this.name,
				fieldName = getFieldName(name),
				eleType,
				val,
				tagName = $this.get(0).tagName,
				type = $this.attr('type');

			if(fieldName == ''){
				return true;
			}
			
			val = null;
			if(typeof it[fieldName] != 'undefined'){
				val = it[fieldName];
			}
			
			if(typeof it[fieldName] == "boolean"){
				val = 0;
				if(it[fieldName]){
					val = 1;
				}
			}			
			
			if(typeof type != 'undefined'){
				type = '_'+type;
			} else {
				type = '';
			}
			
			eleType = tagName + type;
			eleType = eleType.toLowerCase();

			switch(eleType){
				case 'input_radio':
				case 'input_checkbox':
					// if radio let's check we have a YES / NO field
					if(eleType == 'input_radio'){
						var otherRadios = $this.parent().find('[name="'+ name +'"]');
						var booleanVars = [0,1];
						if(otherRadios.length == 2){
							otherRadios.each(function(){
								var ind = booleanVars.indexOf(parseInt($(this).val()));
								if(ind >= 0){
									delete booleanVars[ind];
								}
							});
							if(booleanVars.length == 0){ // we have a YES / NO field
								if(!val){
									val = 0;
								}
							}
						}
					}
				
					var inputVal = $this.val();
					if((!(val instanceof Array) && val == inputVal) || ($.inArray(inputVal.toString(),val) >= 0) || (val === null && inputVal === 0)){
						$this.prop('checked', true).attr('checked','checked');
					} else {
						$this.prop('checked', false).removeAttr('checked');
					}
					break;

				
				default:
					$this.val(val);				
					break;
			}
		});
	},
	
	clone = function(itemIndex,task){ // accept as argument INDEX, an object, an array of objects
			
		var newItemsIndex = [],
			values = [],
			isClone = false,
			$toclone,
			$newclone,
			task = (typeof task != 'undefined') ? task : 'add',
			that = this,
			config =  this.options,
			items = config.$element.find(config.cloneThis),
			isValidItemIndex = isNumber(itemIndex);
		
		switch(true){				
			case ($.isPlainObject(itemIndex)):
				values = [itemIndex];
				itemIndex = null;
				break;
				
			case ($.isArray(itemIndex) && $.isPlainObject(itemIndex[0])):
				values = itemIndex;
				itemIndex = null;
				break;
				
			case isValidItemIndex:
				break;
				
			default:
				itemIndex = null;				
				break;
		}
		isValidItemIndex = isNumber(itemIndex);
		
		$toclone = items.first();
		var tmplContainer = this.options.$element.parent().find('.'+ this.options.hash);
		if(tmplContainer.length){
			if(tmplContainer.first().children().length){
				$toclone = tmplContainer.first().children();
			} else {
				$toclone = $(tmplContainer.first().html());
			}
		}
		
		if(isValidItemIndex){
			$toclone = items.eq(itemIndex);
		}

		// check if we've reached the maximum limit
		if (items.length >= config.max) {
			trigger.call(that,pluginName +'_max_reached', [$toclone]);
			return;
		}

		if(!trigger.call(that,pluginName +'_before_clone', [$toclone])) return;
		
		// clone it
		$newclone = $toclone.clone({
			withDataAndEvents: config.dataClone,
			deepWithDataAndEvents: config.deepClone
		});
		
		var cloneValues = getDataItem($toclone,true);
		
		if(values.length <= 0){
			values = [cloneValues];
			isClone = true;
		}
		
		var itemTmpl = $newclone.wrap('<div>').parent().html();
		
		values.reverse();
		var list_counter = 0;
		if(task != 'replace' && isValidItemIndex){
			list_counter = itemIndex +1;
		}
		
		$.each(values,function(i,obj){
			var isValidObj,$newIt = $(itemTmpl);
			
			isValidObj = ($.isPlainObject(obj) && !$.isEmptyObject(obj));
			
			if(isValidObj){
				setFieldsValues($newIt,obj);
			}
	
			// reformat the id attributes
			$newIt = rebuildIndex.call(that, $newIt, list_counter, obj);
			var position = 'after';
			if (config.clonePosition != 'after') {
				position = 'before';
			}
			
			if(isClone && isValidItemIndex){
				$toclone[position]($newIt);
			} else {			
				if(items.last().length == 1){
					items.last()[position]($newIt);
				} else {					
					config.$element.find(config.itemsContainer).prepend($newIt);
				}
			}
			
			var tmpIndex = config.$element.find(config.cloneThis).index($newIt);
			$newIt = config.$element.find(config.cloneThis).eq(tmpIndex);
			
			var root = buildRoutePath($newIt);
			
			// search nested cloner instances and set data-cloner-id
			$newIt.find('[data-cloner-hash]').each(function(i){
				var hash = $(this).attr('data-cloner-hash'),
					group = $(this).attr('data-cloner-group');
				if(typeof hash == 'undefined' || hash == ''){
					return true;
				}

				// get options for this cloner
				var clonerOpts = window.cloner[hash].options,
					routePath = [];
				
				if(group == ''){
					return true;
				};
				
				routePath = buildRoutePath($(this));

				var root_str = root.join('.'),
					route_str = routePath.join('.');

				routePath = (route_str.indexOf(root_str) == 0) ? route_str.replace(root_str,'').split('.') : routePath;

				// get data values
				var subValues = array_path_value(obj, routePath);

				if(subValues instanceof Array){
					clonerOpts.data = subValues;
				}
				
				// init sub cloner
				$(this).cloner(clonerOpts);
			});
		
			newItemsIndex.push(list_counter);
			list_counter++;
		});
		
		if(task == 'replace'){
			items.remove();
		}
		
		setClonerIds.call(that,true);
		
		var newItems = config.$element.find(config.cloneThis),
			first = newItemsIndex.shift(),
			last = newItemsIndex.pop();
		if(last > 0){
			last += 1;
		} else {
			last = newItems.length +1;
		}
		
		newItems.slice(first,last);
		trigger.call(that,pluginName +'_after_clone', [$toclone, newItems]);
		return newItems;
	},
	
	remove = function(itemIndex){
		var result = false;
		if(typeof itemIndex == 'undefined'){
			return true;
		}

		var that = this,
			config =  this.options,
			items = config.$element.find(config.cloneThis);

		if(!trigger.call(that,pluginName +'_before_delete', [items])) return;
		
		if($.isArray(itemIndex)){			
			if(items.length - itemIndex.length <= 1){
				items.slice(0,items.length).remove();
			} else {
				items.remove()
			}

		} else if(itemIndex !== ''){			
			items.eq(parseInt(itemIndex)).remove();
		} else {
			return false;
		}
		
		trigger.call(that,pluginName +'_after_delete', [itemIndex]);
		return true;
	},
	
	edit = function(itemIndex){
		
		var that = this,
			config =  this.options,
			items = config.$element.find(config.cloneThis),
			itemToEdit = null;
			
		if(typeof itemIndex != 'undefined' && itemIndex >= 0){
			itemToEdit = items.eq(parseInt(itemIndex));
		}
		
		if(typeof config.editingProcess[itemIndex] != 'undefined'){
			// remove editing
			if(itemToEdit){
				itemToEdit.find(config.editForm).hide();
			}
			
			delete config.editingProcess[itemIndex];
			
			trigger.call(that,pluginName +'_after_edit', [itemIndex]);
			return;
		}
		
		if(!trigger.call(that,pluginName +'_before_edit', [itemIndex])) return;
		
		
		if(!itemToEdit || itemToEdit.length == 0){
			// add new			
			var newItems = clone.call(that);
			return;
		}
		
		// edit
		itemToEdit.find(config.editForm).show();		
		
		config.editingProcess[itemIndex] = true;

	};

	$.fn.serializeObject = function(clean,getLabel)
	{
		var that = this,
			thisLevel = this.parents('[data-cloner-hash]').length,			
			clean = (typeof clean != 'undefined' && clean) ? true : false,
			a,o = {};
		a = this.find('input,textarea,select').filter(function(){
			var level = $(this).parents('[data-cloner-hash]').length;
			
			if(level != thisLevel){
				return false;
			}
			return true;
		}).serializeArray();		
		
		$.each(a, function(i,v) {
			var fieldName = this.name;
			if(clean){
				fieldName = getFieldName(fieldName);
			}
			
			var value = this.value;
			//get the label instead of the real value
			if(getLabel){
				var inputEle = that.find('[name="'+ this.name +'"]');
				
				if(inputEle.is('select')){
					// check it's multivalues
					if($.isArray(value)){
						for(var ov=0;ov<value.length;ov++){
							var inputEleOpt = inputEle.find('option[value="'+ value[ov] +'"]');
							if(inputEleOpt.length > 0){
								value[ov] = inputEleOpt.html();
							}
						}
					} else {
						value = inputEle.find('option[value="'+ value +'"]').html();
					}
					
				} else if(inputEle.first().is('[type="checkbox"],[type="radio"]')){
					if($.isArray(value)){
						for(var ov=0;ov<value.length;ov++){
							var id = inputEle.filter(function(){
									if($(this).is('[value="'+ value[ov] +'"]')) return true;
									return false;
								}).attr('id'),
								label = that.find('label[for="'+ id +'"]');
								
							if(id && label){
								value[ov] = label.html();
							}
						}
					} else {
						var id = inputEle.filter(function(){
									if($(this).is('[value="'+ value +'"]')) return true;
									return false;
								}).attr('id')
								,label = that.find('label[for="'+ id +'"]');
								
						if(id && label){
							value = label.html();
						}
					}
					
				}
			}
			
			if (o[fieldName] !== undefined){
				if (!o[fieldName].push) {
					o[fieldName] = [o[fieldName]];
				}
				o[fieldName].push(value || '');
			} else {
				o[fieldName] = value || '';
			}
		});
		return o;
	};

	String.prototype.hashCode = function() {
	  var hash = 0, i, chr, len;
	  if (this.length == 0) return hash;
	  for (i = 0, len = this.length; i < len; i++) {
		chr   = this.charCodeAt(i);
		hash  = ((hash << 5) - hash) + chr;
		hash |= 0; // Convert to 32bit integer
	  }
	  return hash;
	};
	
    // The actual plugin constructor
    var Plugin = function ( element ) {
        /*
         * Plugin instantiation
         */		
		var eventsMap = {};		
		eventsMap[pluginName +'_max_reached'] 		= 'onMaxReached';
		eventsMap[pluginName +'_set_cloner_ids'] 	= 'onSetClonerIds';
		eventsMap[pluginName +'_initialized'] 		= 'onInit';
		eventsMap[pluginName +'_before_edit'] 		= 'onBeforeEdit';			
		eventsMap[pluginName +'_after_edit'] 		= 'onAfterEdit';
		eventsMap[pluginName +'_before_clone'] 	= 'onBeforeClone';
		eventsMap[pluginName +'_after_clone'] 		= 'onAfterClone';
		eventsMap[pluginName +'_before_delete'] 	= 'onBeforeDelete';
		eventsMap[pluginName +'_after_delete'] 	= 'onAfterDelete';

		this.others_opts = {
			events: eventsMap,
			initialized: false,
			editingProcess: {},
			
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
			
			// get options from data attributes
			var eleOpts = {};
			var data = this.others_opts.$element.data();
			for(var i in this.options){
				if(typeof data[i] != 'undefined'){
					eleOpts[i] = data[i];
				}
			}
			
			// get current data
			if(typeof data.targetField != 'undefined' && $(data.targetField).val() != ''){
				try{
					eleOpts.data = JSON.parse($(data.targetField).val());
				} catch(err){
				
				}
			}
			
			$.extend( this.options, eleOpts, options, this.others_opts );
			
			if(!this.options.hash){
				createHash.call(this);
			}

			// set clone template
			if(!this.options.$element.parent().find('.'+ this.options.hash).length){
				var cloneTemplate = this.options.$element.find(this.options.cloneThis).first().clone(true).wrap('<div/>').parent().html();
				cloneTemplate = $('<script type="text/xml" class="'+ this.options.hash +'"/>').html(cloneTemplate);
				
				// let's move the clone in a safe place
				this.options.$element.before(cloneTemplate);
			}
			
			this.options.$element.addClass('cloner_container');
			this.options.$element.attr('data-cloner-hash',this.options.hash);
			
			if(this.options.group != ''){
				this.options.$element.attr('data-cloner-group',this.options.group);
			}
			
			// set global variables
			if(typeof window.cloner == 'undefined'){
				window.cloner = {};
			}
			if(typeof window.cloner[this.options.hash] == 'undefined'){
				window.cloner[this.options.hash] = {};
				window.cloner[this.options.hash].options = this.options;
			}
			
			setClonerIds.call(this);
			
			// add events handlers
			addEventsHandlers.call(this);
			
			if(this.options.enableJSONfields){
				// remove rows and add data from variable IF there are no other nested repeatable fields
				if(this.options.$element.find('.clonerContainer:not([data-cloner-hash])').not(this.options.$element).length == 0){
					this.options.$element.find(this.options.cloneThis).remove();
				}
			}
			
			// add data if any
			if(this.options.data.length > 0){
				var res = clone.call(this,this.options.data,'replace');
			}
			
			// initialization completed
			this.options.initialized = true;
			
			trigger.call(this,pluginName +'_initialized', []);
        },
		
		setData: function(data,task){
			var that = this;
				
			task = (typeof task == 'undefined') ? 'replace' : task;

			if(typeof data == 'undefined' || data.length <= 0){
				return;
			} else if(typeof data == 'string'){
				data = JSON.parse(data);
			}
			
			clone.call(that,data,task);
		},
		
		setClonerIds: function(deep){
			setClonerIds.call(this,deep);
		},
		
		getData: function(deep){
			var that,
				config = this.options,
				inputData = [];
			
			deep = (typeof deep != 'undefined' && deep) ? true : false;
			
			config.$element.find(this.options.cloneThis).each(function(){				
				var item = getDataItem($(this),deep);
				if($.isPlainObject(item) && !$.isEmptyObject(item)){
					inputData.push(item);
				}
			});

			return inputData;			
		},
		
		clone: function($obj){
			executeTask($obj,'clone');
		},
		
		edit: function($obj){
			executeTask($obj,'edit');
		},
		
		'delete': function($obj){
			executeTask($obj,'remove');
		},
		
        destroy: function () {
			// remove class and data-cloner-hash
			this.options.$element
				.removeClass('cloner_container')
				.removeAttr('data-cloner-hash')
				.removeAttr('data-cloner-group');
			
			// remove data-cloner-id from items
			this.options.$element.find(this.options.cloneThis).removeAttr('data-cloner-id');
			
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

		if(this.length <= 0){
			return this;
		}
		
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