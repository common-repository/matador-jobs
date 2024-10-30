<?php
/**
 * Matador / Options
 *
 * This contains the options page logic for the admin user of Matador.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0 some functions pulled out of class-settings.php
 * @since       3.1.0 functions promoted to own class.
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

class Options {

	/**
	 * Variable: Screen
	 *
	 * Saves the screen id of the Matador Settings screen
	 * @since 3.1.0
	 * @var string
	 */
	public $screen;

	/**
	 * Constructor
	 * @since 3.1.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
		add_action( 'current_screen', array( $this, 'save_settings' ) );
	}

	/**
	 * Add Settings Page
	 *
	 * Adds a menu item for Settings inside of Matador Jobs post type menu.
	 * @since 3.0.0
	 *
	 * @uses CLASS::settings_page_content()
	 * @uses WordPress add_submenu_page()
	 *
	 * @return void
	 */
	public function admin_menu() {
		/**
		 * Filter: Matador Settings Page Title
		 *
		 * Change the Matador Settings page title.
		 *
		 * @since 3.5.0
		 *
		 * @param string $title
		 * @return string
		 */
		$title = apply_filters( 'matador_settings_page_title', _x( 'Settings', 'Matador Settings Page Title', 'matador-jobs' ) );

		/**
		 * Filter: Matador Settings Page Menu Label
		 *
		 * Change the Matador Settings page title.
		 *
		 * @since 3.5.0
		 *
		 * @param string $title
		 * @return string
		 */
		$label = apply_filters( 'matador_settings_page_menu_label', _x( 'Settings', 'Matador Settings Page Menu Label', 'matador-jobs' ) );

		$this->screen = add_submenu_page(
			'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ), // Parent Page Slug
			esc_html( $title ), // Page Title
			esc_html( $label ), // Menu Title
			'manage_options', // User capability to see the page.
			Matador::variable( 'options_key' ), // Sub page slug
			array( __CLASS__, 'settings_page' ) //Callable function to output content for the page.
		);
	}

	/**
	 * Render Settings Page
	 *
	 * Calls the template to render the settings page.
	 *
	 * @since 3.0.0
	 *
	 * @uses CLASS::settings_page_content()
	 * @uses WordPress add_submenu_page()
	 *
	 * @return void
	 */
	public static function settings_page() {
		Template_Support::get_template( 'settings.php', null, '', true, true );
	}

	/**
	 * Save Settings Page
	 *
	 * @since 3.0.0
	 * @since 3.1.0 checks current screen prior to running to prevent conflicts
	 *
	 * @uses CLASS::settings_page_content()
	 * @uses WordPress add_submenu_page()
	 *
	 * @return void
	 */
	public function save_settings() {

		if ( get_current_screen()->id !== $this->screen ) {
			return;
		}

		if (
			isset( $_REQUEST[ Settings::$_key ] )
			&& isset( $_REQUEST['_wpnonce'] )
			&& check_admin_referer( Matador::variable( 'options', 'nonce' ) )
		) {
			Matador::$settings->update( $_REQUEST[ Settings::$_key ] );
		}
	}

	/**
	 * Form Field Constructor
	 *
	 * @since 3.0.0
	 *
	 * @uses CLASS::settings_page_content()
	 * @uses WordPress add_submenu_page()
	 *
	 * @return false/array
	 */
	public static function form_field_args( $args, $field ) {

		// Passed argument must be an array.
		if ( ! is_array( $args ) || ! $field ) {

			return false;
		}

		// If this is a credential, modify the settings field.
		if ( defined( strtoupper( 'matador_' . $field ) ) ) {
			$args['type']        = 'text';
			$args['attributes']  = array(
				'disabled'    => true,
				'placeholder' => __( 'View/Change in wp-config.php', 'matador-jobs' ),
			);
		}

		// Parse Args
		list( $args, $template ) = Helper::form_field_args( $args, $field );

		// Update Field Name With Settings Key Prefix
		$args['name'] = Matador::variable( 'options_key' ) . '[' . $field . ']';

		// Check if an Error Exists & Assign it.
		$errors = get_transient( Matador::variable( 'settings_fields_errors', 'transients' ) );
		$args['error'] = isset( $errors[ $field ] ) ? esc_html( $errors[ $field ] ) : null;

		if ( defined( strtoupper( 'matador_' . $field ) ) ) {
			$args['value'] = '';
		} elseif ( null !== Matador::setting( $field ) ) {
			$args['value'] = wp_unslash( Matador::setting( $field ) );
		} else {
			$args['value'] = $args['default'];
		}

		return array( $args, $template );
	}

}
