jQuery(document).ready(function($){
	$('#snfr-close-button').click(function(e) {
		$(this).parent().fadeOut();
		$.cookie('snfr-notice-closed', '1');
	});

	if ($.cookie('snfr-notice-closed') != '1') { $('#snfr-notice').show(); }
});