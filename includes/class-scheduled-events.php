<?php
/**
 * Matador / Scheduled Events
 *
 * This sets up the scheduled events using the WP Cron.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Query;

/**
 * Matador Scheduled Events Class
 *
 * This class handles scheduled events
 *
 * @since 3.0.0
 */
class Scheduled_Events {

	/**
	 * Default Recurrence
	 *
	 * @since 3.0.0
	 *
	 * @param string
	 */
	private static $recurrence = 'matador_ten_minutes';

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'cron_schedules', array( __CLASS__, 'add_recurrence' ), 99 );

		add_action( 'init', array( __CLASS__, 'schedule' ) );

		add_action( 'matador_sync', array( __CLASS__, 'run_next_event' ) );

		add_filter( 'matador_schedulable_events', array( __CLASS__, 'schedule_core_events' ) );

		add_action( 'matador_sync_applications', array( __CLASS__, 'application_sync' ), 10, 2 );

		if ( apply_filters( 'matador_experimental_sync', false ) ) {
			add_action( 'matador_sync_jobs', [ 'matador\MatadorJobs\Sync\Sync', 'run' ] );
			add_action( 'matador_job_sync_now', [ 'matador\MatadorJobs\Sync\Sync', 'run' ] );
			add_action( 'matador_sync_continue', [ 'matador\MatadorJobs\Sync\Sync', 'run' ] );
		} else {
			add_action( 'matador_sync_jobs', array( __CLASS__, 'jobs_sync' ) );
			add_action( 'matador_job_sync_now', array( __CLASS__, 'jobs_sync' ) );
		}
	}

	/**
	 * Schedule Matador Events
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function schedule() {

		/**
		 * Filter Matador Scheduled Event Recurrence
		 *
		 * @since 3.0.0
		 *
		 * @param string $recurrence The name of the registered recurrence.
		 *
		 * @return string The name of the registered recurrence. Must be registered properly with WordPress
		 *                'cron_schedules' filter.
		 */
		$recurrence = apply_filters( 'matador_scheduled_event_recurrence__all', self::$recurrence );

		//
		// Verify the recurrence exists, and if not, use default
		//
		$recurrence = self::validate_recurrence( $recurrence );

		if ( false === wp_next_scheduled( 'matador_sync' ) ) {
			wp_schedule_event( current_time( 'timestamp', 1 ), $recurrence, 'matador_sync' );
		}
	}

	/**
	 * Run Next Event
	 *
	 * Matador Jobs, as of 3.7.0, holds its various scheduled events in an array and runs one at a time, in order, every
	 * 5 minutes. This function is called every 5 minutes and determines which event to run.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function run_next_event() {

		//
		// If doing a sync (manual or automatic) then we should not do this new automatic sync at this time.
		//
		if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {
			wp_schedule_single_event( current_time( 'timestamp' ) + ( 2 * MINUTE_IN_SECONDS ), 'matador_sync' );
			return;
		}

		/**
		 * Filter: Matador Schedulable Events
		 *
		 * @since 3.7.0
		 *
		 * @param array $actions Array of action names to call on the schedule.
		 *
		 * @return array
		 */
		$events = array_values( apply_filters( 'matador_schedulable_events', array() ) );

		//
		// Array of Schedulable Events must be an array and not empty
		//
		if ( empty( $events ) || ! is_array( $events ) ) {

			return;
		}

		//
		// Get the name and index of the "next" event to run from transients. Set to the first item in the array if not
		// found.
		//
		$this_event = get_transient( Matador::variable( 'next_scheduled_event', 'transients' ) );
		if ( ! $this_event ) {

			$this_event = $events[0];

			//
			// On the first run of a new site or after you clear transients or flush object caches, the site will not
			// find a "Next Scheduled Event." By default, we set the next event to the first item in the array, usually
			// a job sync. In a rare situation where a site's object caching or transients are being flushed too
			// frequently, this value is never allowed to remain set, meaning scheduled events beyond the first routine
			// never run. This log message is designed to note all occurrences of a missing "Next Scheduled Event" to
			// help highlight possible occurrences of a transients or object caching issue.
			//
			Logger::add( 'error', 'missing_next_scheduled_event', __( 'The "Matador Next Scheduled Event" was not set. If you see this log message regularly, contact Matador support.', 'matador-jobs' ) );

		}
		$this_event_index = array_search( $this_event, $events, true );

		//
		// Get the name of the next event by selecting the next item in the array by index. Save it to transients
		//
		if ( $this_event_index === count( $events ) - 1 ) {
			$next_event = $events[0];
		} else {
			$next_event = $events[ $this_event_index + 1 ];
		}
		set_transient( Matador::variable( 'next_scheduled_event', 'transients' ), $next_event, DAY_IN_SECONDS );

		/**
		 * Action: $this_event
		 *
		 * Runs an action from the array of action names added to the $events array.
		 *
		 * @see 'matador_schedulable_events' documentation
		 *
		 * @since 3.7.0
		 */
		do_action( $this_event );
	}

	/**
	 * Schedule "Core" Events
	 *
	 * Adds Job and Application Sync to the Schedulable Events array if settings permit.
	 *
	 * @since 3.7.0
	 *
	 * @param array $schedulable_events
	 *
	 * @return array;
	 */
	public static function schedule_core_events( $schedulable_events ) {

		if ( 0 !== (int) Matador::setting( 'bullhorn_auto_sync' ) ) {
			$schedulable_events[] = 'matador_sync_jobs';
		}

		if ( 0 !== (int) Matador::setting( 'applications_sync' ) ) {
			$schedulable_events[] = 'matador_sync_applications';
		}

		return $schedulable_events;
	}

	/**
	 * Sync Jobs
	 *
	 * @since 3.0.0
	 *
	 * @param string $method Default 'auto'
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public static function jobs_sync( $method = 'auto' ) {

		if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {

			return;
		}

		set_transient( Matador::variable( 'doing_sync', 'transients' ), true, 2 * MINUTE_IN_SECONDS );

		if ( 'manual' === $method ) {
			new Event_Log( 'jobs_sync_start_manual', __( 'Manual Sync Starting', 'matador-jobs' ) );
		} elseif ( empty( $method ) || 'auto' === $method ) {
			new Event_Log( 'jobs_sync_start_auto', __( 'Automatic Sync Starting', 'matador-jobs' ) );
		} else {
			// Translators: Placeholder is for the unexpected method name
			new Event_Log( 'jobs_sync_start_invalid_method', sprintf( __( 'Invalid method (%s) called on jobs_sync()', 'matador-jobs' ), $method ) );
			return;
		}

		if ( ! (
			Matador::credential( 'bullhorn_api_client' ) &&
			Matador::credential( 'bullhorn_api_secret' ) &&
			Matador::credential( 'bullhorn_api_user' ) &&
			Matador::credential( 'bullhorn_api_pass' )
		) ) {
			new Event_Log( 'jobs_sync_fail_not_connected', 'Cannot complete automatic sync, API Credentials do not exist.' );
			delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
			delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );
			return;
		}

		$bullhorn = new Bullhorn_Import();
		$sync     = $bullhorn->sync();

		if ( true !== $sync ) {
			Admin_Notices::add( __( 'Bullhorn Automatic Jobs Sync failed. See log for details.', 'matador-jobs' ), 'error', 'bullhorn-sync-fail' );
		} else {
			Admin_Notices::remove( 'bullhorn-sync-fail' );
		}

		delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
		delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );

	}

	/**
	 * Sync Applications
	 *
	 * @todo this function contains both scheduling and querying related behavior. This needs to be broken up.
	 *
	 * @since 3.0.0
	 *
	 * @param int $id
	 * @param int $last
	 *
	 * @return void
	 */
	public static function application_sync( $id = null, $last = null ) {

		if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {

			return;
		}

		set_transient( Matador::variable( 'doing_sync', 'transients' ), true, 2 * MINUTE_IN_SECONDS );

		if ( ! (
			Matador::credential( 'bullhorn_api_client' ) &&
			Matador::credential( 'bullhorn_api_secret' ) &&
			Matador::credential( 'bullhorn_api_user' ) &&
			Matador::credential( 'bullhorn_api_pass' )
		) ) {
			new Event_Log( 'bullhorn_application_sync_not_connected', 'Cannot complete automatic sync, API Credentials do not exist.' );
			delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
			delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );
			return;
		}

		if ( ! $id ) {

			new Event_Log( 'bullhorn_application_sync_start_batch', __( 'Batch Application Sync Start', 'matador-jobs' ) );

			$application_query = array(
				'post_type'      => Matador::variable( 'post_type_key_application' ),
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => Matador::variable( 'candidate_sync_status' ),
						'compare' => 'IN',
						/**
						 * Filter: Application Batch Sync Statuses
						 *
						 * Allows you to extend the statuses found in an application batch sync.
						 *
						 * @since 3.3.7
						 *
						 * @param array
						 */
						'value'   => apply_filters( 'matador_application_batch_sync_allowed_statuses', array( '-1', '2', '3' ) ),
					),
					array(
						'relation' => 'OR',
						array (
							'key'     => Matador::variable( 'submission_type' ),
							'value'   => 'application',
							'compare' => '=',
						),
						// Back-compat call for prior to 3.7.0 applications
						array(
							'key'     => Matador::variable( 'submission_type' ),
							'compare' => 'NOT EXISTS',
						),
					),
				),
				'date_query' => array(
					array(
						'inclusive' => false,
						/**
						 * Filter: Application Batch Sync Duration
						 *
						 * Allows you to set the time limit for applications to sync in a batch. Default is two weeks.
						 * requires a strtotime() valid string. IE: '2 Weeks Ago'.
						 *
						 * @since 3.3.7
						 *
						 * @param string
						 */
						'after'  => apply_filters( 'matador_application_batch_sync_allowed_duration', '2 weeks ago' ),
					),
				),
			);

			if ( $last ) {
				$application_query['date_query'][0]['before'] = $last;
			}

			$application = new WP_Query( $application_query );

			if ( $application->have_posts() && ! is_wp_error( $application ) ) {
				$id   = $application->posts[0]->ID;
				$last = $application->posts[0]->post_date;
			} else {
				delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
				delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );
				new Event_Log( 'bullhorn_application_sync_none_found', __( 'No applications found to sync', 'matador-jobs' ) );
				return;
			}
		} else {
			new Event_Log( 'bullhorn_application_sync_start_manual', __( 'Manual Application Sync Start', 'matador-jobs' ) );
		}

		// Translators: placeholder is the wpid of the post type "application" to be synced
		new Event_Log( 'bullhorn-application_sync', esc_html( sprintf( __( 'Syncing local application with ID %s', 'matador-jobs' ), $id ) ) );

		new Application_Sync( $id );

		delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
		delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );

		// create another cron task to run now do upload another application if needed
		wp_schedule_single_event( time(), 'matador_sync_applications', array( null, $last ) );
	}

	/**
	 * Add Recurrence
	 *
	 * Adds the 'matador_ten_minutes' recurrence to WordPress's array of recurrences
	 *
	 * @since 3.7.0
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public static function add_recurrence( $schedules ) {
		$schedules[ self::$recurrence ] = array(
			'interval' => 10 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every Ten Minutes (for Matador Jobs)', 'matador-jobs' ),
		);

		return $schedules;
	}

	/**
	 * Validate Recurrence
	 *
	 * @since 3.0.0
	 *
	 * @param $reccurrence
	 *
	 * @return string
	 */
	private static function validate_recurrence( $reccurrence = '' ) {
		$valid_recurrences = wp_get_schedules();
		if ( array_key_exists( strtolower( $reccurrence ), $valid_recurrences ) ) {
			return strtolower( $reccurrence );
		} else {
			return self::$recurrence;
		}
	}
}
