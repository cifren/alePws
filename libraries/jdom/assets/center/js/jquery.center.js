/*
* @name			center jQuery plugin
* @version		0.0.1
* @package		jForms
* @copyright	G. Tomaselli
* @author		Girolamo Tomaselli - http://bygiro.com - girotomaselli@gmail.com
* @license		GNU GPL v3 or later
*/

jQuery.fn.center = function ()
{
	// fix for hidden elements
	var cloned = this.clone();
	
	cloned.css({
		display: 'block',
		top: -10000000,
		left: -10000000,
		position: 'absolute'
	});
	
	cloned.appendTo('body');
	
	var eleW = cloned.outerWidth(),
	eleH = cloned.outerHeight();
	
	cloned.remove();

	this.css("position","fixed");
	this.css("top", (jQuery(window).height() / 2) - (eleH / 2));
	this.css("left", (jQuery(window).width() / 2) - (eleW / 2));
	return this;
}