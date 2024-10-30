<?php
/**
 * Matador / Module / Campaign Tracking
 *
 * This class saves Campaign and Traffic Source data to Candidate and Job Submissions. It will detect a Google Analytics
 * cookie and use that as the source of data if available, and if not, can use more limited information from UTM queries
 * and/or HTTP Referrer values to provide some insight in certain circumstances.
 *
 * @link        https://matadorjobs.com/
 * @since       3.5.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Modules / Google Indexing
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2019-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

use DateTime;
use MatadorSoftware\CampaignTrafficMonitor\CookieMonster;

/**
 * Class Campaign Tracking
 *
 * @final
 *
 * @since  3.5.0
 */
final class Campaign_Tracking {

	/**
	 * Property: Cookie Name
	 *
	 * Default name of the Matador Campaign Traffic Monitor Cookie. Use self::cookie() to get the filtered cookie name.
	 *
	 * @since 3.5.0
	 *
	 * @var string
	 */
	private static $cookie = 'matador_visitor';

	/**
	 * Property: Campaign Fields
	 *
	 * Array of fields tracked in the Campaigns (same as Google UTMZ fields)
	 *
	 * @since 3.5.0
	 *
	 * @var array
	 */
	public static $campaign_fields = [ 'campaign', 'source', 'medium', 'term', 'content' ];

	/**
	 * Constructor
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'matador_options_fields_applications_privacy', [ __CLASS__, 'settings' ] );

		if ( ! Matador::setting( 'application_report_user_traffic_data' ) ) {
			return;
		}

		self::require_files();

		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'scripts' ] );
		add_action( 'matador_application_before_fields', [ __CLASS__, 'add_campaign_data_to_form' ] );
		add_filter( 'matador_application_handler_start_ignored_fields', [ __CLASS__, 'ignore_campaign_fields_in_application' ] );
		add_filter( 'matador_application_data_processed', [ __CLASS__, 'save_campaign_data_to_application' ], 10, 2 );
		add_filter( 'matador_data_source_description', [ __CLASS__, 'modify_source_description' ], 25, 4 );
	}

	/**
	 * Should Not Track
	 *
	 * Logical test to determine if the tracking script/alternate tracking method should load.
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	private static function should_not_track() {

		$should_not_track = false;

		// If the current logged in user is an Admin, Editor, or Author, do not load the tracker.
		if ( current_user_can( 'administrator' )
			|| current_user_can( 'editor' )
			|| current_user_can( 'author' ) ) {
			$should_not_track = true;
		}

		// If WP_DEBUG is true, however, always load the tracker
		if ( WP_DEBUG ) {
			$should_not_track = false;
		}

		// If a site-level setting is available for users to turn on/off tracking, write a filter to turn off tracking
		// in Matador.
		/**
		 * Filter: Matador Campaign Tracking Should Not Track
		 *
		 * After the module loads but before anything truly kicks off, this function checks if the site should track.
		 * Return true to this filter to disable tracking. This is useful if a site operator has a server-level reason
		 * to disable tracking, ie: a user is logged in as a non-standard capability that shouldn't be tracked.
		 *
		 * @since 3.5.0
		 *
		 * @param bool
		 *
		 * @return bool
		 */
		if ( apply_filters( 'matador_campaign_tracking_should_not_track', false ) ) {
			$should_not_track = true;
		}

