/*
* @name			extra.js - a little helper for common JS functions
* @version		0.0.1
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

// extends the Date prototype
;(function (d) {
	d.getMaxDays = function(){
		var tmpDate	= new Date(this.toString()),
			d		= 28,
			m		= tmpDate.getMonth();
		while (tmpDate.getMonth() == m) {
			++d;
			tmpDate.setDate(d);
		}
		return d - 1;
	};
	d.addDays		= function (n) {
		this.setDate(this.getDate() + n);
	};
	d.addHours		= function (n) {
		this.setHours(this.getHours() + n);
	};
	d.addMins		= function (n) {
		this.setMinutes(this.getMinutes() + n);
	};
	d.addMonths	= function (n){
		var day	= this.getDate();
		this.setDate(1);
		this.setMonth(this.getMonth() + n);
		this.setDate(Math.min(day, this.getMaxDays()));
	};
	d.addYears	= function (n) {
		var day	= this.getDate();
		this.setDate(1);
		this.setFullYear(this.getFullYear() + n);
		this.setDate(Math.min(day, this.getMaxDays()));
	};
	d.setZeroSec = function (n) {
		this.setSeconds(0);
		this.setMilliseconds(0);
	};
	d.getDayOfYear	= function() {
		var now		= new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
		var then	= new Date(this.getFullYear(), 0, 0, 0, 0, 0);
		var time	= now - then;
		return Math.floor(time / 24*60*60*1000);
	};
	d.text = {
		days		: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
		daysShort	: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
		months	: ['January','February','March','April','May','June','July','August','September','October','November','December'],
		monthsShort	: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']	
	};
	d.strToDate = function(date, format) {
		format = (typeof format != 'undefined' && format != '') ? format : 'Y-m-d H:i:s';
		if(!date){
			var fakeDate = new Date();		
			this.setTime(fakeDate.valueOf());		
			return true;		
		} else if(date instanceof Date) {
			this.setTime(date.valueOf());
			return true;
		} else if (date.indexOf('now') >= 0) {
			var method,pa,paClean,dateSTR = date.replace('now',''),			
			separator	= new RegExp('(.*?(mins|min|hours|hour|days|day|months|month|years|year))','gi'),
			parts		= dateSTR.match(separator),
			date = new Date();		
			if(!(parts instanceof Array)){
				this.setTime(date.valueOf());
				return true;
			}
			
			for(var i=0;i<parts.length;i++){
				pa = parts[i];
				
				if(pa.indexOf('mins') >= 0 || pa.indexOf('min') >= 0){
					method = 'addMins';
				} else if(pa.indexOf('hours') >= 0 || pa.indexOf('hour') >= 0){
					method = 'addHours';
				} else if(pa.indexOf('days') >= 0 || pa.indexOf('day') >= 0){
					method = 'addDays';
				} else if(pa.indexOf('months') >= 0 || pa.indexOf('month') >= 0){
					method = 'addMonths';
				} else if(pa.indexOf('years') >= 0 || pa.indexOf('year') >= 0){
					method = 'addYears';
				}
				
				paClean = parseInt(pa.replace(/[^0-9+-]/g, ""));
				if(!isNaN(parseFloat(paClean)) && isFinite(paClean)){
					date[method](paClean);
				}
			}
			
			this.setTime(date.valueOf());
			return true;
		}
		
		var months_text	= this.text.monthsShort.join(')(') + ')(' + this.text.months.join(')('),
			separator	= new RegExp('[^0-9a-zA-Z(' + months_text + ')]+'),
			parts		= date.split(separator),
			against		= format.split(separator),
			d,m,y,h,min,sec,mil,timestamp,parsed_date,
			now = new Date();

		for (var i = 0; i < parts.length; i++) {
				/*
		OK		d      Day of the month, 2 digits with leading zeros                                "01" to "31"
				D      A textual representation of a day, three letters                             "Mon" through "Sun"
		OK		j      Day of the month without leading zeros                                       "1" to "31"
				l      A full textual representation of the day of the week (lowercase "L")         "Sunday" through "Saturday"
				N      ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)  "1" (for Monday) through "7" (for Sunday)
				S      English ordinal suffix for the day of the month, 2 characters                "st", "nd", "rd" or "th". Works well with j
				w      Numeric representation of the day of the week                                "0" (for Sunday) through "6" (for Saturday)
				z      The day of the year (starting from "0")                                      "0" through "365"      
				W      ISO-8601 week number of year, weeks starting on Monday                       "00" to ("52" or "53")
		OK		F      A full textual representation of a month, such as January or March           "January" through "December"
		OK		m      Numeric representation of a month, with leading zeros                        "01" through "12"
		OK		M      A short textual representation of a month, three letters                     "Jan" through "Dec"
		OK		n      Numeric representation of a month, without leading zeros                     "1" through "12"
				t      Number of days in the given month                                            "28" through "31"
				L      Whether it's a leap year                                                     "1" if it is a leap year, "0" otherwise
				o      ISO-8601 year number. This has the same value as Y, except that if the       "2008"
					ISO week number (W) belongs to the previous or next year, that year 
					is used instead.
		OK		Y      A full numeric representation of a year, 4 digits                            "2008"
		OK		y      A two digit representation of a year                                         "08"
		OK		a      Lowercase Ante meridiem and Post meridiem                                    "am" or "pm"
		OK		A      Uppercase Ante meridiem and Post meridiem                                    "AM" or "PM"
				B      Swatch Internet time                                                         "000" through "999"
		OK		g      12-hour format of an hour without leading zeros                              "1" through "12"
		OK		G      24-hour format of an hour without leading zeros                              "0" through "23"
		Ok		h      12-hour format of an hour with leading zeros                                 "01" through "12"
		OK		H      24-hour format of an hour with leading zeros                                 "00" through "23"
		OK		i      Minutes with leading zeros                                                   "00" to "59"
		OK		s      Seconds, with leading zeros                                                  "00" through "59"
		OK		u      Milliseconds                                                                 "54321"
				e      Timezone identifier                                                          "UTC", "EST", "PST"
				I      Whether or not the date is in daylight saving time (uppercase i)             "1" if Daylight Saving Time, "0" otherwise
				O      Difference to Greenwich time (GMT) in hours                                  "+0200", "-0600"
				P      Difference to Greenwich time (GMT) with colon between hours and minutes      "+02:00", "-06:00"
				T      Timezone abbreviation                                                        "UTC", "EST", "PST"
				Z      Timezone offset in seconds. The offset for timezones west of UTC is          "-43200" through "50400"
					always negative, and for those east of UTC is always positive.
				c      ISO 8601 date                                                                "2004-02-12T15:19:21+00:00"
				r      RFC 2822 formatted date                                                      "Thu, 21 Dec 2000 16:01:07 +0200"
		OK		U      Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)                   "0"
				*/
			switch (against[i]) {
				case 'M':
					m = this.text.monthsShort.indexOf(parts[i]);
				break;
				case 'F':
					m = this.text.months.indexOf(parts[i]);
				break;
				case 'd':
				case 'j':
					d = parseInt(parts[i],10);
				break;
				case 'm':
				case 'n':
					m = parseInt(parts[i], 10)-1;
				break;
				case 'Y':
				case 'y':
					y = parseInt(parts[i], 10);
					y += y > 100 ? 0 : (y < 29 ? 2000 : 1900);
				break;
				case 'i':
					min = parseInt(parts[i], 10);
				break;
				case 's':
					sec = parseInt(parts[i], 10);
				break;
				case 'u':
					mil = parseInt(parts[i], 10);
				break;
				case 'U':
					timestamp = parseInt(parts[i], 10);
				break;
				case 'H':
				case 'G':
				case 'h':
				case 'g':
					h = parseInt(parts[i], 10);						
				break;
				case 'A':
				case 'a':
					if (/pm/i.test(parts[i]) && h < 12) {
						h += 12;
					} else if (/am/i.test(parts[i]) && h >= 12) {
						h -= 12;
					}
				break;
			}
		}
		
		if(typeof timestamp != 'undefined'){
			parsed_date = new Date((timestamp * 1000));
		} else {
			parsed_date = new Date(
				y === undefined ? now.getFullYear() : y,
				m === undefined ? now.getMonth() : m,
				d === undefined ? now.getDate() : d,
				h === undefined ? now.getHours() : h,
				min === undefined ? now.getMinutes() : min,
				sec === undefined ? now.getSeconds() : sec,
				mil === undefined ? now.getMilliseconds() : mil
			);
		}
		if (parsed_date.toString() == 'Invalid Date') {
			parsed_date = new Date;
		}
		this.setTime(parsed_date.valueOf());
		return true;
	};	
	d.dateToStr = function(format){
		var date = this,
			format = (typeof format != 'undefined' && format != '') ? format : 'Y-m-d H:i:s',
			m = date.getMonth(),
			d = date.getDate(),
			y = date.getFullYear(),
			w = date.getDay(),
			s = {},
			hr = date.getHours(),
			pm = (hr >= 12),
			ir = (((pm) ? (hr - 12) : hr) == 0) ? 12 : ir,
			dy = date.getDayOfYear(),
			min = date.getMinutes(),
			sec = date.getSeconds(),
			parts = format.split(''), part;
			
		for (var i = 0; i < parts.length; i++) {
			part = parts[i];
			
				/*
		OK		d      Day of the month, 2 digits with leading zeros                                "01" to "31"
		OK		D      A textual representation of a day, three letters                             "Mon" through "Sun"
		OK		j      Day of the month without leading zeros                                       "1" to "31"
		OK		l      A full textual representation of the day of the week (lowercase "L")         "Sunday" through "Saturday"
		OK		N      ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)  "1" (for Monday) through "7" (for Sunday)
				S      English ordinal suffix for the day of the month, 2 characters                "st", "nd", "rd" or "th". Works well with j
		OK		w      Numeric representation of the day of the week                                "0" (for Sunday) through "6" (for Saturday)
		OK		z      The day of the year (starting from "0")                                      "0" through "365"      
				W      ISO-8601 week number of year, weeks starting on Monday                       "00" to ("52" or "53")
		OK		F      A full textual representation of a month, such as January or March           "January" through "December"
		OK		m      Numeric representation of a month, with leading zeros                        "01" through "12"
		OK		M      A short textual representation of a month, three letters                     "Jan" through "Dec"
		OK		n      Numeric representation of a month, without leading zeros                     "1" through "12"
				t      Number of days in the given month                                            "28" through "31"
				L      Whether it's a leap year                                                     "1" if it is a leap year, "0" otherwise
				o      ISO-8601 year number. This has the same value as Y, except that if the       "2008"
					ISO week number (W) belongs to the previous or next year, that year 
					is used instead.
		OK		Y      A full numeric representation of a year, 4 digits                            "2008"
		OK		y      A two digit representation of a year                                         "08"
		OK		a      Lowercase Ante meridiem and Post meridiem                                    "am" or "pm"
		OK		A      Uppercase Ante meridiem and Post meridiem                                    "AM" or "PM"
				B      Swatch Internet time                                                         "000" through "999"
		OK		g      12-hour format of an hour without leading zeros                              "1" through "12"
		OK		G      24-hour format of an hour without leading zeros                              "0" through "23"
		OK		h      12-hour format of an hour with leading zeros                                 "01" through "12"
		OK		H      24-hour format of an hour with leading zeros                                 "00" through "23"
		OK		i      Minutes with leading zeros                                                   "00" to "59"
		OK		s      Seconds, with leading zeros                                                  "00" through "59"
		OK		u      Milliseconds                                                                 "54321"
				e      Timezone identifier                                                          "UTC", "EST", "PST"
				I      Whether or not the date is in daylight saving time (uppercase i)             "1" if Daylight Saving Time, "0" otherwise
				O      Difference to Greenwich time (GMT) in hours                                  "+0200", "-0600"
				P      Difference to Greenwich time (GMT) with colon between hours and minutes      "+02:00", "-06:00"
				T      Timezone abbreviation                                                        "UTC", "EST", "PST"
				Z      Timezone offset in seconds. The offset for timezones west of UTC is          "-43200" through "50400"
					always negative, and for those east of UTC is always positive.
				c      ISO 8601 date                                                                "2004-02-12T15:19:21+00:00"
				r      RFC 2822 formatted date                                                      "Thu, 21 Dec 2000 16:01:07 +0200"
		OK		U      Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)                   "0"
				*/			
			
			switch (part) {
				case 'D':
					part = this.text.daysShort[w];
				break;
				case 'l':
					part = this.text.days[w];
				break;
				case 'M':
					part = this.text.monthsShort[m];
				break;
				case 'F':
					part = this.text.months[m];
				break;
				case 'y':
					part = 1 + Math.floor(y / 100);
				break;
				case 'd':
					part = (d < 10) ? ("0" + d) : d;
				break;
				case 'j':
					part = d;
				break;
				case 'H':
					part = (hr < 10) ? ("0" + hr) : hr;
				break;
				case 'h':
					part = hr;
				break;
				case 'G':
					part = (ir < 10) ? ("0" + ir) : ir;
				break;
				case 'g':
					part = ir;
				break;
				case 'z':
					part = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy;
				break;
				break;
				case 'm':
					part = (m < 9) ? ("0" + (1+m)) : (1+m);
				break;
				case 'n':
					part = m+1;
				break;
				case 'i':
					part = (min < 10) ? ("0" + min) : min;
				break;
				case 'a':
					part = pm ? "pm" : "am";
				break;
				case 'A':
					part = pm ? "PM" : "AM";
				break;
				case 'U':
					part = Math.floor(date.getTime() / 1000);
				break;
				case 's':
					part = (sec < 10) ? ("0" + sec) : sec;
				break;
				case 'u':
					part = date.getMilliseconds();
				break;
				case 'w':
					part = w;
				break;
				case 'N':
					part = (w == 0) ? 7 : w;
				break;
				case 'y':
					part = ('' + y).substr(2, 2);
				break;
				case 'Y':
					part = y;
				break;
			}
			parts[i] = part;
		}
		return parts.join('');
	};
})(Date.prototype);

