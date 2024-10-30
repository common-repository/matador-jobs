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
final class DeveloperTools {

	/**
	 * Menu Slug
	 *
	 * @var string
	 */
	const SLUG = 'matador-developer';

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
	// public static function tools() : void {

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
		$tools = apply_filters( 'matador_admin_developer_tools', $core_tools );

		$to_sort = [];

		// Remove any tools that don't have a class, add the tool name and menu position to an array for sorting
		foreach ( $tools as $tool => $props ) {
			if ( ! class_exists( $props['class'] ) ) {
				unset( $tools[ $tool ] );
			}
			$to_sort[ $tool ] = $props['position'];
		}

		// sort the array by menu position
		asort( $to_sort );

		$sorted = [];

		// rebuild the output array by the sorting
		foreach ( $to_sort as $tool => $order ) {
			$sorted[ $tool ] = $tools[ $tool ];
		}

		return $sorted;
	}

	/**
	 * Add Admin Menu
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function add_admin_menu() {
	// public static function add_admin_menu() : void {

		// Check if we have Developer Tools
		if ( empty( self::tools() ) ) {

			return;
		}

		add_menu_page(
			__( 'Matador Developer Tools', 'matador-extension-developer' ),
			__( 'Matador Developer', 'matador-extension-developer' ),
			'manage_options',
			self::SLUG,
			[ __CLASS__, 'render_admin_page' ],
			'dashicons-admin-tools',
			19
		);

		foreach ( self::tools() as $tool ) {
			add_submenu_page(
				self::SLUG,
				$tool['name'],
				$tool['menu_name'],
				'manage_options',
				self::SLUG . '-' . $tool['slug'],
				[ $tool['class'], 'render' ],
				$tool['position']
			);
		}
	}

	/**
	 * Render Page Content
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function render_admin_page() {
	// public static function render_admin_page() : void {
		Template_Support::get_template( 'developer-main.php', [], 'admin', '', true );
	}
}