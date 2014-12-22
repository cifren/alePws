
	function getRelOption(object, property, defaultValue)
	{
		if (typeof(defaultValue) == 'undefined')
			defaultValue = null;

		var rel = jQuery(object).attr('rel');
		if (typeof(rel) == 'undefined')
			return defaultValue;
			
		var options = jQuery.parseJSON(rel);
	
		if (typeof(options[property]) != 'undefined')
			return options[property];
			
		return defaultValue;
	}

	function radiosInit(mainSelector){
	
		if(!(mainSelector instanceof jQuery)){
			mainSelector = jQuery(mainSelector);
		}
		
		mainSelector.find(".btn-group label").on('click', function() {			
			var label = jQuery(this);
			
			var input = mainSelector.find('#' + label.attr('for'));
			if (!input.prop('checked'))
			{
				var color = getRelOption(input, 'color', 'success');
					
				label.addClass('active btn-' + color);
				input.prop('checked', true).attr('checked','checked');

				label.closest('.btn-group').find("label").each(function(){
					var input = mainSelector.find('#' + jQuery(this).attr('for'));
					if (input.prop('checked'))
						return true;
						
					var color = getRelOption(input, 'color', 'success');
					jQuery(this).removeClass('active btn-success btn-' + color);
					
					input.removeAttr('checked').prop('checked',false);
				});
			}
		});
		
		mainSelector.find('.btn-group input[type="radio"], .btn-group input[type="checkbox"]').each(function()
		{
			if (!jQuery(this).prop('checked') && !jQuery(this).is(':checked'))
				return;
				
			var color = getRelOption(jQuery(this), 'color', 'success');
			mainSelector.find("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-' + color).trigger('click');
		});	
	}
	
	jQuery('document').ready(function(){
		radiosInit('body');
	});
