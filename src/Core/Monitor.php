<?php
/**
 * Matador / Monitor
 *
 * This class will hold routines that are run by Matador to monitor the health of the plugin's operation.
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

use matador\Admin_Notices;
use matador\Helper;
use matador\Logger;
use matador\Matador;
use matador\MatadorJobs\Email\AdminNoticeCronErrorMessage;

class Monitor {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 */
	public function __construct() {
		add_action( 'init', [ __CLASS__, 'check_cron_event' ] );
		add_action( 'init', [ __CLASS__, 'check_domain' ] );
	}

	/**
	 * Monitor Cron
	 *
	 * @see https://webheadcoder.com/ for their awesome WP Cron monitor plugin, upon which some of this code is based
	 *      off of.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $force Default false
	 *
	 * @return array|void
	 */
	public static function cron( $force = false ) {

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return [ 'info' => esc_html__( 'Matador Jobs did not check WP Cron as WP Cron is currently running.', 'matador-jobs' ) ];
		}

		if ( apply_filters( 'matador_monitor_cron_using_system_cron', false ) ) {
			return [ 'info' => esc_html__( 'Matador Jobs did not check WP Cron as your server is set to use system cron tasks.', 'matador-jobs' ) ];
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return [ 'warning' => esc_html__( 'The `DISABLE_WP_CRON` constant is set to true. WP-Cron is disabled and will not run on it\'s own.', 'matador-jobs' ) ];
		}

		if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
			return [ 'warning' => esc_html__( 'The `ALTERNATE_WP_CRON` constant is set to true. This plugin cannot determine the status of your WP-Cron system.', 'matador-jobs' ) ];
		}

		$transient = Matador::variable( 'monitor_cron_last_test', 'transients' );

		$cache = get_transient( $transient );

		if ( ! $force && $cache ) {
			return $cache;
		}

		$result = [];

		global $wp_version;

		$sslverify     = version_compare( $wp_version, 4.0, '<' );
		$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

		$cron_request = apply_filters( 'cron_request', [
			'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
			'key'  => $doing_wp_cron,
			'args' => [
				'timeout'   => 3,
				'blocking'  => true,
				'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
			],
		] );

		// Force a blocking request
		$cron_request['args']['blocking'] = true;

		$test = wp_remote_post( $cron_request['url'], $cron_request['args'] );

		if ( is_wp_error( $result ) ) {
			$result = [ 'error' => esc_html__( 'WP ', 'matador-jobs' ) ];
		} else if ( wp_remote_retrieve_response_code( $test ) >= 300 ) {
			$result = [ 'error' => esc_html( sprintf( __( 'Unexpected HTTP response code: %s', 'matador-jobs' ), (int) wp_remote_retrieve_response_code( $test ) ) ) ];
		} else {
			$result = [ 'success' => esc_html__( 'WP Cron was working at last check.', 'matador-jobs' ) ];
		}

		set_transient( $transient, $result, 4 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Monitor Cron Event
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public static function cron_event() {

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		if ( empty( self::cron() ) ) {
			return true;
		}

		if ( ! wp_next_scheduled( 'matador_sync' ) ) {
			return true;
		}

		$scheduled      = wp_next_scheduled( 'matador_sync' );
		$six_hours_late = $scheduled + ( 6 * HOUR_IN_SECONDS );
		$timestamp      = time();

		if ( $six_hours_late < $timestamp ) {
			return false;
		}

		return true;
	}

	/**
	 * Check Cron Event
	 *
	 * Actually runs the Cron Event monitor and logs and notifies if needed.
	 *
	 * @todo I don't want this function in this class. Move to a more appropriate 'Loading' class?
	 * @since 3.7.0
	 *
	 * @return void
	 *
	 */
	public static function check_cron_event() {

		$transient      = Matador::variable( 'monitor_cron_event_late', 'transients' );
		$wait_transient = Matador::variable( 'monitor_cron_event_delay', 'transients' );

		//
		// Since WordPress can do a lot in mere seconds, we prevent the check from occurring to once per 2 minutes
		//
		if ( get_transient( $wait_transient ) ) {

			return;
		} else {
			set_transient( $wait_transient, true, 2 * MINUTE_IN_SECONDS );
		}

		if ( self::cron_event() ) {
			delete_transient( $transient );
			delete_transient( $wait_transient );

			return;
		}

		$times_late = get_transient( $transient ) ?: 0;

		$message = __( 'A Matador Jobs cron task is at least 6 hours late. If this issue persists, it could indicate a possible problem with WP Cron tasks which are necessary for the function of Matador Jobs.', 'matador-jobs' );

		if ( 5 <= $times_late ) {
			Logger::add( 'error', 'monitor-late-cron-error', $message );
			Admin_Notices::add( $message, 'error', 'monitor-late-cron-error' );
			AdminNoticeCronErrorMessage::message();
		} elseif ( 2 < $times_late ) {
			Logger::add( 'error', 'monitor-late-cron-warning', $message );
			set_transient( $transient, ++ $times_late, 24 * HOUR_IN_SECONDS );
		} else {
			set_transient( $transient, ++ $times_late, 24 * HOUR_IN_SECONDS );
		}
	}

	/**
	 * Domain
	 *
	 * Check that the site domain hasn't changed since the previous login.
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	public static function domain() {
		return ( Matador::setting( 'matador_site_url' ) === Helper::get_domain_md5() );
	}

	/**
	 * Check Domain
	 *
	 * Runs the Domain Check monitor and logs and notifies if needed.
	 *
	 * @todo I don't want this function in this class. Move to a more appropriate 'Loading' class?
	 * @since 3.7.0
	 *
	 * @return void
	 *
	 */
	public static function check_domain() {

		if ( ! Matador::setting( 'matador_site_url' ) ) {
			return;
		}

		if ( self::domain() ) {
			return;
		}

		delete_option( Matador::variable( 'bullhorn_api_credentials_key' ) );

		$message = __( 'Matador Jobs detected a change in your site URL. This is usually caused by a migration from a development or staging environment to a production or live environment. Matador Jobs requires you reauthorize with Bullhorn when this occurs.', 'matador-jobs' );
		$button  = ' <a class="button button-primary" href="%s">%s</a>';
		$url     = admin_url() . 'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ) . '&page=connect-to-bullhorn';
		$message .= sprintf( $button, $url, esc_html__( 'Open Bullhorn Connection Assistant', 'matador-jobs' ) );
		Admin_Notices::add( $message, 'notice', 'bullhorn-disconnected-migration' );
	}
}