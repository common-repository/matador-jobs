<?php
/**
 * Matador / Admin / Admin Tasks
 *
 * This contains the settings structure and provides functions to manipulate saved settings.
 * This class is extended to create and validate field input on the settings page.
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

use matador\MatadorJobs\Core\Monitor;
use matador\MatadorJobs\Sync\Sync;

class Admin_Tasks {

	public function __construct() {
		add_action( 'current_screen', array( __CLASS__, 'admin_tasks' ), 50 );
		add_action( 'init', array( __CLASS__, 'applications_sync_now' ), 8 );
		add_action( 'init', array( __CLASS__, 'applications_delete_synced' ), 8 );
	}

	/**
	 * Add Tasks to this Screen.
	 *
	 * @since  3.0.0
	 * @since  3.7.0 Added Check Cron Routines
	 * @since  3.8.0 Added License Activation and Deactivation Routines
	 *
	 * @return void
	 */
	public static function admin_tasks() {

		self::flush_rewrite_rules();
		self::check_for_valid_client();
		self::check_cron();
		self::check_license();
		self::check_career_portal_domain_root();

		// Everything after this point are user actions triggered in settings. Ensure user input is valid or early return.
	   if ( ! isset( $_POST['_wpnonce'] ) || ( isset( $_REQUEST[ Matador::variable( 'options', 'nonce' ) ] ) && ! check_admin_referer( Matador::variable( 'options', 'nonce' ) ) ) ) {

			return;
		}

		$bullhorn_action = isset( $_POST['matador_action'] ) ? strtolower( $_POST['matador_action'] ) : false;

		if ( in_array( $bullhorn_action, array( 'connect_to_bullhorn', 'sync', 'sync-full', 'sync-tax', 'sync-jobs' ), true ) ) {

			switch ( $bullhorn_action ) {

				case 'connect_to_bullhorn':
					wp_safe_redirect( Bullhorn_Connection_Assistant::get_url() );
					die();

				case 'sync-full':
					// @todo this is code repeated from Settings_Actions::trigger_cache_flush lets clean this up
					delete_metadata( 'post', 0, '_matador_source_date_modified', '', true );

				case 'sync-full':
				case 'sync':
					self::import_sync_now();
					break;

			}
		}

		if ( isset( $_POST['license_activate'] ) ) {
			Updater::activate();
		}

		if ( isset( $_POST['license_deactivate'] ) ) {
			Updater::deactivate( Matador::setting( 'license_core' ) );
		}
	}

	/**
	 * Jobs Sync Now
	 *
	 * This is triggered by an admin manually requesting a sync.
	 *
	 * @since 3.1.0
	 * @since 3.4.0 $url param added
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public static function import_sync_now( $url = '' ) {
		if ( empty( $url ) ) {
			$url = Matador::variable( 'options_url' );
		}
		if ( ! get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {
			new Event_Log( 'jobs-sync-manual-initiated', esc_html__( 'An admin initiated a manual sync.', 'matador-jobs' ) );
			if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
				if ( apply_filters( 'matador_experimental_sync', false ) ) {
					Sync::run();
				} else {
					try { Scheduled_Events::jobs_sync( 'manual' ); } catch ( \Exception $error ) { return; }
				}
			} else {
				wp_schedule_single_event( time(), 'matador_job_sync_now', array( 'manual' ) );
				wp_redirect( $url, 302 );
			}
		} else {
			new Event_Log( 'jobs-sync-manual-blocked', esc_html__( 'An admin initiated a manual sync, but it was ignored as another sync is currently running.', 'matador-jobs' ) );
			Admin_Notices::add( __( 'Cannot start a new manual sync while a sync is currently running.', 'matador-jobs' ), 'error', 'job-sync-manual-blocked' );
		}
	}

	/**
	 * Applications Sync Now
	 *
	 * This is triggered by an admin manually requesting a sync.
	 *
	 * In version 3.1.0, the function was moved to these general admin tasks
	 * so that it could be used by the WPJM Extension and support Applications.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 function location was moved.
	 *
	 * @return void
	 */
	public static function applications_sync_now() {

		if (
			isset( $_REQUEST['application_sync'] ) &&
			wp_verify_nonce( $_REQUEST['application_sync'], 'application_sync' ) &&
			isset( $_REQUEST['sync'] ) &&
			isset( $_REQUEST['post_type'] ) &&
			Matador::variable( 'post_type_key_application' ) === $_REQUEST['post_type']
		) {
			if ( get_transient( Matador::variable( 'doing_app_sync', 'transients' ) ) ) {
				new Event_Log( 'application-manual-sync-single-blocked', esc_html__( 'An admin initiated a manual sync, but it was ignored as another sync is currently running.', 'matador-jobs' ) );
				Admin_Notices::add( __( 'Cannot start a new batch application sync while a sync is currently running.', 'matador-jobs' ), 'error', 'application-manual-sync-single-blocked' );
			} else {
				if ( is_numeric( $_REQUEST['sync'] ) ) {
					// Translators: Placeholder is a WordPress Post ID Number
					new Event_Log( 'application-manual-sync-single', esc_html( sprintf( __( 'An admin initiated a manual sync for local application %s', 'matador-jobs' ), $_REQUEST['sync'] ) ) );
					new Application_Sync( intval( $_REQUEST['sync'] ) );
				} elseif ( 'all' === strtolower( $_REQUEST['sync'] ) ) {
					new Event_Log( 'application-manual-sync-all', esc_html__( 'An admin initiated a manual sync for local applications.', 'matador-jobs' ) );
					Scheduled_Events::application_sync();
					/**
					 * Action `matador_applications_sync_now`. allows to add action in the sync now function before the redirect
					 *
					 * @wordpress-action
					 *
					 * @since 3.8.0
					 *
					 */
					do_action( 'matador_applications_sync_now' );
				}
			}
			wp_safe_redirect( remove_query_arg( array(
				'sync',
				'application_sync',
			), $_SERVER['REQUEST_URI'] ) );
			exit;
		}
	}

	/**
	 * Applications Delete all synced
	 *
	 * If the setting "Keep Local Application on Sync" is set to true, applications will be allowed to stay in the
	 * database. This is great for testing or validation, but bad for data security. This function deletes all
	 * applications that are synced to the remote.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function applications_delete_synced() {
		if (
			isset( $_REQUEST['applications_delete_synced'] ) &&
			wp_verify_nonce( $_REQUEST['applications_delete_synced'], 'applications_delete_synced' ) &&
			isset( $_REQUEST['post_type'] ) &&
			Matador::variable( 'post_type_key_application' ) === $_REQUEST['post_type']
		) {
			new Event_Log( 'applications-delete-synced', esc_html__( 'An admin initiated a delete all routine for synced local applications.', 'matador-jobs' ) );

			$synced = get_posts( array(
				'post_type'   => Matador::variable( 'post_type_key_application' ),
				'numberposts' => 1000,
				'meta_key'    => Matador::variable( 'candidate_sync_status' ),
				'meta_value'  => '1',
			) );

			foreach ( $synced as $each ) {
				wp_delete_post( $each->ID, true );
			}

			wp_safe_redirect( remove_query_arg( array( 'sync', 'applications_delete_synced' ), $_SERVER['REQUEST_URI'] ) );

			exit;
		}
	}

	public static function flush_rewrite_rules() {

		if ( get_transient( Matador::variable( 'flush_rewrite_rules', 'transients' ) ) ) {

			delete_transient( Matador::variable( 'flush_rewrite_rules', 'transients' ) );

			Logger::add( 'success', 'flush-rewrite-rules', 'Matador flushed rewrite rules.' );

			flush_rewrite_rules();
		}
	}

	public static function is_uri_redirect_invalid() {

		if ( ! Matador::variable( 'api_redirect_uri' ) ) {

			return 'null_url';
		}

		$client = Matador::credential( 'bullhorn_api_client' );
		$valid  = Matador::setting( 'bullhorn_api_client_is_valid' );
		$secret = Matador::credential( 'bullhorn_api_secret' );

		// If we don't have a $client that is valid or a $secret
		// we aren't ready to test.
		if ( ! $client || ! $valid || ! $secret ) {

			return 'indeterminate';
		}

		$bullhorn   = new Bullhorn_Connection();
		$is_invalid = $bullhorn->is_redirect_uri_invalid();

		if ( null === $is_invalid ) {

			return 'indeterminate';
		} elseif ( true === $is_invalid ) {

			return 'invalid';
		} else {

			return 'valid';
		}
	}


	public static function bullhorn_authorize() {
		$bullhorn = new Bullhorn_Connection();
		try {
			$bullhorn->authorize();
			return true;
		} catch ( Exception $e ) {
			new Event_Log( $e->getName(), $e->getMessage() );
			return false;
		}
	}

	public static function bullhorn_deauthorize() {
		$bullhorn = new Bullhorn_Connection();
		$bullhorn->deauthorize();
	}

	public static function attempt_login() {
		$bullhorn = new Bullhorn_Connection();
		if ( $bullhorn->is_authorized() ) {
			try {
				$bullhorn->login();
			} catch ( Exception $e ) {
				new Event_Log( $e->getName(), $e->getMessage() );
				Admin_Notices::add( esc_html__( 'Login into Bullhorn failed see log for more info.', 'matador-jobs' ), 'warning', 'bullhorn-login-exception' );
			}
		} else {
			Admin_Notices::add( esc_html__( 'To attempt a login, you must have an authorized site.', 'matador-jobs' ), 'warning', 'bullhorn-test-reconnect' );
		}
	}

	/**
	 * "Break" Connection
	 *
	 * Used in the "Test Auto Reconnect" Routine to create an invalid refresh token that will result in a failed login.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Changed how we "broke" the refresh token, as changes to Bullhorn API resulted in 500 errors instead
	 *              of the desired invalid login errors.
	 *
	 * @return void
	 */
	public static function break_connection() {
		$credentials = get_option( 'bullhorn_api_credentials', array() );
		if ( array_key_exists( 'refresh_token', $credentials ) ) {
			$last_char = ( 'x' === substr( $credentials['refresh_token'], -1 ) ) ? 'y' : 'x';
			$credentials['refresh_token'] = substr( $credentials['refresh_token'], 0, -1 ) . $last_char;
			update_option( 'bullhorn_api_credentials', $credentials );
			delete_option( Matador::variable( 'bullhorn_session', 'transients' ) );
		}
	}

	public static function reset_assistant() {
		// Deauthorize Bullhorn
		self::bullhorn_deauthorize();

		// Delete the 24-hour Transient on Redirect Checks
		delete_transient( Matador::variable( 'bullhorn_valid_redirect', 'transients' ) );

		// Delete the Bullhorn Session "Transient"
		delete_option( Matador::variable( 'bullhorn_session', 'transients' ) );

		// Unset all Bullhorn API Settings
		Matador::$settings->update( array(
			'bullhorn_api_has_authorized'  => null,
			'bullhorn_api_is_connected'    => null,
			'bullhorn_api_assistant'       => null,
			'bullhorn_api_client'          => null,
			'bullhorn_api_client_is_valid' => null,
			'bullhorn_api_secret'          => null,
			'bullhorn_api_user'            => null,
			'bullhorn_api_pass'            => null,
			'bullhorn_api_cluster_id'      => null,
		) );
	}

	private static function check_for_valid_client() {
		if ( Matador::setting( 'bullhorn_api_client_is_valid' ) ) {
			return;
		}

		if ( ! defined( 'MATADOR_BULLHORN_API_CLIENT' ) ) {
			return;
		}

		$bullhorn = new Bullhorn_Connection();

		if ( $bullhorn->is_client_id_valid( Matador::credential( 'bullhorn_api_client' ) ) ) {
			Matador::setting( 'bullhorn_api_client_is_valid', true );
		} else {
			$error = __( 'Your Bullhorn Client ID is invalid. Double check you entered it correctly, and if so, you may need to submit a support ticket to Bullhorn.', 'matador-jobs' );
			Admin_Notices::add( $error, 'error', 'invalid-bullhorn-client' );
		}
	}

	private static function check_cron() {

		$status = Monitor::cron();

		if ( empty( $status ) ) {
			return;
		}

		foreach ( $status as $key => $message ) {
			if ( 'error' !== $key ) {
				return;
			}

			$message = '<strong>' . esc_html__( 'Matador Jobs Cron Check:', 'matador-jobs' ) . '</strong> ' . $message;
			Admin_Notices::add( $message, $key, 'matador_monitor_cron' );
		}
	}

	/**
	 * Check License
	 *
	 * Runs a check on the license if the status is currently 'valid'. Transient prevents check more than once per four
	 * hours.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private static function check_license() {

		$delay = get_transient( Matador::variable( 'admin_check_license_activation', 'transients' ) );

		if ( ! $delay && 'valid' === Matador::setting( 'license_core_status' ) ) {

			Updater::check_license();

			set_transient( Matador::variable( 'admin_check_license_activation', 'transients' ), true, 4 * HOUR_IN_SECONDS );
		}
	}

	/**
	 * Check Bullhorn Career Portal Domain Root
	 *
	 * Check the local job board URL against the Bullhorn setting careerPortalDomainRoot. Sets a transient if there is
	 * not a match. Various things are done to determine whether to check.
	 *
	 * 1. Is a setting to disable this check set?
	 * 2. Is a filter to disable this check set?
	 * 3. Did the user ask to be reminded tomorrow?
	 * 4. Is the transient which prevents the remote call to Bullhorn active?
	 * 5. But if so, was the force re-check called by the user?
	 * 6. Is the site a staging site, local site, or development site?
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private static function check_career_portal_domain_root() {

		/**
		 * @wordpress-filter Should Check Bullhorn careerPortalDomainRoot
		 *
		 * @since 3.8.0
		 *
		 * @param bool $should_check Default true
		 *
		 * @return bool
		 */
		if ( ! apply_filters( 'matador_health_check_bullhorn_careerPortalDomainRoot_should_check', true ) ) {

			return;
		}

		if ( 1 === (int) Matador::setting( 'bullhorn_ignore_career_portal_root' ) ) {

			return;
		}

		if ( isset( $_REQUEST['hide_domain_root_check'] ) ) {
			set_transient( Matador::variable( 'bullhorn_career_portal_remind_24', 'transients' ), true, DAY_IN_SECONDS );

			return;
		}

		if ( false !== get_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ) ) && ! isset( $_REQUEST['recheck_domain_root'] ) ) {

			return;
		}

		if ( true !== Matador::setting( 'bullhorn_api_is_connected' ) ) {

			return;
		}

		$is_local_or_staging = false;

		if ( Helper::is_local_site() && ! isset( $_REQUEST['recheck_domain_root'] ) ) {

			new Event_Log( 'health-check-bullhorn-careerPortalDomainRoot-local', __( 'Matador Bullhorn careerPortalDomainRoot check skipped, detected a local development site.', 'matador-jobs' ) );

			$is_local_or_staging = true;
		}

		if ( Helper::is_staging_site() && ! isset( $_REQUEST['recheck_domain_root'] ) ) {

			new Event_Log( 'health-check-bullhorn-careerPortalDomainRoot-staging', __( 'Matador Bullhorn careerPortalDomainRoot check skipped, detected a staging or development site.', 'matador-jobs' ) );

			$is_local_or_staging = true;
		}

		if ( ! $is_local_or_staging ) {

			$bullhorn = new Bullhorn_Connection();

			$job_board_url = trailingslashit( matador_get_the_jobs_link() ) . '{?}';

			try {
				$remote = $bullhorn->request( 'settings/careerPortalDomainRoot' );

				if ( isset( $remote->careerPortalDomainRoot ) ) {
					$careerPortalDomainRoot = $remote->careerPortalDomainRoot;
				} else {

					new Event_Log( 'health-check-bullhorn-careerPortalDomainRoot-unknown-error', __( 'Matador Bullhorn careerPortalDomainRoot check failed, unknown error.', 'matador-jobs' ) );

					return;
				}

			} catch ( Exception $error ) {

				unset( $error );

				new Event_Log( 'health-check-bullhorn-careerPortalDomainRoot-unknown-error', __( 'Matador Bullhorn careerPortalDomainRoot check failed, API error.', 'matador-jobs' ) );

				return;
			}

			if ( $careerPortalDomainRoot === $job_board_url ) {

				// 1 = Match
				new Event_Log( 'health-check-bullhorn-careerPortalDomainRoot-valid', __( 'Matador Bullhorn careerPortalDomainRoot check, match found. Will validate tomorrow.', 'matador-jobs' ) );

				set_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ), 1, DAY_IN_SECONDS );

			} else {

				// -1 = Mismatch
				set_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ), -1, DAY_IN_SECONDS );
			}

			return;
		}

		// 0 = False, but don't recheck
		set_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ), 0, DAY_IN_SECONDS );
	}

}
