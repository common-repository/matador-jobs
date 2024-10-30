<?php
/**
 * Matador / Sync / Bullhorn / Import
 *
 * Class to handle the import of data from Bullhorn during a sync.
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

namespace matador\MatadorJobs\Sync\Bullhorn;

// PHP
use stdClass;
// MATADOR
use matador\Matador;
use matador\Event_Log;
use matador\Helper;
use matador\Exception;
use matador\Bullhorn_Import;
use matador\MatadorJobs\Sync\Sync;
use matador\MatadorJobs\Sync\Abstracts\Task as SyncTask;
use matador\MatadorJobs\Sync\Traits\LocalJobs;

/**
 * Class Bullhorn Import
 *
 * @since 3.8.0
 */
class Jobs extends SyncTask {

	use LocalJobs;

	/**
	 * Sync Task Name
	 *
	 * This is the name of the Sync Task (for filter generation in the Abstract routine).
	 *
	 * @since 3.8.0
	 *
	 * @param string
	 */
	protected static string $name = 'bullhorn_jobs';

	/**
	 * Source
	 *
	 * This is our default 'source' for Jobs' trait functions.
	 *
	 * @since 3.8.0
	 *
	 * @param string
	 */
	private static string $source = 'bullhorn';

	/**
	 * Bullhorn Connection
	 *
	 * @since 3.8.0
	 *
	 * @var Bullhorn_Import
	 */
	private Bullhorn_Import $bullhorn;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	public function __construct() {

		/**
		 * Sync Task Steps - Bullhorn Jobs
		 *
		 * @wordpress-filter
		 *
		 * @since 3.8.0
		 *
		 * @param array $steps An array of class method names, filters to be  as strings (reserved), callables, or action names as
		 *                     strings.
		 * @return array
		 */
		$this->steps = apply_filters( 'matador_sync_task_steps_' . self::$name, [
			'get_local_jobs',
			'get_remote_jobs',
			'get_latest_synced_job',
			'prepare_sync',
			'remove_duplicate_local_jobs',
			'remove_expired_jobs',
			'save_new_jobs',
			'update_existing_jobs',
		] );

		$this->bullhorn = new Bullhorn_Import();

		parent::__construct();
	}

	/**
	 * Sync
	 *
	 * @since 3.8.0
	 *
	 * @param Sync $sync The instance of the Sync class so we can track the time.
	 *
	 * @return bool
	 */
	protected function sync( Sync $sync ) : bool {

		add_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );

		/**
		 * Action - Matador Bullhorn Before Import
		 *
		 * @wordpress-action
		 *
		 * @since 3.1.0
		 * @since 3.8.0 Added parameters
		 *
		 * @param Jobs $instance An instance of the matador\MatadorJobs\Sync\Bullhorn\Jobs class
		 *                       (which has a Bullhorn instance included at $instance->bullhorn.)
		 * @param Sync $sync     An instance of the matador\MatadorJobs\Sync\Sync class.
		 */
		do_action( 'matador_bullhorn_before_import', self::$instance, $sync );

		if ( false === parent::sync( $sync ) ) {

			return false;
		}

		/**
		 * Action - Matador Bullhorn After Import
		 *
		 * @wordpress-action
		 *
		 * @since 3.1.0
		 * @since 3.8.0 Added parameters
		 *
		 * @param Jobs $instance An instance of the matador\MatadorJobs\Sync\Bullhorn\Jobs class
		 *                       (which has a Bullhorn instance included at $instance->bullhorn.)
		 * @param Sync $sync     An instance of the matador\MatadorJobs\Sync\Sync class.
		 */
		do_action( 'matador_bullhorn_after_import', self::$instance, $sync );

