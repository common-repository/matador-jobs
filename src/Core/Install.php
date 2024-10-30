<?php
/**
 * Matador / Install
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0 (parts since 3.0.0 as matador\Activate)
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador US LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2022 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;
use matador\Admin_Notices;
use matador\Settings_Fields;

final class Install {

	public function __construct() {

		if ( true !== (bool) get_option( 'matador_activated' ) ) {
			return;
		}

		self::send_analytics_report();

		self::create_directories();

		self::defaults();

		self::downgrade();

		// We don't want a daily check in for the first day
		add_option( 'matador_ET_last_phone_home', time() + DAY_IN_SECONDS  );

		set_transient( Matador::variable( 'flush_rewrite_rules', 'transients' ), true, 30 );

		delete_option( 'matador_activated' );
	}

	/**
	 * Creates the Uploads Directories
	 *
	 * @since 3.0.0
	 * @since 3.1.0 fires an Admin Notice now, instead of a die()
	 * @since 3.4.0 Now adds empty index.php files to improve security should Apache directory indexes be turned on.
	 * @since 3.8.15 Upgraded to public function.
	 */
	public static function create_directories() {

		$directory = Matador::variable( 'uploads_cv_dir' );

		if ( ! file_exists( $directory ) && ! wp_mkdir_p( $directory ) ) {
			Admin_Notices::add( __( 'Matador was unable to make the folders where it stores file uploads. Please create the folder <kbd>wp-content/matador_uploads</kbd> and set its permissions to <kbd>644</kbd>', 'matador-jobs' ), 'error', 'matador-folder-create-failed' );
		}

		$index_file = $directory . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'log_file_path' ) . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'json_file_path' ) . '/index.php';
		touch( $index_file );
	}

	/**
	 * Setups Default Settings
	 *
	 * @since    3.0.0
	 */
	private static function defaults() {

		if ( ! Matador::$settings->has_settings() ) {
			$settings = array();
			$fields   = Settings_Fields::instance()->get_just_fields();
			foreach ( $fields as $field => $args ) {
				if ( ! empty( $args['default'] ) ) {
					$settings[ $field ] = $args['default'];
				}
			}
			Matador::$settings->update( $settings );
			Matador::reset();
			Matador::setting( 'matador_version', Matador::VERSION );
		}
	}

	/**
	 * Downgrades a Pro User to a Lite User
	 *
	 * @since 3.1.0
	 */
	public static function downgrade() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'matador-jobs/matador-jobs.php' ) ) {
			return;
		}

		if ( is_plugin_active( 'matador-jobs-pro/matador.php' ) ) {
			return;
		}

		$downgrade_these = array( 'notify_admin', 'logging', 'jsonld_hiring_organization', 'jsonld_salary', 'applications_accept' );
		$settings        = array();

		if ( Matador::$settings->has_settings() ) {
			$fields = Settings_Fields::instance()->get_just_fields();
			foreach ( $fields as $field => $args ) {
				if ( in_array( $field, $downgrade_these, true ) ) {
					if (
						'logging' === $field
						&&
						in_array( Matador::setting( 'logger' ), array( '0', '1', '2' ), true )
					) {
						continue;
					}

					if ( ! empty( $args['default'] ) ) {
						$settings[ $field ] = $args['default'];
					} else {
						$settings[ $field ] = null;
					}
				}
			}
			Matador::$settings->update( $settings );
			Matador::reset();
		}
	}

	/**
	 * Send Analytics Activation Report
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private static function send_analytics_report() {

		if ( ! Matador::$settings->has_settings() ) {
			Analytics::event( 'Instance Installed', [] );
		}

		Analytics::event( 'Instance Activated', [] );
	}
}