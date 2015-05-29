<?php
/*
Plugin Name: Simple Notices for Responsive
Plugin URL: http://filament-studios.com
Description: Add a notice to the responsive theme, between the nav and content
Version: 1.2
Author: Chris Klosowski
Author URI: https://filament-studios.com
Contributors: cklosows
*/

define( 'SNFR_DEFAULT_BORDER'    , '#d4af37' );
define( 'SNFR_DEFAULT_BACKGROUND', '#fcf4cb' );
define( 'SNFR_DEFAULT_TEXT'      , '#000000' );

if ( ! class_exists( 'Simple_Notices_for_Responsive' ) ) {

class Simple_Notices_for_Responsive {

	private static $instance;

	private function __construct() {

		$this->setup_constants();
		$this->actions();

	}

	public static function getInstance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Simple_Notices_for_Responsive ) ) {
			self::$instance = new Simple_Notices_for_Responsive;
		}

		return self::$instance;

	}

	private function setup_constants() {
		define( 'SNFR_VERSION', '1.2' );
		define( 'SNFR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	private function actions() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'settings_menu' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'enqueue_styles_and_scripts' ) );
		add_action( 'responsive_header_end', array( $this, 'add_notices' ) );
	}

	public function load_textdomain() {

	}

	public function register_settings() {
		// Whitelist options
		register_setting( 'snfr-notices-settings', 'simple_notices_settings' );
	}

	public function settings_menu() {
		add_theme_page( __( 'Simple Notices', 'snfr' ), __( 'Simple Notices', 'snfr' ), 'administrator', 'simple-notices-responsive', array( $this, 'simple_notice_admin' ) );
	}

	public function simple_notice_admin() {
		?>
		<div class="wrap">
		<div id="icon-themes" class="icon32"></div><h2><?php _e( 'Simple Notices for Responsive:', 'snfr' ); ?></h2>
		<form method="post" action="options.php">
		  <?php wp_nonce_field( 'snfr-notices-settings' ); ?>
		  <table class="form-table">
			<tr valign="top">
			  <th scope="row"><?php _e( 'Display:', 'snfr' ); ?></th>
			  <td>
				<select id="onoffswitch" name="simple_notices_settings[enabled]">
					<?php $enabled = $this->get_option( 'enabled', 'off' ); ?>
					<option value="on" <?php selected( $enabled, 'on' ); ?>><?php _e( 'On', 'snfr' ); ?></option>
					<option value="off" <?php selected( $enabled, 'off' ); ?>><?php _e( 'Off', 'snfr' ); ?></option>
					<option value="range" <?php selected( $enabled, 'range' ); ?>><?php _e( 'Date Range', 'snfr' ); ?></option>
				</select>
			  </td>
			</tr>

			<tr valign="top">
			  <th scope="row"><?php _e( 'Start Date:', 'snfr' ); ?></th>
			  <td>
				<?php
					$start_date = $this->get_option( 'start_date', '' );
					$end_date   = $this->get_option( 'end_date', '' );
				?>
				<input <?php echo ( $enabled != 'range' ) ? 'disabled="disabled"' : ''; ?> type="text" value="<?php echo $start_date; ?>" id="start-date" name="simple_notices_settings[start_date]" class="snfr-date-field" />
			  </td>
			</tr>

			<tr valign="top">
			  <th scope="row"><?php _e( 'End Date:', 'snfr' ); ?></th>
			  <td>
				<input <?php echo ( $enabled != 'range' ) ? 'disabled="disabled"' : ''; ?> type="text" value="<?php echo $end_date; ?>" id="end-date" name="simple_notices_settings[end_date]" class="snfr-date-field" />
			  </td>
			</tr>

			<tr valign="top">
			  <th scope="row"><?php _e( 'Message:', 'snfr' ); ?></th>
			  <td>
				<?php $message = $this->get_option( 'message', '' ); ?>
				<textarea cols="100" id="message" rows="5" name="simple_notices_settings[message]"><?php echo $message; ?></textarea>
			  </td>
			</tr>

			<?php
				$border_color = $this->get_option( 'border_color', SNFR_DEFAULT_BORDER );
				$bg_color     = $this->get_option( 'bg_color', SNFR_DEFAULT_BACKGROUND );
				$text_color   = $this->get_option( 'text_color', SNFR_DEFAULT_TEXT );
			?>

			<tr valign="top">
			  <th scope="row"><?php _e( 'Border Color:', 'snfr' ); ?></th>
			  <td id="border-color-wrapper">
				<input type="text" value="<?php echo $border_color; ?>" id="border-color" name="simple_notices_settings[border_color]" class="snfr-color-field" data-default-color="#d4af37" />
			  </td>
			</tr>

			<tr valign="top">
			  <th scope="row"><?php _e( 'Background Color:', 'snfr' ); ?></th>
			  <td id="bg-color-wrapper">
				<input type="text" value="<?php echo $bg_color; ?>" id="background-color" name="simple_notices_settings[bg_color]" class="snfr-color-field" data-default-color="#fcf4cb" />
			  </td>
			</tr>

			<tr valign="top">
			  <th scope="row"><?php _e( 'Text Color:', 'snfr' ); ?></th>
			  <td id="bg-color-wrapper">
				<input type="text" value="<?php echo $text_color; ?>" id="color" name="simple_notices_settings[text_color]" class="snfr-color-field" data-default-color="#000000" />
			  </td>
			</tr>

		  <input type="hidden" name="action" value="update" />
		  <input type="hidden" name="page_options" value="simple_notices_settings" />
		  </table>

		  <p class="submit">
		  <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
		  </p>
		  <?php settings_fields( 'snfr-notices-settings' ); ?>
		</form>

		<div id="preview-wrapper" <?php echo empty( $message ) ? 'style="display: none"' : ''; ?>>
			<h3><?php _e( 'Preview:', 'snfr' ); ?></h3>
			<div id="preview-message" style="color: <?php echo $text_color; ?>; background-color: <?php echo $bg_color; ?>; border-color: <?php echo $border_color; ?>;">
				<span id="message-text"><?php echo $message; ?></span>
			</div>
		</div>

		</div>
		<?php
	}

	public function enqueue_styles_and_scripts() {
		wp_register_script( 'snfr-script', SNFR_PLUGIN_URL . 'includes/js/script.js', 'jquery', SNFR_VERSION, true );
		wp_enqueue_script( 'snfr-script' );

		wp_register_script( 'jquery-cookie', SNFR_PLUGIN_URL . 'includes/js/jquery.cookie.js', 'jquery', '1.3.1', true );
		wp_enqueue_script( 'jquery-cookie' );

		wp_register_style( 'snfr-css', SNFR_PLUGIN_URL . 'includes/css/style.css', false, SNFR_VERSION );
		wp_enqueue_style( 'snfr-css' );
	}

	public function admin_print_scripts() {
		?>
		<script type="text/javascript">
			// Translatable strings for admin notices.
			var snfrMissingDate = '<?php _e( 'Please enter a start and end date', 'snfr' ); ?>';
			var snfrInvalidDateRante = '<?php _e( 'Start Date must be before End Date' , 'snfr' ); ?>';
			var snfrUnsavedChanges = '<?php _e( 'You have unsaved changes', 'snfr' ); ?>';
		</script>
		<?php
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( $hook_suffix != 'appearance_page_simple-notices-responsive' ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'simple-notices', SNFR_PLUGIN_URL . 'includes/js/simple-notices-script.js', array( 'wp-color-picker' ), false, true );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );

		wp_register_style( 'snfr-admin-css', SNFR_PLUGIN_URL . 'includes/css/admin-style.css', false, SNFR_VERSION );
		wp_enqueue_style( 'snfr-admin-css' );

		wp_register_style( 'snfr-css', SNFR_PLUGIN_URL . 'includes/css/style.css', false, SNFR_VERSION );
		wp_enqueue_style( 'snfr-css' );
	}

	public function add_notices() {
		$enabled    = $this->get_option( 'enabled', 'off' );
		$start_date = $this->get_option( 'start_date', '' );
		$end_date   = $this->get_option( 'end_date', '' );
		$message    = $this->get_option( 'message', '' );
		if ( ! empty( $message ) && ( $enabled == 'on' || ( $enabled == 'range' && time() > strtotime( $start_date . ' 00:00:00' ) && time() < strtotime( $end_date . ' 23:59:59' ) ) ) ) {
			$border_color = $this->get_option( 'border_color', SNFR_DEFAULT_BORDER );
			$bg_color     = $this->get_option( 'bg_color', SNFR_DEFAULT_BACKGROUND );
			$text_color   = $this->get_option( 'text_color', SNFR_DEFAULT_TEXT );
			?>
			<div id="snfr-notice" style="color: <?php echo $text_color; ?>; background-color: <?php echo $bg_color; ?>; border-color: <?php echo $border_color; ?>; display: none;">
				<span><?php echo $this->get_option( 'message', '' ); ?></span><span id="snfr-close-button">&times;</span>
			</div>
			<?php
		}
	}

	public function get_option( $option = false, $default = '' ) {
		if ( empty( $option ) ) {
			return false;
		}

		$options = get_option( 'simple_notices_settings' );

		$return = ! empty( $options[ $option ] ) ? $options[ $option ] : $default;

		return $return;
	}

}


} // end class exists

function snfr_load_plugin() {
	$snfr_plugin = Simple_Notices_for_Responsive::getInstance();
}
add_action( 'plugins_loaded', 'snfr_load_plugin' );

function snfr_activation() {
	$default_settings = array(
		'enabled'      => 'off',
		'message'      => '',
		'border_color' => SNFR_DEFAULT_BORDER,
		'bg_color'     => SNFR_DEFAULT_BACKGROUND,
		'text_color'   => SNFR_DEFAULT_TEXT,
	);

	update_option( 'simple_notices_settings', $default_settings );
}
register_activation_hook( __FILE__, 'snfr_activation' );