		return $should_not_track;
	}

	/**
	 * Settings
	 *
	 * Register the Settings related to this module
	 * @since 3.5.0
	 *
	 * @param array $settings Array of Settings Fields to Update/Append
	 *
	 * @return array
	 */
	public static function settings( $settings ) {
		$settings['application_report_user_traffic_data'] = [
			'type'        => 'toggle',
			'label'       => esc_html__( 'Collect User Traffic Information', 'matador-jobs' ),
			'description' => esc_html__( 'Depending on user settings, Matador can collect information about inbound user traffic and include that in the "source" field for new candidate and new job submissions. Note: may require additional setup for GDPR compliance.', 'matador-jobs' ),
			'default'     => '1',
			'supports'    => [ 'settings', 'wp_job_manager', 'wp_job_manager_apps' ],
		];
		return $settings;
	}

	/**
	 * Scripts
	 *
	 * Register and enqueue the Matador Traffic Javascript and additionally pass it variables.
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public static function scripts() {

		if ( self::should_not_track() ) {
			return;
		}

		wp_register_script( 'matador_traffic', Matador::$path . 'assets/scripts/matador-traffic' . Scripts::min() . '.js', [], Scripts::version(), true );

		wp_enqueue_script( 'matador_traffic' );

		// Initializing the Matador Traffic JS requires a domain name without a protocol.
		$url = str_replace( [ 'https://', 'http://' ], '', get_site_url() );

		// Optionally, you may initialize Matador Traffic JS with an array of options, each filterable and usable
		// elsewhere in the module.
		$options = [];

		if ( self::source_labels() !== self::source_labels( false ) ) {
			$options['labels'] = self::source_labels();
		}

		if ( self::query_string_keys() !== self::query_string_keys( false ) ) {
			$options['queryParams'] = self::query_string_keys();
		}

		if ( self::known_search_engines() !== self::known_search_engines( false ) ) {
			$options['searchEngines'] = self::known_search_engines();
		}

		if ( self::known_social_networks() !== self::known_social_networks( false ) ) {
			$options['socialNetworks'] = self::known_social_networks();
		}

		/**
		 * Filter: Matador Campaign Tracking Cookie Options
		 *
		 * The script that manages the tracking cookie has a robust number of options that site operators can adjust to
		 * customize its function. This filter allows a site operator to modify the options via PHP array operators,
		 * which are then converted into a JSON string and passed into the javascript class instantiator.
		 *
		 * Note: use the specific filters to modify the source labels, querystring keys, known social networks, and
		 * known search engines, as these are shared by the "backup" behavior of the feature when javascript is disabled.
		 *
		 * @since 3.5.0
		 *
		 * @param array
		 *
		 * @return array
		 */
		$options = apply_filters( 'matador_campaign_tracking_cookie_options', $options );

		// If it is empty, we won't pass a second argument, but if its not empty, convert to JSON to be passed as
		// the second argument, and include necessary markup before and after JSON string.
		$options = empty( $options ) ? '' : ', \'' . wp_json_encode( $options ) . '\'';

		// Inline JS checks for various DoNotTrack settings and then initializes. Use filter to add additional
		// logic and/or your preferred opt-out code. Matador does not provide opt-out code otherwise.
		$inline = 'var matador_visitor = true;' .
		    /**
			 * Filter Matador Campaign Tracking Inline Javascript
			 *
			 * When the site uses some Javascript level setting to track a user's do not track setting, use this filter
			 * to add logic to the inline Javascript that initializes Matador Traffic monitoring that would set
			 * 'matador_traffic' to false if appropriate.
		     *
		     * Note: not to be confused with `matador_campaign_tracking_inline_script` which inserts JS code into the
		     * middle of the default code. If we could go back and rename this filter, we would.
			 *
			 * @since 3.5.0
			 *
			 * @param string $script Empty String
			 *
			 * @return string
			 */
			 apply_filters( 'matador_campaign_tracking_inline_javascript', '' ) . '
			document.addEventListener(\'DOMContentLoaded\', (event) => { 
				if ( matador_visitor ) {
					var matador_traffic = new MatadorTraffic(\'' . $url . '\'' . $options . ');
				}
			}); 
		';

		/**
		 * Filter Matador Campaign Tracking Inline Script
		 *
		 * Modify the whole inline script included for MatadorTraffic. Use with caution, as any modification could
		 * result in this feature completely not working. If modified, the modified script should instantiate the
		 * MatadorTraffic class with options.
		 *
		 * This filter can be used to completely modify instantiation for any of the following situations:
		 *
		 * - A site does JS precompiling/compression resulting in a change of name for the MatadorTraffic class
		 * - A site wishes to load matador-traffic.js asyncronously and therefore requires a different instantiation
		 *   triggering routine. (The default DOMContentLoaded does not work with async.)
		 * - A site has a complex cookie/PII consent process that may require a more complex rewrite as opposed to the
		 *   easier inline injection available with `matador_campaign_tracking_inline_javascript`
		 * - A site wishes to override Matador Software developer's belief that Matador Jobs Pro should honor users "do
		 *   not track" preference. We strongly discourage this, and will not provide support to users in debugging code
		 *   that does this.
		 *
		 * Note: this is not to be confused with the filter `matador_campaign_tracking_inline_javascript` which inserts
		 * JS code into the middle of the default code. Wish we could go back and rename that one.
		 *
		 * @since 3.7.7
		 *
		 * @param string $script  The inline script
		 * @param string $url     The URL used for instantiation.
		 * @param string $options A string of JSON-formatted default options
		 *
		 * @return string
		 */
		apply_filters( 'matador_campaign_tracking_inline_script', $inline, $url, $options );

		if ( $inline ) {
			wp_add_inline_script( 'matador_traffic', $inline );
		}
	}

	/**
	 * Method: Add Campaign to Form
	 *
	 * If the user blocks tracking, Matador can still pass the state of the campaign querystring data one time via the
	 * forms if the inbound traffic landed on a page with an application form. Capture the data and add it to the form
	 * via hidden fields.
	 *
	 * Note: this function only adds collected data from the tracked query variables and/or HTTP_Referer server variable
	 * to hidden fields in the form. The form will POST the data through to the form processing load, thus passing state
	 * in WordPress's otherwise stateless behavior without requiring a Cookie. That said, whether this is used to modify
	 * the Candidate or Job Submission is determined later and by other factors, including the presence, or lack
	 * thereof, of a Matador Traffic tracking cookie.
	 *
	 * @since 3.5.0
	 */
	public static function add_campaign_data_to_form() {

		// If the current logged in user is an Admin, Editor, or Author, do not load the tracker, except when WP_DEBUG
		// is on.
		if ( self::should_not_track() ) {
			return;
		}

		// Check if a Matador Traffic cookie exists, and if so, skip this step.
		if ( isset( $_COOKIE[ self::cookie_name() ] ) ) {
			return;
		}

		// Get the campaign data, if any, from querystring and $_SERVER variables
		$campaign = self::get_campaign();

		// Build hidden input fields to pass these states forward.
		foreach ( $campaign as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			printf( '<input id="%1$s" name="%1$s" type="hidden" value="%2$s" />', esc_attr( $key ), esc_attr( $value ) );
		}
	}

	/**
	 * Method: Save Campaign Data to Application
	 *
	 * Saves the collected campaign data to the Application array and post type. It starts by checking for the presence
	 * of a Matador Traffic tracking cookie, which if found, becomes the basis for the campaign. If one is not found, it
	 * looks inside the $_REQUEST to see if Matador passed campaign data via the application form and uses that as the
	 * basis for the campaign. With a campaign, values are checked against a whitelist (if any) before being added to
	 * the Application and stored in the database prior to processing.
	 *
	 * @since 3.5.0
	 *
	 * @see CookieMonster, used to fetch the values of the Matador Cookie
	 *
	 * @param array $application The work-in-progress Application array before its saved.
	 * @param array $request     The contents of the $_REQUEST array (after sanitization)
	 *
	 * @return array $application The modified Application array
	 */
	public static function save_campaign_data_to_application( $application, $request ) {

		// Get Campaign Data from Google Analytics Cookie
		$campaign = CookieMonster::eat_cookie( self::cookie_name() );

		// If we have no campaign data, it is because we had no cookie,
		// so check if we have any campaign data passed from Application.
		if ( ! $campaign ) {
			$campaign = [];

			foreach ( self::$campaign_fields as $field ) {
				if ( empty( $request[ $field ] ) ) {
					continue;
				}
				$campaign[ $field ] = esc_attr( $request[ $field ] );
			}
		}

		// If we still don't have campaign data, we won't modify the Application
		if ( empty( $campaign ) ) {
			return $application;
		}

		$sanitized = [
			'timestamp' => ! empty( $campaign->timestamp ) ? $campaign->timestamp->format( 'U' ) : ( new DateTime() )->format( 'U' ),
			'sessions'  => ! empty( $campaign['sessions'] ) ? (int) $campaign['sessions'] : 1,
			'campaigns' => ! empty( $campaign['campaigns'] ) ? (int) $campaign['sessions'] : 1,
		];

		// Since we have campaign data, lets check its values against the whitelist, copy to sanitized array
		foreach ( [ 'campaign', 'source', 'term', 'medium', 'content' ] as $key ) {
			if ( empty( $campaign[ $key ] ) ) {
				continue;
			}
			$sanitized[ $key ] = self::should_whitelist( $key, $campaign[ $key ] );
		}

		// Add the campaign data to the Application
		$application['application']['campaign'] = $sanitized;

		return $application;
	}

	/**
	 * Method: Ignore Campaign Fields in Application Processor
	 *
	 * Adds the campaign related fields to the ignore array in the Application Processor so they are not added to the
	 * catch-all notes section at the end.
	 *
	 * @since 3.5.0
	 *
	 * @param array $ignored Array of fields to ignore.
	 *
	 * @return array Modified array of fields to ignore.
	 */
	public static function ignore_campaign_fields_in_application( $ignored ) {
		return array_merge( $ignored, self::$campaign_fields );
	}

	/**
	 * Method: Modify Candidate/Submission Source Values
	 *
	 * Provided our Application stored campaign data, append the Candidate/Submission "Source" field prior to sending
	 * the object to Bullhorn.
	 *
	 * @since 3.5.0
	 *
	 * @param string    $source     The source value, default is '[WEBSITE NAME]' but the filter is called at priority
	 *                              25 which means an extension or user function could have changed it.
	 * @param string    $context    The context of the source. We are checking for the 'submission' and 'candidate'
	 *                              context.
	 * @param \stdClass $candidate  The Candidate object. This will not used and will be unset.
	 * @param array     $submission The form submission raw data, in this case, the $application
	 *
	 * @return string   $source     The modified source value
	 */
	public static function modify_source_description( $source, $context, $candidate, $submission ) {

		// Only modify the source in the candidate or submission context.
		if ( ! in_array( $context, [ 'candidate', 'submission', 'lead' ], true ) ) {
			return $source;
		}

		// We won't use this.
		unset( $candidate );

		// Check the submission. Campaign data needs to exist.
		if ( empty( $submission['campaign'] ) ) {
			return $source;
		}

		// Instantiate a variable to add our appended source data
		$append = '';

		/**
		 * Filter: Matador Campaign Tracking Source Field Separator
		 *
		 * Changes the separator used to break each of the campaign data points.
		 *
		 * @since 3.5.0
		 *
		 * @param string $separator The separator. Default is forward slash (/)
		 *
		 * @param string $separator The modified/changed separator.
		 */
		$separator = apply_filters( 'matador_campaign_tracking_source_separator', '/' );

		if ( ! empty( $submission['campaign']['source'] ) ) {
			$append .= ucwords( str_replace( '_', ' ', $submission['campaign']['source'] ) );
		} else {
			$append .= 'NA';
		}

		$append .= $separator;

		if ( ! empty( $submission['campaign']['medium'] ) ) {
			$append .= ucwords( str_replace( '_', ' ', $submission['campaign']['medium'] ) );
		} else {
			$append .= 'NA';
		}

		$append .= $separator;

		if ( ! empty( $submission['campaign']['campaign'] ) ) {
			$append .= ucwords( str_replace( '_', ' ', $submission['campaign']['campaign'] ) );
		}

		/**
		 * Filter: Matador Campaign Tracking Source Reset
		 *
		 * Fully resets the source field when campaign data is found. This is wise if a website name is long and causing
		 * campaign data to be truncated by the field character limits. Note: if a site operator would like to just
		 * shorten their name, ie: from 'ACME Staffing Solutions' to 'ACME', they should run a
		 * matador_data_source_description filter with a priority earlier than 25 and not use this filter.
		 *
		 * @since 3.5.0
		 *
		 * @param bool $condition True/False to empty the Source. Default false.
		 *
		 * @param bool $condition
		 */
		if ( $append && apply_filters( 'matador_campaign_tracking_source_reset', false ) ) {
			$source = '';
		}

		if ( $append ) {
			$source .= ' (' . $append . ')';
		}

		return $source;
	}

	/**
	 * Method: Get Campaign (Private)
	 *
	 * If the user blocks tracking and/or they disable Javascript, Matador can still pass the state of the campaign data
	 * one time via the forms. This function will check for campaign data in the querystring and/or surmise information
	 * from the referrer to generate campaign data for the current request.
	 *
	 * Note: this function only collects data from the defined querystring variables and/or HTTP_Referer server
	 * variable. Other functions passes them along to the application forms, which will POST them through to the next
	 * page load, thus passing state in WordPress's otherwise stateless behavior without requiring a Cookie. That said,
	 * whether this is used to modify the Candidate or Job Submission is determined later and by other factors,
	 * including the presence, or lack thereof, of a Matador Traffic cookie.
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	private static function get_campaign() {

		$keys = self::query_string_keys();

		$campaign = filter_input( INPUT_GET, $keys['campaign'], FILTER_SANITIZE_URL );
		$source   = filter_input( INPUT_GET, $keys['source'], FILTER_SANITIZE_URL );
		$medium   = filter_input( INPUT_GET, $keys['medium'], FILTER_SANITIZE_URL );
		$term     = filter_input( INPUT_GET, $keys['term'], FILTER_SANITIZE_URL );
		$content  = filter_input( INPUT_GET, $keys['content'], FILTER_SANITIZE_URL );
		$referrer = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url( $_SERVER['HTTP_REFERER'] ) : '';

		// If a source is not listed but a campaign is, use campaign as source.
		if ( ! $source && $campaign ) {
			$source = $campaign;
		}

		// If we don't have a $source but we have a referrer from the $_SERVER variables, lets check it.
		if ( ! $source && $referrer ) {

			$search = self::check_referrer_against_list( $referrer, self::known_search_engines() );
			$social = self::check_referrer_against_list( $referrer, self::known_social_networks() );

			if ( $search ) {
				$source = strtolower( esc_attr( $search ) );
				$medium = $medium ?: 'organic';
			} elseif ( $social ) {
				$source = strtolower( esc_attr( $social ) );
				$medium = $medium ?: 'social';
			}
		}

		// Array keys should match/include keys in
		// self::$campaign_fields
		return [
			'source'   => $source,
			'campaign' => $campaign,
			'medium'   => $medium,
			'term'     => $term,
			'content'  => $content,
		];
	}

	/**
	 * Method: Should Check Whitelist (Private)
	 *
	 * Checks the value against the whitelist and returns the value if found in the whitelist or an empty string if not.
	 *
	 * @since 3.5.0
	 *
	 * @param string $field Field to check Whitelist
	 * @param string $value Item to be checked against Whitelist.
	 *
	 * @return string $value The value or an empty string if value is not found in whitelist.
	 */
	private static function should_whitelist( $field, $value = '' ) {

		if ( empty( $field ) || ! is_string( $field ) || ! in_array( $field, self::$campaign_fields, true ) ) {
			return '';
		}

		if ( empty( $value ) || ! is_string( $value ) ) {
			return $value;
		}

		/**
		 * Filter: Matador Campaign Tracking Check $field Against Whitelist
		 *
		 * This dynamic filter turns on or off the Whitelist check for a given campaign field. Valid campaign fields are
		 * 'source', 'medium', 'campaign', 'term', or 'content'.
		 *
		 * @since 3.5.0
		 *
		 * @param bool $on/off Default false.
		 *
		 * @return bool
		 */
		if ( apply_filters( "matador_campaign_tracking_check_{$field}_against_whitelist", false ) ) {

			/**
			 * Filter: Matador Campaign Tracking $field Whitelist
			 *
			 * This dynamic filter allows you to define the Whitelist for a given field. Pass an array of whitelisted items.
			 *
			 * @since 3.5.0
			 *
			 * @param array $whitelist Single-dimensional array of whitelisted values.
			 *
			 * @return array
			 */
			$whitelist = apply_filters( "matador_campaign_tracking_{$field}_whitelist", [] );

			return self::check_whitelist( $value, $whitelist );
		}

		return $value;
	}

	/**
	 * Method: Check Whitelist (Private)
	 *
	 * Checks the value against the whitelist and returns the value if found in the whitelist or an empty string if not.
	 *
	 * @since 3.5.0
	 *
	 * @param string $value     Item to be looked for in Whitelist.
	 * @param array  $whitelist Array of whitelisted values.
	 *
	 * @return string $value The value or an empty string if value is not found in whitelist.
	 */
	private static function check_whitelist( $value, $whitelist ) {

		if ( empty( $value ) || ! is_string( $value ) ) {
			return '';
		}

		if ( empty( $whitelist ) || ! is_array( $whitelist ) ) {
			return '';
		}

		if ( ! in_array( $value, $whitelist, true ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Method: Check Referrer Against List
	 *
	 * As part of the no-cookies backup behavior, if a page presenting an application is loaded without a cookie or
	 * query string, our backup of a backup is check where the user was prior. This function checks if the user was in
	 * the domain of any of the top websites in the provided list.
	 *
	 * @since 3.5.0
	 *
	 * @param string $referrer Value of $_SERVER['HTTP_REFERRER'], sanitized.
	 * @param array  $list     Array of referrers with an array of RegEx strings to check the referrer against.
	 *
	 * @return string $source Value that should be used in place of the omitted utm_source query string.
	 */
	private static function check_referrer_against_list( $referrer, $list ) {

		foreach ( $list as $source => $regexs ) {
			foreach ( $regexs as $regex ) {
				if ( preg_match( $regex, $referrer ) ) {
					return $source;
				}
			}
		}

		return false;
	}

	/**
	 * Method: Cookie Name
	 *
	 * @since 3.5.0
	 *
	 * @param bool $filtered whether to return the default or filtered values
	 *
	 * @return string
	 */
	private static function cookie_name( $filtered = true ) {
		/**
		 * Filter: Matador Campaign Tracking Cookie Name
		 *
		 * Change the name of the cookie. As some visitors may have tools installed on their blockers to block cookies
		 * with words that suggest the cookie is used for tracking, it may benefit a site from renaming the cookie.
		 * Default is 'matador_traffic'. A change might be 'abc_staffing_apply', for example.
		 *
		 * @since 3.5.0
		 *
		 * @param string $cookie_name
		 *
		 * @return string
		 */
		return $filtered ? apply_filters( 'matador_campaign_tracking_cookie_name', self::$cookie ) : self::$cookie;
	}

	/**
	 * Method: Querystring Keys
	 *
	 * If the visitor blocks Analytics, Matador
	 * can still pass the state of the campaign data one time via the forms. So lets capture that.
	 *
	 * Note: this function only collects data from the UTM_* query variables and/or HTTP_Referer server variable. Other
	 * functions passes them along to the application forms, which will POST them through to the next page load, thus
	 * passing state in WordPress's otherwise stateless behavior without requiring a Cookie. That said, whether this is
	 * used to modify the Candidate or Job Submission is determined later and by other factors, including the presence,
	 * or lack thereof, of a Google Analytics tracking cookie.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $filtered whether to return the default or filtered values
	 *
	 * @return array
	 */
	private static function query_string_keys( $filtered = true ) {

		$keys = [];

		foreach ( self::$campaign_fields as $key ) {
			$keys[ $key ] = 'utm_' . $key;
		}

		/**
		 * Filter: Matador Campaign Tracking Query String Keys
		 *
		 * Change the query string keys, if you'd like to use something other than UTM_*. Note, Google for Jobs sends
		 * referrals with fully-formed UTM parameters.
		 *
		 * @since 3.5.0
		 *
		 * @param array $query_string_keys
		 *
		 * @return array
		 */
		return $filtered ? apply_filters( 'matador_campaign_tracking_query_string_keys', $keys ) : $keys;
	}

	private static function source_labels( $filtered = true ) {
		// Default matches the defaults in the JS file.
		$default = [
			'none'     => 'direct(none)',
			'social'   => 'social',
			'referral' => 'referral',
			'organic'  => 'organic',
		];

		// Be careful with translations. We should try to match whatever terms our Analytics
		// are using, even if not in our native language.
		$translated = [
			'none'     => _x( 'direct(none)', 'The name of the source when a direct or unknown referrer. Should match Google Analytics for this language', 'matador-jobs' ),
			'social'   => _x( 'social', 'The name of the source when a social network referrer. Should match Google Analytics for this language', 'matador-jobs' ),
			'referral' => _x( 'referral', 'The name of the source when a known non-social network, non-search referrer. Should match Google Analytics for this language', 'matador-jobs' ),
			'organic'  => _x( 'organic', 'The name of the source when an organic search referrer. Should match Google Analytics for this language', 'matador-jobs' ),
		];

		/**
		 * Filter: Matador Campaign Tracking UTM_source Labels
		 *
		 * Change the common values for source when auto-detected. Generally recommended you match your languages'
		 * Google Analytics labels.
		 *
		 * @since 3.5.0
		 *
		 * @param array $labels
		 *
		 * @return array
		 */
		return $filtered ? apply_filters( 'matador_campaign_tracking_source_labels', $translated ) : $default;
	}

	/**
	 * Method: Search Engines List
	 *
	 * When we check referrer for a match to known search engines, we will process the referrer against an array where
	 * first level key is the name of the search engine and the first level value is a single-dimensional array of RegEx
	 * rules that coincide with known URLs for the search engine.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $filtered whether to return the default or filtered values
	 *
	 * @return array
	 */
	private static function known_search_engines( $filtered = true ) {

		$list = [
			'google'     => [ '/(.+?)\.google\./' ],
			'bing'       => [ '/(.+?)\.bing\./' ],
			'yahoo'      => [ '/(.+?)\.yahoo\./' ],
			'aol'        => [ '/(.+?)\.aol\./' ],
			'baidu'      => [ '/(.+?)\.baidu\./' ],
			'duckduckgo' => [ '/(.+?)\.duckduckgo\./' ],
		];

		/**
		 * Filter: Matador Campaign Tracking Known Search Engines
		 *
		 * Add/remove known search engines from the list a referrer will be checked against.
		 *
		 * @since 3.5.0
		 *
		 * @param array $known_search_engines key => value array where the key is the name and the value is an array of
		 *                                    regex URLs that will match the key.
		 *
		 * @return array
		 */
		return $filtered ? apply_filters( 'matador_campaign_tracking_known_search_engines', $list ) : $list;
	}

	/**
	 * Method: Social Networks List
	 *
	 * When we check referrer for a match to known social networks, we will process the referrer against an array where
	 * first level key is the name of the search engine and the first level value is a single-dimensional array of RegEx
	 * rules that coincide with known URLs for the search engine.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $filtered whether to return the default or filtered values
	 *
	 * @return array
	 */
	private static function known_social_networks( $filtered = true ) {

		$list = [
			'facebook'  => [ '/(.+?)\.facebook\./', '/(.+?)\.fb\.me/' ],
			'linkedin'  => [ '/(.+?)\.linkedin\./' ],
			'twitter'   => [ '/(.+?)\.twitter\./', '/(.+?)\.t\.co/' ],
			'reddit'    => [ '/(.+?)\.reddit\./' ],
			'instagram' => [ '/(.+?)\.instagram\./' ],
			'youtube'   => [ '/(.+?)\.youtube\./' ],
		];

		/**
		 * Filter: Matador Campaign Tracking Known Social Networks
		 *
		 * Add/remove known social networks from the list a referrer will be checked against.
		 *
		 * @since 3.5.0
		 *
		 * @param array $known_search_engines key => value array where the key is the name and the value is an array of
		 *                                    regex URLs that will match the key.
		 *
		 * @return array
		 */
		return $filtered ? apply_filters( 'matador_campaign_tracking_known_social_networks', $list ) : $list;
	}

	/**
	 * Method: Require Files
	 *
	 * While we have an open issue to update our autoloader to the PSR4 standard, until we do so, our
	 * autoloader won't read certain files that need to otherwise be manually included.
	 *
	 * @todo: Implement PSR4-scheme Autoloader, Load files as needed and automatically. Remove function.
	 *
	 * @since 3.5.0
	 */
	private static function require_files() {
		require_once Matador::$directory . '/includes/modules/campaign-tracking/class-cookiemonster.php';
		require_once Matador::$directory . '/includes/modules/campaign-tracking/class-cookie.php';
		require_once Matador::$directory . '/includes/modules/campaign-tracking/class-parser.php';
		require_once Matador::$directory . '/includes/modules/campaign-tracking/class-matador-visitor.php';
	}
}
