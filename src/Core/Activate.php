<?php
/**
 * Matador / Activate
 *
 * New Activator. Former matador\Activate was moved to matador\Update.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador US LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;

final class Activate {

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 */
	public function __construct() {
		register_activation_hook( Matador::$file, array( __CLASS__, 'activate' ) );
	}

	/**
	 * Plugin Activate
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function activate() {

		self::check_compatibility( '4.7', '5.6' );

		Install::create_directories();

		update_option( 'matador_activated', true );
	}

	/**
	 * Check Compatibility
	 *
	 * Compares version numbers for current and minimum required PHP and WP.
	 *
	 * @since    3.0.0
	 *
	 * @param float $wp  WordPress minimum Version Number
	 * @param float $php PHP minimum Version Number
	 *
	 * @return void
	 */
	private static function check_compatibility( $wp, $php ) {

		global $wp_version;

		$flags = null;

		// Checks for PHP version
		if ( version_compare( PHP_VERSION, $php, '<' ) ) {
			$flags['PHP'] = $php;
		}

		// Checks for WP version
		if ( version_compare( $wp_version, $wp, '<' ) ) {
			$flags['WordPress'] = $wp;
		}

		// If there is a flag, return an error.

		if ( $flags ) {

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'] ) && isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$message = null;
			$i       = 1;
			$errors  = count( $flags );

			foreach ( $flags as $requirement => $version ) {
				// translators: %1$s is requirement, %2$s is version
				$message .= sprintf( esc_html__( '%1$s version %2$s or greater', 'matador-jobs' ), $requirement, $version );

				// If the list has more than one item, add separators
				if ( $i < $flags ) {

					if ( ( $errors > 2 ) && ( $i < $errors ) ) {
						$message .= ', ';
					}

					if ( ( $errors - 1 ) === $i ) {
						$message .= esc_html__( 'and', 'matador-jobs' ) . ' ';
					}
				}

				$i ++;
			}

			deactivate_plugins( Matador::$file );
			// translators: %s is the missing PHP / WP version
			wp_die( sprintf( esc_html__( 'We\'re sorry, but Matador Jobs Board plugin requires %s. Please update your system.', 'matador-jobs' ), esc_html( $message ) ) );
		}
	}
}