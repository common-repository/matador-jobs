<?php
/**
 * Matador / Settings / Fields
 *
 * This contains the settings structure and provides functions to manipulate saved settings.
 * This class is extended to create and validate field input on the settings page.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin / Settings
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Fields {

	/**
	 * Instance of Class (Singleton)
	 *
	 * @var Settings_Fields
	 * @since 3.0.0
	 */
	private static $instance;

	/**
	 * Fields (cached)
	 *
	 * @var array
	 * @since 3.0.0
	 */
	private $fields;

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 3.0.0
	 */
	private function __construct() {

		$this->standard_conditional_fields();
		$this->fields = $this->fields();

		add_filter( 'matador_settings_after_field_post_type_slug_job_listing', [ __CLASS__, 'careerPortalDomainRoot_description' ] );
		add_filter( 'matador_settings_after_field_bullhorn_ignore_career_portal_root', [ __CLASS__, 'careerPortalDomainRoot_description' ] );
	}

	/**
	 * Instance Call
	 *
	 * @return Settings_Fields
	 * @since 3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Settings_Fields();
		}

		return self::$instance;
	}

	/**
	 * Fields
	 *
	 * @return array
	 */
	public function fields() {
		/**
		 * Filter: Options Tabs
		 *
		 * Use to add tab(s) with sections and fields.
		 *
		 * @since   1.0.0
		 */
		return apply_filters( 'matador_options_fields', array(
			'general'      => array(
				esc_html_x( 'General', 'General Settings Tab Name', 'matador-jobs' ),
				/**
				 * Filter: Options Sections in the General Tab
				 *
				 * Use to add sections and fields to a tab.
				 *
				 * @since   1.0.0
				 */
				apply_filters( 'matador_options_fields_bullhorn', array(
					/**
					 * Filter: Options Fields in the General Tab, Licensing Section
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'general_licensing'     => apply_filters( 'matador_options_fields_general_licensing', array(
						esc_html_x( 'Matador Jobs Pro License', 'Licensing Settings Section Name', 'matador-jobs' ),
						'license_core'        => array(
							'type'     => 'license-key',
							'label'    => esc_html__( 'Matador License Key for Support and Updates', 'matador-jobs' ),
							'supports' => array( 'wp_job_manager', 'settings' ),
							'sanitize' => 'trim',
						),
						'license_core_status' => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array( 'bh_api_assistant' ),
							'public'   => false,
						),
					) ),
					/**
					 * Filter: Options Fields in the General Tab, Bullhorn API Section
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'general_bullhorn'      => apply_filters( 'matador_options_fields_general_bullhorn', array(
						esc_html_x( 'Bullhorn API Connection', 'Bullhorn API Settings Section Name.', 'matador-jobs' ),
						'bullhorn_api_connect'         => array(
							'type'     => 'bullhorn-api-connect',
							'supports' => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_api_client'          => array(
							'type'       => 'bullhorn-client',
							'label'      => esc_html__( 'Bullhorn API Client ID', 'matador-jobs' ),
							'attributes' => array( 'required' => true ),
							'supports'   => array( 'bh_api_assistant' ),
							'sanitize'   => 'text',
						),
						'bullhorn_api_secret'          => array(
							'type'       => 'text',
							'label'      => esc_html__( 'Bullhorn API Client Secret Key', 'matador-jobs' ),
							'attributes' => array( 'required' => true ),
							'supports'   => array( 'bh_api_assistant' ),
						),
						'bullhorn_api_user'            => array(
							'type'     => 'text',
							'label'    => esc_html__( 'Bullhorn API User ID', 'matador-jobs' ),
							'supports' => array( 'bh_api_assistant' ),
						),
						'bullhorn_api_pass'            => array(
							'type'     => 'text',
							'label'    => esc_html__( 'Bullhorn API User Password', 'matador-jobs' ),
							'supports' => array( 'bh_api_assistant' ),
						),
						'bullhorn_api_assistant'       => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array( 'bh_api_assistant' ),
							'public'   => false,
						),
						'matador_version'              => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array(),
							'default'  => Matador::VERSION,
						),
						'bullhorn_api_has_authorized'  => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array(),
							'public'   => false,
						),
						'bullhorn_api_is_connected'    => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array(),
							'public'   => false,
						),
						'bullhorn_api_client_is_valid' => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array(),
							'public'   => false,
						),
						'bullhorn_grandfather'         => array(
							'type'     => 'hidden',
							'label'    => null,
							'supports' => array(),
							'public'   => false,
						),
						'bullhorn_api_cluster_id' => array(
							'type' => 'hidden',
							'label' => null,
							'supports' => array(),
							'public' => false,
						),
						'bullhorn_ignore_career_portal_root' => array(
							'type' => 'toggle',
							'label'       => esc_html__( 'Disable Career Portal URL Check', 'matador-jobs' ),
							'default'     => '0',
							'sanitize'    => 'bool',
							'supports'    => array( 'bh_api_assistant' ),
							'description' => __( 'Once per day, Matador will query Bullhorn to check if your site job board is listed as the Career Portal URL. Some sites will skip this check automatically when we detect common hints that explain it is a "staging" or "development" site. You may prevent this test and its warning messages by enabling this setting.', 'matador-jobs' ),
						),
					) ),
					/**
					 * Filter: Options fields in the Notifications Tab, All Settings Section
					 *
					 * Use to add fields to a section.`
					 *
					 * @since   3.6.0
					 */
					'email_options'         => apply_filters( 'matador_options_fields_general_email', array(
						esc_html_x( 'Email Settings', 'Email Settings Section Name', 'matador-jobs' ),
						'email_from_name'    => array(
							'type'       => 'text',
							'label'      => esc_html__( 'Email Default From Name', 'matador-jobs' ),
							'default'    => get_bloginfo( 'name' ),
							'sanitize'   => 'text',
							'attributes' => array(
								'placeholder' => get_bloginfo( 'name' ),
							),
							'supports'   => array( 'settings', 'wp_job_manager' ),
						),
						'email_from_address' => array(
							'type'       => 'text',
							'label'      => esc_html__( 'Email Default From Address', 'matador-jobs' ),
							'default'    => get_bloginfo( 'admin_email' ),
							'sanitize'   => 'email_list',
							'attributes' => array(
								'placeholder' => get_bloginfo( 'admin_email' ),
							),
							'supports'   => array( 'settings', 'wp_job_manager' ),
						),
					) ),
					/**
					 * Filter: Options fields in the General Section, All Settings Section
					 *
					 * @since   3.7.0
					 */
					'global_options'         => apply_filters( 'matador_options_fields_general_general', array(
						esc_html_x( 'General Settings', 'General Settings Section Name', 'matador-jobs' ),
						'enable_styles' => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Matador Styles', 'matador-jobs' ),
							'default'     => '1',
							'sanitize'    => 'bool',
							'supports'    => array( 'settings', 'wp_job_manager' ),
							'description' => __( 'Matador Jobs comes with default styling to help it display nicely on your site. Turn these off when building customized sites.', 'matador-jobs' ),
						),
						'matador_site_url' => array(
							'type' => 'hidden',
							'label'    => null,
							'supports' => array(),
							'public'   => false,
						),
					) ),
					/**
					 * Filter: Options fields in the Notifications Tab, All Settings Section
					 *
					 * Use to add fields to a section.`
					 *
					 * @since   1.0.0
					 */
					'general_notifications' => apply_filters( 'matador_options_fields_general_notification', array(
						esc_html_x( 'Logging & Error Handling', 'Admin Notifications Section Name', 'matador-jobs' ),
						'logging'      => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Matador Logging', 'matador-jobs' ),
							// Translators: %1$s is the path to the folder with log information.
							'description' => __( 'To help monitor the health of your site and its communications with Bullhorn, Matador can keep extensive logs. We recommend you keep at least 2 days of logs, though you can save up to 30 days of logs. Set to "Off" to disable logging.', 'matador-jobs' ) . '<br />' . Event_Log::list_logs(),
							'options'     => array(
								'0'  => esc_html__( 'Off', 'matador-jobs' ),
								'1'  => esc_html__( '1 Days', 'matador-jobs' ),
								'2'  => esc_html__( '2 Days', 'matador-jobs' ),
								'3'  => esc_html__( '3 Days', 'matador-jobs' ),
								'4'  => esc_html__( '4 Days', 'matador-jobs' ),
								'5'  => esc_html__( '5 Days', 'matador-jobs' ),
								'6'  => esc_html__( '6 Days', 'matador-jobs' ),
								'7'  => esc_html__( '7 Days', 'matador-jobs' ),
								'8'  => esc_html__( '8 Days', 'matador-jobs' ),
								'9'  => esc_html__( '9 Days', 'matador-jobs' ),
								'10' => esc_html__( '10 Days', 'matador-jobs' ),
								'20' => esc_html__( '20 Days', 'matador-jobs' ),
								'30' => esc_html__( '30 Days', 'matador-jobs' ),
							),
							'default'     => '2',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'notify_admin' => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Send Admin Email Alerts', 'matador-jobs' ),
							'description' => esc_html__( 'Send an administrator an email when Matador Jobs has a major error that requires user intervention.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'admin_email'  => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Admin Email Address(es)', 'matador-jobs' ),
							'description' => __( 'Separate each email with a comma. You may use the "John Smith &lt;john@website.com&gt;" format or just "john@website.com" format.', 'matador-jobs' ) . ' ' . esc_html__( 'Emails may be filtered by email security protocols unless your site has email validation systems in place like DKIM. If you\'re not sure, use an email address from outside your domain (like at MSN, Yahoo, or Gmail).', 'matador-jobs' ),
							'default'     => get_bloginfo( 'admin_email' ),
							'sanitize'    => 'email_list',
							'attributes'  => array(
								'placeholder' => get_bloginfo( 'admin_email' ),
							),
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
					) ),
				) ),
			),
			/**
			 * Filter: Options Sections in the Bullhorn API Tab
			 *
			 * Use to add sections and fields to a tab.
			 *
			 * @since   1.0.0
			 */
			'jobs'         => array(
				esc_html_x( 'Job Listings', 'Jobs Listings Setting Tab Name', 'matador-jobs' ),
				apply_filters( 'matador_options_jobs_tab_fields', array(
					/**
					 * Filter: Options fields in the Bullhorn Tab, Import Section
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'jobs_import'          => apply_filters( 'matador_options_fields_jobs_import', array(
						esc_html_x( 'Bullhorn Import', 'Bullhorn Import Settings Section Name', 'matador-jobs' ),
						'bullhorn_auto_sync'         => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Automatically Sync Jobs', 'matador-jobs' ),
							'description' => esc_html__( 'Set whether you want your site automatically check for job changes from Bullhorn in the background.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_is_public'         => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Published Status to Import', 'matador-jobs' ),
							'options'     => array(
								// Translators: The name of this needs to match the user's Bullhorn account setting.
								'approved'  => esc_html__( '"Published -- Approved" Jobs', 'matador-jobs' ),
								// Translators: The name of this needs to match the user's Bullhorn account setting.
								'submitted' => esc_html__( '"Published -- Submitted" Jobs', 'matador-jobs' ),
								'all'       => esc_html__( 'All Jobs (careful!)', 'matador-jobs' ),
							),
							'description' => esc_html__( 'Determines which level of "publishing" a job must have to be imported. It is recommended to use only "Published -- Approved", but you may set more general rules. Caution using "All Jobs", as this can potentially import jobs before they\'re done being created.', 'matador-jobs' ),
							'default'     => 'submitted',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_date_field' => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Job Date Field', 'matador-jobs' ),
							'description' => esc_html__( 'Choose the date field that will be used as the "Published" date for your job.', 'matador-jobs' ),
							'options'     => array(
								// Translators: Placeholder keeps the key name for the API field.
								'date_added'          => esc_html( sprintf( __( 'Date Added (%s)', 'matador-jobs' ), 'dateAdded' ) ),
								// Translators: Placeholder keeps the key name for the API field.
								'date_last_modified'  => esc_html( sprintf( __( 'Date Last Updated (%s)', 'matador-jobs' ), 'dateLastModified' ) ),
								// Translators: Placeholder keeps the key name for the API field.
								'date_last_published' => esc_html( sprintf( __( 'Date Last Published (%s)', 'matador-jobs' ), 'dateLastPublished' ) ),
							),
							'default'     => 'date_added',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_description_field' => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Description Field', 'matador-jobs' ),
							'description' => esc_html__( 'Choose to display either the job\'s Description or the Public Description.', 'matador-jobs' ),
							'options'     => array(
								'description'       => esc_html__( 'Description (Bullhorn Default)', 'matador-jobs' ),
								'publicDescription' => esc_html__( 'Public Description', 'matador-jobs' ),
							),
							'default'     => 'publicDescription',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_category_field'    => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Category Field', 'matador-jobs' ),
							'description' => esc_html__( 'Choose to categorize jobs by either the single "Published Category" set during Publishing or by the (one or many) "Job Categories" defined in the Job Order.', 'matador-jobs' ),
							'options'     => array(
								'categories'        => esc_html__( 'Job Categories (multiple)', 'matador-jobs' ),
								'publishedCategory' => esc_html__( 'Published Category (single)', 'matador-jobs' ),
							),
							'default'     => 'categories',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_location_term' => array(
							'type' => 'select',
							'label' => esc_html__( 'Location Taxonomy Term', 'matador-jobs' ),
							'description' => esc_html__( 'Choose which part of a job address should be used for the location taxonomy. The location taxonomy is used for filtering and searching jobs.', 'matador-jobs' ),
							'options'     => array(
								'city'    => esc_html__( 'City/Locality', 'matador-jobs' ),
								'state'   => esc_html__( 'State/Province', 'matador-jobs' ),
								'zip'     => esc_html__( 'ZIP/Postal Code', 'matador-jobs' ),
								'country' => esc_html__( 'Country', 'matador-jobs' ),
							),
							'default'     => 'city',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_location_remote_term' => array(
							'type' => 'toggle',
							'label' => esc_html__( '"Remote" Location Taxonomy Term', 'matador-jobs' ),
							'description' => esc_html__( 'Add the "Remote" Location Taxonomy Term to a job when job is "Remote"/"Work From Home".', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'show_job_meta'              => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Show Job Info Header', 'matador-jobs' ),
							'default'     => '1',
							'description' => esc_html__( 'Turn on/off the Job Info header that shows before the job description.', 'matador-jobs' ),
							'supports'    => [ 'settings' ],
						),
					) ),
					/**
					 * Filter: Salary Related Options Jobs Import Tab
					 *
					 * Use to add fields to a section.
					 *
					 * @since   3.8.4
					 */
					'jobs_salary'          => apply_filters( 'matador_options_fields_jobs_salary', [
						esc_html_x( 'Salary Options', 'Salary Settings Section Name', 'matador-jobs' ),
						'jsonld_salary'              => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Display Salary/Pay Rate', 'matador-jobs' ),
							'default'     => '1',
							'description' => esc_html__( 'Display (and provide external sources, ie: Google for Jobs) information on Job Salary or Pay Rate. You may be obligated under local laws or regulations to present a Salary or Pay Rate amount or range. If this is confidential and you are permitted under law to hide them, turn this setting to "off". Jobs may rank higher in searches on external aggregators like Google for Jobs if a Salary or Pay Rate amount or range is provided.', 'matador-jobs' ),
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'bullhorn_salary_low_field' => [
							'type' => 'select',
							'label' => esc_html__( 'Salary Range Low Field', 'matador-jobs' ),
							'default'     => 'salary',
							'options' => [
								'salary'       => 'Use Default (Salary)',
								'payRate'      => 'Pay Rate',
								'customInt1'   => 'customInt1',
								'customInt2'   => 'customInt2',
								'customInt3'   => 'customInt3',
								'customInt4'   => 'customInt4',
								'customInt5'   => 'customInt5',
								'customInt6'   => 'customInt6',
								'customInt7'   => 'customInt7',
								'customInt8'   => 'customInt8',
								'customFloat1' => 'customFloat1',
								'customFloat2' => 'customFloat2',
								'customFloat3' => 'customFloat3',
							],
							'description' => esc_html__( 'Select the Bullhorn field which will be used for the low value in a Salary or Pay Rate Range. If this value matches that of the high value setting, a single Salary/Pay Rate value will be presented.', 'matador-jobs' ),
							'supports' => [ 'settings', 'wp_job_manager' ],
						],
						'bullhorn_salary_high_field' => [
							'type' => 'select',
							'label' => esc_html__( 'Salary Range High Field', 'matador-jobs' ),
							'default'     => '0',
							'options' => [
								'salary'       => 'Use Default (Salary)',
								'payRate'      => 'Pay Rate',
								'customInt1'   => 'customInt1',
								'customInt2'   => 'customInt2',
								'customInt3'   => 'customInt3',
								'customInt4'   => 'customInt4',
								'customInt5'   => 'customInt5',
								'customInt6'   => 'customInt6',
								'customInt7'   => 'customInt7',
								'customInt8'   => 'customInt8',
								'customFloat1' => 'customFloat1',
								'customFloat2' => 'customFloat2',
								'customFloat3' => 'customFloat3',
							],
							'description' => esc_html__( 'Select the Bullhorn field which will be used for the high value in a Salary or Pay Rate Range. If this value matches that of the low value setting, a single Salary/Pay Rate value will be presented.', 'matador-jobs' ),
							'supports' => [ 'settings', 'wp_job_manager' ],
						],
						'salary_information' => [
							'type' => 'information',
							'label' => esc_html__( 'More Info', 'matador-jobs' ),
							'description' => '<a href="https://docs.matadorjobs.com/articles/salary-range-options/" target="_blank">' . esc_html__( 'See this help doc for more information.', 'matador-jobs' ) . '</a>',
							'supports' => [ 'settings', 'wp_job_manager' ],
						]
					] ),
					/**
					 * Filter: Jobs Structured Data Fields in the Jobs
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'jobs_structured_data' => apply_filters( 'matador_options_fields_jobs_structured_data', array(
						esc_html_x( 'Jobs Structured Data Settings', 'Jobs Structured Data Settings Name', 'matador-jobs' ),
						'jsonld_enabled'             => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Add Structured Data', 'matador-jobs' ),
							'default'     => '1',
							'description' => esc_html__( 'Structured Data is the best search engine optimization for your jobs website. Structured Data is hidden code built by and added to your jobs pages by Matador. You will not see it, but the robots that index your site for search engines like Google for Jobs and Indeed will love it.', 'matador-jobs' ),
							'supports'    => array( 'settings' ),
						),
						'jsonld_hiring_organization' => array(
							'type'        => 'select',
							'label'       => esc_html__( '"Hiring Company" Data Source', 'matador-jobs' ),
							'options'     => array(
								'company' => esc_html__( 'use Client Company info', 'matador-jobs' ),
								'agency'  => esc_html__( 'use Hiring Agency info', 'matador-jobs' ),
							),
							'default'     => 'agency',
							'description' => esc_html__( 'Structured Data requires a "Hiring Company" Name and Website Address. When this is set to "use Client Company info", the Client Company Name and Website set in Bullhorn will be provided. When set to "use Hiring Agency info", the name of this website and its URL will be provided.', 'matador-jobs' ),
							'supports'    => array( 'settings' ),
						),
					) ),
					/**
					 * Filter: Jobs Sorting Fields in the General Tab
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'jobs_sort'            => apply_filters( 'matador_options_fields_jobs_sort', array(
						esc_html_x( 'Jobs Sorting Options', 'Jobs Sortings Settings Section Name', 'matador-jobs' ),
						'sort_jobs'  => array(
							'type'     => 'select',
							'label'    => esc_html__( 'Sort Jobs By', 'matador-jobs' ),
							'options'  => array(
								'date'        => esc_html__( 'By Date (default)', 'matador-jobs' ),
								'name'        => esc_html__( 'By Name', 'matador-jobs' ),
								'bullhorn_id' => esc_html__( 'By Bullhorn ID', 'matador-jobs' ),
								'random'      => esc_html__( 'Randomly', 'matador-jobs' ),
							),
							'default'  => 'date',
							'supports' => array( 'settings' ),
						),
						'order_jobs' => array(
							'type'     => 'select',
							'label'    => esc_html__( 'Order Jobs', 'matador-jobs' ),
							'options'  => array(
								'DESC' => esc_html__( 'Descending 9-1 (default)', 'matador-jobs' ),
								'ASC'  => esc_html__( 'Ascending 1-9', 'matador-jobs' ),
							),
							'default'  => 'DESC',
							'supports' => array( 'settings' ),
						),
					) ),
					/**
					 * Filter: Options fields in the General Tab, Slugs Section
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'jobs_rewrites'        => apply_filters( 'matador_options_fields_jobs_slugs', array(
						esc_html_x( 'Jobs Pages URL Options', 'URL Slugs Settings Section Name', 'matador-jobs' ),
						'post_type_slug_job_listing_each' => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Job Single URL Slug', 'matador-jobs' ),
							'description' => esc_html__( 'Choose how Matador constructs the URL for the job. Note: spaces and invalid characters are ignored or changed into dashes, e.g.: "My Job Title" becomes "my-job-title".', 'matador-jobs' ),
							'options'     => array(
								'title'    => esc_html__( 'Job Title', 'matador-jobs' ),
								'title_id' => esc_html__( 'Job Title, Bullhorn Job ID', 'matador-jobs' ),
								'id_title' => esc_html__( 'Bullhorn Job ID, Job Title', 'matador-jobs' ),
							),
							'default'     => 'title',
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'post_type_job_board_location'    => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'Job Board Location', 'matador-jobs' ),
							'description'           => esc_html__( 'Choose the page where you put the [matador_jobs] shortcode, or elect to use the WordPress "archive" and use your theme defaults.', 'matador-jobs' ),
							                           // . '<br /><br />' . esc_html__( 'If you are using the "Also Publish to Indeed" checkbox in Bullhorn, you need to ask Bullhorn support to set "careerPortalDomainPath" to this page ' , 'matador-jobs' ) . Template_Support::the_jobs_link() . '{?}',
							'show_option_no_change' => esc_html__( 'Use Archive', 'matador-jobs' ),
							'supports'              => array( 'settings' ),
						),
						'post_type_slug_job_listing'      => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Job Listings Slug', 'matador-jobs' ),
							'description' => esc_html__( 'This is both the URL slug for the all jobs page and the part of the URL in front of each jobs\' slug. If you job is "my-job-title" at "mysite.com/my-jobs/my-job-title", this option customizes the "my-jobs" part.', 'matador-jobs' ),
							'default'     => Matador::variable( 'post_type_slug_job_listing' ),
							'sanitize'    => 'slug',
							'supports'    => array( 'settings' ),
						),
					) ),
				) ),
			),
			/**
			 * Filter: Options Tab - Applications
			 *
			 * Use to add sections and fields to a tab.
			 *
			 * @since   1.0.0
			 */
			'applications' => array(
				esc_html_x( 'Applications', 'Applications Settings Tab Name', 'matador-jobs' ),
				apply_filters( 'matador_options_application_tab_fields', array(
					/**
					 * Filter: Options - Applications Tab - Applications Settings
					 *
					 * Use to add fields to a section.
					 *
					 * @since   1.0.0
					 */
					'applications_general'       => apply_filters( 'matador_options_fields_applications_general', array(
						esc_html_x( 'Applications General Settings', 'Applications General Settings Section Name', 'matador-jobs' ),
						'applications_accept'                  => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Use Matador Application Processing', 'matador-jobs' ),
							'default'     => '1',
							'description' => esc_html__( 'Matador has built-in support for accepting and processing applications. Turn off to use your own solution.', 'matador-jobs' ),
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'applications_sync'                    => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Submit Applicants to Bullhorn', 'matador-jobs' ),
							'description' => esc_html__( 'Matador can submit candidates to Bullhorn immediately as they apply, in a background task after they apply, or only when you manually trigger the sync. When set to "Immediate" processing, the user waits for the communication with Bullhorn to complete, but can send more information back in the confirmation email. When set to "Background" processing, ther user gets confirmation immediately, but with less information available to the confirmation email. Manual processing requires a logged in admin user to trigger.', 'matador-jobs' ),
							'options'     => array(
								'1'  => esc_html__( 'Submit Applications to Bullhorn in the Background (Faster)', 'matador-jobs' ),
								'-1' => esc_html__( 'Submit Applications to Bullhorn Immediately (Slower)', 'matador-jobs' ),
								'0'  => esc_html__( 'Disable Automatic Submissions to Bullhorn (Manual Only)', 'matador-jobs' ),
							),
							'default'     => '-1',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'applications_apply_method'            => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Application Method', 'matador-jobs' ),
							'description' => esc_html__( 'How would you like your visitors to access an application? You may add the application form to the bottom of the job description page, the plugin can generate an application page for you, or you can direct users to a custom WordPress page that you can use the [matador-application] or other plugin (link).', 'matador-jobs' ),
							'options'     => array(
								'append' => esc_html__( 'Add Application Form to Job Detail', 'matador-jobs' ),
								'create' => esc_html__( 'Generate Application Page', 'matador-jobs' ),
								'custom' => esc_html__( 'Link to Custom Page', 'matador-jobs' ),
							),
							'default'     => 'append',
							'supports'    => array( 'settings' ),
						),
						'applications_apply_page'              => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'Custom Application Page', 'matador-jobs' ),
							'description'           => esc_html__( 'Choose the custom WordPress page where application links should go to. Leaving this blank will cause the application links to go to your home page.', 'matador-jobs' ),
							'show_option_no_change' => esc_html__( 'No page selected', 'matador-jobs' ),
							'supports'              => array( 'settings' ),
						),
						'applications_general_apply_page'              => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'General Application Page', 'matador-jobs' ),
							'description'           => esc_html__( 'Select a WordPress page where you will present a "General Application." This is where job seekers will apply as a general candidate but not to a specific firm. This option may not need to be set unless using layouts the feature functions/shortcodes that reference it.', 'matador-jobs' ),
							'show_option_no_change' => esc_html__( 'No page selected', 'matador-jobs' ),
							'supports'              => array( 'settings' ),
						),
						'applications_confirmation_method'     => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Confirmation Method', 'matador-jobs' ),
							'description' => esc_html__( 'When a user completes an application, they will redirect to a confirmation and "thank you" screen. You can redirect users to the job detail page with the "thank you" added to the top, you may redirect users to an automatically generated confirmation page, or you can direct users to a custom WordPress page where you customize the message, look, and feel.', 'matador-jobs' ),
							'options'     => array(
								'append' => esc_html__( 'Add Confirmation to Job Detail Page', 'matador-jobs' ),
								'create' => esc_html__( 'Generate Confirmation Page', 'matador-jobs' ),
								'custom' => esc_html__( 'Link to Custom Page', 'matador-jobs' ),
							),
							'default'     => 'append',
							'supports'    => array( 'settings' ),
						),
						'applications_confirmation_page'       => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'Custom Confirmation Page', 'matador-jobs' ),
							'description'           => esc_html__( 'Choose the custom WordPress page where application confirmations should be redirected to. Leaving this blank will cause the application links to go to your home page.', 'matador-jobs' ),
							'show_option_no_change' => esc_html__( 'No page selected', 'matador-jobs' ),
							'supports'              => array( 'settings' ),
						),
						'resume_or_cv'                         => array(
							'type'        => 'select',
							'label'       => esc_html__( '"Resume" or "CV"', 'matador-jobs' ),
							'options'     => array(
								'resume'  => esc_html__( 'as "Resume"', 'matador-jobs' ),
								'cv_abbr' => esc_html__( 'as "CV"', 'matador-jobs' ),
								'cv'      => esc_html__( 'as "Curriculum Vitae"', 'matador-jobs' ),
							),
							'default'     => 'resume',
							'description' => esc_html__( 'What does your business call the work history and education summary document?', 'matador-jobs' ),
							'supports'    => array( 'settings' ),
						),
						'application_fields'                   => array(
							'type'        => 'checkbox',
							'label'       => esc_html__( 'Default Application Fields', 'matador-jobs' ),
							'description' => esc_html__( 'By default, the checked fields will display when the application or application shortcode is shown. A simple filter added to your theme functions.php file can add more fields as well.', 'matador-jobs' ),
							'options'     => apply_filters( 'matador_default_shortcode_options', array(
								'name'    => esc_html__( 'Name', 'matador-jobs' ),
								'email'   => esc_html__( 'Email', 'matador-jobs' ),
								'phone'   => esc_html__( 'Phone', 'matador-jobs' ),
								'address' => esc_html__( 'Address', 'matador-jobs' ),
								'message' => esc_html__( 'Message', 'matador-jobs' ),
								'resume'  => esc_html__( 'Resume/CV Upload', 'matador-jobs' ),
							) ),
							'default'     => Application_Handler::application_fields_defaults(),
							'supports'    => array( 'settings', 'wp_job_manager' ),
						),
						'applications_honeypot'                => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Use Anti-Spam Honeypot', 'matador-jobs' ),
							'default'     => '1',
							'description' => esc_html__( 'When set to "On", Matador will set a "trap" for internet robots that make spam. This anti-spam method may cause usability issues for visitors using screen readers, and sophisticated spam bots can sometimes bypass these protections.', 'matador-jobs' ),
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
						'bullhorn_mark_application_as'         => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Classify Applications', 'matador-jobs' ),
							'options'     => array(
								'submitted' => esc_html__( 'as Job Submission', 'matador-jobs' ),
								'lead'      => esc_html__( 'as Web Response', 'matador-jobs' ),
							),
							'description' => esc_html__( 'When a Candidate is added as an applicant to a job, they can be marked as either a Submission or Web Response. Per Bullhorn documentation, a Job Submission should occur after a candidate is "evaluated, interviewed, and otherwise assessed, and the parties involved have agreed that the Candidate may be suitable", whereas a Web Response "is an informal job submission" and pending review. Matador allows you choose from either, depending on your organization\s workflow.', 'matador-jobs' ),
							'default'     => 'lead',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'bullhorn_candidate_owner'    => array(
							'type'        => 'select',
							'label'       => esc_html__( 'Preferred New Candidate "Owner"', 'matador-jobs' ),
							'options'     => array(
								'api'      => esc_html__( '"API User"', 'matador-jobs' ),
								'owner'    => esc_html__( 'Job\'s "Owner"', 'matador-jobs' ),
								'response' => esc_html__( 'Job\'s "Published Contact Recruiter"', 'matador-jobs' ),
							),
							'description' => esc_html__( 'When a new candidate is created in Bullhorn, the logged in Bullhorn user "owns" the candidate by default. When a Bullhorn API application adds a user, the "API User" gets assigned ownership. Matador can attempt to assign ownership to the published response recruiter or job owner. Note: this is a preference setting, and many factors can result in the default "API User" being assigned, including with "General Applications" and more.', 'matador-jobs' ),
							'default'     => 'api',
							'supports'    => array(
								'settings',
								'wp_job_manager',
								'wp_job_manager_apps',
								'wp_job_manager_resume'
							),
						),
						'applications_sync_check_for_existing' => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Prevent Duplicate Candidates', 'matador-jobs' ),
							'description' => esc_html__( 'Prior to creating a candidate record from an application, Matador will check your Bullhorn account for existing records with the matching email address and last name. When found, Matador will update that record with submitted information and apply the existing record for the job, preventing duplicate candidates. Turn off to create a new Bullhorn candidate for each application.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'bullhorn_process_resumes'             => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Use Resume Processor', 'matador-jobs' ),
							'description' => esc_html__( 'Turn on or off resume processing. When processed, Bullhorn will fill fields on the candidate\'s form with information from their resume/CV.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'applications_backup_job'              => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Default "Job" to Apply To', 'matador-jobs' ),
							'description' => esc_html__( 'When an application is shown with the "jobs-select" field, or if you use custom local jobs, or if you set up a general application page not tied to a job, an application may be submitted without an associated Bullhorn job to submit to. This will create just a candidate, but some people do not use the candidates screen in Bullhorn and like to have these "general applications" sent to a "General Job". If you\'d like to do this, enter the the Bullhorn ID for that "General Job" here.', 'matador-jobs' ),
							'sanitize'    => 'integer',
							'supports'    => array( 'settings' ),
						),
					) ),
					/**
					 * Filter: Options - Applications Tab - Applications Privacy Settings
					 *
					 * Use to add fields to a section.
					 *
					 * @since   3.1.0
					 */
					'applications_privacy' => apply_filters( 'matador_options_fields_applications_privacy', array(
						esc_html_x( 'Consents, Privacy, and Data Security Settings', 'Applications Privacy Settings Section Name', 'matador-jobs' ),
						'application_privacy_field'        => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Require Applicant to Agree to Privacy Policy', 'matador-jobs' ),
							'description' => esc_html__( 'Matador will add a field to the application form requiring users to acknowledge your site\'s Privacy Policy. You are unable to remove this from the form (via shortcodes, settings) if on.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
						'privacy_policy_page'              => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'Privacy Policy Page', 'matador-jobs' ),
							'description'           => esc_html__( 'Choose the custom WordPress page where your Privacy Policy page is located. Leaving this blank will result in no link to Privacy Policy being added to your forms.', 'matador-jobs' ),
							'show_option_no_change' => esc_html__( 'No page selected', 'matador-jobs' ),
							'supports'              => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
						'application_terms_field'          => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Require Applicant to Agree to Terms of Service', 'matador-jobs' ),
							'description' => esc_html__( 'Matador will add a field to the application form requiring users to acknowledge your site\'s Terms of Service.', 'matador-jobs' ),
							'default'     => '1',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
						'terms_policy_page'                => array(
							'type'                  => 'page',
							'label'                 => esc_html__( 'Terms of Service Page', 'matador-jobs' ),
							'description'           => esc_html__( 'Choose the custom WordPress page where your Terms of Service is located. When selected, the Terms of Service form field will display a link to the selected page.', 'matador-jobs' ),
							'show_option_no_change' => esc_html__( 'No page selected', 'matador-jobs' ),
							'supports'              => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
						'application_delete_local_on_sync' => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Delete Local Application After Sync', 'matador-jobs' ),
							'description' => esc_html__( 'If on, an application will be immediately deleted from the WordPress site as soon as a successful sync is completed by Matador.', 'matador-jobs' ),
							'default'     => '0',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps' ),
						),
					) ),
					/**
					 * Filter: Options - Applications Tab - Applications Notifications Settings
					 *
					 * Use to add fields to a section.
					 *
					 * @since   3.1.0
					 */
					'applications_notifications' => apply_filters( 'matador_options_fields_applications_notifications', array(
						esc_html_x( 'Email Settings', 'Email Settings Section Name', 'matador-jobs' ),
						'notify_applicant'      => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Send Applicant Confirmation', 'matador-jobs' ),
							'description' => esc_html__( 'Send the applicant an email confirmation of their application.', 'matador-jobs' ),
							'default'     => '0',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'notify_recruiter'      => array(
							'type'     => 'toggle',
							'label'    => esc_html__( 'Send Notice to Recruiter', 'matador-jobs' ),
							'default'  => '0',
							'supports' => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'notify_recruiter_when' => array(
							'type'        => 'toggle',
							'label'       => esc_html__( 'Send Recruiter Notification After Sync', 'matador-jobs' ),
							'default'     => '0',
							'description' => esc_html__( 'If set to Off, recruiter notification will be sent immediately after submission by the candidate. If set to On, recruiter notification will be sent after the application is saved to Bullhorn. When On, email templates can show extra data like the Bullhorn ID of the candidate and include a link to the Bullhorn record, but if there is a disconnection or error no email will be sent at all.', 'matador-jobs' ),
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'notify_type'           => array(
							'type'        => 'checkbox',
							'label'       => esc_html__( 'Recruiter Type(s) to Notify', 'matador-jobs' ),
							'description' => esc_html__( 'Recruiter notifications are sent to the recruiters assigned to the job in Bullhorn. Select which types of assigned user(s) should be notified.', 'matador-jobs' ),
							'options'     => array(
								'owner'         => esc_html__( 'Job Owner', 'matador-jobs' ),
								'assignedUsers' => esc_html__( 'Assigned User(s)', 'matador-jobs' ),
								'responseUser'  => esc_html__( 'Published Contact Recruiter', 'matador-jobs' ),
							),
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'recruiter_no_email'    => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Default Recruiter', 'matador-jobs' ),
							'description' => esc_html__( 'If assigned recruiters of the selected types are not found or the applicant is applying to no role via the general application, Matador can send the notification to a default recruiter. Okay to leave unset if "Additional Recruiters" are defined below or you do not want emails sent when no recruiter is assigned.', 'matador-jobs' ) . ' ' . __( 'Separate each email with a comma. You may use the "John Smith &lt;john@website.com&gt;" format or just "john@website.com" format.', 'matador-jobs' ),
							'sanitize'    => 'email_list',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
						'recruiter_email'       => array(
							'type'        => 'text',
							'label'       => esc_html__( 'Additional Recruiter(s)', 'matador-jobs' ),
							'description' => esc_html__( 'Optionally, also send copies of all applications to these email address(es).', 'matador-jobs' ) . ' ' . __( 'Separate each email with a comma. You may use the "John Smith &lt;john@website.com&gt;" format or just "john@website.com" format.', 'matador-jobs' ),
							'sanitize'    => 'email_list',
							'supports'    => array( 'settings', 'wp_job_manager', 'wp_job_manager_apps', 'wp_job_manager_resume' ),
						),
					) ),
				) ),
			),
		) );
	}

	/**
	 * Get Field
	 *
	 * @since 3.0.0
	 *
	 * @var string $field
	 *
	 * @return array|boolean
	 */
	public function get_field( $field ) {
		$fields = $this->get_just_fields();

		if ( array_key_exists( $field, $fields ) ) {
			return $fields[ $field ];
		}

		return false;
	}

	/**
	 * Method: Get Just Fields
	 *
	 * Array of Options Fields Only
	 *
	 * @since 3.0.0
	 *
	 * @var string $supports
	 *
	 * @return array
	 */
	public function get_just_fields( $supports = null ) {
		$just_fields = array();

		foreach ( $this->fields as $tab => $sections ) {
			foreach ( $sections[1] as $section => $fields ) {
				foreach ( $fields as $key => $args ) {
					if ( is_array( $args ) ) {
						if ( $supports && array_key_exists( 'supports', $args ) && in_array( $supports, $args['supports'], true ) ) {
							$just_fields[ $key ] = $args;
						} elseif ( ! $supports ) {
							$just_fields[ $key ] = $args;
						}
					}
				}
			}
		}

		return $just_fields;
	}

	/**
	 * Get Settings Tabs
	 *
	 * Array of Options Tabs Only
	 *
	 * @since 3.0.0
	 * @return array of tabs with label
	 */
	public function get_settings_tabs() {

		$just_tabs = array();

		foreach ( $this->fields as $tab => $contents ) {
			$just_tabs[ $tab ] = $contents[0];
		}

		return $just_tabs;

	}

	/**
	 * Get Settings Fields and Structure
	 *
	 * @since 3.0.0
	 *
	 * @var string $supports
	 *
	 * @return array
	 */
	public function get_settings_fields_with_structure( $supports = null ) {

		if ( ! $supports ) {

			return $this->fields;
		} else {
			$return = $this->fields;

			foreach ( $return as $tab => $sections ) {
				foreach ( $sections[1] as $section => $fields ) {
					foreach ( $fields as $key => $args ) {
						if ( is_array( $args ) && array_key_exists( 'supports', $args ) && ! in_array( $supports, $args['supports'], true ) ) {
							unset( $return[ $tab ][1][ $section ][ $key ] );
						}
					}
				}
				if ( 1 === count( $return[ $tab ][1][ $section ] ) ) {
					unset( $return[ $tab ][1][ $section ] );
				}
			}
			if ( 1 === count( $return[ $tab ] ) ) {
				unset( $return[ $tab ] );
			}

			return $return;
		}
	}

	/**
	 * Standard Conditional Fields
	 *
	 * @since 3.0.0
	 */
	private function standard_conditional_fields() {

		// Add the Taxonomy Slugs fields procedurally
		add_filter( 'matador_options_fields_jobs_slugs', array( __CLASS__, 'taxonomy_rewrites' ) );

		// Remove options if a theme/plugin makes the option irrelevant
		if ( has_filter( 'matador_import_job_description_field' ) ) {
			add_filter( 'matador_options_fields_jobs_import', array( __CLASS__, 'unset_field_job_description' ) );
		}

		// Remove the Privacy Policy Page setting if WP 4.9.6
		if ( function_exists( 'get_privacy_policy_url' ) ) {
			add_filter( 'matador_options_fields_applications_privacy', array( __CLASS__, 'unset_privacy_policy_page' ) );
		}

		// Turn Off/Hide Settings for Lite
		if ( ! Matador::is_pro() ) {
			add_filter( 'matador_options_fields', array( __CLASS__, 'matador_jobs_lite_settings_fields' ) );
		}

		add_filter( 'matador_options_fields', [ __CLASS__, 'careerPortalDomainRoot_options' ] );
	}

	/**
	 * CareerPortalDomainRoot Handling
	 *
	 * @since 3.8.0
	 * @since 3.8.2 Renamed function from `careerPortalDomainRoot_description`
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function careerPortalDomainRoot_options( $fields ) {

		/**
		 * @wordpress-filter Should Check Bullhorn careerPortalDomainRoot
		 * @see includes\class-admin-tasks.php::check_career_portal_domain_root() for documentation
		 */
		if ( ! apply_filters( 'matador_health_check_bullhorn_careerPortalDomainRoot_should_check', true ) ) {
			unset( $fields['bullhorn'][1]['bullhorn_ignore_career_portal_root'] );
			return $fields;
		}

		return $fields;
	}

	/**
	 * CareerPortalDomainRoot Description
	 *
	 * @since 3.8.2
	 *
	 * @return void
	 */
	public static function careerPortalDomainRoot_description() {

		/**
		 * @wordpress-filter Should Check Bullhorn careerPortalDomainRoot
		 * @see includes\class-admin-tasks.php::check_career_portal_domain_root() for documentation
		 */
		if ( ! apply_filters( 'matador_health_check_bullhorn_careerPortalDomainRoot_should_check', true ) ) {
			return;
		}

		if ( 1 === (int) Matador::setting( 'bullhorn_ignore_career_portal_root' ) ) {
			return;
		}

		$careerPortalDomainRoot_value = trailingslashit( get_home_url() . '/' . Matador::setting( 'post_type_slug_job_listing' ) ) . '{?}';

		$html_code_wrap = '<code>%s</code>';

		// Translators: placeholder 1 is the Bullhorn API Field Name, placeholder 2 is a URL
		$description = sprintf( esc_html__( 'Based on your current settings, this site\'s %1$s should be %2$s.', 'matador-jobs' ), sprintf( $html_code_wrap, 'careerPortalDomainRoot' ), sprintf( $html_code_wrap, $careerPortalDomainRoot_value ) );

		printf( '<div class="matador-field-group" style="margin-top:0;"><div class="matador-label"></div><div class="matador-field">%1$s</div></div>', $description );
	}

	/**
	 * Matador Jobs Lite Settings Adjustments
	 *
	 * Function is new as of 3.1.0 and combines former functions into one clean one.
	 *
	 * @since 3.1.0
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function matador_jobs_lite_settings_fields( $fields ) {

		// Remove License Key Section
		unset( $fields['general'][1]['general_licensing'] );

		// No Admin Notifications
		$fields['general'][1]['general_notifications']['notify_admin']['default']     = '0';
		$fields['general'][1]['general_notifications']['notify_admin']['attributes']  = array( 'disabled' => true );
		$fields['general'][1]['general_notifications']['notify_admin']['description'] = sprintf( '<br /><br ><em>%s</em>', __( 'This setting is a Pro setting that requires Matador Jobs Pro.', 'matador-jobs' ) );
		unset( $fields['general'][1]['general_notifications']['admin_email'] );

		// Fewer Logging Options
		$fields['general'][1]['general_notifications']['logging']['options'] = array(
			'0'  => esc_html__( 'Off', 'matador-jobs' ),
			'1'  => esc_html__( '1 Days', 'matador-jobs' ),
			'2'  => esc_html__( '2 Days', 'matador-jobs' ),
			'-1' => esc_html__( 'Longer log keeping available in Matador Jobs Pro.', 'matador-jobs' ),
		);

		// Disable Structured Data Options
		$fields['jobs'][1]['jobs_structured_data']['jsonld_hiring_organization']['default']     = 'company';
		$fields['jobs'][1]['jobs_structured_data']['jsonld_hiring_organization']['attributes']  = array( 'disabled' => true );
		$fields['jobs'][1]['jobs_structured_data']['jsonld_hiring_organization']['description'] .= sprintf( '<br /><br ><em>%s</em>', __( 'This setting is a Pro setting and requires Matador Jobs Pro.', 'matador-jobs' ) );
		$fields['jobs'][1]['jobs_structured_data']['jsonld_enabled']['default']                 = '1';
		$fields['jobs'][1]['jobs_structured_data']['jsonld_enabled']['attributes']              = array( 'disabled' => true );
		$fields['jobs'][1]['jobs_structured_data']['jsonld_enabled']['description']             .= sprintf( '<br /><br ><em>%s</em>', __( 'This setting is a Pro setting and requires Matador Jobs Pro.', 'matador-jobs' ) );

		// Disable or Remove Applications Options if not Grandfathered
		if ( ! Matador::setting( 'bullhorn_grandfather' ) ) {
			$fields['applications'][1]['applications_general']['applications_accept']['default']     = '0';
			$fields['applications'][1]['applications_general']['applications_accept']['attributes']  = array( 'disabled' => true );
			$fields['applications'][1]['applications_general']['applications_accept']['description'] = sprintf( '<em>%s</em>', __( 'Application processing and syncing application data to Bullhorn is a feature of Matador Jobs Pro.', 'matador-jobs' ) );
			$fields['applications'][1]['applications_general']['applications_apply_page']['label']   = __( 'Application Page', 'matador-jobs' );
			foreach ( $fields['applications'][1]['applications_general'] as $key => $unused ) {
				if ( ! in_array( $key, array( 0, 'applications_apply_page', 'applications_accept' ), true ) ) {
					unset( $fields['applications'][1]['applications_general'][ $key ] );
				}
			}
			unset( $fields['applications'][1]['applications_privacy'] );
			unset( $fields['applications'][1]['applications_notifications'] );
		}

		// Add a "Go Pro" Tab to Settings
		$fields['pro'] = array(
			esc_html_x( 'Get Matador Jobs Pro', 'Get Pro Settings Tab Name', 'matador-jobs' ),
			apply_filters( 'matador_options_pro_tab_fields', array(
				/**
				 * Filter: Options Fields in the Licensing Tab, Core Plugin Section
				 *
				 * Use to add fields to a section.
				 *
				 * @since   3.0.0
				 */
				'pro_core' => apply_filters( 'matador_options_fields_pro_core', array(
					esc_html_x( 'Get More Out of Matador Jobs', 'Go Pro Settings Section Name', 'matador-jobs' ),
					'pro_core' => array(
						'type'     => 'get-pro',
						'supports' => array( 'wp_job_manager', 'settings' ),
					),
				) ),
			) ),
		);

		return $fields;
	}

	/**
	 * Unset Field
	 *
	 * Unset a default field.
	 *
	 * @since 3.4.0
	 *
	 * @param array  $fields
	 * @param string $field
	 *
	 * @return mixed
	 */
	public static function unset_field( $fields, $field = '' ) {
		if ( empty( $fields ) || ! is_array( $fields ) || empty( $field ) || ! is_string( $field ) ) {
			return $fields;
		}
		unset( $fields[ $field ] );

		return $fields;
	}

	/**
	 * Unset Field : Job Description
	 *
	 * Called when Job Description setting is being overridden by a theme/plugin.
	 *
	 * @since 3.4.0
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public static function unset_field_job_description( $fields ) {

		return self::unset_field( $fields, 'bullhorn_description_field' );
	}

	/**
	 * Unset Privacy Policy Page
	 *
	 * We encourage users to upgrade to at least WordPress 4.9.6 to access its new
	 * Privacy features compliant with GDPR. In the event they do not, we provide our
	 * own alternate. That said, this function should be called conditionally on the
	 * matador_options_fields_applications_privacy filter to remove the backward
	 * compatibility setting.
	 *
	 * @since 3.1.0
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public static function unset_privacy_policy_page( $fields ) {

		return self::unset_field( $fields, 'privacy_policy_page' );
	}

	/**
	 * Taxonomy Rewrites
	 *
	 * Because taxonomies are defined more fluidly, we generate their
	 * slug fields procedurally.
	 *
	 * @since 3.1.0
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public static function taxonomy_rewrites( $fields ) {
		foreach ( Matador::variable( 'job_taxonomies' ) as $key => $taxonomy ) {
			$fields[ 'taxonomy_slug_' . $key ] = array(
				'type'     => 'text',
				// Translators:: the elsewhere translated name of the taxonomy is the placeholder.
				'label'    => esc_html( sprintf( __( '%1$s URL Slug', 'matador-jobs' ), ucfirst( $taxonomy['plural'] ) ) ),
				'default'  => $key,
				'sanitize' => 'slug',
				'supports' => array( 'settings' ),

			);
		}

		return $fields;
	}
}
