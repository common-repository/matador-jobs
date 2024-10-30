<?php
/**
 * Matador / Bullhorn Connection Assistant
 *
 * This contains the functions that adds a page called Bullhorn Connection Assistant,
 * where users will configure Bullhorn and manage the connection.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bullhorn_Connection_Assistant {

	/**
	 * Variable: Screen
	 *
	 * Saves the screen id of the Matador Settings screen
	 *
	 * @since 3.1.0
	 *
	 * @var string
	 */
	private $screen;

	/**
	 * Variable: Page Key
	 *
	 * Stores the page key (name) of the page
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private static $page_key = 'connect-to-bullhorn';

	/**
	 * Class Constructor
	 *
	 * Class constructor sets up the class and hooks into WP.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_head', [ $this, 'hide_admin_page' ] );
		add_filter( 'admin_title', array( $this, 'admin_page_title' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'admin_page_actions' ), 10, 2 );
	}

	/**
	 * Add Admin Page
	 *
	 * Hooks into WordPress and registers the admin page, but
	 * hides the menu item when not active.
	 *
	 * @uses add_submenu_page()
	 *
	 * @since 3.0.0
	 * @since 3.1.0 assigned add_admin_page to $screen
	 *
	 * @return void
	 */
	public function add_admin_page() {
		$this->screen = add_submenu_page(
			'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ), // Parent Page Slug
			esc_html_x( 'Bullhorn Connection Assistant', 'Matador Bullhorn Connection Assistant Admin Page Title', 'matador-jobs' ), // Page Title
			esc_html_x( 'Bullhorn Connection Assistant', 'Matador Bullhorn Connection Assistant Admin Page Title', 'matador-jobs' ), // Menu Title
			'manage_options', // User capability to see the page.
			self::$page_key, // Sub page slug
			array( $this, 'admin_page_content' ) // Callable function to output content for the page.
		);
	}

	/**
	 * Hide Admin Page
	 *
	 * Hooks into WordPress and registers the admin page, but
	 * hides the menu item when not active.
	 *
	 * @see https://core.trac.wordpress.org/ticket/18850#comment:11
	 * @uses remove_submenu_page()
	 *
	 * @since 3.9.13
	 *
	 * @return void
	 */
	public function hide_admin_page() {
		// Despite the name, doesn't unregister the page, but hides it from the menu
		remove_submenu_page(
			'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ), // Parent Page Slug
			self::$page_key // Sub page slug
		);
	}

	/**
	 * Admin Title
	 *
	 * Filters the WordPress admin page title.
	 *
	 * @since 3.0.0
	 *
	 * @var string $admin_title
	 *
	 * @return string
	 */
	public function admin_page_title( $admin_title ) {

		if ( get_current_screen()->id !== $this->screen ) {
			return $admin_title;
		}

		return __( 'Bullhorn API Connection Assistant', 'matador-jobs' ) . $admin_title;
	}

	/**
	 * Admin Page Actions
	 *
	 * Handles user actions, via POST requests.
	 *
	 * @since 3.0.0
	 */
	public function admin_page_actions() {

		if ( get_current_screen()->id !== $this->screen ) {
			return;
		}

		$nonce = Matador::variable( 'bh-api-assistant', 'nonce' );

		if ( isset( $_POST[ $nonce ] ) && check_admin_referer( $nonce, $nonce ) ) {

			if ( isset( $_REQUEST['matador-settings']['bullhorn_api_assistant'] ) ) {
				$progress = $_REQUEST['matador-settings']['bullhorn_api_assistant'];
				unset( $_REQUEST['matador-settings']['bullhorn_api_assistant'] );
			}

			// Update Settings, if any.
			if ( ! empty( $_REQUEST['matador-settings'] ) ) {
				Matador::$settings->update( $_REQUEST['matador-settings'] );
			}

			// Determine if user wants to exit.
			if ( isset( $_REQUEST['exit'] ) ) {
				$redirect = Matador::variable( 'options_url' );
				wp_safe_redirect( isset( $redirect ) ? $redirect : $this->get_url() );
			}

			$errors = get_transient( Matador::variable( 'settings_fields_errors', 'transients' ) );

			// Unset the API Progress When There Is An Error
			if ( isset( $errors ) && isset( $errors['bullhorn_api_client'] ) ) {
				unset( $progress );
			}

			// Set Progress
			if ( isset( $progress ) ) {
				Matador::setting( 'bullhorn_api_assistant', $progress );
			}

			// Check if an action was called.
			if ( ! empty( $_REQUEST['matador-action'] ) ) {
				switch ( $_REQUEST['matador-action'] ) {
					case 'authorize':
						flush_rewrite_rules();
						Admin_Tasks::bullhorn_authorize();
						break;
					case 'deauthorize':
						Admin_Tasks::bullhorn_deauthorize();
						break;
					case 'test-reconnect':
						Admin_Tasks::break_connection();
						Admin_Tasks::attempt_login();
						break;
					case 'reset-assistant':
						Admin_Tasks::reset_assistant();
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Admin Page Content
	 *
	 * Callback to provide content for the admin page.
	 *
	 * @uses Template_Support::get_template()
	 *
	 * @since 3.0.0
	 */
	public function admin_page_content() {
		Template_Support::get_template( 'bullhorn-connection-assistant.php', array( 'progress' => $this->get_progress() ), '', true, true );
	}

	/**
	 * Get Assistant Progress
	 *
	 * Helper that gets the current progress (step) of the
	 * connection assistant.
	 *
	 * @since 3.0.0
	 */
	public function get_progress() {

		$saved_step = Matador::setting( 'bullhorn_api_assistant' );

		if ( $saved_step ) {
			return $saved_step;
		}

		return 'prepare';
	}

	/**
	 * Get Assistant Progress
	 *
	 * Helper that gets the current progress (step) of the
	 * connection assistant.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public static function get_progress_steps() {
		return array( 'prepare', 'prepare-forgot', 'prepare-get', 'authorize', 'callback', 'complete', 'credentials' );
	}

	/**
	 * Get Url
	 *
	 * Helper that gets the current progress (step) of the
	 * connection assistant.
	 *
	 * @since 3.0.0
	 */
	public static function get_url() {
		return get_admin_url() . 'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ) . '&page=' . self::$page_key;
	}

}
