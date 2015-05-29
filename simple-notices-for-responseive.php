<?php
/*
Plugin Name: Simple Notices for Responsive
Plugin URL: http://filament-studios.com
Description: Add a notice to the responsive theme, between the nav and content
Version: 1.3
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
		$this->includes();
		$this->actions();

	}

	public static function getInstance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Simple_Notices_for_Responsive ) ) {
			self::$instance = new Simple_Notices_for_Responsive;
		}

		return self::$instance;

	}

	private function setup_constants() {
		define( 'SNFR_VERSION', '1.3' );
		define( 'SNFR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'SNFR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'SNFR_PLUGIN_NAME', 'Simple Notices for Responsive' );
		define( 'SNFR_STORE_URL', 'https://filament-studios.com' );
	}

	private function includes() {
		require SNFR_PLUGIN_DIR . 'includes/EDD_SL_Plugin_Updater.php';
	}

	private function actions() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'settings_menu' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_action( 'admin_init', array( $this, 'plugin_updater' ) );
			add_action( 'admin_init', array( $this, 'activate_license' ) );
			add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'enqueue_styles_and_scripts' ) );
		add_action( 'responsive_header_end', array( $this, 'add_notices' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'snfr', false, '/simple-notices-for-responsive/languages/' );
	}

	public function register_settings() {
		// Whitelist options
		register_setting( 'snfr-notices-settings', 'simple_notices_settings' );
		register_setting( 'snfr-notices-settings', '_snfr_license_key' );
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

			<?php
			$license = get_option( '_snfr_license_key' );
			$status  = get_option( '_snfr_license_key_status' );
			?>
			<tr valign="top">
				<th scope="row" valign="top">
					<?php _e( 'License Key', 'ppp-txt' ); ?>
				</th>
				<td>
					<input id="snfr_license_key" name="_snfr_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" /><?php if( $status !== false && $status == 'valid' ) { ?>
					<span style="color:green;">&nbsp;<?php _e( 'active', 'snfr' ); ?></span><?php } ?>
				</td>
			</tr>

			<?php if( false !== $license ) { ?>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Activate License', 'ppp-txt' ); ?>
					</th>
					<td>
						<?php if( $status !== false && $status == 'valid' ) { ?>
							<?php wp_nonce_field( 'snfr_deactivate_nonce', 'snfr_deactivate_nonce' ); ?>
							<input type="submit" class="button-secondary" name="snfr_license_deactivate" value="<?php _e( 'Deactivate License', 'snfr' ); ?>"/>
						<?php } else {
							wp_nonce_field( 'snfr_activate_nonce', 'snfr_activate_nonce' ); ?>
							<input type="submit" class="button-secondary" name="snfr_license_activate" value="<?php _e( 'Activate License', 'snfr' ); ?>"/>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>

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


	/**
	 * Handle Software Licensing
	 */

	/**
	 * Sets up the EDD SL Plugin updated class
	 * @return void
	 */
	public function plugin_updater() {

		$license_key = trim( get_option( '_snfr_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( SNFR_STORE_URL, __FILE__, array(
				'version'   => SNFR_VERSION,         // current version number
				'license'   => $license_key,        // license key (used get_option above to retrieve from DB)
				'item_name' => SNFR_PLUGIN_NAME,     // name of this plugin
				'author'    => 'Filament Studios'  // author of this plugin
			)
		);
	}

	/**
	 * Deactivates the license key
	 * @return void
	 */
	public function deactivate_license() {
		// listen for our activate button to be clicked
		if( isset( $_POST['snfr_license_deactivate'] ) ) {

			// run a quick security check
			if( ! check_admin_referer( 'snfr_deactivate_nonce', 'snfr_deactivate_nonce' ) ) {
				return;
			}
			// get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( '_snfr_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( SNFR_PLUGIN_NAME ) // the name of our product in EDD
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, SNFR_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( '_snfr_license_key_status' );
			}

		}
	}

	/**
	 * Activates the license key provided
	 * @return void
	 */
	public function activate_license() {
		// listen for our activate button to be clicked
		if( isset( $_POST['snfr_license_activate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'snfr_activate_nonce', 'snfr_activate_nonce' ) ) {
		 		return;
		 	}
		 	// get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( '_snfr_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license'   => $license,
				'item_name' => urlencode( SNFR_PLUGIN_NAME ) // the name of our product in EDD
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, SNFR_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "active" or "inactive"

			update_option( '_snfr_license_key_status', $license_data->license );

		}
	}

	/**
	 * Sanatize the liscense key being provided
	 * @param  string $new The License key provided
	 * @return string      Sanitized license key
	 */
	public function snfr_sanitize_license( $new ) {
		$old = get_option( '_snfr_license_key' );
		if( $old && $old != $new ) {
			delete_option( '_snfr_license_key_status' ); // new license has been entered, so must reactivate
		}
		return $new;
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