		remove_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );

		return true;
	}

	/**
	 * Get Bullhorn Jobs
	 *
	 * This retrieves all available jobs from Bullhorn.
	 *
	 * @since 3.8.0
	 */
	protected function get_remote_jobs() {

		$jobs_arrays = [];

		while ( true ) {

			$limit  = 500;
			$offset = $offset ?? 0;

			// API Method
			$request = 'query/JobOrder';

			// API Method Parameters
			$params = array(
				'fields'  => 'id,' . $this->get_job_date_compare_field(),
				'where'   => $this->bullhorn->the_jobs_where(),
				'count'   => $limit,
				'start'   => $offset,
				'orderBy' => $this->get_job_date_compare_field(),
			);

			try {
				// API Call
				$response = $this->bullhorn->request( $request, $params );
			} catch ( Exception $error ) {
				$this->data['remote_ids'] = null;
				return;
			}

			// Process API Response
			if ( isset( $response->data ) ) {

				// Merge Results Array with Return Array
				$jobs_arrays[] = $response->data;

				if ( count( $response->data ) < $limit ) {
					// If the size of the result is less than the results per page
					// we got all the jobs, so end the loop
					break;
				}

				// Otherwise, increment the offset by the results per page, and re-run the loop.
				$offset += $limit;
			} else {

				if ( is_wp_error( $response ) ) {
					new Event_Log( 'bullhorn-import-request-jobs-timeout', esc_html__( 'Operation timed out. Will try again.', 'matador-jobs' ) );
				} elseif ( isset( $response->errorMessage ) ) {
					new Event_Log(  'bullhorn-import-request-jobs-bullhorn-error', sprintf( esc_html__( 'Bullhorn Request Jobs failed due to Bullhorn error. Will stop routine and try again later. Error message: %s', 'matador-jobs' ), $response->errorMessage ) );
				} else {
					new Event_Log( 'bullhorn-import-request-jobs-unexpected-result', esc_html__( 'Operation failed for an unknown reason. Raw response data will be output. Please contact Matador Support.', 'matador-jobs' ) . 'Data:' . var_export( $response, true ) );
				}

				$this->data['remote_ids'] = null;
				return;
			}
		}

		// Setting to null helps with memory usage.
		$response = null;

		// Merge jobs into single array
		$jobs = array_merge( [], ... $jobs_arrays );

		if ( empty( $jobs ) ) {
			new Event_Log( 'bullhorn-import-no-found-jobs', esc_html__( 'Sync found no eligible jobs for import.', 'matador-jobs' ) );

			$this->data['remote_ids'] = [];

			return $this->data['remote_ids'];
		}

		// Translators: Placeholder is for number of found jobs.
		new Event_Log( 'bullhorn-import-found-jobs-count', esc_html( sprintf( __( 'Sync found %1$s jobs.', 'matador-jobs' ), count( $jobs ) ) ) );

		$remote_ids = [];

		foreach( $jobs as $job ) {

			$remote_ids[ $job->id ] = Helper::bullhorn_timestamp_to_datetime( $job->{$this->get_job_date_compare_field() } );
		}

		// Setting to null helps with memory usage.
		$jobs = null;

		/**
		 * Filter : Matador Bullhorn Import Get Remote Jobs
		 *
		 * Modify the imported jobs object prior to performing actions on it.
		 *
		 * @since 3.5.0
		 *
		 * @param stdClass $jobs
		 *
		 * @return stdClass
		 */
		$this->data['remote_ids'] = apply_filters( 'matador_bullhorn_import_get_remote_jobs', $remote_ids );

		return $this->data['remote_ids'];
	}

	/**
	 * Get Bullhorn Job Objects
	 *
	 * This retrieves complete job objects from Bullhorn
	 *
	 * @since 3.8.0
	 *
	 * @param array $ids
	 *
	 * @return ?array
	 */
	protected function get_remote_job_objects( array $ids = [] ): ?array {

		if ( empty( $ids ) ) {

			return [];
		}

		// API Method
		$request = 'entity/JobOrder/' . implode( ',', $ids );

		// API Method Parameters
		$params = array(
			'fields'  => Bullhorn_Import::the_jobs_fields(),
		);

		// API Call
		try {
			$response = $this->bullhorn->request( $request, $params );
		} catch ( Exception $error ) {
			new Event_Log( 'bullhorn-get-remote-objects-request-error', 'Error' );
		}

		// Process API Response
		if ( isset( $response->data ) ) {
			if ( is_object( $response->data ) ) {

				return [ $response->data ];
			} elseif ( is_array( $response->data ) ) {

				return $response->data;
			}
		} else {
			if ( is_wp_error( $response ) ) {
				new Event_Log( 'bullhorn-import-request-jobs-timeout', esc_html__( 'Operation timed out. Will try again.', 'matador-jobs' ) );
			} elseif ( isset( $response->errorMessage ) ) {
				new Event_Log( 'bullhorn-import-request-jobs-bullhorn-error', sprintf( esc_html__( 'Bullhorn Request Jobs failed due to Bullhorn error. Will stop routine and try again later. Error message: %s', 'matador-jobs' ), $response->errorMessage ) );
			} else {
				new Event_Log( 'bullhorn-import-request-jobs-unexpected-result', esc_html__( 'Operation failed for an unknown reason. Raw response data will be output. Please contact Matador Support.', 'matador-jobs' ) );
				new Event_Log( 'bullhorn-import-request-jobs-unexpected-result-data', var_export( $response, true ) );
			}
		}
		return [];
	}

	protected function save_new_jobs() : bool {

		if ( empty( $this->data['to_import'] ) ) {

			return true;
		}

		/**
		 * Filter: Matador Import Bullhorn Remote Jobs Limit
		 *
		 * Adjust the number of jobs fetched per batch of the get_remote_jobs() loop. Default is 20 and is a "safe"
		 * number for users on PHP 5.6 and lower-powered webhosts. Many sites, including those on PHP 7.2+ can
		 * safely fetch more jobs at once.
		 *
		 * @since 3.7.0
		 *
		 * @param int $limit Default 20
		 * @param int
		 */
		$limit = apply_filters( 'matador_bullhorn_import_get_remote_jobs_limit', 20 );

		if ( count( $this->data['to_import'] ) > $limit ) {
			$batch_ids = array_splice( $this->data['to_import'], 0, $limit );
		} else {
			$batch_ids = $this->data['to_import'];
			$this->data['to_import'] = [];
		}

		$batch = $this->get_remote_job_objects( array_values( $batch_ids ) );

		foreach ( $batch as $job ) {
			$this->save_job( $job );
		}

		if ( ! empty( $this->data['to_import'] ) ) {
			return false;
		}

		return true;
	}

	protected function update_existing_jobs() : bool {

		if ( empty( $this->data['to_update'] ) ) {

			return true;
		}

		/**
		 * Filter: Matador Import Bullhorn Remote Jobs Limit
		 *
		 * Adjust the number of jobs fetched per batch of the get_remote_jobs() loop. Default is 20 and is a "safe"
		 * number for users on PHP 5.6 and lower-powered webhosts. Many sites, including those on PHP 7.2+ can
		 * safely fetch more jobs at once.
		 *
		 * @since 3.7.0
		 *
		 * @param int $limit Default 20
		 * @param int
		 */
		$limit = apply_filters( 'matador_bullhorn_import_get_remote_jobs_limit', 20 );

		if ( count( $this->data['to_update'] ) > $limit ) {
			$batch_ids = array_splice( $this->data['to_update'], 0, $limit );
		} else {
			$batch_ids = $this->data['to_update'];
			$this->data['to_update'] = [];
		}

		$batch = $this->get_remote_job_objects( $batch_ids );

		foreach ( $batch as $job ) {
			$this->save_job( $job, $this->data['existing'][ $job->id ] ?? 0 );
		}

		if ( ! empty( $this->data['to_update'] ) ) {
			return false;
		}

		return true;
	}

	protected function save_job( $job, $wpid = 0 ) : void {

		// fix weird date format used by Bullhorn
		$job->dateAdded         = Helper::bullhorn_timestamp_to_datetime( $job->dateAdded );
		$job->dateEnd           = Helper::bullhorn_timestamp_to_datetime( $job->dateEnd );
		$job->dateLastPublished = Helper::bullhorn_timestamp_to_datetime( $job->dateLastPublished );
		$job->dateLastModified  = Helper::bullhorn_timestamp_to_datetime( $job->dateLastModified );

		// Determine should we update/save post? Check if $wpid is present.
		if ( 0 !== $wpid ) {

			/**
			 * Filter : Matador Bullhorn Import Skip Job on Update
			 *
			 * Should Matador overwrite existing data on a job sync. Use this to turn off overwrite when you want to edit the job locally and not have sync overwrite your work. EG: when using multi-language plugins.
			 *
			 * @since 3.5.0
			 *
			 * @param bool     $overwrite default true
			 * @param stdClass $job the current job being imported
			 * @param int      $wpid the ID corresponding to the current job if it exists in DB, else null
			 *
			 * @return bool
			 */
			if ( apply_filters( 'matador_bullhorn_import_skip_job_on_update', false, $job, $wpid ) ) {
				// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
				new Event_Log( 'bullhorn-import-skip-update-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is skipped.', 'matador-jobs' ), $job->id, $wpid ) ) );
				return;
			}

			if ( isset( $_REQUEST['bhid'] ) ) {
				// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
				new Event_Log( 'bullhorn-import-overwrite-force-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is force updated due to user action.', 'matador-jobs' ), $job->id, $wpid ) ) );
			}

			// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
			new Event_Log( 'bullhorn-import-overwrite-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is updated.', 'matador-jobs' ), $job->id, $wpid ) ) );

		} else {
			/**
			 * Filter : Matador Bullhorn Import Skip New Job on Create
			 *
			 * Should Matador skip or not create a job on a job sync.
			 *
			 * @since 3.5.4
			 *
			 * @param bool     $skip default true
			 * @param stdClass $job the current job being imported
			 *
			 * @return bool
			 */
			if ( apply_filters( 'matador_bullhorn_import_skip_job_on_create', false, $job ) ) {
				// Translators: Placeholders are for Bullhorn ID
				new Event_Log( 'bullhorn-import-skip-new-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s is not created.', 'matador-jobs' ), $job->id ) ) );
				return;
			}

			// Translators: Placeholders are for Bullhorn ID
			new Event_Log( 'bullhorn-import-new-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s is new and will be added.', 'matador-jobs' ), $job->id, $wpid ) ) );
		}

		$wpid = $this->bullhorn->save_job( $job, $wpid, self::$source );

		if ( ! is_wp_error( $wpid ) ) {

			// Translators: Placeholder is Job ID number.
			//new Event_Log( 'bullhorn-import-save-job-action', esc_html( sprintf( __( 'Running action: matador_bullhorn_import_save_job for Job #%1$s.', 'matador-jobs' ), $job->id ) ) );
			do_action( 'matador_bullhorn_import_save_job', $job, $wpid, $this->bullhorn );

			if ( ! isset( $existing[ $job->id ] ) ) {
				do_action( 'matador_bullhorn_import_save_new_job', $job, $wpid, $this->bullhorn );
			}
		} else {
			new Event_Log( 'bullhorn-import-save-job-error',  esc_html__( 'Unable to save job.', 'matador-jobs' ) );
		}

		// Translators: Placeholder is Job ID number.
		new Event_Log( 'bullhorn-import-job-complete', esc_html( sprintf( __( 'Bullhorn Job #%1$s has been imported.', 'matador-jobs' ), $job->id ) ) );
	}

	/**
	 * Prepare Sync
	 *
	 * @return void
	 */
	protected function prepare_sync() : void {

		if ( empty( $this->data['remote_ids'] ) ) {

			return;
		}

		// Ensure all parts are included, even with empty arrays.
		$this->data['to_import'] = [];
		$this->data['to_update'] = [];
		$this->data['to_delete'] = [];
		$this->data['to_ignore'] = [];

		foreach ( $this->data['remote_ids'] as $id => $date ) {

			if ( ! array_key_exists( $id, $this->data['existing'] ) ) {
				$this->data['to_import'][] = $id;
			} else if (
				$date > $this->data['latest_sync'] ||
				empty( get_post_meta( $this->data['existing'][ $id ], '_matador_source_date_modified', true ) )
			) {
				$this->data['to_update'][] = $id;
			} else {
				$this->data['to_ignore'][] = $id;
			}
		}

		foreach ( $this->data['existing'] as $remote_id => $local_id ) {
			if ( ! array_key_exists( $remote_id, $this->data['remote_ids'] ) ) {
				$this->data['to_delete'][] = $local_id;
			}
		}

		new Event_Log( 'sync', sprintf( __( 'Sync found %1$s jobs on remote, of which %2$s are new, %3$s require an update, and %4$s require no update. Further, %5$s are expired and will be removed.', 'matador-jobs' ), count( $this->data['remote_ids'] ), count( $this->data['to_import'] ), count( $this->data['to_update'] ), count( $this->data['to_ignore'] ), count( $this->data['to_delete'] ) ) );

		$this->data['remote_ids'] = [];
	}

	/**
	 * Removed Expired Jobs
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	protected function remove_expired_jobs() : bool {

		if ( empty( $this->data['to_delete'] ) ) {

			return true;
		}

		$ids_string = implode( ', ', array_values( $this->data['to_delete'] ) );

		/**
		 * @wordpress-filter Should Delete Expired Jobs During Import
		 *
		 * @since 3.5.4
		 *
		 * @param bool $should
		 * @return bool
		 *
		 * @todo rename?
		 */
		if ( ! apply_filters( 'matador_bullhorn_delete_missing_job_on_import', true ) ) {

			new Event_Log( 'matador-bullhorn-import-expired-jobs-remove-skip', sprintf( __( 'The following jobs are deemed expired, but will not be removed due to user filter: %1$s.', 'matador-jobs' ), $ids_string ) );

			return true;
		}

		$limit = 25;

		if ( count( $this->data['to_delete'] ) > $limit ) {
			$batch_to_delete = array_splice( $this->data['to_delete'], 0, $limit );
		} else {
			$batch_to_delete = $this->data['to_delete'];
			$this->data['to_delete'] = [];
		}

		$ids_string = implode( ', ', array_values( $batch_to_delete ) );

		new Event_Log( 'matador-bullhorn-import-expired-jobs-remove', sprintf( __( 'Deleting Expired Jobs with WordPress IDs of %1$s.', 'matador-jobs' ), $ids_string ) );

		foreach ( $batch_to_delete as $to_delete ) {

			self::delete_local_job( $to_delete );
		}

		if ( ! empty( $this->data['to_delete'] ) ) {

			return false;
		}

		return true;
	}

	/**
	 * Jobs Request "Fields"
	 *
	 * @since 3.0.0
	 *
	 * @return string 'where' clause.
	 */
	private function get_remote_jobs_fields() : string {
		$fields = 'id';

		switch ( Matador::setting( 'bullhorn_is_public' ) ) {
			case 'all':
				$fields .= ',dateLastModified';
				break;
			default:
				$fields .= ',dateLastPublished';
				break;
		}

		return $fields;
	}

	/**
	 * Jobs Request "Where"
	 *
	 * Prepares the "where" clause for the Bullhorn Jobs Request.
	 * Uses the settings and filters to prepare it.
	 *
	 * @since 3.0.0
	 *
	 * @return string 'where' clause.
	 */
	private function get_job_date_compare_field() : string {

		switch ( Matador::setting( 'bullhorn_is_public' ) ) {
			case 'all':
				return 'dateLastModified';
			default:
				return 'dateLastPublished';
		}
	}
}