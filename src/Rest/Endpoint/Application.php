<?php
/**
 * Matador / REST API / Application/Preload Endpoint
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.18
 *
 * @package     Matador Jobs Board
 * @subpackage  REST API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2024, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// @todo: for PHP 8.0 update, add union type return typing

namespace matador\MatadorJobs\Rest\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Matador\Matador;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use matador\MatadorJobs\Rest\Schema\Application as Schema;

/**
 * Application Endpoint Class
 *
 * @since 3.8.18
 */
class Application extends EndpointAbstract {

	/**
	 * Class Name
	 *
	 * @var string
	 *
	 * @since 3.8.18
	 */
	protected static $class = __CLASS__;
	// protected static string $class = __CLASS__;

	/**
	 * Routes
	 *
	 * Called in a WordPress rest_api_init action to add the routes.
	 *
	 * @since 3.8.18
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function routes() {
	// public static function routes() : void {

		register_rest_route( self::rest_namespace(), '/application/preload', [
		// register_rest_route( self::namespace(), '/application/preload', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'create' ],
				'permission_callback' => '__return_true',
				'args'                => Schema::index(),
			],
		] );
	}

	/**
	 * Create
	 *
	 * Accepts a form at page load and updates with preload information.
	 *
	 * @since 3.8.18
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create( WP_REST_Request $request ) {
	// public static function index( WP_REST_Request $request ) : WP_REST_Response|WP_Error {

		$form = $request->get_params();

		if ( empty( $form ) ) {

			return new WP_REST_Response( [ 'error' => __( 'No form data was submitted.', 'matador-jobs' ) ], 400 );
		}

		$nonce_key = Matador::variable( 'application', 'nonce' );

		$data = [];

		if ( ! isset( $form[ $nonce_key ] ) ) {

			return new WP_REST_Response( [ 'error' => __( 'Invalid form data was submitted.', 'matador-jobs' ) ], 406 );
		}

		$nonce = $form[ $nonce_key ];

		if ( ! empty( $request['_wpuser'] ) ) {

			global $current_user;

			$existing = $current_user->ID;

			// The WP Rest API does not log in a user from a cookie, but calls to wp_verify_nonce() and wp_create_nonce()
			// pass the session token from the logged in user as part of the verification, so we need to set the user during
			// this check.
			$current_user = wp_set_current_user( $request['_wpuser'] );

			if ( 1 !== wp_verify_nonce( $nonce, $nonce_key ) ) {
				$data[ $nonce_key ] = wp_create_nonce( $nonce_key );
			}

			// For safety, undo the changes after the nonce is validated/created.
			$current_user = wp_set_current_user( $existing );

		} else {

			if ( 1 !== wp_verify_nonce( $nonce, $nonce_key ) ) {
				$data[ $nonce_key ] = wp_create_nonce( $nonce_key );
			}
		}

		/**
		 * Preload Form Data Filter
		 * @wordpress-filter 'matador_jobs_application_form_preload_data'
		 *
		 * @since 3.8.18
		 *
		 * @param array $data To be used to prefill/update form.
		 * @param array $form Form data from page load on site/autofill/cache.
		 *
		 * @return array Data to be returned via script to update/preload the form.
		 */
		$data = apply_filters( 'matador_jobs_application_form_preload_data', $data, $request );

		return new WP_REST_Response( $data, 200 );
	}
}
