<?php
/**
 * Matador / REST API / Endpoint Abstract
 *
 * An abstract class to aid in the creation and maintenance of WP REST API endpoints
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  REST API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Rest\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Endpoint Abstract
 *
 * @since 3.8.0
 */
abstract class EndpointAbstract implements EndpointInterface {

	/**
	 * Endpoint Namespace
	 *
	 * @var string
	 *
	 * @since 3.8.0
	 */
	protected static $namespace = 'matador';
	// protected static string $namespace = 'matador';

	/**
	 * Endpoint Version
	 *
	 * @var string
	 *
	 * @since 3.8.0
	 */
	protected static $version = '1';
	// protected static string $version = '1';

	/**
	 * Class Name
	 *
	 * @var string
	 *
	 * @since 3.8.0
	 */
	protected static $class = __CLASS__;
	// protected static string $class = __CLASS__;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ static::$class, 'routes' ] );
	}

	/**
	 * Namespace
	 *
	 * Returns a constructed namespace based on the namespace and version static properties.
	 *
	 * @since 3.8.0
	 *
	 * @access public
	 * @static
	 *
	 * @return string
	 */
	public static function rest_namespace() {
	// public static function namespace() : string {

		return static::$namespace . '/v' . static::$version;
	}

	/**
	 * Routes
	 *
	 * Called in a WordPress rest_api_init action to add the routes.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function routes() {
	// public static function routes() : void {
		/* translators: %s: routes() */
		_doing_it_wrong( 'matador\MatadorJobs\Rest\EndpointAbstract::register_routes', sprintf( esc_html__( "Abstract class method '%s' must be defined by inheriting class.", 'matador-jobs' ), __METHOD__ ), '1.0' );
	}

	/**
	 * Index
	 *
	 * Gets a collection (index) of items in the resource from a GET call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function index( WP_REST_Request $request ) {
	// public static function index( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		return self::_501( __METHOD__ );
	}

	/**
	 * Create
	 *
	 * Creates an item or items in the resource with a POST call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create( WP_REST_Request $request ) {
	// public static function create( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		return self::_501( __METHOD__ );
	}

	/**
	 * Read
	 *
	 * Gets an item in the resource with a GET call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function read( WP_REST_Request $request ) {
	// public static function read( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		return self::_501( __METHOD__ );
	}

	/**
	 * Update
	 *
	 * Updates an item or items in the resource with a PUT call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update( WP_REST_Request $request ) {
	// public static function update( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		return self::_501( __METHOD__ );
	}

	/**
	 * Destroy
	 *
	 * Deletes an item or items in the resource with a DELETE call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function destroy( WP_REST_Request $request ) {
	// public static function destroy( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		return self::_501( __METHOD__ );
	}

	/**
	 * Not Implemented
	 *
	 * Returns an HTTP 501 "Not Implemented" or a WP_Debug output for endpoints which are not implemented.
	 *
	 * @since 3.8.0
	 *
	 * @param string $method
	 *
	 * @return WP_Error
	 */
	private static function _501( $method = '' ) {
	// private static function _501( string $method = '' ) : WP_Error {
		/* translators: %s: called method name */
		_doing_it_wrong( $method, sprintf( esc_html__( "Abstract class method '%s' must be defined by inheriting class.", 'matador-jobs' ), $method ), '3.8.0' );

		return new WP_Error(
			__( 'Not Implemented', 'matador-jobs' ),
			__( 'This call is not available.', 'matador-jobs' ),
			[ 'status' => 501 ]
		);
	}
}
