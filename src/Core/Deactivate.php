<?php
/**
 * Matador / Deactivator
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;
use matador\Admin_Notices;

class Deactivate {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 */
	public function __construct() {
		register_deactivation_hook( Matador::$file, array( __CLASS__, 'deactivate' ) );
	}

	/**
	 * Deactivate Routine
	 *
	 * @since 3.7.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'matador_sync' );
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