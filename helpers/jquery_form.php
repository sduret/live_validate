<?php
/**
 *  jQuery Live Form Validation Helper
 *
 *  @author Marc Grabanski <m@marcgrabanski.com>
 *  @author Jeff Loiselle <jeff@newnewmedia.com>
 *  @license Copyright 2009 Marc Grabanski under MIT
 */
class JqueryFormHelper extends AppHelper {
	
	var $forms = array();
	var $helpers = array('Javascript', 'Form');
	
	/**
	 * Writes the jQuery code that handles the AJAX
	 */
	function afterRender() {
		
		if (isset($this->forms) && count($this->forms) > 0) {
			$forms = array();
			foreach ($this->forms as $id) {
				$forms[] = "#$id :input";
			}
			$forms = implode(', ', $forms);
			
			$js = <<<END
$(document).ready(function() {
	$('$forms').blur(function() {
		var theFieldId = $(this).attr('id');
		
		if ($('#'+theFieldId+' ~ .loading').length == 0) {
			$('#'+theFieldId).after("<div class=\"loading\" style=\"display:block;\">&nbsp;</div>");
		}
		$('#'+theFieldId+' ~ .error-message').remove();
		$(this).parents('form:first').ajaxSubmit({
			dataType: 'json',
			success:function(response){
				var ids = [];
				$(response).each(function(i, field){
					ids[i] = field.id;
					$('#'+field.id+' + .loading').remove();
					if (field.id == theFieldId) {
						if (field.message) {
							input = $("#"+field.id);
							input.parents('div.input:first').addClass('error');
							input.removeClass('valid');
							input.addClass('form-error');
							if (input.siblings('.error-message').length > 0) {
								input.siblings('.error-message').html(field.message);
							} else {
								$('<div class="error-message">' + field.message + '</div>')
									.data('input.id', field.id)
									.insertAfter(input);
							}
						}
					}
				});
				
				if ($.inArray(theFieldId, ids) < 0){
					$("#"+theFieldId).addClass('valid');
					$('#'+theFieldId+' ~ .loading').remove();
				}
				
				$("div.error-message").each(function(i, errorDiv){
					invalid = $.inArray($(errorDiv).data('input.id'), ids);
					if (invalid < 0) {
						$(errorDiv).prev().removeClass('form-error');
						$(errorDiv).parents('div.error:first').removeClass('error');
						$(errorDiv).fadeOut().remove();
					}
				});
			}
		});
	});
});
END;
			print $this->Javascript->codeBlock($js, array('inline' => true));
		}
	}
	
	/**
	 * Creates a hidden form element that triggers AJAX validation by the associated component
	 */
	function validate($id) {
		$this->forms[] = $id;
		return $this->Form->input('__validate.action', array('type' => 'hidden', 'value' => $this->params['action']));
	}
}

?>