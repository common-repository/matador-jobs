<?php
/**
 * Matador / Core / Analytics
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador US, LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador US, LP
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use stdClass;
use matador\Matador;
use matador\Template_Support;

/**
 * Class Analytics
 *
 * @since 3.8.0
 */
class Analytics {

	/**
	 * Analytics Write Key
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	private static $key = 'cuuRvJk5qZ5EvD2o';
	//private static string $key = 'OkgeCcHCD3lDhdVM';

	/**
	 * Analytics Event URL
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	private static $url = 'https://fp.matadorjobs.com/events/v1/track';
	//private static string $url = 'https://fp.matadorjobs.com/events/v1/track';

	/**
	 * Constructor
	 *
	 * Sets up default Analytics events.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	function __construct() {
		// @todo remove/replace
		// add_action( 'wp_loaded', [ __CLASS__, 'daily_instance_report' ], 20 );
	}

	/**
	 * Analytics Event
	 *
	 * Sends an analytics event from the passed variables.
	 *
	 * @since 3.8.0
	 *
	 * @param string $event      The name of the analytics "Event". "Noun Verb" pattern recommended.
	 * @param array  $properties Array of properties in a $key => (string) $value pattern.
	 *
	 * @return void
	 */
	public static function event( $event, array $properties = [] ) {
	// public static function event( string $event, array $properties = [] ) : void {

		// @todo: remove/replace

		// $args = [
		//	'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
		//	'body'        => self::manifest( $event, $properties ),
		//	'blocking'    => false,
		//	'timeout'     => '0.1',
		// ];

		// wp_remote_post( self::$url, $args );
	}

	/**
	 * Build Manifest
	 *
	 * Builds an Analytics Manifest from the passed variables with defaults.
	 *
	 * @since 3.8.0
	 *
	 * @param string $event The name of the analytics "Event". "Noun Verb" pattern recommended.
	 * @param array $properties Array of properties in a $key => (string) $value pattern.
	 *
	 * @return string JSON-formatted string ready for transmittal to Analytics.
	 */
	private static function manifest( $event, array $properties = [] ) {
	//private static function manifest( string $event, array $properties = [] ) : string {

		$manifest = new stdClass();

		$manifest->name = $event;

		$manifest->write_key = defined( 'MATADOR_ANALYTICS_WRITE_KEY' ) ? MATADOR_ANALYTICS_WRITE_KEY : self::$key;

		$manifest->properties = $properties + self::properties();

		return wp_json_encode( $manifest );
	}

	/**
	 * (Default) Properties
	 *
	 * Generates a default analytics properties array, upon which we can build additional data points for specific
	 * calls.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	private static function properties() {
	// private static function properties() : array {

		global $wp_version;

		$properties = [
			'license_key'       => Matador::setting( 'license_core' ),
			'matador_version'   => Matador::VERSION,
			'is_pro'            => Matador::is_pro(),
			'bullhorn_client'   => Matador::credential( 'bullhorn_api_client' ),
			'bullhorn_user'     => Matador::credential( 'bullhorn_api_user' ),
			'wordpress_url'     => get_home_url(),
			'timezone'          => wp_timezone_string(),
			'wordpress_version' => $wp_version,
		];

		global $wp_rewrite;

		if ( isset( $wp_rewrite ) && method_exists( $wp_rewrite, 'get_page_permastruct' ) ) {
			$properties['matador_jobs_url'] = Template_Support::the_jobs_link();
			$properties['matador_jobs_base'] = trailingslashit( get_home_url() . '/' . Matador::setting( 'post_type_slug_job_listing' ) );
		}

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$properties['multisite'] = true;
		}

		return $properties;
	}

	/**
	 * Daily Instance Report
	 *
	 * Maybe sends the daily analytics Instance Report event.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function daily_instance_report() {
	// public static function daily_instance_report() : void {

		$option = get_option( 'matador_ET_last_phone_home' );

		if ( false === $option || time() > $option ) {

			$properties = [
				'active_jobs'  => (int) wp_count_posts( Matador::variable('post_type_key_job_listing' ) )->publish,
				'applications' => (int) get_transient( Matador::variable( 'recent_applications', 'transients' ) ) ?: 0,
			];

			delete_transient( Matador::variable( 'recent_applications', 'transients' ) );

			if ( $option ) {
				update_option( 'matador_ET_last_phone_home', time() + DAY_IN_SECONDS );
			} else {
				add_option( 'matador_ET_last_phone_home', time() + DAY_IN_SECONDS  );
			}

			self::event( 'Instance Report', $properties );
		}
	}
}