<?php
/**
 * WordPress Endpoints for Bullhorn API Integration
 *
 * @link        https://matadorjobs.com/
 * @since       2.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2016-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

use matador\MatadorJobs\Sync\Sync;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Endpoints {

	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_request', array( $this, 'parse_request' ) );
	}

	/**
	 * Add Rewrite Rules
	 *
	 * @return void
	 */
	public function add_endpoint() {

		$prefix = Matador::variable( 'api_endpoint_prefix' );

		add_rewrite_rule( $prefix . 'sync?([^/]+)', 'index.php?_matador_api=1&endpoint=sync&$matches[1]', 'top' );
		add_rewrite_rule( $prefix . 'authorize?([^/]+)', 'index.php?_matador_api=1&endpoint=authorize&$matches[1]', 'top' );
		add_rewrite_rule( $prefix . 'application?([^/]+)', 'index.php?_matador_api=1&endpoint=application&$matches[1]', 'top' );

		$job_slug = trailingslashit( Matador::variable( 'post_type_slug_job_listing' ) );
		$job_key  = Matador::variable( 'post_type_key_job_listing' );

		add_rewrite_rule( $job_slug . '([^/]+)/' . Matador::variable( 'apply_url_suffix' ) . '/?$', 'index.php?' . $job_key . '=$matches[1]&matador-apply=apply', 'top' );
		add_rewrite_rule( $job_slug . '([^/]+)/' . Matador::variable( 'confirmation_url_suffix' ) . '/?$', 'index.php?' . $job_key . '=$matches[1]&matador-apply=complete', 'top' );
	}

	/**
	 * Add Matador Query Variables
	 *
	 * @since 3.0.0
	 * @since 3.6.0 added 'xid' and 'xsource' query variables
	 *
	 * @param array $vars existing query variables
	 *
	 * @return array $vars appended query variables
	 */
	public function add_query_vars( $vars ) {
		$vars[] = '_matador_api';
		$vars[] = 'endpoint';
		$vars[] = 'matador-apply';
		$vars[] = 'xid';
		$vars[] = 'xsource';

		return $vars;
	}

	/**
	 * Check for Bullhorn API requests
	 *
	 * @return void
	 */
	public function parse_request() {

		global $wp;

		// _matador_api and endpoint query vars must be set
		if ( ! isset( $wp->query_vars['_matador_api'] ) && ! isset( $wp->query_vars['endpoint'] ) ) {
			return;
		}

		switch ( $wp->query_vars['endpoint'] ) {

			case 'authorize':
				self::handle_authorization();
				break;

			case 'application':
				self::handle_application();
				break;

			case 'sync':
				self::sync();
				break;

			default:
				if ( has_action( 'matador_endpoint_' . $wp->query_vars['endpoint'] ) ) {
					/**
					 * Matador Endpoint $endpoint Dynamic Action
					 *
					 * Allows users to add custom endpoints in extensions.
					 *
					 * @since 3.7.0
					 */
					do_action( 'matador_endpoint_' . $wp->query_vars['endpoint'] );
				}

				wp_safe_redirect( home_url() );
				die();
		}
	}

	private static function handle_authorization() {

		$auto   = get_transient( Matador::variable( 'bullhorn_auto_reauth', 'transients' ) );
		$user   = current_user_can( 'manage_options' );
		$access = $auto ?: $user;

		$code   = isset( $_GET['code'] ) ? esc_attr( urldecode( $_GET['code'] ) ) : null;
		$client = isset( $_GET['client_id'] ) && $_GET['client_id'] === Matador::credential( 'bullhorn_api_client' ) ? esc_attr( urldecode( $_GET['client_id'] ) ) : null;
		$error  = isset( $_GET['error'] ) ? esc_html( urldecode( $_GET['error'] ) ) : null;

		if ( ! $access ) {
			status_header( '403', esc_html__( 'Forbidden', 'matador-jobs' ) );
			$message = esc_html__( 'An unauthorized request was made to the authorization endpoint and was blocked.', 'matador-jobs' );
			Logger::add( 'security-notice', 'matador-bullhorn-authorize-unauthorized', $message );
			die();
		}

		if ( $user ) {

			if ( $client && $code ) {

				$message = esc_html__( 'A valid authorization response was received. Will log in with Authorization Code', 'matador-jobs' ) . ': ' . $code;

				Logger::add( 'notice', 'bullhorn-authorization-received', $message );

				$bullhorn = new Bullhorn_Connection();

				try {
					$bullhorn->request_access_token( $code );

					$username = $bullhorn->get_logged_in_username( true );

					$success = sprintf( esc_html__( 'Matador successfully authorized with Bullhorn as %s', 'matador-jobs' ), $username );


					Admin_Notices::add( $success, 'success', 'bullhorn-authorization-complete' );
					Admin_Notices::remove( 'save-settings' );
					$redirect = Bullhorn_Connection_Assistant::get_url();
					wp_safe_redirect( $redirect );
					die();

				} catch ( Exception $e ) {
					Admin_Notices::add( $e->getMessage(), $e->getLevel(), $e->getName() );
					$redirect = Bullhorn_Connection_Assistant::get_url();
					wp_safe_redirect( $redirect );
					die();

				}
			}

			if ( $error ) {

				$message = esc_html( __( 'An authorization response from Bullhorn resulted in an error: ', 'matador-jobs' ) . $error );
				Admin_Notices::add( $message, 'error', 'bullhorn-authorization-returned-error' );
				Logger::add( 'notice', 'bullhorn-authorization-returned-error', $message );

			}
			$redirect = Bullhorn_Connection_Assistant::get_url();
			wp_safe_redirect( $redirect );
			die();
		}

		if ( $auto ) {

			$response = [];

			if ( $code & $client ) {
				Logger::add( 'notice', 'bullhorn-authorization-received-start', __( 'A valid automatic reauthorization request was processed.', 'matador-jobs' ) );
				$response = [
					'code' => $code
				];
			} elseif ( $error ) {
				Logger::add( 'notice', 'bullhorn-authorization-received-start', __( 'An invalid automatic reauthorization request was processed.', 'matador-jobs' ) );
				$response = [
					'error' => $error
				];
			}

			header( 'content-type: application/json; charset=utf-8' );
			echo json_encode( $response );
			die();
		}
	}

	private static function handle_application() {
		if ( ! (bool) Matador::setting( 'applications_accept' ) ) {

			return;
		}

		$application = new Application_Handler();

		$applied = $application->apply();

		if ( $applied ) {
			wp_safe_redirect( self::get_confirmation_redirect() );
		} else {
			wp_safe_redirect( self::get_form_error_redirect() );
		}

		die();
	}

	private static function get_confirmation_redirect() {

		$job_id  = ( isset( $_REQUEST['wpid'] ) ) ? esc_attr( $_REQUEST['wpid'] ) : false;

		if ( ! $job_id && isset( $_REQUEST['bhid'] ) ) {
			$id_from_bhid = Helper::get_post_by_bullhorn_id( esc_attr( $_REQUEST['bhid'] ) );
			if ( $id_from_bhid ) {
				$job_id = $id_from_bhid;
			}
		}

		$referer = ( isset( $_REQUEST['_wp_http_referer'] ) ) ? esc_url( $_REQUEST['_wp_http_referer'] ) : '';
		$page_id = Matador::setting( 'applications_confirmation_page' );
		$method  = Matador::setting( 'applications_confirmation_method' );

		switch ( $method ) {
			case 'custom':
				if ( - 1 !== $page_id ) {
					$permalink = get_page_link( apply_filters( 'wpml_object_id', $page_id, 'page', true ) );
				} else {
					$permalink = esc_url( $referer );
				}
				break;
			case 'create':
				if ( $job_id ) {
					return trailingslashit( get_permalink( $job_id ) ) . Matador::variable( 'confirmation_url_suffix' );
				} else {
					$permalink = esc_url( $referer );
				}
				break;
			case 'append':
			default:
				if ( ! empty( $job_id ) ) {
					$permalink = get_permalink( $job_id );
				} else {
					$permalink = esc_url( $referer );
				}
				break;
		}

		return add_query_arg( 'matador-apply', 'complete', $permalink );
	}

	/**
	 * Sync (from Backup Endpoint)
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function sync() {

		if ( ! Helper::verify_nonce_from_http_headers( Matador::variable( 'sync_continue', 'nonce' ) ) ) {

			new Event_Log( 'matador-experimental-sync-http-loopback-nonce-failed', 'Nonce failed on HTTP Loopback' );

			status_header( '403', esc_html( __( 'Forbidden', 'matador-jobs' ) ) );

			exit();
		}

		Sync::run();

		status_header( '204', esc_html( __( 'No Content', 'matador-jobs' ) ) );

		exit();
	}

	public static function get_form_error_redirect() {

		$job_id  = ( isset( $_REQUEST['wpid'] ) ) ? esc_attr( $_REQUEST['wpid'] ) : false;
		$referer = ( isset( $_REQUEST['_wp_http_referer'] ) ) ? esc_url( $_REQUEST['_wp_http_referer'] ) : '';

		if ( ! empty( $job_id ) ) {
			$permalink = get_permalink( $job_id );
		} else {
			$permalink = esc_url( $referer );
		}

		return add_query_arg( 'matador-apply', 'error', $permalink );
	}
}
