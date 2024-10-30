<?php
/**
 * Matador / Sync
 *
 * The new (since 3.8.0 as beta) single class that handles automatic communications with external resources.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Sync
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Sync;

use WP_Site_Health;
use matador\Event_Log;
use matador\Matador;

/**
 * Class Sync
 *
 * @since 3.8.0
 */
class Sync {

	/**
	 * Singleton Instance Variable
	 *
	 * @since 3.8.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Max Execution Time
	 *
	 * Holds a cache of the max execution time allowed on the server.
	 *
	 * @since 3.8.0
	 *
	 * @var int
	 */
	private int $max_execution_time;

	/**
	 * Execution Time Start
	 *
	 * Holds a cache of the start time the execution of the sync started.
	 *
	 * @since 3.8.0
	 *
	 * @var int
	 */
	private int $execution_start_time;

	/**
	 * Tasks
	 *
	 * @since 3.8.0
	 *
	 * @var array
	 */
	private array $tasks;

	/**
	 * Continuation Method
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	private string $continuation_method;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	public function __construct() {

		$this->execution_start_time();

		$this->continuation_method();

		$this->tasks();
	}

	/**
	 * Initializer
	 *
	 * @since 3.8.0
	 *
	 * @return Sync
	 */
	public static function run() : Sync {

		if ( ! ( isset( self::$instance ) ) && ! ( self::$instance instanceof Sync ) ) {

			self::$instance = new Sync();

		}

		self::$instance->start();

		return self::$instance;
	}

	/**
	 * Tasks
	 *
	 * Each parent action that runs on a sync needs to be registered to the Tasks array. Parent actions can separately
	 * manage child tasks if desired (recommended for any routine that will run longer than a few seconds).
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public function tasks() : array {

		$transient = get_transient( Matador::variable( 'doing_sync_tasks', 'transients' ) );

		$this->tasks = is_array( $transient ) ? $transient : [];

		if ( empty( $this->tasks ) ) {

			$tasks = [
				[ 'matador\MatadorJobs\Sync\Bullhorn\Jobs', 'run' ],
			];

			/**
			 * Matador Sync Tasks List
			 *
			 * @wordpress-filter
			 *
			 * @since 3.8.0
			 *
			 * @param array Array of fully-qualified callables.
			 *
			 * @param array
			 */
			$this->tasks = apply_filters( 'matador_sync_tasks', $tasks );

