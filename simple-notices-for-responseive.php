<?php
/*
Plugin Name: Simple Notices for Responsive
Plugin URL: http://wp-push.com
Description: Add a notice to the responsive theme, between the nav and content
Version: 1.1
Author: Chris Klosowski
Author URI: https://wp-push.com
Contributors: cklosows
*/


if ( is_admin() ) {
	add_action( 'admin_init', 'wppush_notices_register_settings' );
	add_action( 'admin_menu', 'wppush_notices_settings_menu' );
	add_action( 'admin_enqueue_scripts', 'wppush_enqueue_color_picker' );
} else {
	add_action( 'responsive_header_end', 'wppush_notices_add_notices' );
}

function wppush_notices_register_settings() {
	// Whitelist options
	register_setting( 'wppush-notices-settings', 'wppush_notices_settings' );
}

function wppush_notices_settings_menu() {
	add_theme_page( 'Simple Notices', 'Simple Notices', 'administrator', 'simple-notices-responsive', 'wppush_simple_notice_admin' );
}

function wppush_simple_notice_admin() {
	$current_settings = get_option( 'wppush_notices_settings' );
	?>
	<div class="wrap">
	<div id="icon-themes" class="icon32"></div><h2>Simple Notices for Responsive</h2>
	<form method="post" action="options.php">
	  <?php wp_nonce_field( 'wppush-notices-settings' ); ?>
	  <table class="form-table">
		<tr valign="top">
		  <th scope="row">Display?</th>
		  <td>
		  	<select id="onoffswitch" name="wppush_notices_settings[enabled]">
		  		<option value="on" <?php selected( $current_settings['enabled'], 'on' ); ?>>On</option>
		  		<option value="off" <?php selected( $current_settings['enabled'], 'off' ); ?>>Off</option>
		  	</select>
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row">Message:</th>
		  <td>
		  	<textarea cols="100" id="message" rows="5" name="wppush_notices_settings[message]"><?php echo ( isset( $current_settings['message'] ) ) ? $current_settings['message'] : ''; ?></textarea>
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row">Border Color:</th>
		  <td id="border-color-wrapper">
		  	<input type="text" value="<?php echo $current_settings['border_color']; ?>" id="border-color" value="" name="wppush_notices_settings[border_color]" class="my-color-field" data-default-color="#d4af37" />
		  </td>
		</tr>

		<tr valign="top">
		  <th scope="row">Background Color:</th>
		  <td id="bg-color-wrapper">
		  	<input type="text" value="<?php echo $current_settings['bg_color']; ?>" id="background-color" value="" name="wppush_notices_settings[bg_color]" class="my-color-field" data-default-color="#fcf4cb" />
		  </td>
		</tr>

	  <input type="hidden" name="action" value="update" />
	  <input type="hidden" name="page_options" value="wppush_notices_settings" />
	  </table>

	  <p class="submit">
	  <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
	  </p>
	  <?php settings_fields( 'wppush-notices-settings' ); ?>
	</form>

	<div id="preview-wrapper" <?php echo ( !isset( $current_settings['message'] ) || empty( $current_settings['message'] ) ) ? 'style="display: none"' : ''; ?>>
		<h3>Preview:</h3>
		<div id="preview-message" style="color: #333; font-size: 13px; margin: 15px auto 0 auto; width: 95%; background-color: <?php echo $current_settings['bg_color']; ?>; border: 1px solid <?php echo $current_settings['border_color']; ?>; height: auto; border-radius: 8px; -moz-border-radius: 8px; -webkit-border-radius: 8px; padding: 10px 15px;">
			<span id="message-text"><?php echo $current_settings['message']; ?></span>
		</div>
	</div>

	</div>
	<?php
}

function wppush_enqueue_color_picker( $hook_suffix ) {
	// first check that $hook_suffix is appropriate for your admin page
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wppush-simple-notices', plugins_url('wppush-simple-notices-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

function wppush_notices_add_notices() {
	$options = get_option( 'wppush_notices_settings' );
	if ( $options['enabled'] == 'on' ) {
		?>
		<div style="color: #333; font-size: 13px; margin: 15px auto 0 auto; width: 95%; background-color: <?php echo $options['bg_color']; ?>; border: 1px solid <?php echo $options['border_color']; ?>; height: auto; border-radius: 8px; -moz-border-radius: 8px; -webkit-border-radius: 8px; padding: 10px 15px;">
			<span><?php echo $options['message']; ?></span>
		</div>
		<?php
	}
}