Array.prototype.AllValuesSame = function(){
    if(this.length > 0) {
        for(var i = 1; i < this.length; i++)
        {
            if(this[i] !== this[0])
                return false;
        }
    } 
    return true;
}

function scrollToElement( options ) {
	var defaults = {
		target: null,
		topoffset: 100,
		parent: 'html,body',
		speed: 1300
	}

	var o = jQuery.extend( defaults, options);
	
	if (typeof o.target == 'undefined' || !o.target){
		return;
	} else if(!(o.target instanceof jQuery)){
		o.target = jQuery(o.target);
	}

	if(o.target.length < 0){
		return;
	}
	
	if((!(o.parent instanceof jQuery) && o.parent != '')){
		o.parent = jQuery(o.parent);
	}

	if(o.parent.length < 0){
		o.parent = jQuery(defaults.parent);
	}

	// check the element is inside a bootstrap modalWindow
	if(o.target.closest('.modal-body').length > 0){
		o.parent = o.target.closest('.modal-body');
		o.topoffset = 0;
	}

	var destination = o.target.offset().top;
	if(o.parent.css('position') != 'fixed' && !o.parent.is('.modal-body')){
		destination -= o.parent.offset().top;
	}
	destination -= o.topoffset;
	
	// console.log('tg.top: '+ o.target.offset().top +' parent.off: '+ o.parent.offset().top +' offset: '+ o.topoffset +' dest: '+ destination);
	o.parent.animate( { scrollTop: destination}, o.speed, function() {
	/*	window.location.hash = target; */
	});

    return false;
}

