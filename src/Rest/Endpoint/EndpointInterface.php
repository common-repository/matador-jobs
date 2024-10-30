<?php
/**
 * Matador / REST API / Endpoint Interface
 *
 * An interface to document the creation and maintenance of WP REST API endpoints
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

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API Endpoint Interface
 *
 * @since 3.8.0
 */
interface EndpointInterface {

	/**
	 * Implementing Classes Should Declare the Following Properties
	 *
	 * @property static string $namespace Endpoint Namespace.
	 * @property static string $version   Endpoint Version.
	 * @property static string $class     Class name.
	 */

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function __construct();

	/**
	 * Namespace
	 *
	 * Returns a constructed namespace based on the namespace and version static properties.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public static function rest_namespace();
	// public static function namespace();

	/**
	 * Routes
	 *
	 * Called to the WordPress in a WordPress rest_api_init action to add the routes.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function routes();

	/**
	 * Index
	 *
	 * Gets a collection (index) of items in the resource with a GET call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function index( WP_REST_Request $request );
	// public static function index( WP_REST_Request $request ) : WP_Error|WP_REST_Response;

	/**
	 * Create
	 *
	 * Creates an item or items in the resource with a POST call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function create( WP_REST_Request $request );
	// public static function create( WP_REST_Request $request ) : WP_Error|WP_REST_Response;

	/**
	 * Read
	 *
	 * Gets an item in the resource with a GET call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function read( WP_REST_Request $request );
	// public static function read( WP_REST_Request $request ) : WP_Error|WP_REST_Response;;

	/**
	 * Update
	 *
	 * Updates an item or items in the resource with a PUT call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update( WP_REST_Request $request );
	// public static function update( WP_REST_Request $request ) : WP_Error|WP_REST_Response;;

	/**
	 * Destroy
	 *
	 * Deletes an item or items in the resource with a DELETE call.
	 *
	 * @since 3.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function destroy( WP_REST_Request $request );
	// public static function destroy( WP_REST_Request $request ) : WP_Error|WP_REST_Response;;
}
