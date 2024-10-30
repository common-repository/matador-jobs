<?php
/**
 * Matador / Setup Variables
 *
 * This defines the plugin-wide variables and provides filters to manipulate them.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Variables {

	/**
	 * Variable: Labels
	 *
	 * Holds an array of default labels.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	private static $_values;

	/**
	 * Magic Method: Constructor
	 *
	 * Class constructor prepares '$_values' variable with defaults.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// Simple Default Assignments;
		$d = array();

		// Post Types
		$d['post_type_key_application']  = 'matador-applications';
		$d['post_type_key_job_listing']  = 'matador-job-listings';
		$d['post_type_slug_job_listing'] = Matador::setting( 'post_type_slug_job_listing' ) ?: 'jobs';

		// Applications Related
		$d['application_data']               = '_application_data';
		$d['accepted_files_types']           = __( 'DOC, DOCX, PDF, HTML, and TXT', 'matador-jobs' );
		$d['accepted_file_extensions']       = array( '.doc', '.docx', '.pdf', '.html', '.txt' );
		$d['accepted_file_size_limit']       = 2; //in megabyte(s). Max per Bullhorn is 5mb.
		$d['application_name_suffixes']      = array(
			'jr',
			'sr',
			'ii',
			'iii',
			'iv',
			'v',
			'vi',
			'vii',
			'1st',
			'2nd',
			'3rd',
			'4th',
			'5th',
			'6th',
			'7th'
		);
		$d['application_allowed_files_keys'] = array( 'resume', 'letter', 'files' );
		$d['submission_type']                = '_matador_submission_type';
		$d['recruiter_email_sent']           = '_matador_submission_recruiter_email_sent';

		// Candidate Sync Related
		$d['candidate_bhid']        = '_bullhorn_candidate_bhid';
		$d['candidate_data']        = '_bullhorn_candidate_data';
		$d['candidate_resume']      = '_bullhorn_candidate_resume';
		$d['candidate_sync_status'] = '_bullhorn_candidate_sync_status';
		$d['candidate_sync_step']   = '_bullhorn_candidate_sync_step';
		$d['candidate_sync_log']    = '_bullhorn_candidate_sync_log';
		$d['candidate_file_links']  = '_bullhorn_candidate_file_links';

		// Options Page/Saving
		$d['options_key']  = 'matador-settings';
		$d['options_page'] = $d['post_type_key_job_listing'] . '_page_' . $d['options_key'];
		$d['options_url']  = get_admin_url() . 'edit.php?post_type=' . $d['post_type_key_job_listing'] . '&page=' . $d['options_key'];

		// Upgrade Page/Saving
		$d['upgrade_key']  = 'matador-upgrade';
		$d['upgrade_page'] = $d['post_type_key_job_listing'] . '_page_' . $d['upgrade_key'];
		$d['upgrade_url']  = get_admin_url() . 'edit.php?post_type=' . $d['post_type_key_job_listing'] . '&page=' . $d['upgrade_key'];

		// URL & Endpoints
		$d['api_endpoint_prefix']     = 'matador/api/';
		$d['api_redirect_uri']        = trailingslashit( home_url() ) . $d['api_endpoint_prefix'] . 'authorize/';
		$d['apply_url_suffix']        = 'apply';
		$d['confirmation_url_suffix'] = 'confirmation';

		// Uploads Location/URL Access
		$uploads                    = wp_upload_dir();
		$d['uploads_base_dir_name'] = 'matador_uploads';
		$d['uploads_base_dir']      = wp_normalize_path( $uploads['basedir'] . DIRECTORY_SEPARATOR . $d['uploads_base_dir_name'] . DIRECTORY_SEPARATOR );
		$d['uploads_base_url']      = trailingslashit( $uploads['baseurl'] ) . trailingslashit( $d['uploads_base_dir_name'] );
		$d['uploads_cv_dir_name']   = 'application_files';
		$d['uploads_cv_dir']        = wp_normalize_path( $d['uploads_base_dir'] . $d['uploads_cv_dir_name'] . DIRECTORY_SEPARATOR );
		$d['uploads_cv_path']       = $d['uploads_cv_dir'];
		$d['uploads_cv_url']        = $d['uploads_base_url'] . trailingslashit( $d['uploads_cv_dir_name'] );
		$d['log_file_path']         = $d['uploads_base_dir'];
		$d['log_file_url']          = $d['uploads_base_url'];
		$d['json_file_path']        = $d['uploads_base_dir'];

		// Other Labels
		$d['css_class_prefix'] = 'matador';

		// Transient Keys
		$d['transients'] = array(

			'bullhorn_session'                                             => 'matador_bullhorn_session',
			'bullhorn_skills_cache'                                        => 'matador_bullhorn_skills_cache',
			'bullhorn_business_sectors_cache'                              => 'matador_bullhorn_business_sectors_cache',
			'bullhorn_categories_cache'                                    => 'matador_bullhorn_categories_cache',
			'bullhorn_specialties_cache'                                   => 'matador_bullhorn_specialties_cache',
			'bullhorn_api_assistant'                                       => 'matador_bullhorn_api_assistant',
			'bullhorn_auto_reauth'                                         => 'matador_bullhorn_auto_reauth',
			'bullhorn_valid_redirect'                                      => 'matador_bullhorn_valid_redirect',
			'bullhorn_import_jobs_done'                                    => 'matador_bullhorn_import_jobs_done',
			'bullhorn_companies_cache'                                     => 'matador_bullhorn_companies_cache',
			'bullhorn_contact_meta_cache'                                  => 'matador_bullhorn_contact_meta_cache',
			'bullhorn_entitlements'                                        => 'matador_bullhorn_entitlements',
			'bullhorn_consent_object_skip'                                 => 'matador_bullhorn_consent_object_skip',
			'bullhorn_logged_in_user'                                      => 'matador_bullhorn_logged_in_user',
			'bullhorn_career_portal_domain_root'                           => 'matador_bullhorn_career_portal_domain_root',
			'bullhorn_career_portal_remind_24'                             => 'matador_bullhorn_career_portal_remind_24',
			'google_indexing_api_grant'                                    => 'matador_google_indexing_api_grant',
			'google_indexing_api_watch_each'                               => 'matador_google_indexing_api_watch_',
			'flush_rewrite_rules'                                          => 'matador_flush_rewrite_rules',
			'settings_fields_errors'                                       => 'matador_settings_fields_errors',
			'email_timeout-administrator-notice-consent-object-permission' => 'email_timeout_administrator_notice_consent_object_permission',
			'email_timeout-administrator-notice-cron'                      => 'email_timeout_administrator_notice_cron',
			'email_timeout-administrator-notice-disconnected'              => 'email_timeout_administrator_notice_disconnected',
			'email_timeout-administrator-notice-general'                   => 'email_timeout_administrator_notice_general',
			'email_delay-administrator-notice-disconnected'                => 'email_delay_administrator_notice_disconnected',
			// "ET" = "E.T. Phone Home" = Analytics
			'analytics_recent_applications'                                => 'matador_ET_recent_applications',
			'doing_sync'                                                   => 'matador_doing_sync',
			'doing_sync_task'                                              => 'matador_doing_sync_task',
			'doing_sync_tasks'                                             => 'matador_doing_sync_tasks',
			'doing_sync_task_data'                                         => 'matador_doing_sync_task_data',
			'doing_sync_task_step'                                         => 'matador_doing_sync_task_step',
			'doing_sync_no_cache'                                          => 'matador_doing_sync_no_cache',
			'doing_app_sync'                                               => 'matador_doing_app_sync',
			'doing_sync_continuation_method'                               => 'matador_doing_sync_continuation_method',
			'consent_object'                                               => 'matador_bullhorn_candidate_consent_object',
			'next_scheduled_event'                                         => 'matador_next_scheduled_event',
			'monitor_cron_last_test'                                       => 'matador_monitor_cron_last_test',
			'monitor_cron_event_late'                                      => 'matador_monitor_cron_event_late',
			'monitor_cron_event_delay'                                     => 'matador_monitor_cron_event_delay_next_check',
			'admin_check_license_activation'                               => 'matador_admin_check_license_activation',
		);

		// Nonce Keys
		$d['nonce'] = array(
			'options'          => 'matador-settings',
			'application'      => 'matador_application',
			'bh-api-assistant' => 'matador_bullhorn_api_assistant',
			'sync'             => 'matador_sync',
			'sync_continue'    => 'matador_sync_continue',
		);

		$d['job_taxonomies'] = apply_filters( 'matador_job_taxonomies', array(
			'category' => array(
				'key'    => 'matador-categories',
				'single' => _x( 'category', 'Job Category Singular Name.', 'matador-jobs' ),
				'plural' => _x( 'categories', 'Job Category Plural Name.', 'matador-jobs' ),
				'args'   => array(
					'public'             => true,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'show_in_nav_menus'  => true,
					'show_tagcloud'      => true,
					'show_in_quick_edit' => true,
					'show_admin_column'  => true,
					'hierarchical'       => false,
				),
			),
			'location' => array(
				'key'    => 'matador-locations',
				'single' => _x( 'location', 'Job Location Singular Name.', 'matador-jobs' ),
				'plural' => _x( 'locations', 'Job Location Plural Name.', 'matador-jobs' ),
				'args'   => array(
					'show_admin_column' => true,
				),
			),
			'type'     => array(
				'key'    => 'matador-types',
				'single' => _x( 'type', 'Job Type Singular Name.', 'matador-jobs' ),
				'plural' => _x( 'types', 'Job Type Plural Name.', 'matador-jobs' ),
			),
		) );

		foreach ( $d['job_taxonomies'] as $taxonomy => $args ) {
			$slug_key = 'taxonomy_slug_' . $taxonomy;

			$d['job_taxonomies'][ $taxonomy ]['slug'] = Matador::setting( $slug_key ) ?: 'matador-' . $taxonomy;
		}

		self::$_values = apply_filters( 'matador_variable_all', $d );
	}

	/**
	 * Magic Method: Get
	 *
	 * Magic method gets option from $_data
	 *
	 * @since 3.0.0
	 *
	 * @param string $key the name of the option
	 *
	 * @return mixed value of the option
	 */
	public function __get( $key ) {
		$raw = array_key_exists( $key, self::$_values ) ? self::$_values[ $key ] : null;

		/**
		 * Dynamic Filter: Get Option
		 *
		 * Filter the option after its been pulled out of the database.
		 *
		 * @since   3.0.0
		 */
		return apply_filters( 'matador_variable_' . $key, $raw );
	}

	/**
	 * @return array
	 */
	public static function get_values() {

		return self::$_values;
	}

}
