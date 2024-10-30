<?php
/**
 * Matador / Developer / Bullhorn API Debugger (Deprecated)
 *
 * Prior to Matador Jobs 3.8.7, a Developer Bullhorn API Debug tool was included as a hidden admin screen. For security
 * and to, improve performance mitigate security risks, this was removed in favor of an opt-in plugin. This class was
 * created to act as a placeholder for this feature so users of it are made aware of the new plugin.
 *
 * This class is deprecated and may be removed in future versions of Matador Jobs.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.7
 * @deprecated  3.8.7
 *
 * @package     Matador Jobs Board
 * @subpackage  Developer
 * @author      Matador US, LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2023, Matador US, LP
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Developer;

/**
 * Class Developer Tools (Deprecated)
 *
 * @since 3.8.7
 * @deprecated 3.8.7
 */
final class BullhornAPIDebugger {

	/**
	 * Constructor
	 *
	 * @since 3.8.7
	 */
	function __construct() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_page' ] );
	}

	/**
	 * Add Placeholder Admin Page
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function add_admin_page() {
		add_submenu_page(
			'', // empty string disconnects the page from an actual menu
			'Matador Bullhorn API Debug Tool',
			'', // Blank, as we are not using menu
			'manage_options',
			'matador_api_debug',
			[ __CLASS__, 'render_admin_page' ]
		);
	}

	/**
	 * Render Placeholder Page Content
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Debug the Bullhorn API', 'matador-jobs' ); ?></h1>
			<p>
				<em>
					<?php esc_html_e( 'The "Debug the Bullhorn API" feature is disabled in core Matador Jobs and Matador Jobs Pro as of version 3.8.7.', 'matador-jobs' ); ?>
					<?php esc_html_e( 'The feature, while useful, caused performance issues and made available advanced calls to the Bullhorn API without providing guard rails to protect users from making potentially irreversible actions on their live data.', 'matador-jobs' ); ?>
					<?php esc_html_e( 'The tool is still available to advanced users, for use at their own risk, as an extension plugin available on the Matador Jobs website.', 'matador-jobs' ); ?>
				</em>
			</p>
		</div>
		<?php
	}
}