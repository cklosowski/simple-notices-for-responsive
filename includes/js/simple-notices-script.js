jQuery(document).ready(function($){
	var myOptions = {
		// a callback to fire whenever the color changes to a valid color
		change: function snfrUpdatePreview(event, ui) {
			var attribute = $(this).attr("id");
			var color     = $(this).val();

			$('#preview-message').css(attribute, color);
			snfrSettingsNotSaved();
		}
		,
		// hide the color picker controls on load
		hide: true
	};

	$('.snfr-color-field').wpColorPicker(myOptions);

    if( 0 < $('.snfr-date-field').length ) {
        $('.snfr-date-field').datepicker();
    } // end if

	$('#message').blur(function(e) {
		var message = $('#message').val();

		if (message.trim() == '')
			return true;

		$('#preview-wrapper').show();
		if (message != $('#message-text').text()) {
			snfrSettingsNotSaved();
		}
		$('#message-text').html(message);
	});

	$('#onoffswitch').change(function(e) {
			snfrSettingsNotSaved();

			if ( $(this).val() == 'range' ) {
				$('#start-date,#end-date').removeAttr('disabled');
			} else {
				$('#start-date,#end-date').attr('disabled', 'disabled').val('');
			}
	});

	$('.button-primary').click(function(e) {
		var dateRangeSelected = ($('#onoffswitch').val() == 'range') ? true : false;

		if (dateRangeSelected) {
			var startDate = $('#start-date').val();
			var endDate   = $('#end-date').val();

			if (startDate.length == 0 || endDate.length == 0) { alert(snfrMissingDate); return false; }
			if (endDate < startDate) { alert(snfrInvalidDateRante); return false; }
		}

		return true;
	});

	function snfrSettingsNotSaved() {
		if ($('#wppush-save-notice').length) { return; }

		$('.button-primary').parent().before('&nbsp;<span id="wppush-save-notice" style="color: red"><em><strong>' + snfrUnsavedChanges + '</strong></em></span>');
	}
});