			set_transient( Matador::variable( 'doing_sync_tasks', 'transients' ), $this->tasks );
		}

		return $this->tasks;
	}

	/**
	 * Start Sync
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private function start() : void {

		if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {

			return;
		}

		$transient_key_doing_sync = Matador::variable( 'doing_sync', 'transients' );
		$transient_key_sync_tasks = Matador::variable( 'doing_sync_tasks', 'transients' );
		$transient_key_sync_task  = Matador::variable( 'doing_sync_task', 'transients' );

		set_transient( $transient_key_doing_sync, true, $this->max_execution_time() );

		$progress = get_transient( $transient_key_sync_task ) ?: 0;

		if ( $progress > 0 ) {
			new Event_Log( 'matador-experimental-sync-continue', __( 'Continuing a Matador Sync (Experimental)', 'matador-jobs' ) );
		} else {
			new Event_Log( 'matador-experimental-sync-start', __( 'Starting Matador Sync (Experimental)', 'matador-jobs' ) );
		}

		$will_continue = false;

		while( $progress < count( $this->tasks ) ) {

			if ( ! $this->has_execution_time_left() ) {

				$will_continue = true;

				break;
			}

			new Event_Log( 'matador-experimental-task-start', sprintf( __( 'Running Sync Task: %s', 'matador-jobs' ), implode( ':', $this->tasks[ $progress ] ) ) );

			// Each function should return true or false. True if we can continue, false if we need to repeat the task
			$task_complete = call_user_func( $this->tasks[ $progress ], self::$instance );

			if ( false !== $task_complete ) {
				new Event_Log( 'matador-experimental-task-complete', sprintf( __( 'Completed Sync Task: %s', 'matador-jobs' ), implode( ':', $this->tasks[ $progress ] ) ) );
				set_transient( $transient_key_sync_task, ++$progress, 2 * MINUTE_IN_SECONDS );
			}
		}

		delete_transient( $transient_key_doing_sync );

		if ( $will_continue ) {
			self::continue();
		} else {
			new Event_Log( 'matador-experimental-sync-complete', __( 'Matador Sync (Experimental) complete.', 'matador-jobs' ) );
			delete_transient( $transient_key_sync_tasks );
			delete_transient( $transient_key_sync_task );
		}
	}

	/**
	 * Spawn a Continuation
	 *
	 * Spawn a continuation, if possible, via the method appropriate for the site, based on our check for continuation
	 * method at the instantiation of the script.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private function continue() : void {

		new Event_Log( 'matador-experimental-sync-break', __( 'Matador Sync (Experimental) incomplete but out of time. Will spawn a continuation.', 'matador-jobs' ) );

		if ( 'system' !== $this->continuation_method ) {

			switch ( $this->continuation_method ) {
				case 'rest':
					new Event_Log( 'matador-experimental-sync-continuation-method', __( 'Matador Sync (Experimental) spawning a continuation via REST API method.', 'matador-jobs' ) );
					$url = rest_url( '/matador/v1/sync' );
					break;
				default:
					new Event_Log( 'matador-experimental-sync-continuation-method', __( 'Matador Sync (Experimental) spawning a continuation via HTTP Loopback method.', 'matador-jobs' ) );
					$url = site_url( Matador::variable( 'api_endpoint_prefix' ) . 'sync' );
					break;
			}

			// Set a trivially short timeout and don't block so the script can continue immediately
			$timeout  = .1;
			$blocking = false;

			// Credit to WP Core Team for how to set up the call headers, nonce, and HTTP Auth
			// @see wordpress/wp-admin/includes/class-wp-site-health.php

			$cookies = wp_unslash( $_COOKIE );

			$headers = array(
				'Cache-Control' => 'no-cache',
				'X-WP-Nonce'    => wp_create_nonce( Matador::variable( 'sync_continue', 'nonce' ) ),
			);

			$sslverify = apply_filters( 'https_local_ssl_verify', false );

			// Include Basic auth in loopback requests.
			if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
				$headers['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
			}

			wp_remote_post( $url, compact( 'cookies', 'headers', 'timeout', 'blocking', 'sslverify' ) );

			return;
		}

		wp_schedule_single_event( time(), 'matador_sync_continue' );

		new Event_Log( 'matador-experimental-sync-continuation-method', __( 'Matador Sync (Experimental) spawning a continuation via WP Cron. Expect delays.', 'matador-jobs' ) );
	}

	/**
	 * Sync Has Runtime Left
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	public function has_execution_time_left() : bool {

		// 0 is acceptable here and representative of unlimited runtime.
		// Otherwise we want to make sure we haven't hit our limit.
		return 0 === $this->max_execution_time() || $this->execution_expiration_time() > time();
	}

	/**
	 * Gets the Execution Start Time
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	private function execution_start_time() : int {

		if ( ! isset( $this->execution_start_time ) ) {

			// If the Server Variable REQUEST_TIME is available, use it, if not get the time now.
			$this->execution_start_time = $_SERVER['REQUEST_TIME'] ?: time();
		}

		return $this->execution_start_time;
	}

	/**
	 * Gets the Execution Expiration Time
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	private function execution_expiration_time() : int {

		// Add our max execution time in seconds to our start time
		return $this->execution_start_time() + $this->max_execution_time();
	}

	/**
	 * Gets the Max Execution Time
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	private function max_execution_time() : int {

		if ( ! isset( $this->max_execution_time ) ) {

			// Look up PHP.ini value of `max_execution_time`. This isn't always reliable as beyond PHP there may also be
			// process killers, like at the Nginx or Apache level, etc. If not found, it defaults to 30 seconds.
			$limit = (int) ini_get( 'max_execution_time' ) ?: 30;

			// Pass the value through 'matador_sync_runtime_limit' filter. This is used when a server-level tool is
			// killing long processes and we learn the runtime limit of that tool.

			/**
			 * Matador Sync Runtime Limit
			 *
			 * Return an integer number representing the time in seconds the server allows scripts to run for. Pass 0 if
			 * the server allows unlimited runtime and Matador is also allowed to run for unlimited time.
			 *
			 * @wordpress-filter
			 *
			 * @since 3.8.0
			 *
			 * @param int $limit Integer in seconds representing the max runtime. Default 30
			 * @return int
			 */
			$limit = apply_filters( 'matador_sync_runtime_limit', $limit );

			// Check that filter passed an int, if not, reset to default of 30.
			$limit = is_int( $limit ) ? $limit : 30;

			// If we have a non-zero number above 15, reduce our runtime by 5 seconds. Our script needs some time to
			// save transients and enter final log entries.
			$limit = ( 15 <= $limit ) ? $limit - 5 : $limit;

			// This is redundant code, but done for the purpose of readability.
			$this->max_execution_time = $limit;
		}

		return $this->max_execution_time;
	}

	/**
	 * Sync Continuation Method
	 *
	 * Let's check either the transient or test WordPress to determine the continuation method. At the end of this, we
	 * should know if we can continue via (in order of preference) a REST API call, an HTTP loopback, or if we cannot
	 * continue and must rely on system cron (or WP CLI).
	 *
	 * @see /wordpress/wp-admin/includes/class-wp-site-health.php
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private function continuation_method() : void {

		$transient_key = Matador::variable( 'doing_sync_continuation_method', 'transients' );

		$this->continuation_method = get_transient( $transient_key );

		if ( ! $this->continuation_method ) {

			$wp_site_health = WP_Site_Health::get_instance();

			//
			// Check if WP Rest API is Available
			//

			$test_rest_availability = $wp_site_health->get_test_rest_availability();

			if ( 'good' === $test_rest_availability['status'] ) {

				$this->continuation_method = 'rest';

				set_transient( $transient_key, $this->continuation_method, 6 * HOUR_IN_SECONDS );

				return;
			}

			//
			// Check if HTTP Loopbacks Are Available (cron or loopback)
			//

			$test_http_loopback = $wp_site_health->can_perform_loopback();

			if ( 'good' === $test_http_loopback->status ) {

				$this->continuation_method = 'loopback';

				set_transient( $transient_key, $this->continuation_method, 6 * HOUR_IN_SECONDS );

				return;
			}

			//
			// Rely on Server Spawned System Cron
			//

			$this->continuation_method = 'system';

			set_transient( $transient_key, $this->continuation_method, 6 * HOUR_IN_SECONDS );
		}
	}
}
