/*
 *  Project: jQuery condRules
 *  Description: it performs show/hide (and not only) task on DOM elements based on form input values
 *  Author: Girolamo Tomaselli http://bygiro.com
 *  License: GNU General Public License
 */

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";
	
    var pluginName = "condRules",
    // the name of using in .data()
	dataPlugin = "plugin_" + pluginName,

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
	
	addEventsHandlers = function(rulesGroups){
		var that = this;

		$.each(rulesGroups,function(tHash, rules){
			if($.isEmptyObject(rules)) return true;

			execRule.call(that,rules);
			
			var triggerEle = $();
			for(var i in rules){
				triggerEle = rules[i].trigger;
				break;
			}
			if(triggerEle.length == 0) return true;
			
			triggerEle.on('keyup change click',function(event){
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
				
				// check all values to see if we match the condition rule
				delay(
					function(){
						execRule.call(that,rules);
					},
					withDelay,
					tHash
				);
			});
		});		
	},

	execRule = function(rules,test,allRules){
		var that = this,
		test = (typeof test != 'undefined') ? test : false;
		if($.isEmptyObject(rules)) return;
		
		if(typeof rules.trigger != 'undefined' || typeof rules.target != 'undefined'){
			rules = {
				whatever: rules
			};
		}
		
		for(var r in rules){			
			var cond = false,
			rule = rules[r],
			targetEle = rule.target,
			triggerEle = rule.trigger,
			task = rule.task;
			
			triggerEle.each(function(){
				var eleType,
					self = $(this),
					tagName = self.get(0).tagName,
					type = self.attr('type');
					
				if(typeof type != 'undefined'){
					type = '_'+type;
				} else {
					type = '';
				}
				
				eleType = (tagName + type).toLowerCase();						

				var thatVal = self.val();
				if(typeof thatVal == 'undefined' || !thatVal || thatVal == ''){
					thatVal = [];
				}
				if(!(thatVal instanceof Array) && typeof thatVal == 'string'){
					thatVal = [thatVal];
				}
				var cond1 = true,
				cond2 = (thatVal.length > 0);
				
				if(eleType == 'input_radio' || eleType == 'input_checkbox'){
					cond1 = cond2 = (self.is(':checked') || self.prop('checked'));
				}
				
				if(!rule.values.length && thatVal.length == 0){
					cond = true;
				} else if(rule.values.length){					
					if(rule.values.indexOf('*') < 0){
						for(var i=0;i<thatVal.length;i++){
							if(rule.values.indexOf(thatVal[i]) >= 0){
								cond = cond1;
								break;
							}
						}
					} else {
						cond = cond2;
					}
				}
					
				if(cond){
					return false;
				}
			});
							
			// verify other rules on this target
			var otherRules = rule.target.data('condRulesData');			
			if(
				((rule.or && !cond) || (!rule.or && cond)) &&
				otherRules && 
				typeof otherRules[rule.task] != 'undefined' && 
				(typeof allRules == 'undefined' || allRules)
			){

				for(var c in otherRules[rule.task]){
					var oRule = otherRules[rule.task][c];
					if(oRule.hash == rule.hash) continue;
					
					cond = execRule.call(that,oRule,true,false);
					if(cond){
						if(rule.or){
							break;
						}
					} else {
						if(!rule.or){
							break;
						}
					}
				}
				
			}
			
			if(test) return cond;			

			// callback
			var eType = '_matched';
			if(!cond){
				eType = '_unmatched';
			}
			trigger.call(this,pluginName + eType, [rule]);

			if(that.defaultTasks.indexOf(rule.task) < 0){
				if(typeof $.fn[rule.task] == 'function'){
					// check it's a jQuery plugin
					$[rule.task](rule,cond);
				} else if(typeof window[rule.task] == 'function') {
					// check for global function
					window[rule.task](rule,cond);
				}
				return;
			}
			
			var targetInputs = targetEle;
			if(!targetEle.is('select,input,textarea')){
				targetInputs = targetEle.find('select,input,textarea');
			}

			var negativeTasks = ['hide','slideup','deselect'];
			if(negativeTasks.indexOf(rule.task) >= 0){
				cond = !cond;
			}
			
			var showHideTasks = ['show','hide','slidedown','slideup'],
			taskValues = false,
			selectAll;
			if($.isArray(rule.taskValues) && rule.taskValues.length){
				taskValues = rule.taskValues,
				selectAll = taskValues.indexOf('*') >= 0;
			}

			// task to form elements
			targetInputs.each(function(){
				var thisEle = $(this);
				if(showHideTasks.indexOf(rule.task) < 0){	// select / deselect
					if(!taskValues) return true;
					
					if(thisEle.is('select')){
						// if we have to select all the values
						if(selectAll){
							var newTaskValues = [];
							thisEle.find('options').each(function(){
								newTaskValues.push($(this).attr('value'));
							});
							taskValues = newTaskValues;
						}
					
					
						var currValues = thisEle.val(),
						isArray = $.isArray(currValues);
						
						if(!isArray){
							if(currValues != ''){
								currValues = [currValues];
							} else {
								currValues = [];
							}
						}
						
						currValues = $.map( currValues, function( val, i ) {
							if(taskValues.indexOf(val) >= 0){
								return;
							}
							return val;
						});
							
						if(cond == true){
							// select
							currValues = currValues.concat(taskValues);
						}
						
						thisEle.val(currValues).trigger('change');
					} else if(thisEle.is('input[type="checkbox"],input[type="radio"]')){
						// check the input value if it matches the taskValues
						if(!selectAll && taskValues.indexOf(thisEle.val()) < 0) return true;
						
						if(cond != true){ // do the opposite, because then we have the click
							thisEle.prop('checked',true).attr('checked','checked');
						} else {
							thisEle.prop('checked',false).removeAttr('checked');
						}

						thisEle.trigger('click');
						
					} else {
						// it maybe a simple input text
						if(cond == true){
							thisEle.val(taskValues.join(' '));
						} else {
							var newVal = thisEle.val();
							for(var v=0;v<taskValues.length;v++){
								newVal = newVal.replace(taskValues[v],'');
							}
							thisEle.val(newVal);
						}
					}
				} else {
					if(thisEle.hasClass('condRulesExcludeDisabled')) return true;
					
					// show / hide
					if(thisEle.is('select')){
						if(!taskValues){
							thisEle.prop('disabled',true).attr('disabled','disabled');
							if(cond == true){	// show
								thisEle.prop('disabled',false).removeAttr('disabled');
							}
						} else {
							// get options
							thisEle.find('option').each(function(){
								if(!selectAll && taskValues.indexOf($(this)) < 0) return true;
								
								$(this).prop('disabled',true).attr('disabled','disabled').addClass('invalid');
								if(cond == true){	// show
									$(this).prop('disabled',false).removeAttr('disabled').removeClass('invalid');
								}
							});
						}
					} else {
						if(
							taskValues
							&&
							thisEle.is('input[type="checkbox"],input[type="radio"]')
							&&
							(!selectAll && taskValues.indexOf(thisEle.val()) < 0)
						){
							// the input value does not matches the taskValues
							return true;						
						}
						
						if(cond == true){ // show
							thisEle.prop('disabled',false).removeAttr('disabled');
						} else {
							thisEle.prop('disabled',true).attr('disabled','disabled');
						}
					}			
				}
			});
			
			// for select / deselect no more task to do, all done.
			if(showHideTasks.indexOf(rule.task) < 0) continue;

			// do the task
			if(cond != true){
				if(rule.task == 'show' || rule.task == 'hide'){
					targetEle.hide();
				} else {
					targetEle.slideUp();
				}
			} else {
				if(rule.task == 'show' || rule.task == 'hide'){
					targetEle.show();
				} else {
					targetEle.slideDown();
				}			
			}
		}
	},
	
	cleanRule = function(rule) {
		var that = this,
		container,tagName,triggerEle,mainTrigger,
		hash = '',
		triggerHash = '';
		
		// get the jQuery object of the trigger			
		container = rule.element.closest('.formFieldsContainer');			
		if(container.length <= 0){
			container = rule.element.closest('form');
		}

		if(container.length <= 0){
			container = $('body');
		}		
		
		// fix for BAD HTML when many targets have the same ID
		if(rule.trigger == 'this'){
			mainTrigger = rule.element;
		} else {
			mainTrigger = container.find(rule.trigger);
		}
		
		if(mainTrigger.length <= 0){
			return false;
		}
		
		tagName = mainTrigger.get(0).tagName || '';
		tagName = tagName.toLowerCase();
		
		triggerEle = mainTrigger;
		if($.inArray(tagName,that.formElements) < 0){
			// find all the form elements inside this element
			triggerEle = mainTrigger.find(that.formElements.join(','));
		}
				
		rule.trigger = triggerEle;		
		rule.trigger.each(function(){
			triggerHash += $(this).get(0).tagName;
			triggerHash += $(this).attr('id');
			triggerHash += $(this).attr('class');
			triggerHash += $(this).attr('type');
			triggerHash += $(this).text();
		});
		rule.triggerHash = triggerHash;
		
		rule.target.each(function(){
			hash += $(this).get(0).tagName;
			hash += $(this).attr('id');
			hash += $(this).attr('class');
			hash += $(this).attr('type');
			hash += $(this).text();
		});
		hash += rule.task;
		if(rule.taskValues) hash += JSON.stringify(rule.taskValues);				
		hash += JSON.stringify(rule.values);				
		
		rule.hash = hash;		
		
		return rule;
	},
	
	parseCondRulesString = function(str){
		var that = this,
		mainTrigger,rule = {},par,
		ruleParts = str.substring(9,(str.length -1));		
		ruleParts = ruleParts.split(',');

		if(ruleParts.length < 3) return false;
		
		rule.or = (typeof ruleParts[4] != 'undefined' && parseInt(ruleParts[4]) > 0) ? true : false;
		rule.task = ruleParts[0].toLowerCase().trim();
		if(rule.task == '') return false;
		
		rule.trigger = ruleParts[1].split('|');
		rule.trigger = $.map( rule.trigger, function( val, i ) {
			val = val.trim();
			if(val == '') return;
			return val;
		});
		if(rule.trigger.length == 0) return;
		rule.trigger = rule.trigger.join(',');
		
		rule.values = ruleParts[2].split('|');			
		rule.values = $.map( rule.values, function( val, i ) {
			return val.trim();
		});
	
		par = rule.task.indexOf('(');
		if(par >= 0){
			rule.taskValues = rule.task.substring(par+1,rule.task.length-1).split('|');
			rule.taskValues = $.map( rule.taskValues, function( val, i ) {
				return val.trim();
			});
			rule.task = rule.task.substring(0,par);
		}
		
		return rule;
	},
	
	getLength = function(o){
		var length = 0;
		if(o instanceof Array) return o.length;
		
		for (var i in o) {
			length++;
		}
		return length;
	},
	
	getRules = function(){
		var that = this,
		rulesGroups = {},
		rules = {},
		condRulesElements = $(),
		condSelector = '[class^="condRule["], [class*=" condRule["], [cond-rules], [cond-rule-task]';
		
		// get all the input elements		
		this.element.each(function(){
			var $ele = $(this),
			subEle = $ele.find(condSelector);
			
			if($ele.is(condSelector)){
				condRulesElements = condRulesElements.add($ele);
			} else if(subEle.length > 0) {
				condRulesElements = condRulesElements.add(subEle);
			}
		});
		
		condRulesElements.each(function(){
			var targetEle = $(this),
				self = $(this),
				typeEle = self.get(0).tagName,
				eleData = self.data(),
				classes = self.attr('class').split(' '),
				rules = [],eleRules;
			
			typeEle = typeEle.toLowerCase();
			if($.inArray(typeEle,that.formElements) >= 0 || self.hasClass('form-widget')){
				var cGroup = self.closest('.control-group');
				if(cGroup.length > 0){
					targetEle = cGroup;
				}			
			}

			
			// classes method
			for(var c=0;c<classes.length;c++){
				var val = classes[c];		
				if(val.indexOf('condRule[')<0){
					continue;
				}
				
				var rule,rawRule = parseCondRulesString.call(that,val);
				if(!rawRule) continue;
				
				rawRule.element = self;
				rawRule.target = targetEle;
				
				rule = cleanRule.call(that,rawRule);				
				if(!rule) continue;
				
				rules.push(rule);			
			};			
			
			// data method
			if(typeof eleData.condRuleTask != 'undefined'){
				// TODO
			}
			
			// data-rules method
			if(self.data('cond-rules')){
				// TODO
			}
			
			// parsing rules
			eleRules = targetEle.data('condRulesData');
			if(!eleRules || !$.isPlainObject(eleRules)){
				eleRules = {};
			}
			
			for(var r=0;r<rules.length;r++){
				var rule = rules[r];
				
				if(typeof rulesGroups[rule.triggerHash] == 'undefined'){
					rulesGroups[rule.triggerHash] = {};
				}
				rulesGroups[rule.triggerHash][rule.hash] = rule;
				
				if(!$.isPlainObject(eleRules[rule.task])){
					eleRules[rule.task] = {};
				}
				
				eleRules[rule.task][rule.hash] = rule;
			}
			
			targetEle.data('condRulesData',eleRules);			
		});	
		
		return rulesGroups;
	},
	
	timers = {},
	
	delay = (function(){
		return function(callback, ms, hash){
			hash = hash || 0;
			if(typeof timers[hash] != 'undefined'){
				clearTimeout (timers[hash]);
			}
			timers[hash] = setTimeout(callback, ms);
		};
	})();
	
    // The actual plugin constructor
	var Plugin = function ( element ) {
		if(typeof element.length == 0){
			element = $('body');
		}
		this.element = element;
		
		this.formElements = ['input','select','textarea'];
		this.defaultTasks = ['show','hide','slideup','slidedown','select','deselect'];
		
		this.events = {};		
		this.events[pluginName +'_initialized'] = 'onInit';
	};

	Plugin.prototype = {
		init: function () {
			// add events handlers
			addEventsHandlers.call(this, getRules.call(this));
			
			// initialization completed	
			trigger.call(this,pluginName +'_initialized', []);
		},
	
		execRule: function(rule){
			delay(
				function(){
					execRule.call(this,rule);
				},
				100,
				rule.triggerHash
			);
		},
		
		checkRule: function(rule, allRules){
			return execRule.call(this,rule,true,allRules);
		},
		
		getRules: function(){
			return getRules.call(this);
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

		instance = new Plugin( this );
		
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

jQuery(document).ready(function(){
	jQuery('form').each(function(){
		jQuery(this).condRules();
	});
});