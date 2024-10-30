<?php
/**
 * Matador Jobs - Developer Tools / Admin / Main
 *
 * Sets up the main page and menu that holds Matador Jobs Developer Tools.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.7
 *
 * @package     Matador Jobs Board
 * @subpackage  Developer
 * @author      Matador US, LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2023, Matador US, LP
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Developer;

use matador\Template_Support;

/**
 * Admin Main Page/Menu
 *
 * @since 3.8.7
 */
final class Main {

	public static string $menu_slug = 'matador-developer';

	/**
	 * Constructor
	 *
	 * @since 3.8.7
	 */
	function __construct() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
	}

	/**
	 * Registered Tools
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function tools() {

		$core_tools = [];

		/**
		 * @wordpress-filter `matador_admin_developer_tools`
		 *
		 * Allows extension developers to add tools to the Matador Developer submenu.
		 *
		 * @since 3.8.7
		 *
		 * @param array $registered_tools Array of fully-qualified Class names containing tools build off the
		 *                                Developer\Tool abstract.
		 * @return array
		 */
		return apply_filters( 'matador_admin_developer_tools', $core_tools );

		// @todo: perform a check the class exists and is an instance of the abstract?
	}

	/**
	 * Add Admin Menu
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function add_admin_menu() {

		// Check if we have Developer Tools
		if ( empty( self::tools() ) ) {

			return;
		}

		add_menu_page(
			__( 'Matador Developer Tools', 'matador-extension-developer' ),
			__( 'Matador Developer', 'matador-extension-developer' ),
			'manage_options',
			self::$menu_slug,
			[ __CLASS__, 'render_admin_page' ],
			'dashicons-admin-tools',
			19
		);
	}

	/**
	 * Render Page Content
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function render_admin_page() {
		Template_Support::get_template( 'developer-main.php', [], 'admin', '', true );
	}
}