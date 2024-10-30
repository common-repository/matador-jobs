<?php
/**
 * Matador / REST API / "Sync" Endpoint
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

// @todo: for PHP 8.0 update, add union type return typing

namespace matador\MatadorJobs\Rest\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use matador\MatadorJobs\Sync\Sync as Routine;
use matador\MatadorJobs\Rest\Schema\Sync as Schema;

/**
 * Sync Endpoint Class
 *
 * @since 3.8.0
 */
class Sync extends EndpointAbstract {

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
	 * Routes
	 *
	 * Called in a WordPress rest_api_init action to add the routes.
	 *
	 * @since 3.8.0
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function routes() {
	// public static function routes() : void {

		register_rest_route( self::rest_namespace(), '/sync', [
		// register_rest_route( self::namespace(), '/sync', [
			[
				'methods' => WP_REST_Server::READABLE,
				'callback' => [ __CLASS__, 'index' ],
				'permission_callback' => '__return_true',
				'args'    => Schema::index(),
			],
		] );
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

		Routine::run();

		return new WP_REST_Response( null, 204 );
	}
}
