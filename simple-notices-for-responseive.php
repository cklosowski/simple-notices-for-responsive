<?php
/*
Plugin Name: Simple Notices for Responsive
Plugin URL: http://kungfugrep.com
Description: Add a notice to the responsive theme, between the nav and content
Version: 1.2
Author: Chris Klosowski
Author URI: https://kungfugrep.com
Contributors: cklosows
*/


if ( is_admin() ) {
	add_action( 'admin_init', 'snfr_notices_register_settings' );
	add_action( 'admin_menu', 'snfr_notices_settings_menu' );
	add_action( 'admin_print_scripts', 'snfr_admin_print_scripts' );
	add_action( 'admin_enqueue_scripts', 'snfr_admin_enqueue_scripts' );
} else {
	add_action( 'init', 'snfr_enqueue_styles_and_scripts' );
	add_action( 'responsive_header_end', 'snfr_notices_add_notices' );
}

function snfr_notices_register_settings() {
	// Whitelist options
	register_setting( 'snfr-notices-settings', 'wppush_notices_settings' );
}

function snfr_notices_settings_menu() {
	add_theme_page( __( 'Simple Notices', 'snfr' ), __( 'Simple Notices', 'snfr' ), 'administrator', 'simple-notices-responsive', 'snfr_simple_notice_admin' );
}

function snfr_simple_notice_admin() {
	$current_settings = get_option( 'wppush_notices_settings' );
	?>
	<div class="wrap">
	<div id="icon-themes" class="icon32"></div><h2><?php _e( 'Simple Notices for Responsive:', 'snfr' ); ?></h2>
	<form method="post" action="options.php">
	  <?php wp_nonce_field( 'snfr-notices-settings' ); ?>
	  <table class="form-table">
		<tr valign="top">
		  <th scope="row"><?php _e( 'Display:', 'snfr' ); ?></th>
		  <td>
		  	<select id="onoffswitch" name="wppush_notices_settings[enabled]">
		  		<option value="on" <?php selected( $current_settings['enabled'], 'on' ); ?>><?php _e( 'On', 'snfr' ); ?></option>
		  		<option value="off" <?php selected( $current_settings['enabled'], 'off' ); ?>><?php _e( 'Off', 'snfr' ); ?></option>
		  		<option value="range" <?php selected( $current_settings['enabled'], 'range' ); ?>><?php _e( 'Date Range', 'snfr' ); ?></option>
		  	</select>
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row"><?php _e( 'Start Date:', 'snfr' ); ?></th>
		  <td>
		  	<input <?php echo ( $current_settings['enabled'] != 'range' ) ? 'disabled="disabled"' : ''; ?> type="text" value="<?php echo $current_settings['start_date']; ?>" id="start-date" name="wppush_notices_settings[start_date]" class="snfr-date-field" />
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row"><?php _e( 'End Date:', 'snfr' ); ?></th>
		  <td>
		  	<input <?php echo ( $current_settings['enabled'] != 'range' ) ? 'disabled="disabled"' : ''; ?> type="text" value="<?php echo $current_settings['end_date']; ?>" id="end-date" name="wppush_notices_settings[end_date]" class="snfr-date-field" />
		  </td>
		</tr>	

		<tr valign="top">
		  <th scope="row"><?php _e( 'Message:', 'snfr' ); ?></th>
		  <td>
		  	<textarea cols="100" id="message" rows="5" name="wppush_notices_settings[message]"><?php echo ( isset( $current_settings['message'] ) ) ? $current_settings['message'] : ''; ?></textarea>
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row"><?php _e( 'Border Color:', 'snfr' ); ?></th>
		  <td id="border-color-wrapper">
		  	<input type="text" value="<?php echo $current_settings['border_color']; ?>" id="border-color" name="wppush_notices_settings[border_color]" class="snfr-color-field" data-default-color="#d4af37" />
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row"><?php _e( 'Background Color:', 'snfr' ); ?></th>
		  <td id="bg-color-wrapper">
		  	<input type="text" value="<?php echo $current_settings['bg_color']; ?>" id="background-color" name="wppush_notices_settings[bg_color]" class="snfr-color-field" data-default-color="#fcf4cb" />
		  </td>
		</tr>

	  <input type="hidden" name="action" value="update" />
	  <input type="hidden" name="page_options" value="wppush_notices_settings" />
	  </table>

	  <p class="submit">
	  <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
	  </p>
	  <?php settings_fields( 'snfr-notices-settings' ); ?>
	</form>

	<div id="preview-wrapper" <?php echo ( !isset( $current_settings['message'] ) || empty( $current_settings['message'] ) ) ? 'style="display: none"' : ''; ?>>
		<h3><?php _e( 'Preview:', 'snfr' ); ?></h3>
		<div id="preview-message" style="background-color: <?php echo $current_settings['bg_color']; ?>; border-color: <?php echo $current_settings['border_color']; ?>;">
			<span id="message-text"><?php echo $current_settings['message']; ?></span>
		</div>
	</div>

	</div>
	<?php
}

function snfr_enqueue_styles_and_scripts() {
	wp_register_script( 'snfr-script', plugins_url( 'includes/js/script.js', __FILE__ ), 'jquery', '1.1', true );
	wp_enqueue_script( 'snfr-script' );

	wp_register_script( 'jquery-cookie', plugins_url( 'includes/js/jquery.cookie.js', __FILE__ ), 'jquery', '1.3.1', true );
	wp_enqueue_script( 'jquery-cookie' );

	wp_register_style( 'snfr-css', plugins_url( 'includes/css/style.css', __FILE__ ), false, '1.1' );
	wp_enqueue_style( 'snfr-css' );
}

function snfr_admin_print_scripts() {
	?>
	<script type="text/javascript">
		// Translatable strings for admin notices.
		var snfrMissingDate = '<?php _e( 'Please enter a start and end date', 'snfr' ); ?>';
		var snfrInvalidDateRante = '<?php _e( 'Start Date must be before End Date' , 'snfr' ); ?>';
		var snfrUnsavedChanges = '<?php _e( 'You have unsaved changes', 'snfr' ); ?>';
	</script>
	<?php
}

function snfr_admin_enqueue_scripts( $hook_suffix ) {
	if ( $hook_suffix != 'appearance_page_simple-notices-responsive' )
		return;

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wppush-simple-notices', plugins_url('includes/js/wppush-simple-notices-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );

	wp_register_style( 'snfr-admin-css', plugins_url( 'includes/css/admin-style.css', __FILE__), false, '1.1' );
	wp_enqueue_style( 'snfr-admin-css' );

	wp_register_style( 'snfr-css', plugins_url( 'includes/css/style.css', __FILE__ ), false, '1.1' );
	wp_enqueue_style( 'snfr-css' );
}

function snfr_notices_add_notices() {
	$options = get_option( 'wppush_notices_settings' );
	if ( $options['enabled'] == 'on' || ( $options['enabled'] == 'range' && time() > strtotime( $options['start_date'] . ' 00:00:00' ) && time() < strtotime( $options['end_date'] . ' 23:59:59' ) ) ) {
		?>
		<div id="snfr-notice" style="background-color: <?php echo $options['bg_color']; ?>; border-color: <?php echo $options['border_color']; ?>; display: none;">
			<span><?php echo $options['message']; ?></span><span id="snfr-close-button">X</span>
		</div>
		<?php
	}
}
