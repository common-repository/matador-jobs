<?php
/**
 * Matador / Bullhorn API / Import Jobs
 *
 * Extends Bullhorn_Connection and imports jobs into the WordPress CPT.
 *
 * - Names that begin with get_ retrieve data, mostly from Bullhorn.
 * - Names that begin with save_
 * - Names that begin with the_
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Bullhorn API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

use DateTimeImmutable;
use NumberFormatter;
use stdClass;
use WP_Query;

// Exit if accessed directly or if parent class doesn't exist.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bullhorn_Import extends Bullhorn_Connection {

	/**
	 * @var array
	 */
	private $organization_url_cache;

	/**
	 * Property: Latest Synced Modified Job Time
	 *
	 * @var int|null
	 */
	private $latest_sync = null;

	/**
	 * Constructor
	 *
	 * Child class constructor class calls parent
	 * constructor to set up some variables and logs
	 * us into Bullhorn.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_categories' ), 5, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_type' ), 10, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_address' ), 20, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_location' ), 22, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_remote_location' ), 22, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_meta' ), 25, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_salary' ), 26, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_jsonld' ), 30, 2 );

		add_filter( 'matador_save_job_meta', array( $this, 'matador_save_job_meta' ), 10, 2 );
	}

	/**
	 * Sync
	 *
	 * This is THE method that does all the import magic. This is the only
	 * method publicly callable.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $sync_tax
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function sync( $sync_tax = true ) {

		add_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );
		set_transient( Matador::variable( 'doing_sync', 'transients' ), true, 5 * MINUTE_IN_SECONDS );

		Logger::add( 'info', 'sync_start', __( 'Starting Sync with bullhorn.', 'matador-jobs' ) );

		/**
		 * Action - Matador Bullhorn Before Import
		 *
		 * @since 3.1.0
		 */
		do_action( 'matador_bullhorn_before_import' );

		if ( is_null( $this->url ) ) {
			Logger::add( 'info', 'sync_not_logged_in', __( 'Bullhorn not logged in and cannot import.', 'matador-jobs' ) );

			return false;
		}

		if ( $sync_tax ) {
			$this->save_taxonomy_terms();
		}

		try {
			$remote_jobs = $this->get_remote_jobs();
		} catch ( Exception $error ) {
			$remote_jobs = false;
		}

		if ( false === $remote_jobs ) {

			Logger::add( 'info', 'bullhorn-sync-failed', __( 'We encountered an error during sync and ended sync early. Will try again later.', 'matador-jobs' ) );

		} else {

			$local_jobs = $this->get_local_jobs();

			$this->save_jobs( $remote_jobs, $local_jobs );

			$expired_jobs = $this->get_expired_jobs( $remote_jobs, $local_jobs );

			if ( is_array( $expired_jobs ) && apply_filters( 'matador_bullhorn_delete_missing_job_on_import', true ) ) {
				$this->destroy_jobs( $expired_jobs );
			}

			/**
			 * Action - Matador Bullhorn After Import
			 *
			 * @since 3.1.0
			 */
			do_action( 'matador_bullhorn_after_import' );

			$now = date( 'G:i j-M-Y T' ) . '.';

			Admin_Notices::add( esc_html__( 'Bullhorn Jobs Sync completed successfully at ', 'matador-jobs' ) . $now, 'success', 'bullhorn-sync' );

			Logger::add( 'info', 'sync_finish', __( 'Finished Sync with bullhorn.', 'matador-jobs' ) );
		}

		remove_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );
		delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
		delete_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) );

		return true;
	}

	/**
	 * Get Jobs
	 *
	 * This retrieves all available jobs from Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @throws Exception
	 */
	private function get_remote_jobs() {

		while ( true ) {

			// Things we need
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
			$limit  = apply_filters( 'matador_bullhorn_import_get_remote_jobs_limit', 20 );
			$offset = isset( $offset ) ? $offset : 0;
			$jobs   = isset( $jobs ) ? $jobs : array();

			// API Method
			$request = 'query/JobOrder';

			// API Method Parameters
			$params = array(
				'fields'  => self::the_jobs_fields(),
				'where'   => $this->the_jobs_where(),
				'count'   => $limit,
				'start'   => $offset,
				'orderBy' => 'dateLastModified',
			);

			try {
				// API Call
				$response = $this->request( $request, $params );
			} catch ( Exception $error ) {
				return false;
			}

			// Process API Response
			if ( isset( $response->data ) ) {

				// Merge Results Array with Return Array
				$jobs = array_merge( $jobs, $response->data );

				if ( count( $response->data ) < $limit ) {
					// If the size of the result is less than the results per page
					// we got all the jobs, so end the loop
					break;
				}

				// Otherwise, increment the offset by the results per page, and re-run the loop.
				$offset += $limit;
			} elseif ( is_wp_error( $response ) ) {
				throw new Exception( 'error', 'bullhorn-import-request-jobs-timeout', esc_html__( 'Operation timed out. Will try again.', 'matador-jobs' ) );
			} elseif ( isset( $response->errorMessage ) ) {
				throw new Exception( 'error', 'bullhorn-import-request-jobs-bullhorn-error', sprintf( esc_html__( 'Bullhorn Request Jobs failed due to Bullhorn error. Will stop routine and try again later. Error message: %s', 'matador-jobs' ), $response->errorMessage ) );
			} else {
				throw new Exception( 'error', 'bullhorn-import-request-jobs-unexpected-result', esc_html__( 'Operation failed for an unknown reason. Raw response data will be output. Please contact Matador Support.', 'matador-jobs' ) );
				throw new Exception( 'error', 'bullhorn-import-request-jobs-unexpected-result-data', var_export( $response, true ) );
				return false;
			}
		}

		if ( empty( $jobs ) ) {
			new Event_Log( 'bullhorn-import-no-found-jobs', esc_html__( 'Sync found no eligible jobs for import.', 'matador-jobs' ) );

			return [];
		}

		// Translators: Placeholder is for number of found jobs.
		new Event_Log( 'bullhorn-import-found-jobs-count', esc_html( sprintf( __( 'Sync found %1$s jobs.', 'matador-jobs' ), count( $jobs ) ) ) );

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
		return apply_filters( 'matador_bullhorn_import_get_remote_jobs', $jobs );
	}


	/**
	 * Get Existing Jobs
	 *
	 * This retrieves all existing jobs from WordPress with a Bullhorn Job ID meta field
	 * and returns an array of Bullhorn IDs with the value of WordPress IDs.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Added $source parameter
	 *
	 * @param string $source What is the external source for the jobs in the search? Empty string returns all jobs. Default 'bullhorn'.
	 *
	 * @return boolean|array
	 */
	public function get_local_jobs( $source = 'bullhorn' ) {

		while ( true ) {

			// Things we need
			$limit    = 100;
			$offset   = isset( $offset ) ? $offset : 0;
			$existing = isset( $existing ) ? $existing : array();
			$dupes    = isset( $dupes ) ? $dupes : array();

			// WP Query Args.
			$args = array(
				'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => 'any',
				'fields'         => 'ids',
			);

			// Looking to a future where Matador can query all its local jobs, not just those with Bullhorn

			if ( ! empty( $source ) ) {
				$args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key'     => '_matador_source',
						'value'   => $source,
						'compare' => '=',
					),
					array(
						'key'     => '_matador_source_id',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					),
				);
			}

			// WP Query
			$posts = new WP_Query( $args );

			if ( $posts->have_posts() && ! is_wp_error( $posts ) ) {

				foreach ( $posts->posts as $post_id ) {
					$source_id = get_post_meta( $post_id, '_matador_source_id', true );
					if ( isset( $existing[ $source_id ] ) ) {
						$dupes[ $post_id ] = $source_id;
					} else {
						$existing[ $source_id ] = $post_id;
					}
				}

				// If the size of the result is less than the limit, break, otherwise increment and re-run
				if ( $posts->post_count < $limit ) {
					break;
				}

				$offset += $limit;
			} else {
				break;
			}
		}

		wp_reset_postdata();

		if ( ! empty( $dupes ) ) {
			Logger::add( 'notice', 'matador-import-found-duplicate-entries', __( 'Matador found duplicate entries for your jobs. Will remove newest copies.', 'matador-jobs' ) );

			foreach ( $dupes as $post_id => $source_id ) {
				// Translators: Placeholder(s) are for numeric ID's of local entry (WP Post) and remote entry (ie: Bullhorn Job)
				Logger::add( 'notice', 'matador-import-remove-duplicate-entry', sprintf( __( 'Removing duplicate local entry #%1$s for remote id #%2$s.', 'matador-jobs' ), $post_id, $source_id ) );
				wp_delete_post( $post_id );
			}
		}

		if ( empty( $existing ) ) {
			Logger::add( 'notice', 'matador-import-existing-none', __( 'No existing jobs were found', 'matador-jobs' ) );

			return false;
		}

		// Translators: placeholder is number of jobs found.
		Logger::add( 'notice', 'matador-import-existing-found', sprintf( __( '%s existing jobs were found', 'matador-jobs' ), count( $existing ) ) );

		return $existing;
	}

	/**
	 * Get Expired Jobs
	 *
	 * This takes the list of remote jobs and the list of local jobs and
	 * creates an array of remote jobs that exist in the local storage also.
	 * It then compares that list to the list of all local jobs to create a
	 * list of local jobs that don't exist remotely, and therefore should be
	 * removed. This process saves us from running another WP_Query.
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $remote_jobs
	 * @param array    $local_jobs
	 *
	 * @return array (empty or populated)
	 */
	private function get_expired_jobs( $remote_jobs = null, $local_jobs = null ) {

		$current_jobs = array();
		$expired_jobs = array();

		if ( $remote_jobs && $local_jobs ) {
			foreach ( $remote_jobs as $job ) {
				if ( array_key_exists( $job->id, $local_jobs ) ) {
					$current_jobs[] = $local_jobs[ $job->id ];
				}
			}
			foreach ( $local_jobs as $bhid => $wpid ) {
				if ( ! in_array( $wpid, $current_jobs, true ) ) {
					$expired_jobs[] = $wpid;
				}
			}
		} elseif ( false === $remote_jobs && $local_jobs ) {
			foreach ( $local_jobs as $bhid => $wpid ) {
				$expired_jobs[] = $wpid;
			}
		}

		return $expired_jobs;

	}

	/**
	 * Get Categories
	 *
	 * This retrieves all available jobs from Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $job_id optional, if passed requests categories for only single job.
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function get_category_terms( $job_id = null ) {
		$cache_key = 'matador_bullhorn_categories_list' . ( null !== $job_id ) ? '_' . $job_id : '';
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}
		// API Method
		$request = $job_id ? 'entity/JobOrder/' . $job_id . '/categories' : 'options/Category';

		// API Method Parameters
		$params = array(
			'fields' => $job_id ? 'name' : 'label',
		);

		// Only Get Enabled
		if ( ! $job_id ) {
			$params['where'] = 'enabled=true';
		}

		try {
			// Submit Request
			$response = $this->request( $request, $params );

			// Format response into array
			$result = array();
			$name   = $job_id ? 'name' : 'label';
			foreach ( $response->data as $category ) {
				$result[] = $category->{$name};
			}
			set_transient( $cache_key, $result, MINUTE_IN_SECONDS * 15 );
		} catch ( Exception $e ) {
			$result = [];
		}

		// Return Categories
		return $result;

	}

	/**
	 * Get Countries
	 *
	 * Bullhorn stores country as an ID and not as a name.
	 * So we need to format country data into an array of
	 * IDs and names.
	 *
	 * @return array|boolean;
	 *
	 * @throws Exception
	 */
	public function get_countries() {

		$cache_key = 'matador_bullhorn_countries_list';
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		// API Method
		$request = 'options/Country';

		// API Method Parameters
		$params = array(
			'count'  => '300',
			'fields' => 'value,label',
		);

		// API Call
		$response = $this->request( $request, $params );

		if ( ! is_wp_error( $response ) ) {

			$country_list = array();
			foreach ( $response->data as $country ) {
				$country_list[ $country->value ] = $country->label;
			}
		} else {

			return false;

		}

		set_transient( $cache_key, $country_list, DAY_IN_SECONDS );

		return $country_list;
	}

	/**
	 * Save Taxonomy Terms
	 *
	 * @throws Exception
	 */
	public function save_taxonomy_terms() {

		Logger::add( 'info', 'sync_bullhorn_tax_to_wp', __( 'Starting taxonomies sync.', 'matador-jobs' ) );

		do_action( 'matador_bullhorn_import_save_taxonomies', $this );

		$category = Matador::variable( 'category', 'job_taxonomies' );
		$this->save_taxonomy( $this->get_category_terms(), $category['key'] );

		// Translators: Placeholder for datettime.
		// Admin_Notices::add( sprintf( esc_html__( 'Taxonomies Sync completed successfully at %s', 'matador-jobs' ), date( 'G:i j-M-Y T' ) ), 'success', 'bullhorn-sync' );
		Logger::add( 'info', 'sync_bullhorn_tax_to_wp', __( 'Finished Category Sync with bullhorn.', 'matador-jobs' ) );
	}

	/**
	 * Get Hiring Organization URL
	 *
	 * Bullhorn stores HiringOrganization as an ID and to
	 * get data from that, we need to separately query the
	 * HiringOrganization via its ID.
	 *
	 * @param integer $organization_id ID from Bullhorn for Organization
	 *
	 * @return boolean|string;
	 *
	 * @throws Exception
	 */
	private function get_hiring_organization_url( $organization_id = 0 ) {

		// Requires an Org ID
		if ( empty( $organization_id ) ) {
			return false; //error
		}

		$cache_key = 'matador_import_organization_urls';

		if ( is_null( $this->organization_url_cache ) ) {
			$this->organization_url_cache = get_transient( $cache_key );
		}

		if ( is_array( $this->organization_url_cache ) && array_key_exists( $organization_id, $this->organization_url_cache ) ) {
			return $this->organization_url_cache[ $organization_id ];
		}

		// Translators: placeholder for organization ID
		new Event_Log( 'matador_import_get_hiring_organization_url', esc_html( sprintf( __( 'Requesting organization URL for organization id #%s', 'matador-jobs' ), $organization_id ) ) );

		// API Method
		$request = 'entity/ClientCorporation/' . $organization_id;

		// API Method Parameters
		$params = array(
			'fields' => 'companyURL',
		);

		$organization_url = "";

		try {
			$response = $this->request( $request, $params );

			// Handle Response
			if ( ! is_wp_error( $response ) ) {
				if ( isset( $response->data->companyURL ) ) {
					$organization_url = ( isset( $response->data->companyURL ) || empty( $response->data->companyURL ) ) ? $response->data->companyURL : null;
				}
			}
		} catch ( Exception $e ) {
			Logger::add( 'info', 'bullhorn-request-ClientCorporation-failed', esc_html__( 'Call to ClientCorporation failed. Client Corporation data is set to blank.', 'matador-jobs' ) );
		}

		$this->organization_url_cache[ $organization_id ] = $organization_url;

		set_transient( $cache_key, $this->organization_url_cache, HOUR_IN_SECONDS * 24 );

		return $organization_url;
	}

	/**
	 * Save Jobs
	 *
	 * Given an array existing jobs and an array of retrieved jobs,
	 * save jobs to database.
	 *
	 * @since  2.1.0
	 * @since  3.8.0 Added $source parameter
	 *
	 * @param array $jobs
	 * @param array $existing
	 * @param string $source The key for the external source. Empty string creates a generic Matador job. Default 'bullhorn'.
	 *
	 * @return array Array of WordPress IDs for imported/updated jobs.
	 */
	public function save_jobs( $jobs, $existing, $source = 'bullhorn' ) {

		$cache_key = Matador::variable( 'bullhorn_import_jobs_done', 'transients' );

		$updated = array();

		if ( ! empty( $jobs ) ) {

			wp_defer_term_counting( true );

			foreach ( $jobs as $index => $job ) {

				$ids_done = get_transient( $cache_key );

				$ids_done = ( false === $ids_done ) ? array() : $ids_done;

				if ( in_array( $job->id, $ids_done, true ) ) {
					// Translators:: placeholder 1 is Job ID
					new Event_Log( 'bullhorn-import-new-job-skip', esc_html( sprintf( __( 'Bullhorn Job #%1$s is was in recent synced list skipping this time.', 'matador-jobs' ), $job->id ) ) );
					continue;
				}

				/**
				 * Bullhorn Import Before Save Job
				 *
				 * Modify the raw imported job data before processing.
				 *
				 * @wordpress-filter 'matador_bullhorn_import_before_save_job'
				 *
				 * @since 3.8.0
				 *
				 * @param object $job
				 * @return object
				 */
				$job = apply_filters( 'matador_bullhorn_import_before_save_job', $job );

				// We give users the opportunity to select a fully custom field for Job Title. If they select a field
				// that can be an array, we must unfortunately handle that. array_filter() removes any empty values,
				// array_map( 'trim', $array ) will remove leading and trailing whitespace, and explode will combine
				// them into a single string with spaces between each.
				if ( is_array( $job->{ self::the_jobs_title_field() } ) ) {
					$job->{self::the_jobs_title_field()} = implode( ' ', array_filter( array_map( 'trim', $job->{self::the_jobs_title_field()} ) ) );
				} else {
					$job->{self::the_jobs_title_field()} = $job->{ self::the_jobs_title_field() };
				}

				// fix weird date format used by Bullhorn
				$job->dateAdded         = Helper::bullhorn_timestamp_to_datetime( $job->dateAdded );
				$job->dateEnd           = Helper::bullhorn_timestamp_to_datetime( $job->dateEnd );
				$job->dateLastPublished = Helper::bullhorn_timestamp_to_datetime( $job->dateLastPublished );
				$job->dateLastModified  = Helper::bullhorn_timestamp_to_datetime( $job->dateLastModified );

				$wpid = isset( $existing[ $job->id ] ) ? $existing[ $job->id ] : 0;

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
						continue;
					}

					if ( ! isset( $_REQUEST['bhid'] ) && $this->get_latest_synced_job_date() && $this->get_date_modified( $job ) <= $this->get_latest_synced_job_date() ) {
						// Translators:: placeholder 1 is Job ID
						new Event_Log( 'bullhorn-import-skip-job-not-modified-skip', esc_html( sprintf( __( 'Bullhorn Job #%1$s was not modified since last import skipping save.', 'matador-jobs' ), $job->id ) ) );
						continue;
					}

					if ( isset( $_REQUEST['bhid'] ) ) {
						// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
						new Event_Log( 'bullhorn-import-overwrite-force-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is force updated due to user action.', 'matador-jobs' ), $job->id, $wpid ) ) );
					} else {
						// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
						new Event_Log( 'bullhorn-import-overwrite-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is updated.', 'matador-jobs' ), $job->id, $wpid ) ) );
					}

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
						continue;
					}

					// Translators: Placeholders are for Bullhorn ID
					new Event_Log( 'bullhorn-import-new-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s is new and will be added.', 'matador-jobs' ), $job->id, $wpid ) ) );
				}

				$wpid = $this->save_job( $job, $wpid, $source );

				if ( ! is_wp_error( $wpid ) ) {

					$updated[] = $wpid;

					// Translators: Placeholder is Job ID number.
					//new Event_Log( 'bullhorn-import-save-job-action', esc_html( sprintf( __( 'Running action: matador_bullhorn_import_save_job for Job #%1$s.', 'matador-jobs' ), $job->id ) ) );
					do_action( 'matador_bullhorn_import_save_job', $job, $wpid, $this );

					if ( ! isset( $existing[ $job->id ] ) ) {
						do_action( 'matador_bullhorn_import_save_new_job', $job, $wpid, $this );
					}
				} else {
					Logger::add( '5', esc_html__( 'Unable to save job.', 'matador-jobs' ) );

					return false;
				}

				$ids_done[] = $job->id;

				set_transient( $cache_key, $ids_done, MINUTE_IN_SECONDS * 10 );
				// Translators: Placeholder is Job ID number.

				new Event_Log( 'bullhorn-import-job-complete', esc_html( sprintf( __( 'Bullhorn Job #%1$s has been imported.', 'matador-jobs' ), $job->id ) ) );
			}

			wp_defer_term_counting( false );

			delete_transient( $cache_key );

		}

		return $updated;
	}

	/**
	 * Insert or Update Job into WordPress
	 *
	 * Given a job object and an optional WP post ID,
	 * insert or add a job post type post to WordPress.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Added $source parameter
	 *
	 * @param array|stdClass $job
	 * @param integer        $wpid
	 * @param string         $source The key for the external source. Empty string creates a generic Matador job. Default 'bullhorn'.
	 *
	 * @return integer WP post ID
	 */
	public function save_job( $job, $wpid = 0, $source = 'bullhorn' ) {

		$status = ( 0 !== $wpid ) ? get_post_status( $wpid ) : apply_filters( 'matador_bullhorn_import_job_status', 'publish' );

		if ( ! array_key_exists( $status, get_post_statuses() ) ) {
			$status = 'publish';
		}

		$post_content = $job->{ self::the_jobs_description_field() } ?: '';
		// $post_content = $job->{ self::the_jobs_description_field() } ?? '';

		if ( is_array( $post_content ) ) {
			$post_content = implode( PHP_EOL . PHP_EOL, $post_content );
		} elseif ( ! is_string( $post_content ) ) {
			$post_content = '';
		}

		/**
		 * Filter : Matador Import Job Description
		 *
		 * Filter the imported job description. Useful to replace, prepend, or append the content.
		 *
		 * @since 3.4.0
		 *
		 * @param string
		 */
		$post_content = wp_kses( apply_filters( 'matador_import_job_description', $post_content ), $this->the_jobs_description_allowed_tags(), $this->the_jobs_description_allowed_protocols() );

		// wp_insert_post args
		$args = array(
			/**
			 * Filter : Matador Import Job Title
			 *
			 * Filter the imported job title. Useful to replace, prepend, or append the title.
			 *
			 * @since 3.4.0
			 *
			 * @param string
			 */
			'post_title'   => apply_filters( 'matador_import_job_title', $job->{self::the_jobs_title_field()} ),
			'post_content' => $post_content,
			'post_type'    => Matador::variable( 'post_type_key_job_listing' ),
			'post_name'    => self::the_jobs_slug( $job ),
			'post_date'    => Helper::format_datetime_to_mysql( $this->get_post_date( $job ) ),
			'post_status'  => $status,
		);

		if ( ! empty( $source ) ) {
			$args[ 'meta_input' ] = array(
				'_matador_source'               => $source,
				'_matador_source_id'            => $job->id,
				'_matador_source_date_modified' => $this->get_date_modified( $job )->format( 'U' ),
			);
		}

		// if this is an existing job, add the ID, else set the status (publish, draft, etc) of the imported job
		if ( $wpid ) {
			$args['ID'] = $wpid;
		}

		/**
		 * Filter : Matador Import save job args
		 *
		 * Filter the imported job save args.
		 * Useful to stop overwriting description for adding exta meta.
		 *
		 * @since 3.5.4
		 *
		 * @param array    $args to passed to wp_insert_posts
		 * @param stdClass $job the being imported
		 * @param int      $wpid the wp id to be update or null
		 */
		$args = apply_filters( 'matador_import_job_save_args', $args, $job, $wpid );

		return wp_insert_post( $args );
	}

	/**
	 * Save Job Categories
	 *
	 * @since 1.0.0
	 * @since 3.5.0 added logic to handle new publishedCategory field in Bullhorn
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function save_job_categories( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return false;
		}

		$taxonomies = Matador::variable( 'job_taxonomies' );

		if ( ! isset( $taxonomies['category'] ) ) {

			return false;
		}

		if ( 'categories' !== self::the_category_field() ) {

			if ( empty( $job->{self::the_category_field()} ) ) {

				return false;
			}

			$categories[] = $job->{self::the_category_field()}->name;
		} else {

			$count      = $job->categories->total;
			$categories = array();

			if ( 0 === $count ) {

				return false;
			}

			if ( ( 0 < $count ) && ( $count <= 5 ) ) {
				foreach ( $job->categories->data as $category ) {
					$categories[] = $category->name;
				}
			} else {
				Logger::add( 'info', 'starting_term_import', esc_html__( 'More than 5 terms doing a full term sync', 'matador-jobs' ) );
				$categories = $this->get_category_terms( $job->id );
			}
		}

		// Need this for the JSON LD builder
		set_transient( 'matador_import_categories_job_' . $wpid, $categories, MINUTE_IN_SECONDS * 5 );

		return wp_set_object_terms( $wpid, $categories, $taxonomies['category']['key'] );
	}

	/**
	 * Save Job Types
	 *
	 * @param stdClass $job
	 * @param integer  $wpid
	 *
	 * @return boolean
	 */
	public function save_job_type( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return false;
		}

		if ( isset( $job->employmentType ) ) {
			$taxonomies = Matador::variable( 'job_taxonomies' );

			if ( isset( $taxonomies['type']['key'] ) ) {

				return wp_set_object_terms( $wpid, $job->employmentType, $taxonomies['type']['key'] );
			}
		}

		return true;
	}

	/**
	 * Save Job Meta
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Added support for saveas = object, flatten an array to a string when saveas = meta
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function save_job_meta( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {

			return;
		}

		$all_fields = self::the_jobs_fields( 'array' );

		$job_meta_fields = array();

		foreach ( $all_fields as $key => $field ) {

			if ( ! empty( $job->{$key} ) ) {

				$meta_key = array_key_exists( 'name', $field ) ? esc_attr( $field['name'] ) : esc_attr( $key );

				$save_as_meta   = false;
				$save_as_object = false;

				if ( array_key_exists( 'saveas', $field ) ) {
					if ( is_array( $field['saveas'] ) ) {
						if ( in_array( 'meta', $field['saveas'], true ) ) {
							$save_as_meta = true;
						}
						if ( in_array( 'object', $field['saveas'], true ) ) {
							$save_as_object = true;
						}
					} elseif ( is_string( $field['saveas'] ) ) {
						if ( 'meta' === strtolower( $field['saveas'] ) ) {
							$save_as_meta = true;
						} elseif ( 'object' === strtolower( $field['saveas'] ) ) {
							$save_as_object = true;
						}
					}
				}

				$meta = $job->{$key};

				if ( $save_as_meta ) {

					if ( is_array( $meta ) ) {
						/**
						 * Filter: Matador Import Meta Item Separator
						 *
						 * When an array or object is sent with job data and the user wishes to include it into post
						 * meta via a string, Matador will flatten the values into a string separated, by default, with
						 * a comma followed by a space. Change the comma and space to another separator with this
						 * filter.
						 *
						 * @since 3.4.0
						 *
						 * @param string         $separator
						 * @param string         $meta_key
						 * @param stdClass|array $value
						 *
						 * @return string
						 */
						$separator = apply_filters( 'matador_import_meta_item_separator', ', ', $meta_key, $meta );
						$meta      = implode( $separator, $meta );
					}

					// legacy support for 'time', use 'timestamp'
					if ( in_array( strtolower( $field['type'] ), [ 'time', 'timestamp' ], true ) ) {

						if ( ! $meta instanceof DateTimeImmutable ) {
							$meta = Helper::bullhorn_timestamp_to_datetime( $meta );
						}
						$meta = $meta->format( 'Y-m-d H:i:s' );
					}

					$job_meta_fields[ $meta_key ] = $meta;
				}

				if ( $save_as_object ) {
					if ( is_array( $meta ) ) {
						$job_meta_fields[ '_' . $meta_key ] = $meta;
					} elseif ( is_string( $meta ) ) {
						$job_meta_fields[ '_' . $meta_key ] = preg_split( '/(\s*,\s*)*,+(\s*,\s*)*/', $meta );
					}
				}
			}
		}

		if ( isset( $job->clientCorporation->id ) && 'company' === Matador::setting( 'jsonld_hiring_organization' ) ) {
			$job_meta_fields['hiringOrganizationURL'] = $this->get_hiring_organization_url( (int) $job->clientCorporation->id );
		}

		if ( ! isset( $job_meta_fields['responseUser']->id ) ) {
			$job_meta_fields['responseUser'] = $job_meta_fields['owner'];
		}

		if ( empty( $job_meta_fields['dateLastPublished'] ) ) {
			$job_meta_fields['dateLastPublished'] = $job_meta_fields['dateLastModified'];
		}

		foreach ( $job_meta_fields as $key => $val ) {
			/**
			 * @wordpress-filter Matador Import Save Job Meta
			 *
			 * Similar to core `update_post_metadata` with simpler parameters for Matador specific use.
			 *
			 * @since 3.1.0
			 *
			 * @param mixed  $value The value to be saved.
			 * @param string $key   The post meta key
			 * @param int    $wpid  The WordPress Post Type key
			 *
			 * @return mixed $value The filtered, if any, value
			 */
			update_post_meta( $wpid, $key, apply_filters( 'matador_save_job_meta', $val, $key, $wpid ) );
		}

		$this_post_meta_keys = array_keys( $job_meta_fields );
		$last_post_meta_keys = get_post_meta( $wpid, '_matador_synced_meta_keys', true );

		if ( is_array( $last_post_meta_keys ) && $last_post_meta_keys !== $this_post_meta_keys ) {
			foreach ( $last_post_meta_keys as $key ) {
				if ( ! in_array( $key, $this_post_meta_keys ) ) {
					delete_post_meta( $wpid, $key );
				}
			}
		}

		update_post_meta( $wpid, '_matador_synced_meta_keys', $this_post_meta_keys );
	}

	/**
	 * Save Job Salary
	 *
	 * @since 3.8.4
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function save_job_salary( $job = null, $wpid = null ) {

		// Instantiate an array to hold our new/updated Post Meta
		$meta = [];

		/**
		 * Salary Range Separator
		 *
		 * Modify the string that separates two formatted currency values in a salary range.
		 *
		 * @wordpress-filter
		 *
		 * @since 3.8.4
		 * @param string $range_separator The default separator string, ' - '.
		 * @return string
		 */
		$range_separator = apply_filters( 'matador_bullhorn_import_salary_range_separator', ' - ' );

		/**
		 * Salary Unit Separator
		 *
		 * Modify the string that separates either a salary or a salary string.
		 *
		 * @wordpress-filter
		 *
		 * @since 3.8.4
		 * @param string $unit_separator The default separator string, defaults to one non-breaking space ' '.
		 * @return string
		 */
		$unit_separator = apply_filters( 'matador_bullhorn_import_salary_unit_separator', ' ' );

		/**
		 * @wordpress-filter Bullhorn Import Salary Currency Field.
		 * Documented at /includes/class-bullhorn-import.php::the_jobs_fields()
		 */
		$currency_field = apply_filters( 'matador_bullhorn_import_bullhorn_salary_currency_field', '' );

		if ( $currency_field && array_key_exists( strtoupper( $job->$currency_field ), Helper::get_currency_codes() ) ) {
			$meta['salary_currency'] = strtoupper( $job->$currency_field );
		} else {
			$meta['salary_currency'] = $this->get_bullorn_currency_format();
		}

		// Note: At this point, we already have 'salary' and 'salaryUnit' in Post Meta due to legacy behavior, but we
		// will re-do it for use in our code.

		$meta['salary']        = $job->salary ? (float) $job->salary : false;
		$meta['salaryUnit']    = $job->salaryUnit ? esc_html( $job->salaryUnit ) : '';
		$meta['salary_string'] = '';

		// This will format the number with localized punctuation and currency symbol
		$meta['salary_formatted'] = ( ! empty( $meta['salary'] ) ) ? Helper::format_currency( $meta['salary'], $meta['salary_currency'] ) : '';

		$salary_low_setting  = Matador::setting( 'bullhorn_salary_low_field' );
		$salary_high_setting = Matador::setting( 'bullhorn_salary_high_field' );

		// New installs and new upgrades to 3.8.4 can have no value here, so set to default
		if ( ! $salary_low_setting ) {
			$salary_low_setting = 'salary';
		}
		if ( ! $salary_high_setting ) {
			$salary_high_setting = 'salary';
		}

		$salary_low_value  = ( 0 === (int) $job->$salary_low_setting || ! empty( $job->$salary_low_setting ) ) ? (float) $job->$salary_low_setting : false;
		$salary_high_value = ! empty( $job->$salary_high_setting ) ? (float) $job->$salary_high_setting : false;

		if ( false !== $salary_high_value && false !== $salary_low_value && $salary_low_value > $salary_high_value ) {
			$temp = $salary_low_value;
			$salary_low_value = $salary_high_value;
			$salary_high_value = $temp;
			unset( $temp );
		}

		// This user is not using custom values
		if ( 'salary' === $salary_high_setting && 'salary' === $salary_low_setting ) {

			if ( $meta['salary_formatted'] ) {
				$meta['salary_string'] = $meta['salary_formatted'] . ( $meta['salaryUnit'] ? $unit_separator . $meta['salaryUnit'] : '' );
			}
		// Else, this user is using custom values in some capacity
		} elseif ( false !== $salary_high_value && false !== $salary_low_value ) {

			$meta['salary_low_value']     = $salary_low_value;
			$meta['salary_low_formatted'] = Helper::format_currency( $salary_low_value, $meta['salary_currency'] );

			$meta['salary_high_value']     = $salary_high_value;
			$meta['salary_high_formatted'] = Helper::format_currency( $salary_high_value, $meta['salary_currency'] );

			if ( ! $meta['salary'] ) {
				$meta['salary']           = $salary_high_value;
				$meta['salary_formatted'] = $meta['salary_high_formatted'];
			}

			if ( $salary_high_value === $salary_low_value ) {
				$meta['salary_string'] = $meta['salary_high_formatted'];
			} else {
				$meta['salary_string'] = $meta['salary_low_formatted'] . $range_separator . $meta['salary_high_formatted'];
			}

			$meta['salary_string'] .= ( ! empty( $meta['salary_string'] && $meta['salaryUnit'] ) ) ? $unit_separator . $meta['salaryUnit'] : '';

		} elseif ( ! $meta['salary'] && ( false !== $salary_high_value || false !== $salary_low_value ) ) {

			$salary = $salary_high_value ?: $salary_low_value;

			if ( 0 !== (int) $salary ) {
				$meta['salary']           = $salary;
				$meta['salary_formatted'] = Helper::format_currency( $salary, $meta['salary_currency'] );
				$meta['salary_string']    = $meta['salary_formatted'] . ( $meta['salaryUnit'] ? $unit_separator . $meta['salaryUnit'] : '' );
			}
		} elseif ( $meta['salary'] ) {
			$meta['salary_string'] = $meta['salary_formatted'] . ( $meta['salaryUnit'] ? $unit_separator . $meta['salaryUnit'] : '' );
		}

		/**
		 * Formatted Salary String
		 *
		 * @wordpress-filter
		 *
		 * @since 3.8.4
		 *
		 * @param string $salary_string As constructed by Matador.
		 * @param array $meta
		 *
		 * @return string
		 */
		$meta['salary_string'] = apply_filters( 'matador_bullhorn_import_salary_string', $meta['salary_string'], $meta );

		foreach ( $meta as $key => $val ) {
			if ( 0.0 === $val || ! empty( $val ) ) {
				/**
				 * @wordpress-filter Matador Import Save Job Meta
				 *
				 * @see Bullhorn_Import::save_job_meta() for documentation
				 */
				update_post_meta( $wpid, $key, apply_filters( 'matador_save_job_meta', $val, $key, $wpid ) );
			}
		}
	}

	/**
	 * Save Job Address
	 *
	 * @since  2.1.0
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function save_job_address( $job = null, $wpid = null ) {
		if ( ! $job || ! $wpid ) {

			return;
		}

		/**
		 * Matador Import Before Save Job Location Action
		 *
		 * Run before the Job Address data is saved
		 *
		 * @since 3.6.0
		 *
		 * @param stdClass $job Job Object (from Bullhorn)
		 * @param int      $wpid WordPress Job Post Type ID
		 */
		do_action( 'matador_import_before_save_job_location', $job, $wpid );

		$street     = isset( $job->address->address1 ) ? $job->address->address1 : null;
		$city       = isset( $job->address->city ) ? $job->address->city : null;
		$state      = isset( $job->address->state ) ? $job->address->state : null;

		if ( ! empty( $job->publishedZip ) ) {
			$zip = $job->publishedZip;
		} else {
			$zip = isset( $job->address->zip ) ? $job->address->zip : null;
		}

		$country_id = isset( $job->address->countryID ) ? $job->address->countryID : null;
		$country    = $country_id ? $this->the_job_country_name( $country_id ) : null;

		// Some Formatting Help
		$comma = ( $city && $state ) ? ', ' : '';
		$space = ( $city || $state ) && $zip ? ' ' : '';
		$dash  = ( ( $city || $state || $zip ) && $country ) ? ' - ' : '';

		if ( $street ) {
			update_post_meta( $wpid, 'bullhorn_street', $city );
		}
		if ( $city ) {
			update_post_meta( $wpid, 'bullhorn_city', $city );
		}
		if ( $state ) {
			update_post_meta( $wpid, 'bullhorn_state', $state );
		}
		if ( $country ) {
			update_post_meta( $wpid, 'bullhorn_country', $country );
		}
		if ( $zip ) {
			update_post_meta( $wpid, 'bullhorn_zip', $zip );
		}

		$location_string = sprintf( '%s%s%s%s%s%s%s', $city, $comma, $state, $space, $zip, $dash, $country );
		update_post_meta( $wpid, 'bullhorn_job_location', $location_string );

		$location_data = array(
			'street'  => $street,
			'city'    => $city,
			'state'   => $state,
			'zip'     => $zip,
			'country' => $country,
		);
		/**
		 * Job General Location Filter
		 *
		 * @since 3.4.0
		 * @since 3.6.0 added $wpid arg
		 *
		 * @param string $general_location
		 * @param array  $location_data . Values are "street", "city" for city or locality, "state", "zip" for ZIP or Postal Code, and "country"
		 * @param int    $wpid ID of the current job post.
		 *
		 * @return string $general_location
		 */
		$general_location = apply_filters( 'matador_import_job_general_location', $city . $comma . $state, $location_data, $wpid );

		update_post_meta( $wpid, 'job_general_location', $general_location );

		/**
		 * Matador Import After Save Job Address Action
		 *
		 * Run after the Job Address data is saved
		 *
		 * @since 3.6.0 added $job to sync
		 *
		 * @param stdClass $job Job Object (from Bullhorn)
		 * @param int      $wpid WordPress Job Post Type ID
		 */
		do_action( 'matador_import_after_save_job_location', $job, $wpid );

		/**
		 * Matador Import Save Job Address
		 *
		 * Run after the Job Address is saved.
		 *
		 * @since 3.0.0
		 *
		 * @param string $location_string
		 * @param int    $wpid
		 * @param array  $data
		 *
		 * @deprecated 3.6.0 Use 'matador_import_after_save_job_address'
		 *
		 */
		do_action( 'matador_save_job_address', $location_string, $wpid, array(
			'street'  => $street,
			'city'    => $city,
			'state'   => $state,
			'zip'     => $zip,
			'country' => $country,
		) );
	}

	/**
	 * Save Job Location (to Taxonomy/Taxonomies)
	 *
	 * This function accepts location data stored as meta values to generate taxonomy terms. It includes a routine
	 * where a site operator can generate additional location-based taxonomy, ie: one for state, one for city.
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 */
	public function save_job_location( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return;
		}

		/**
		 * Filter Location Taxonomy Allowed Fields
		 *
		 * This defines which possible values are allowed as terms for the Location Taxonomy. This should correlate to
		 * job meta fields.
		 *
		 * @since 3.0.0
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		$allowed = apply_filters( 'matador_import_location_taxonomy_allowed_fields', array(
			'city',
			'state',
			'zip',
			'country',
			'job_general_location'
		) );

		$field = Matador::setting( 'bullhorn_location_term' );

		/**
		 * Filter Location Taxonomy Field
		 *
		 * This allows the user to set which value determines the location taxonomy term. Must be from list of allowed
		 * fields, which in turn is a list of valid meta previously defined.
		 *
		 * @since 3.0.0
		 *
		 * @param string $field , default is 'city'
		 *
		 * @return string
		 */
		$field = apply_filters( 'matador_import_location_taxonomy_field', $field );

		$field = in_array( $field, $allowed, true ) ? $field : 'city';

		// Legacy support for older versions of Matador save city, state, etc as 'bullhorn_city', etc.
		// Add prefix to those fields.
		if ( in_array( $field, array( 'city', 'state', 'zip', 'country' ), true ) ) {
			$field = 'bullhorn_' . $field;
		}

		$taxonomies = Matador::variable( 'job_taxonomies' );

		if ( isset( $taxonomies['location']['key'] ) ) {
			$this->save_job_meta_to_tax( $field, $taxonomies['location']['key'], $wpid );
		}

		// You may declare separate taxonomies for the other location fields by
		// creating a taxonomy with the keyname of city, state, zip, or country.
		foreach ( $allowed as $meta ) {
			if ( array_key_exists( $meta, $taxonomies ) ) {
				if ( get_post_meta( $wpid, $meta, true ) ) {
					$field = $meta;
				} elseif ( get_post_meta( $wpid, 'bullhorn_' . $meta, true ) ) {
					$field = 'bullhorn_' . $meta;
				}
				if ( $field ) {
					$this->save_job_meta_to_tax( $field, $taxonomies[ $meta ]['key'], $wpid );
				}
			}
		}
	}

	/**
	 * Save Job Remote Location (to Taxonomy/Taxonomies)
	 *
	 * This function accepts location data stored as meta values to generate taxonomy terms. It includes a routine
	 * where a site operator can generate additional location-based taxonomy, ie: one for state, one for city.
	 *
	 * @since 3.7.0
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 */
	public function save_job_remote_location( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {

			return;
		}

		if ( ! Matador::setting( 'bullhorn_location_remote_term' ) ) {

			return;
		}

		if ( ! self::is_remote_job( $job ) ) {

			return;
		}

		/**
		 * Filter: Matador Import Location Remote Term Name
		 *
		 * @since 3.7.0
		 *
		 * @param string $term Default "Remote"
		 *
		 * @return string
		 */
		$term = apply_filters( 'matador_import_location_remote_term_name', __( 'Remote', 'matador-jobs' ) );

		$taxonomies = Matador::variable( 'job_taxonomies' );

		if ( isset( $taxonomies['location']['key'] ) ) {

			wp_set_object_terms( $wpid, $term, $taxonomies['location']['key'], true );
		}

		update_post_meta( $wpid, 'isRemote', $term );
	}

	/**
	 * @since 3.7.0
	 *
	 * @param null $job
	 *
	 * @return bool
	 */
	private static function is_remote_job( $job = null ) {

		if ( ! Matador::is_pro() ) {
			return false;
		}

		if ( empty ( $job ) ) {

			return false;
		}

		if ( empty( $job->onSite ) && empty( $job->isWorkFromHome )  ) {

			return false;
		}

		if ( ! empty( $job->isWorkFromHome ) ) {

			return true;
		}

		$remote_possible_values = array(
			'no preference',
			'off-site',
			'off site',
			'remote',
			'work from home',
			'wfh',
			'telecommute',
		);

		/**
		 * Filter: Matador Bullhorn Import Telecommute Types
		 *
		 * @since 3.6.0
		 * @deprecated 3.7.0 in favor of the more clear 'matador_import_remote_definitions'
		 */
		$remote_possible_values = apply_filters( 'matador_bullhorn_import_telecommute_types', $remote_possible_values );

		/**
		 * Filter: Import Remote Definitions
		 *
		 * @since 3.7.0
		 *
		 * @param array $remote_possible_values Array of terms that will trigger a job as "Remote"
		 *
		 * @return array
		 */
		$remote_possible_values = apply_filters( 'matador_import_remote_definitions', $remote_possible_values );

		// All terms string to lowercase, then remove duplicates
		$remote_possible_values = array_map( 'strtolower', $remote_possible_values );
		$remote_possible_values = array_unique( $remote_possible_values );

		//We can get an array from Bullhorn so cast to array and loop just in case
		foreach ( (array) $job->onSite as $onSite ) {
			if ( in_array( strtolower( $onSite ), $remote_possible_values, true ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $field
	 * @param $taxonomy
	 * @param $wpid
	 */
	public function save_job_meta_to_tax( $field, $taxonomy, $wpid ) {

		/**
		 * Filter Matador Bullhorn Import Meta to Taxonomy Value
		 *
		 * Empowers user to override the Taxonomy name set by import. EG: replace 'Finance' and 'Banking' with
		 * 'Financial Services'.
		 *
		 * @since unknown
		 *
		 * @param string|int|array $value A single term slug, single term id, or array of either term slugs or ids.
		 *                                    Will replace all existing related terms in this taxonomy. Passing an
		 *                                    empty value will remove all related terms.
		 * @param int              $wpid The WordPress post (job) ID
		 * @param string           $field The Bullhorn Import field name.
		 *
		 * @return string|int|array
		 */
		$value = apply_filters( 'matador_import_meta_to_taxonomy_value', get_post_meta( $wpid, $field, true ), $wpid, $field );

		if ( ! empty( $value ) ) {
			wp_set_object_terms( $wpid, $value, $taxonomy );
		}
	}

	/**
	 * Format Job As JSON LD
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $job
	 * @param int      $wpid
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function save_job_jsonld( $job = null, $wpid = null ) {
		if ( ! $job || ! $wpid ) {
			return;
		}

		$ld                                    = array();
		$ld['@context']                        = 'https://schema.org';
		$ld['@type']                           = 'JobPosting';
		$ld['title']                           = apply_filters( 'matador_import_job_title', $job->{self::the_jobs_title_field()} );
		$ld['description']                     = $job->{self::the_jobs_description_field()};
		$ld['datePosted']                      = Helper::format_datetime_to_8601( $this->get_post_date( $job ) );
		$ld['jobLocation']['@type']            = 'Place';
		$ld['jobLocation']['address']['@type'] = 'PostalAddress';
		$ld['hiringOrganization']['@type']     = 'Organization';

		if ( Matador::is_pro() && Matador::setting( 'applications_accept' ) ) {
			$ld['directApply'] = 'true';
		} else {
			// If Matador Jobs Lite, we cannot guarantee directApply, and also if
			// applications_accept setting is off
			$ld['directApply'] = 'false';
		}

		/**
		 * Filter: Job Structured Direct Apply value
		 *
		 * Modify the directApply property defaults to true.
		 *
		 * @since 3.7.5
		 *
		 * @param bool $direct_apply
		 *
		 * @return bool
		 **/
		$ld['directApply'] = apply_filters( 'matador_bullhorn_import_save_job_jsonld_direct_apply', $ld['directApply'] );

		if ( null !== $job->dateEnd && $job->dateAdded < $job->dateEnd ) {
			$ld['validThrough'] = Helper::format_datetime_to_8601( $job->dateEnd );
		} else {
			$d = $this->get_post_date( $job );
			$d = $d->modify( '+ 1 years' );
			$ld['validThrough'] = Helper::format_datetime_to_8601( $d );
		}

		// Append $ld['jobLocation']
		if ( isset( $job->address->address1 ) ) {
			$ld['jobLocation']['address']['streetAddress'] = $job->address->address1;
		}
		if ( ! empty( $job->address->city ) ) {
			$ld['jobLocation']['address']['addressLocality'] = $job->address->city;
		}
		if ( ! empty( $job->address->state ) ) {
			$ld['jobLocation']['address']['addressRegion'] = $job->address->state;
		}
		if ( ! empty( $job->publishedZip ) ) {
			$ld['jobLocation']['address']['postalCode'] = $job->publishedZip;
		} elseif ( ! empty( $job->address->zip ) ) {
			$ld['jobLocation']['address']['postalCode'] = $job->address->zip;
		}
		if ( $this->the_job_country_name( $job->address->countryID ) ) {
			$ld['jobLocation']['address']['addressCountry'] = $this->the_job_country_name( $job->address->countryID );
		}

		$categories = get_transient( 'matador_import_categories_job_' . $wpid );

		if ( is_array( $categories ) ) {
			$ld['occupationalCategory'] = implode( ',', $categories );
		}

		delete_transient( 'matador_import_categories_job_' . $wpid );

		// Is Company checks for a setting if user wants to make LD data based on the hiring company or agency
		$is_company = Matador::setting( 'jsonld_hiring_organization' );

		$hiring_company_name = get_bloginfo( 'name' );
		$hiring_company_url  = get_bloginfo( 'url' );

		if ( 'company' === $is_company ) {
			if ( isset( $job->clientCorporation->name ) ) {
				$hiring_company_name = $job->clientCorporation->name;
			}
			if ( isset( $job->clientCorporation->id ) && $this->get_hiring_organization_url( (int) $job->clientCorporation->id ) ) {
				$hiring_company_url = $this->get_hiring_organization_url( (int) $job->clientCorporation->id );
			}
		}

		/**
		 * Filter: Job Structured Data Hiring Organization Name
		 *
		 * Modify the company name.
		 *
		 * @since 3.1.0
		 * @since 3.5.0 Added $is_company param.
		 *
		 * @param string   $name
		 * @param int      $wpid
		 * @param stdClass $job
		 * @param bool     $is_company
		 *
		 * @return string
		 **/
		$ld['hiringOrganization']['name'] = apply_filters( 'matador_get_hiring_organization_name', $hiring_company_name, $wpid, $job, $is_company );
		/**
		 * Filter Matador Get Hiring Organization URL
		 *
		 * Modify the company url.
		 *
		 * @since 3.1.0
		 * @since 3.5.0 Added $is_company param
		 *
		 * @param string   $url
		 * @param int      $wpid
		 * @param stdClass $job
		 * @param bool     $is_company
		 *
		 * @return string   $url
		 **/
		$ld['hiringOrganization']['sameAs'] = apply_filters( 'matador_get_hiring_organization_url', $hiring_company_url, $wpid, $job, $is_company );


		if ( ! empty( $job->educationDegree ) ) {

			if ( is_array( $job->educationDegree ) ) {
				$job->educationDegree = $job->educationDegree[0];
			}

			/**
			 * Filter: Bullhorn Import Education Degree "None" Trigger
			 *
			 * When jobs are imported from Bullhorn where the `educationDegree` field has a value that is equal to "none" as
			 * in "no education requirements for this role" Matador should know the value the company is using, which can be
			 * custom from company to company, to ensure we communicate via the Structured Data that there are no
			 * educational requirements.
			 *
			 * @since 3.8.0
			 *
			 * @param array $none_values Default: array with 'none' as its single element.
			 *
			 * @return array
			 */
			if ( in_array( strtolower( $job->educationDegree ), apply_filters( 'matador_bullhorn_import_education_degree_none', [ 'none' ] ), true ) ) {

				$ld['educationRequirements'] = 'no requirements';

			} else {

				$ld['educationRequirements']['@type'] = 'EducationalOccupationalCredential';
				/**
				 * Filter: Matador Bullhorn Import Education Degree (Before)
				 *
				 * Allows user to filter the educationDegree before we map it to allowed values
				 *
				 * should be set to one of the following:
				 * high school
				 * associate degree
				 * bachelor degree
				 * professional certificate
				 * postgraduate degree
				 *
				 * @since 3.7.9
				 *
				 * @param string educationDegree
				 *
				 * @return string
				 */
				$ld['educationRequirements']['credentialCategory'] = apply_filters( 'matador_bullhorn_import_education_degree', $job->educationDegree );

				// @see https://developers.google.com/search/docs/advanced/structured-data/job-posting#education-and-experience-properties-beta
				// "In addition to adding [educationRequirements->credentialCategory] property, continue to describe the
				// education requirements in the description property."
				if ( ! empty( $job->degreeList ) ) {
					$ld['educationRequirements']['description'] = esc_html( $job->degreeList );
				}
			}
		}

		if ( ! empty( $job->yearsRequired ) ) {
			$ld['experienceRequirements']['@type'] = 'OccupationalExperienceRequirements';
			$ld['experienceRequirements']['monthsOfExperience'] = $job->yearsRequired * 12;
		}

		if ( ! empty( $job->employmentType ) ) {

			$employment_type = $job->employmentType;

			/**
			 * Filter: Matador Bullhorn Import Employment Type (Before)
			 *
			 * Allows user to filter the employment_type before we map it to allowed values
			 *
			 * @since 3.6.0
			 *
			 * @param string $employment_type
			 *
			 * @return string
			 */
			$employment_type = apply_filters( 'matador_bullhorn_import_employment_type_before', $employment_type );

			$employment_types = array(
				'direct hire'      => 'FULL_TIME',
				'w2'               => 'FULL_TIME',
				'full'             => 'FULL_TIME',
				'fulltime'         => 'FULL_TIME',
				'full_time'        => 'FULL_TIME',
				'full-time'        => 'FULL_TIME',
				'full time'        => 'FULL_TIME',
				'part time'        => 'PART_TIME',
				'part_time'        => 'PART_TIME',
				'part-time'        => 'PART_TIME',
				'parttime'         => 'PART_TIME',
				'part'             => 'PART_TIME',
				'1099'             => 'CONTRACTOR',
				'1099+'            => [ "FULL_TIME", "CONTRACTOR" ],
				'contract'         => 'CONTRACTOR',
				'contractor'       => 'CONTRACTOR',
				'contracttohire'   => [ "FULL_TIME", "CONTRACTOR" ],
				'contract-to-hire' => [ "FULL_TIME", "CONTRACTOR" ],
				'contract_to_hire' => [ "FULL_TIME", "CONTRACTOR" ],
				'contract to hire' => [ "FULL_TIME", "CONTRACTOR" ],
				'temp'             => 'TEMPORARY',
				'temporary'        => 'TEMPORARY',
				'intern'           => 'INTERN',
				'volunteer'        => 'VOLUNTEER',
				'perdiem'          => 'PER_DIEM',
				'per_diem'         => 'PER_DIEM',
				'per-diem'         => 'PER_DIEM',
				'per diem'         => 'PER_DIEM',
				'other'            => 'OTHER',
			);

			/**
			 * Filter: Matador Job Structured Data Employment Type Map
			 *
			 * We've mapped Schema.org/Google Employment Types to default Bullhorn values with similar meaning. If using
			 * custom "Employment Types", modify this array with mappings for best results.
			 *
			 * @since 3.6.0
			 *
			 * @param array $employment_types
			 *
			 * @return array
			 */
			$employment_types = apply_filters( 'matador_bullhorn_import_employment_types', $employment_types );

			if ( array_key_exists( strtolower( $employment_type ), $employment_types ) ) {
				$employment_type = $employment_types[ strtolower( $employment_type ) ];
			} else {
				$employment_type = strtoupper( $employment_type );
			}

			/**
			 * Filter: Matador Bullhorn Import Employment Type (after processing, before set)
			 *
			 * Allows user to filter the after processing but before we finally set it
			 *
			 * @since 3.6.0
			 *
			 * @param string $employment_type
			 * @param string $input_employment_type
			 * @param array  $employment_types
			 *
			 * @return string
			 */
			$employment_type = apply_filters( 'matador_bullhorn_import_employment_type', $employment_type, $job->employmentType, $employment_types );

			if ( ! empty( $employment_type ) ) {
				$ld['employmentType'] = $employment_type;
			}
		}

		if ( isset( $job->benefits ) && ! empty( $job->benefits ) ) {
			$ld['jobBenefits'] = $job->benefits;
		}
		/**
		 * Filter: hide the salary in LD+JSON
		 *
		 * Use to hide the salary in LD+JSON.
		 *
		 * @since   3.8.4
		 */
		if ( apply_filters( 'matador_structured_data_include_salary', Matador::setting( 'jsonld_salary' ) ) ) {

			$min         = get_post_meta( $wpid, 'salary_low_value', true );
			$max         = get_post_meta( $wpid, 'salary_high_value', true );
			$salary      = get_post_meta( $wpid, 'salary', true );
			$salary_unit = strtoupper( get_post_meta( $wpid, 'salaryUnit', true ) );
			$currency    = get_post_meta( $wpid, 'salary_currency', true );

			if ( ( 0 === $min || ! empty( $min ) ) && ( ! empty( $max ) ) ) {
				$ld['baseSalary']['@type']          = 'MonetaryAmount';
				$ld['baseSalary']['currency']       = $currency;
				$ld['baseSalary']['value']['@type'] = 'QuantitativeValue';
				$ld['baseSalary']['value']['minValue'] = (int) $min;
				$ld['baseSalary']['value']['maxValue'] = (int) $max;
			} elseif ( ! empty( $salary ) ) {
				$ld['baseSalary']['@type']          = 'MonetaryAmount';
				$ld['baseSalary']['currency']       = $currency;
				$ld['baseSalary']['value']['@type'] = 'QuantitativeValue';
				$ld['baseSalary']['value']['value'] = (int) $salary;
			}

			if ( ! empty( $ld['baseSalary'] ) && ! empty( $salary_unit ) ) {
				//
				// @todo can we use translations to partially avoid this need? Our normalization method only considers English words.
				//
				switch (  preg_replace( '/\s+/', '', $salary_unit ) ) {
					case 'PERHOUR':
					case 'HOURLY':
					case '/HR':
					case '/HOUR':
						$unit = 'HOUR';
						break;
					case 'PERDAY':
					case 'DAILY':
					case '/DAY':
						$unit = 'DAY';
						break;
					case 'PERWEEK':
					case 'WEEKLY':
					case '/WEEK':
						$unit = 'WEEK';
						break;
					case 'PERMONTH':
					case 'MONTHLY':
					case '/MO':
					case '/MONTH':
						$unit = 'MONTH';
						break;
					case '/YR':
					case '/YEAR':
					case 'PERYEAR':
					case 'YEARLY':
					case 'PER ANNUM':
					case 'ANNUAL':
					case 'ANNUALLY':
						$unit = 'YEAR';
						break;
					default:
						if ( in_array( $salary_unit, [ 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR' ], true ) ) {
							$unit = strtoupper( $salary_unit );
						} else {
							$unit = '';
						}
						break;
				}

				/**
				 * Filter: Matador Job Structured Data Salary Unit
				 *
				 * Allows user to filter the Salary Unit, which is especially helpful if the company does not use
				 * standard terms supported by Google or impacted by our Google normalization.
				 *
				 * @param string $unit
				 * @param string $bullhorn_unit
				 *
				 * @return string
				 */
				$unit = apply_filters( 'matador_job_structured_data_salary_unit', $unit, $salary_unit );

				if ( $unit ) {
					$ld['baseSalary']['value']['unitText'] = $unit;
				}
			}
		}

		if ( isset( $job->bonusPackage ) && ! empty( $job->bonusPackage ) ) {
			$ld['incentiveCompensation'] = $job->bonusPackage;
		}

		// Remote/Work From Home/Telecommute
		if ( self::is_remote_job( $job ) ) {

			$ld['jobLocationType'] = "TELECOMMUTE";

			// Job Listing Schema (JSON+LD) requests we set a location type and value when a work from home/telecommute
			// job is posted. If not set, the country of the job hiring org will be automatically assumed. If the user
			// needs to limit a job for example to a specific state, ie: AZ, or can have multiple countries, they must
			// pass those into the value through Bullhorn custom fields.

			/**
			 * Filter: Matador Job Structured Data sets the BH field hold the type and value for applicantLocationRequirements
			 *
			 * Allows user to set which of the options set in bullhorn to be used for applicantLocationRequirements in the JSON+LD
			 *
			 * @param array $remote_type default ['type' => '', 'name' => '']
			 *
			 * @return array
			 */
			$fields = apply_filters( 'matador_bullhorn_import_location_requirements_fields', [
				'type' => '',
				'name' => '',
			] );

			if ( ! empty( $fields['type'] ) && ! empty( ! $job->{$fields['type']} ) && ! empty( $fields['name'] ) && ! empty( ! $job->{$fields['name']} ) ) {
				$ld['applicantLocationRequirements']['@type'] = $job->$fields['type'];
				$ld['applicantLocationRequirements']['@name'] = $job->$fields['name'];
			}
		}

		/**
		 * Matador Bullhorn Import JSON+LD
		 *
		 * @since 2.1.0
		 *
		 * @param array               $ld Array of keys and values that PHP will output into formatted JSON+LD
		 * @param stdClass            $job The job object as imported from Bullhorn
		 * @param Bullhorn_Connection $connection Instance of the Bullhorn Connection class, should you need to send
		 *                                        send additional requests to Bullhorn for LD-related data (not
		 *                                        recommended).
		 *
		 * @return array Array of keys and values that PHP will output into formatted JSON+LD
		 */
		$ld = apply_filters( 'matador_bullhorn_import_save_job_jsonld', $ld, $job, $this );

		update_post_meta( $wpid, 'jsonld', $ld );
	}

	/**
	 * Save Taxonomy Terms
	 *
	 * This will take an array of items from Bullhorn and insert it into
	 * WordPress as taxonomy terms.
	 *
	 * @since 2.1
	 *
	 * @param string $taxonomy
	 *
	 * @param array  $terms
	 */
	private function save_taxonomy( $terms = array(), $taxonomy = '' ) {
		if ( isset( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_insert_term( $term, $taxonomy );
			}
		}
	}

	/**
	 * get_currency format
	 *
	 * This gets the Currency format set in Bullhorn.
	 *
	 * @since 3.0.6
	 *
	 * @return string
	 * @throws Exception
	 *
	 */
	private function get_bullorn_currency_format() {
		$cache_key = 'matador_currency_format';

		$currency_format = get_transient( $cache_key );

		if ( false === $currency_format ) {
			new Event_Log( 'matador_import_get_currency_format', esc_html( sprintf( __( 'Requesting currency Format', 'matador-jobs' ) ) ) );

			// API Method
			$request = 'settings/currencyFormat';

			// API Method Parameters
			$params = array();

			// API Call
			$response = $this->request( $request, $params );
			// Handle Response
			if ( ! is_wp_error( $response ) ) {
				if ( isset( $response->currencyFormat ) ) {
					$currency_format = $response->currencyFormat;
					set_transient( $cache_key, $currency_format, DAY_IN_SECONDS );
				} else {
					$currency_format = '';
				}
			}
		}

		return $currency_format;
	}


	public function matador_save_job_meta( $data, $meta_id ) {

		switch ( $meta_id ) {

			case 'assignedUsers':
				foreach ( $data->data as $key => $user ) {
					$data->data[ $key ]->email = $this->get_email_user_id( $user->id );
				}

				break;
			case 'responseUser':
			case 'owner':
				$data->email = $this->get_email_user_id( $data->id );

				break;
		}

		return $data;
	}

	/**
	 * Get Email User ID
	 *
	 * @param $id
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function get_email_user_id( $id ) {

		$cache_key = 'matador_user_email';

		$email = get_transient( $cache_key );

		if ( false === $email || ! isset( $email[ $id ] ) ) {
			new Event_Log( 'matador_import_get_email_for_user_id', esc_html( sprintf( __( 'Requesting user data', 'matador-jobs' ) ) ) );

			// API Method
			$request = 'entity/CorporateUser/' . $id;

			// API Method Parameters
			$params = array(
				'fields' => 'email',
			);

			// API Call
			$response = $this->request( $request, $params );
			// Handle Response
			if ( ! is_wp_error( $response ) ) {
				if ( isset( $response->data->email ) ) {
					$email[ $id ] = $response->data->email;
					set_transient( $cache_key, $email, DAY_IN_SECONDS );
				} else {
					return '';
				}
			}
		}

		return $email[ $id ];
	}

	/**
	 * Before we start adding in new jobs, we need to delete jobs that are no
	 * longer in Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @param array $jobs
	 */
	private function destroy_jobs( $jobs = array() ) {

		if ( ! empty( $jobs ) ) {
			foreach ( $jobs as $job ) {
				// Translators: placeholder is Job ID
				Logger::add( 'info', 'destroy_jobs', sprintf( esc_html__( 'Delete Job(%1$s).', 'matador-jobs' ), $job ) );
				wp_delete_post( $job, true );
			}
		}

	}

	/**
	 * By using the 'matador_bullhorn_import_fields' filter, you can import any job field from your JobOrder object,
	 * including a number of
	 *
	 * By default, Matador Jobs Lite/Pro will import the following fields: id, title, description (or
	 * publicDescription), categories (or publishedCategory), dateAdded, dateEnd, status, address, clientCorporation,
	 * benefits, salary, salaryUnit, educationDegree, employmentType, yearsRequired, degreeList, bonusPackage, payRate,
	 * taxStatus, travelRequirements, willRelocate, notes, assignedUsers, responseUser, and owner.
	 *
	 * Use settings to change description or categories to publicDescription or publishedCategory.
	 *
	 * Filters can change the title to a custom field.
	 *
	 * All other fields needed should be added using this function and filter. Each to import is declared by name
	 * exactly matching its name in the Bullhorn field mappings and takes an array of arguments. 'name' is the name by
	 * which you wish Matador to refer to this field when saved. 'type' is the type of field it is. Currently we accept
	 * 'string' or 'association', and this affects how the data is sanitizied for security purposes. Finally, 'saveas'
	 * determines how its saved. When 'meta' it will be saved as Job Meta, 'core' is for use by custom functions or
	 * Matador core, or both via an array. Jobs Request "Fields"
	 *
	 * Prepares the "fields" clause for the Bullhorn Jobs Request.
	 * Uses settings and filters to prepare it nicely.
	 *
	 * @since 3.0.0
	 *
	 * @param string $format format to return
	 *
	 * @return string|array
	 */
	public static function the_jobs_fields( $format = 'string' ) {

		/**
		 * Filter: Matador Job Import Fields
		 *
		 * Use this filter to add fields to your Bullhorn JobOrder import. The returned array will be merged with the
		 * system-required defaults. Please use this filter instead of `matador_bullhorn_import_fields_return` whenever
		 * possible to prevent risk of breaking the core Matador Jobs sync.
		 *
		 * @since 3.0.0
		 *
		 * @param array Default empty array.
		 *
		 * @return array
		 */
		$fields = apply_filters( 'matador_bullhorn_import_fields', array() );

		$fields = array_merge( array(
			'id'                                => array(
				'type'   => 'integer',
				'saveas' => array( 'core', 'meta' ),
				'name'   => 'bullhorn_job_id',
			),
			self::the_jobs_title_field()       => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			self::the_jobs_description_field() => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			'dateAdded'                         => array(
				'type'   => 'timestamp',
				'saveas' => [ 'core', 'meta' ],
			),
			'dateLastPublished'                 => array(
				'type'   => 'timestamp',
				'saveas' => [ 'core', 'meta' ],
			),
			'dateLastModified'                  => array(
				'type'   => 'timestamp',
				'saveas' => [ 'core', 'meta' ],
			),
			'status'                            => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			'address'                           => array(
				'type'   => 'address',
				'saveas' => 'core',
			),
			'publishedZip'                      => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			self::the_category_field()         => array(
				'type'   => 'association',
				'saveas' => 'core',
			),
			'clientCorporation'                 => array(
				'type'   => 'association',
				'saveas' => array( 'core', 'meta' ),
			),
			'dateEnd'                           => array(
				'type'   => 'timestamp',
				'saveas' => 'meta',
			),
			'benefits'                          => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'salary'                            => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'salaryUnit'                        => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'educationDegree'                   => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'employmentType'                    => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'yearsRequired'                     => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'degreeList'                        => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'bonusPackage'                      => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'payRate'                           => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'taxStatus'                         => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'travelRequirements'                => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'willRelocate'                      => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'notes'                             => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'assignedUsers'                     => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'responseUser'                      => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'owner'                             => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'onSite'                            => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'isWorkFromHome'       => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
		), $fields );

		if ( Matador::setting( 'bullhorn_salary_low_field' ) && 'salary' !== Matador::setting( 'bullhorn_salary_low_field' ) ) {
			$fields[ Matador::setting( 'bullhorn_salary_low_field' ) ] = [
				'type' => 'string',
				'saveas' => 'core',
			];
		}

		if ( Matador::setting( 'bullhorn_salary_high_field' ) && 'salary' !== Matador::setting( 'bullhorn_salary_high_field' ) ) {
			$fields[ Matador::setting( 'bullhorn_salary_high_field' ) ] = [
				'type' => 'string',
				'saveas' => 'core',
			];
		}

		if ( apply_filters( 'matador_bullhorn_import_bullhorn_salary_currency_field', '' ) ) {
			$fields[ apply_filters( 'matador_bullhorn_import_bullhorn_salary_currency_field', '' ) ] = [
				'type' => 'string',
				'saveas' => 'core',
			];
		}

		/**
		 * Filter: Matador Job Structured Data sets the BH field hold the type and value for applicantLocationRequirements
		 *
		 * Allows user to set which of the options set in bullhorn to be used for applicantLocationRequirements in the JSON+LD
		 *
		 * @since 3.7.0
		 *
		 * @param array $remote_type default ['type' => '', 'name' => '']
		 *
		 * @return array
		 */
		$applicant_location_type = apply_filters( 'matador_bullhorn_import_location_requirements_fields', array(
			'type' => '',
			'name' => ''
		) );

		if ( ! empty( $applicant_location_type['type'] ) && ! empty( $applicant_location_type['name'] ) ) {

			$fields[ $applicant_location_type['name'] ] = array(
				'type'   => 'string',
				'saveas' => 'core',
			);
			$fields[ $applicant_location_type['type'] ] = array(
				'type'   => 'string',
				'saveas' => 'core',
			);
		}

		/**
		 * Filter: Matador Job Import Fields (Return)
		 *
		 * Allows you to modify the import fields before they are used for a Bullhorn import sync. You should avoid
		 * using this filter at all costs and use `matador_bullhorn_import_fields` if possible. The only right use case
		 * for this filter is if you need to modify `saveas` arguments for default fields. Use at your own risk as
		 * misuse can drastically affect Matador Job's ability to function.
		 *
		 * @since 3.7.0
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		$fields = apply_filters( 'matador_bullhorn_import_fields_return', $fields );

		if ( 'string' === $format ) {
			return implode( ',', array_keys( $fields ) );
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
	public function the_jobs_where() {

		$where = 'isOpen=true AND isDeleted=false AND status<>\'Archive\'';

		switch ( Matador::setting( 'bullhorn_is_public' ) ) {
			case 'all':
				break;
			case 'submitted':
				$where .= ' AND ( isPublic=1 OR isPublic=-1 )';
				break;
			case 'approved':
			case 'published':
			default:
				$where .= ' AND isPublic=1';
				break;
		}

		/**
		 * Deprecated Filter : Matador the Job Where
		 *
		 * @todo add deprecated handler
		 * @since      3.0.0
		 *
		 * @param string $where
		 *
		 * @return string
		 *
		 * @deprecated 3.5.0
		 *
		 */
		$where = apply_filters( 'matador-the-job-where', $where );

		/**
		 * Filter : Matador Bullhorn Import the Job Where
		 *
		 * @since 3.5.0
		 *
		 * @param string $where
		 *
		 * @return string $where
		 */
		return apply_filters( 'matador_bullhorn_import_the_job_where', $where );
	}

	/**
	 * Get Latest Synced Job Date
	 *
	 * This function determines the latest last updated date of the synced items to use as a baseline for determining
	 * if a found item should be updated by the Matador sync.
	 *
	 * @since 3.6.0
	 *
	 * @return int
	 */
	private function get_latest_synced_job_date() {

		if ( ! is_null( $this->latest_sync ) ) {
			return $this->latest_sync;
		}

		$this->latest_sync = 0;

		if ( get_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) ) ) {
			return $this->latest_sync;
		}

		$latest_synced_post = get_posts( array(
			'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
			'posts_per_page' => 1,
			'meta_key'       => '_matador_source_date_modified',
			'orderby'        => 'meta_value_num',
		) );

		if ( ! empty( $latest_synced_post ) && ! is_wp_error( $latest_synced_post ) ) {
			$last_modified = get_post_meta( $latest_synced_post[0]->ID, '_matador_source_date_modified', true );
			if ( ! empty( $last_modified ) ) {
				$this->latest_sync = (new DateTimeImmutable())->setTimestamp( (int) $last_modified );
			}
		}

		return $this->latest_sync;
	}

	/**
	 * Job Title Field
	 *
	 * Which field will be used for the title.
	 *
	 * @since 3.4.0
	 *
	 * @return string title field name
	 */
	private static function the_jobs_title_field() {

		/**
		 * Filter Matador Import Job Description
		 *
		 * If there is a filter here, it is overriding one of the two core fields.
		 *
		 * @since 3.4.0
		 *
		 * @return string description field name (in external source)
		 */
		return apply_filters( 'matador_import_job_title_field', 'title' );
	}


	/**
	 * Job URL Slug (Post_Name)
	 *
	 * How should the importer determine the job URL slug
	 *
	 * @since 3.4.0
	 *
	 * @param stdClass $job
	 *
	 * @return string URL formatted for the job slug.
	 */
	private static function the_jobs_slug( $job ) {

		$slug = '';

		$setting = Matador::setting( 'post_type_slug_job_listing_each' );

		switch ( $setting ) {
			case 'title_id':
				$slug = $job->{self::the_jobs_title_field()} . ' ' . $job->id;
				break;
			case 'id_title':
				$slug = $job->id . ' ' . $job->{self::the_jobs_title_field()};
				break;
			case 'title':
				$slug = $job->{self::the_jobs_title_field()};
				break;
			default:
				break;
		}

		/**
		 * Filter : Matador Import Job Slug
		 *
		 * Filter the imported job slug. Useful to replace, prepend, or append the slug. Also, can be used to add a
		 * custom option to the job slug setting and handle it. Should return a string.
		 *
		 * @since 3.4.0
		 *
		 * @param string   $slug
		 * @param stdClass $job
		 * @param string   $setting
		 *
		 * @return string
		 */
		$slug = apply_filters( 'matador_import_job_slug', $slug, $job, $setting );

		// We can't return an empty string, so set the job title as the slug if the string is false/empty
		$slug = ! empty( $slug ) ? $slug : $job->{self::the_jobs_title_field()};

		// WordPress core function sanitize_title(), which converts a string into URL safe slug.
		return sanitize_title( $slug );
	}

	/**
	 * Job Description Field
	 *
	 * Looks for a setting for Job Description field and verifies its a valid option.
	 *
	 * @since 3.0.0
	 *
	 * @return string description field name
	 */
	private static function the_jobs_description_field() {

		$setting = Matador::setting( 'bullhorn_description_field' );

		$description = in_array( $setting, array(
			'description',
			'publicDescription'
		), true ) ? $setting : 'description';

		/**
		 * Filter Matador Import Job Description
		 *
		 * If there is a filter here, it is overriding one of the two core fields.
		 *
		 * @since 3.4.0
		 * @return string description field name (in external source)
		 */
		return apply_filters( 'matador_import_job_description_field', $description );
	}

	/**
	 * Job Categories Field
	 *
	 * Looks for a setting for Job Category field and verifies its a valid option.
	 *
	 * @since 3.5.0
	 *
	 * @return string category field name
	 */
	private static function the_category_field() {

		$setting = Matador::setting( 'bullhorn_category_field' );

		$categories = in_array( $setting, array( 'categories', 'publishedCategory' ), true ) ? $setting : 'categories';

		/**
		 * Filter Matador Import Job Category Field
		 *
		 * Override the setting and/or the default fields.
		 *
		 * @since 3.5.0
		 * @return string categories field name (in external source)
		 *
		 */
		return apply_filters( 'matador_import_job_category_field', $categories );
	}

	/**
	 * Job Country Name
	 *
	 * Looks for a setting for Job Description field and verifies its a valid option.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $country_id
	 *
	 * @return string country name
	 *
	 * @throws Exception
	 */
	private function the_job_country_name( $country_id ) {

		$country_list = $this->get_countries();

		if ( array_key_exists( $country_id, $country_list ) ) {
			return $country_list[ $country_id ];
		}

		return null;
	}

	/**
	 * Job Description Allowed Fields
	 *
	 * Allowed fields array for the wp_kses() filter on the description imported from Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function the_jobs_description_allowed_tags() {
		return apply_filters( 'matador_the_jobs_description_allowed_tags', array(
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
			),
			'br'     => array(),
			'hr'     => array(),
			'em'     => array(),
			'i'      => array(),
			'strong' => array(),
			'b'      => array(),
			'p'      => array(
				'align' => true,
			),
			'img'    => array(
				'alt'    => true,
				'align'  => true,
				'height' => true,
				'src'    => true,
				'width'  => true,
			),
			'div'    => array(
				'align' => true,
			),
			'table'  => array(
				'border'      => true,
				'cellspacing' => true,
				'cellpadding' => true,
			),
			'thead'  => array(),
			'tbody'  => array(),
			'tr'     => array(),
			'th'     => array(
				'colspan' => true,
				'rowspan' => true,
			),
			'td'     => array(
				'colspan' => true,
				'rowspan' => true,
			),
			'span'   => array(),
			'h1'     => array(
				'align' => true,
			),
			'h2'     => array(
				'align' => true,
			),
			'h3'     => array(
				'align' => true,
			),
			'h4'     => array(
				'align' => true,
			),
			'h5'     => array(
				'align' => true,
			),
			'h6'     => array(
				'align' => true,
			),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'dl'     => array(),
			'dt'     => array(),
			'dd'     => array(),
			'video'  => array(
				'autoplay' => true,
				'controls' => true,
				'height'   => true,
				'loop'     => true,
				'muted'    => true,
				'poster'   => true,
				'preload'  => true,
				'src'      => true,
				'width'    => true,
			),
		) );
	}

	/**
	 * Job Description Allowed Protocols
	 *
	 * Allowed protocols array for the wp_kses() filter on the job description.
	 *
	 * WARNING: Allowing additional protocols deemed unsafe by WordPress is DANGEROUS. Only do so if you know what you
	 * are doing and the implications of such. Matador Jobs provides this filter as a tool to limit additional allowed
	 * protocols to only job descriptions, but provides no warranty of the security of what you let through.
	 *
	 * @since 3.6.4
	 *
	 * @return array
	 */
	private function the_jobs_description_allowed_protocols() {

		/**
		 * Job Description Allowed Protocols
		 *
		 * @since 3.6.4
		 *
		 * @param array $protocols Default is returned value from wp_allowed_protocols(), subject to any global-level
		 *                         filtered values.
		 *
		 * @return array
		 */
		return apply_filters( 'matador_the_jobs_description_allowed_protocols', wp_allowed_protocols() );
	}

	/**
	 * Get Job Date Last Modified
	 *
	 * @param $job
	 *
	 * @return mixed
	 */
	private function get_date_modified( $job ) {

		switch ( Matador::setting( 'bullhorn_is_public' ) ) {
			case 'all':
				return $job->dateLastModified;
			default:
				// If we get here, we should always have dateLastPublished, but this is
				// a backup in case of something weird and/or an integration is misusing the function.
				return ( ! is_null( $job->dateLastPublished ) ) ? $job->dateLastPublished : $job->dateLastModified;
			// return $job->dateLastPublished ?? $job->dateLastModified; @todo PHP 7.0
		}
	}

	/**
	 * Get Job Post Date
	 *
	 * @param $job
	 *
	 * @return DateTimeImmutable
	 */
	private function get_post_date( $job ) {

		switch ( Matador::setting( 'bullhorn_date_field' ) ) {
			case 'date_last_published':
				$post_date = $job->dateLastPublished ?: $job->dateLastModified;
				// $post_date = $job->dateLastPublished ?? $job->dateLastModified; // @todo PHP 7.0
				break;
			case 'date_last_modified':
				$post_date = $job->dateLastModified;
				break;
			default:
				$post_date = $job->dateAdded;
				break;
		}

		if (
			'all' === Matador::setting( 'bullhorn_is_public' ) &&
			'date_last_published' === Matador::setting( 'bullhorn_date_field' )
		) {
			$post_date = $job->dateLastModified;
		}

		return $post_date;
	}
}
