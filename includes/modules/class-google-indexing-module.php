<?php
/**
 * Matador / Module / Google Indexing Module
 *
 * This class handles behavior around the Google Indexing Module
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Modules / Google Indexing
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

/**
 * Class Google Indexing API
 *
 * @since  3.4.0
 */
final class Google_Indexing_Module {

	/**
	 * Google Indexing API Object
	 *
	 * @var Google_Indexing_Api
	 */
	private $index;

	/**
	 * Constructor
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'matador_options_fields_jobs_structured_data', array( $this, 'add_settings_fields' ) );
		add_action( 'matador_options_sanitize_field_google_indexing_api_key', array( $this, 'key_input_sanitizer' ) );

		if ( ! Matador::is_pro() ) {
			return;
		}

		add_action( 'wp', array( $this, 'monitor_googlebot_traffic' ) );

		if ( Matador::setting( 'google_indexing_api_on' ) ) {
			$this->index = new Google_Indexing_Api();
			add_action( 'matador_add_job', array( $this, 'on_job_add' ), 10, 1 );
			add_action( 'matador_delete_job', array( $this, 'on_job_delete' ), 10, 1 );
			add_action( 'matador_transition_job_status', array( $this, 'on_job_status_transition' ), 10, 3 );
		}
		new Google_Indexing_Testing();
	}

	/**
	 * Send Add Notice
	 *
	 * @since 3.4.0
	 *
	 * @param int      $post_id The local (WordPress) ID of the Job Listing being deleted.
	 */
	public function on_job_add( $post_id ) {

		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		if ( 'api' === Matador::setting( 'google_indexing_monitor' ) ) {
			set_transient( Matador::variable( 'google_indexing_api_watch_each', 'transients' ) . get_the_id(), 4 * HOUR_IN_SECONDS );
		}

		$this->index->add( get_permalink( $post_id ) );
	}

	/**
	 * Send Remove Notice
	 *
	 * Function runs on before_delete_post and checks that we are removing a job and, if so, begins the call to remove
	 * the job.
	 *
	 * @since 3.4.0
	 *
	 * @param int      $post_id The local (WordPress) ID of the Job Listing being deleted.
	 */
	public function on_job_delete( $post_id ) {

		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		$this->index->remove( get_permalink( $post_id ) );
	}

	/**
	 * Post Status Transition
	 *
	 * We want to track changes to post status for Matador Job Listings. Even though our users shouldn't be using
	 * features like trash, draft, etc, when they do, it can cause 404s at Google. Google strongly suggests that 404s
	 * from jobs could result in the source being rank lower than others.
	 *
	 * @since 3.4.0
	 *
	 * @param string $new     The new status for the job.
	 * @param string $old     The old status for the job.
	 * @param int    $post_id The local (WordPress) ID for the job.
	 */
	public function on_job_status_transition( $new, $old, $post_id ) {

		if ( $new === $old ) {
			return;
		}

		if ( 'publish' === $new && 'auto-draft' !== $old ) {

			if ( 'api' === Matador::setting( 'google_indexing_monitor' ) ) {
				set_transient( Matador::variable( 'google_indexing_api_watch_each', 'transients' ) . get_the_id(), 4 * HOUR_IN_SECONDS );
			}

			$this->index->add( get_permalink( $post_id ) );
		}

		if ( 'publish' === $old ) {
			$this->index->remove( get_permalink( $post_id ) );
		}
	}