jQuery.fn.serializeObject = function()
{
    var a,o = {};
	a = this.find('input,textarea,select').serializeArray();

    jQuery.each(a, function(i,v) {
        if (o[this.name] !== undefined){
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function basename(path){
	var result = '';
	if(typeof path == 'string'){
		result = path.split(/[\\/]/).pop();
	}
	return result;
}

function ajaxCall(opts){
	var defaults = {
		route: '',
		varsToSend: {},
		caller: 'body',
		target: '#system-message',
		method: 'html',
		callBack: function(){},
		loadingMsg: true
	}

	if(jQuery(defaults.target).length <= 0){
		defaults.target = '#system-message-container';
	}
	
	var o = jQuery.extend( defaults, opts);
	
	o.method = (o.method || 'html');
	if(!(o.caller instanceof jQuery)){
		o.caller = jQuery(o.caller);
	}

	if(!(o.target instanceof jQuery)){
		o.target = jQuery(o.target);
	}
	
	// hack for google maps cursor
	// find divs inside gmaps on page
	var gmapsEle = jQuery('.gm-style div').filter(function(){
		return jQuery(this).css('cursor').toLowerCase().indexOf('maps.gstatic.com') > -1;
	});

	gmapsEle.each(function(){
		jQuery(this).data('originalCursor',jQuery(this).css('cursor'));
	});

	var addMsg = function(response){
		var msg = response.transaction.htmlExceptions;
		if(typeof msg != 'undefined'){
			parent.jdomAjax.showMessages(msg);
		} else {
			if(typeof response.transaction.rawExceptions != 'undefined'){
				msg = response.transaction.rawExceptions;
			}
			
			if (!response.transaction.result && (typeof msg == 'undefined' || msg.trim() == '')){
				msg = 'Unknown error';
			}
			
			if(msg){
				alert(msg);
			}
		}	
	};
	
	o.caller.jdomAjax({
		namespace:o.route,
		vars:o.varsToSend,
		loading: function(object)
		{
			jQuery('html, body').css('cursor','progress');
			gmapsEle.css('cursor','progress');

			if(o.loadingMsg && 	typeof jQuery.msg != 'undefined'){
				var msg_opts = {
					autoUnblock : false,
					clickUnblock : true,
					bgPath: (window.jQuery_msg.bgPath || ''),
					content: (window.jQuery_msg.content || 'Please wait...')
				};
				msg_opts = jQuery.extend({},window.jQuery_msg,msg_opts);
				jQuery.msg(msg_opts);
			}
		},
		success: function(object, data, textStatus, jqXHR)
		{
			var html = '';
			jQuery('html, body').css('cursor','auto');
			gmapsEle.each(function(){
				var originalCursor = jQuery(this).data('originalCursor');
				jQuery(this).css('cursor',originalCursor);
			});
			
			var thisp = this;

			// try to decode a JSON string
			try {
				var json = JSON.parse(data);
			} catch(err) {
				// console.log(err);
			}
			
			if(typeof json != 'undefined' && jQuery.isPlainObject(json)){
				// console.log(json);
				
				// output messages
				addMsg(json);
				
				// add returned html
				html = json.response.html;
			} else {
				//fill the target with the returned html IF IT'S NOT a JSON STRING
				html = data;
			}
			
			if(html && html != ''){
				o.target[o.method](html);
			}

			if(o.loadingMsg && typeof jQuery.msg == 'function'){
				jQuery.msg( 'unblock' );
			}
/*
			jQuery(object).ready(function()
			{
				if (typeof(thisp.ready) != 'undefined'){
					thisp.ready(object, data, textStatus, jqXHR);
				}
			});
	*/		
			if(typeof o.callBack == 'function'){
				o.callBack(data);
			}
		},
		error: function(object, jqXHR, textStatus, errorThrown)
		{
			jQuery('html, body').css('cursor','auto');
			gmapsEle.each(function(){
				var originalCursor = jQuery(this).data('originalCursor');
				jQuery(this).css('cursor',originalCursor);
			});
		}
	});
}

function add(a, b, precision) {
    var x = Math.pow(10, precision || 2);
    return (Math.round(a * x) + Math.round(b * x)) / x;
}

function sub(a, b, precision) {
    var x = Math.pow(10, precision || 2);
    return (Math.round(a * x) - Math.round(b * x)) / x;
}

var delayTimers = {},
delay = (function(){
	return function(callback, ms, hash){
		hash = hash || 0;
		if(typeof delayTimers[hash] != 'undefined'){
			clearTimeout (delayTimers[hash]);
		}
		delayTimers[hash] = setTimeout(callback, ms);
	};
})();

function countProperties(obj) {
    var count = 0;
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            ++count;
    }
    return count;
}


function checkFormStep(index,formID){
	var scrollExecuted = false,validations = [],
		form = jQuery('#'+ formID +'Step'+index),
		formElements = form.find('input, select, textarea');

	if(formElements.length == 0){
		return true;
	}
	formElements.each(function(ind){
		var validationResult = !jQuery(this).validationEngine('validate');
		validations.push(validationResult);
		if(!scrollExecuted && !validationResult){
			var scroll_opts = {
					target: jQuery(this)
				}
			scrollToElement(scroll_opts);
			scrollExecuted = true;
		}
	});
	
	if(validations.AllValuesSame()) {
		if(validations[0]){
			return true;
		}
	}

	return false;
}

function dynamicSort(property) {
    var sortOrder = 1;
    if(property[0] === "-") {
        sortOrder = -1;
        property = property.substr(1);
    }
    return function (a,b) {
        var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
        return result * sortOrder;
    }
}

function dynamicSortMultiple() {
    /*
     * save the arguments object as it will be overwritten
     * note that arguments object is an array-like object
     * consisting of the names of the properties to sort by
     */
    var props = arguments;
    return function (obj1, obj2) {
        var i = 0, result = 0, numberOfProperties = props.length;
        /* try getting a different result from 0 (equal)
         * as long as we have extra properties to compare
         */
        while(result === 0 && i < numberOfProperties) {
            result = dynamicSort(props[i])(obj1, obj2);
            i++;
        }
        return result;
    }
}

var array_keys = function (input) {
	var output = [];
	var counter = 0;
	for (i in input) {
		output[counter++] = i;
	} 
	return output; 
}

var array_values = function (input) {
	var output = [];
	var counter = 0;
	for (i in input) {
		output[counter++] = input[i];
	} 
	return output; 
}

var array_flip = function(trans){
	var key, tmp_ar = {};
	for ( key in trans ){
		if ( trans.hasOwnProperty( key ) ){
			tmp_ar[trans[key]] = key;
		}
	}

	return tmp_ar;
}

function dirname(path) {
  //  discuss at: http://phpjs.org/functions/dirname/
  //        http: //kevin.vanzonneveld.net
  // original by: Ozh
  // improved by: XoraX (http://www.xorax.info)
  //   example 1: dirname('/etc/passwd');
  //   returns 1: '/etc'
  //   example 2: dirname('c:/Temp/x');
  //   returns 2: 'c:/Temp'
  //   example 3: dirname('/dir/test/');
  //   returns 3: '/dir'

  return path.replace(/\\/g, '/')
    .replace(/\/[^\/]*\/?$/, '');
}

function basename(path, suffix) {
  //  discuss at: http://phpjs.org/functions/basename/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Ash Searle (http://hexmen.com/blog/)
  // improved by: Lincoln Ramsay
  // improved by: djmix
  // improved by: Dmitry Gorelenkov
  //   example 1: basename('/www/site/home.htm', '.htm');
  //   returns 1: 'home'
  //   example 2: basename('ecra.php?p=1');
  //   returns 2: 'ecra.php?p=1'
  //   example 3: basename('/some/path/');
  //   returns 3: 'path'
  //   example 4: basename('/some/path_ext.ext/','.ext');
  //   returns 4: 'path_ext'

  var b = path;
  var lastChar = b.charAt(b.length - 1);

  if (lastChar === '/' || lastChar === '\\') {
    b = b.slice(0, -1);
  }

  b = b.replace(/^.*[\/\\]/g, '');

  if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
    b = b.substr(0, b.length - suffix.length);
  }

  return b;
}

function escapeHtml(text){
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
	if(typeof text == 'string') return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function readURL(input,target){
	if (input.files && input.files[0]) {
		var reader = new FileReader();

		reader.onload = function (e){
			if(!(target instanceof jQuery)) target = jQuery(target);
			target.attr('src', e.target.result);
		}

		reader.readAsDataURL(input.files[0]);
	}
}

function bytesToString(bytes)
{
	var suffix = "",
	units = ['K', 'M', 'G', 'T'];

	var i = 0;
	while (bytes >= 1024)
	{
		bytes = bytes / 1024;
		suffix = units[i];
		i++;
	}

	
	return Number(bytes.toFixed(2)) + suffix;
}