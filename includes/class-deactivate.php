<?php
/**
 * Matador / Deactivator
 *
 * @link        http://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017, Jeremy Scott, Paul Bearne
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Matador_Deactivator {

	/**
	 * Short Description.
	 *
	 * Long description.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'bullhorn_hourly_event' );
		self::remove_nonexpiring_transients();
		flush_rewrite_rules();
	}

	/**
	 * Remove Transients
	 *
	 * Some transients do not expire. For those that do not, we need to remove those manually.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function remove_nonexpiring_transients() {
		delete_transient( Admin_Notices::$transient_key );
		delete_transient( 'matador_upgrade_email' );
	}

}