	/**
	 * Monitor Googlebot Traffic on Jobs
	 *
	 * Detect when the GoogleBot visits and log the URL
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function monitor_googlebot_traffic() {

		if ( is_admin() ) {

			return;
		}

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {

			return;
		}

		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) || false !== str_contains( $_SERVER['HTTP_USER_AGENT'], 'Googlebot' ) ) {

			return;
		}

		$track_google = Matador::setting( 'google_indexing_monitor' );

		if ( ! $track_google ) {

			return;
		}

		$should_log_this_post = get_transient( Matador::variable( 'google_indexing_api_watch_each', 'transients' ) . get_the_id() );

		if ( 'api' === $track_google && ! $should_log_this_post ) {

			return;
		}

		if ( $should_log_this_post ) {
			delete_transient( Matador::variable( 'google_indexing_api_watch_each', 'transients' ) . get_the_id() );
		}

		Logger::add( 'info', 'google-indexing-log-googlebot', sprintf( __( 'The Googlebot Indexed Job Posting with WordPress ID %s' , 'matador-jobs' ), get_permalink( get_the_ID() ) ) );
	}

	/**
	 * Add Settings Fields
	 *
	 * @since  3.4.0
	 *
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_settings_fields( $fields ) {

		$fields['google_indexing_monitor'] = array(
			'type' => 'select',
			'label' => esc_html__( 'Log Googlebot Visits to Jobs', 'matador-jobs' ),
			'default' => '0',
			'options' => array(
				'0'   => esc_html__( 'Off', 'matador-jobs' ),
				'all' => esc_html__( 'All Googlebot Traffic', 'matador-jobs' ),
				'api' => esc_html__( 'Only Googlebot Traffic After Indexing API Call', 'matador-jobs' ),
			),
			'supports' => array( 'settings', 'wp_job_manager' ),
			'description' => __( 'Whether Matador should log Googlebot visits to Job Listings. Great for observing response time to jobs. (May affect site performance if set to "All".)', 'matador-jobs' ),
		);

		$fields['google_indexing_api_on']   = array(
			'type'        => 'toggle',
			'label'       => esc_html__( 'Use Google Indexing API', 'matador-jobs' ),
			'default'     => '0',
			'supports'    => array( 'settings', 'wp_job_manager' ),
			'description' => __( 'Whether Matador will notify Google immediately about new, updated, or removed jobs, which means faster listings on Google for Jobs.', 'matador-jobs' ),
		);

		$fields['google_indexing_api_key'] = array(
			'type'        => 'textarea',
			'label'       => esc_html__( 'Google Indexing API private_key:', 'matador-jobs' ),
			'default'     => '',
			'supports'    => array( 'settings', 'wp_job_manager' ),
			'description' => __( 'Copy & Paste the private_key value from your Google Indexing API keys file. Without enclosing quotes', 'matador-jobs' ) . ' <a href="https://matadorjobs.com/support/documentation/setting-up-google-indexing/" target="_blank">' . __( 'View our Help Docs page on this topic.', 'matador-jobs' ) . '</a>',
			'attributes' => array( 'style' => 'font-size: 10px;height: 440px', 'placeholder' => '-----BEGIN PRIVATE KEY-----*-----END PRIVATE KEY-----' ),
		);

		$fields['google_indexing_api_email'] = array(
			'type'        => 'text',
			'label'       => esc_html__( 'Google Indexing API client_email:', 'matador-jobs' ),
			'default'     => '',
			'supports'    => array( 'settings', 'wp_job_manager' ),
			'description' => __( 'Copy & Paste the client_email value from your Google Indexing API keys file. Without enclosing quotes', 'matador-jobs' ) . ' <a href="' . get_admin_url() . '?page=matador_google_test" target="_blank">' . __( 'After saving use this page to test it works.', 'matador-jobs' ) . '</a>',
		);

		if ( ! Matador::credential( 'google_indexing_api_key' ) || ! Matador::credential( 'google_indexing_api_email' )  ) {
			$fields['google_indexing_api_on']['attributes']['disabled'] = true;

			$description = sprintf(
				'<br /><br ><em>%s</em>',
				__( 'This field is disabled until you have entered your API key and email.', 'matador-jobs' ) . ' '
				. __( '(You may need to reload the page once after saving.)', 'matador-jobs' )
			);

			$fields['google_indexing_api_on']['description'] .= $description;
		}

		if ( ! Matador::is_pro() ) {
			$fields['google_indexing_api_on']['attributes']['disabled']    = true;
			$fields['google_indexing_api_email']['attributes']['disabled'] = true;
			$fields['google_indexing_api_key']['attributes']['disabled']   = true;
			$fields['google_indexing_monitor']['attributes']['disabled'] = true;

			$is_pro = sprintf(
				'<br /><br ><em>%s</em>',
				__( 'This setting is a Pro setting and requires Matador Jobs Pro.', 'matador-jobs' )
			);

			$fields['google_indexing_api_on']['description']     .= $is_pro;
			$fields['google_indexing_api_email']['description']  .= $is_pro;
			$fields['google_indexing_api_key']['description']    .= $is_pro;
			$fields['google_indexing_monitor']['description']  .= $is_pro;
		}

		return $fields;
	}

	/**
	 * Google Indexing API Input Sanitizer
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param string $str textarea input to be filtered
	 *
	 * @return string
	 */
	public function key_input_sanitizer( $str = '' ) {
		// remove all the \n from the JSON
		$str = str_replace( "\\\\n", PHP_EOL, $str );
		$str = str_replace( "\\n", PHP_EOL, $str );

		preg_match( '/(-----BEGIN PRIVATE KEY-----.*-----END PRIVATE KEY-----)/s',$str, $key );

		if( ! isset( $key[1] ) ){

			return 'Couldn\'t extract key please retry include whole key -----BEGIN PRIVATE KEY-----*-----END PRIVATE KEY-----';
		}

		return trim( $key[1] );
	}
}
