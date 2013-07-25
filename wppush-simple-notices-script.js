jQuery(document).ready(function($){
	var myOptions = {
		// a callback to fire whenever the color changes to a valid color
		change: function wppushUpdatePreview(event, ui) {
			var attribute = $(this).attr("id");
			var color     = $(this).val();

			$('#preview-message').css(attribute, color);
			wppushSettingsNotSaved();
		}
		,
		// hide the color picker controls on load
		hide: true
	};

	$('.my-color-field').wpColorPicker(myOptions);

	$('#message').blur(function(e) {
		var message = $('#message').val();

		if (message.trim() == '')
			return true;

		$('#preview-wrapper').show();
		if (message != $('#message-text').text()) {
			wppushSettingsNotSaved();
		}
		$('#message-text').html(message);
	});

	$('#onoffswitch').change(function(e) {
			wppushSettingsNotSaved();
	});

	function wppushSettingsNotSaved() {
		if ($('#wppush-save-notice').length) { return; }

		$('.button-primary').after('&nbsp;<span id="wppush-save-notice" style="color: red"><em><strong>You have unsaved changes</strong></em></span>');
	}
});