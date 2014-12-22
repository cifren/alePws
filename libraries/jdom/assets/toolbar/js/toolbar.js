jQuery(document).ready(function(){
	jQuery("#adminForm").validationEngine('attach', {promptPosition : "topRight:0,0"});
});

Joomla.submitform = function(task, form)
{
	if (typeof form == 'undefined'){
		form = jQuery(this).closest('form[name=adminForm]');
		
		if(form.length == 0){
			form = jQuery('form[name=adminForm]');
		}
	} else if(!(form instanceof jQuery)){
		form = jQuery(form);
	}
	
	if(form.length == 0){
		return false;
	}
	
	var valid = checkForm(task,form);
	
	
	if(valid){
		// remove cloner fields
		jQuery('form').find('[data-cloner-group]').each(function(){
			var target = jQuery(this).attr('data-target-field');
			if(target){
				jQuery(this).find('input,textarea,select').remove();
			}
		});
	
		if(typeof tinyMCE != 'undefined'){
			tinyMCE.triggerSave();
		} 
		
		if(typeof tinymce != 'undefined'){
			tinymce.triggerSave();
		}
		
		form.submit();
		
		if(typeof jQuery.msg != 'undefined'){
			var msg_opts = {
				autoUnblock : false,
				bgPath: (window.jQuery_msg.bgPath || ''),
				content: (window.jQuery_msg.content || 'Please wait...')
			};
			msg_opts = jQuery.extend({},window.jQuery_msg,msg_opts);
			jQuery.msg(msg_opts);
		}		
	} else {
		//Lock the page
		holdForm = false;
	}
}

function checkForm(task,form){
	//Unlock the page
	holdForm = false;
	var valid = false,
		taskName = '';

	taskName = '';
	if (typeof(task) != 'undefined'){		
		var parts = task.split('.');		
		form.find("input#task").val(task);
		taskName = parts[parts.length-1];
	}
	
	switch(taskName)
	{
		case 'save':
		case 'save2copy':
		case 'save2new':
		case 'apply':
		case 'delete':
		case 'publish':
		case 'unpublish':
		case 'trash':
		case 'archive':
		case 'checkin':
			
			valid = checkFullForm(form);
			
			
			//Call the validator
			break;

		default:
			valid = true;
			form.validationEngine('detach');
			break;
	}
	
	return valid;
}

function checkFullForm(form){

	if (typeof form == 'undefined'){
		form = jQuery('#adminForm');
	} else if(!(form instanceof jQuery)){
		form = jQuery(form);
	}
	
	form.find('.fields_errors').remove();
	var validate = form.validationEngine('validate');
	
	if(validate == true){	
		return true;
	}
	
	var tabs = form.find('.tab-content .tab-pane');

	var error_msg = Joomla.JText._("JSHOP_FORM_WITH_ERRORS");
	
	
	tabs.each(function(index){
		var errors = jQuery(this).find('.formError:not(.greenPopup)');
		var numErrors = errors.length;
		var tabId = jQuery(this).attr('id');
		var tabHeader = jQuery('.nav-tabs li a[href="#'+ tabId +'"]');
		
		if(numErrors <= 0){
			return true;
		}
		var prompt = '<div class="formError" style="position: absolute; opacity: 0.87;"><div class="formErrorContent">';
			prompt += error_msg;
			prompt += '</div><div class="formErrorArrow"><div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div><div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div></div></div>';
			
			tabHeader.append('<span class="fields_errors"><span class="num">'+ numErrors +'</span>'+ prompt +'</span>');
	
		var fieldError = tabHeader.find('.fields_errors .num');
		var tabH_offParent = fieldError.offsetParent().offset();
		var tabH_off = fieldError.offset();
		
		if(typeof tabH_off != 'undefined'){
			tabHeader.find('.formError').css({
				top: tabH_off.top - (tabH_offParent.top +40),
				left: tabH_off.left - (tabH_offParent.left +17)
			});
		}
		
	});

	return false;
}

Joomla.submitformAjax = function(task, form)
{
	var that = this;
	if (typeof form === 'undefined'){
		form = jQuery(this).closest('form[name=adminForm]');
		
		if(form.length == 0){
			form = jQuery('form[name=adminForm]');
		}
	} else if(!(form instanceof jQuery)){
		form = jQuery(form);
	}

	if(form.length == 0){
		return false;
	}
		
	// checkForm
	taskName = '';
	if (typeof(task) !== 'undefined' && task !== ''){
		var parts = task.split('.');
		taskName = parts[parts.length-1];
	
		var valid = checkForm(task,form);	
		form.find('.cktoolbar span.' + taskName).addClass('spinner');
	} else {
		//Not ajax when controller task is empty (ex: filters, search, ...)
		return Joomla.submitform();
	}
	
	if(valid){		
		if(typeof tinyMCE != 'undefined'){
			tinyMCE.triggerSave();
		} 
		
		if(typeof tinymce != 'undefined'){
			tinymce.triggerSave();
		}
		
		jQuery.ajax({
			url: "index.php?return=json",
			cache: false,
			data: form.serialize(),
			beforeSend: function () {
				// console.log("Loading");
			},

			error: function (jqXHR, textStatus, errorThrown) {
				// console.log(jqXHR);
				// console.log(textStatus);
				// console.log(errorThrown);
			},

			success: function (response) {
				// console.log('Success');
				// console.log(data);
				response = jQuery.parseJSON(response);
				if (response.transaction.result){
					if(taskName != 'cancel' && response.transaction.refresh){
						parent.holdForm = false;
						parent.location.reload(false);
					}
					
					parent.jdomAjax.closeModal();
				} 
				
				var msg = response.transaction.htmlExceptions;
				if(typeof msg != 'undefined'){
					parent.jdomAjax.showMessages(msg);
				} else {			
					var msg = response.transaction.rawExceptions;
					if (!response.transaction.result && (typeof msg == 'undefined' || msg.trim() == '')){
						msg = 'Unknown error';
					}
					
					if(msg){
						alert(msg);
					}
				}
				
				var taskName = task.replace('.','_');
				if(typeof window[taskName] == 'function'){
					window[taskName](response);
				}
			},

			complete: function () {
				// console.log('Finished all tasks');
				if(typeof jQuery.msg == 'function'){
					jQuery.msg( 'unblock' );
				}
			}
		});		

		if(typeof jQuery.msg != 'undefined'){
			var msg_opts = {
				autoUnblock : false,
				bgPath: (window.jQuery_msg.bgPath || ''),
				content: (window.jQuery_msg.content || 'Please wait...')
			};
			msg_opts = jQuery.extend({},window.jQuery_msg,msg_opts);
			jQuery.msg(msg_opts);
		}		
	} else {
		//Lock the page
		holdForm = false;
	}
};
