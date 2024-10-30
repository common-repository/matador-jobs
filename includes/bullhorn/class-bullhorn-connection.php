<?php
/**
 * Matador / Bullhorn API / Connection
 *
 * Parent class to all Bullhorn routines.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\MatadorJobs\Core\Analytics;
use matador\MatadorJobs\Email\AdminNoticeDisconnectedMessage;

class Bullhorn_Connection {

	/**
	 * Property: Logged In
	 *
	 * Stores a boolean with the result of the login attempt.
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $logged_in = false;

	/**
	 * Property: API Credentials
	 *
	 * Stores the authorized API credentials we use to log into Bullhorn.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	protected $credentials;

	/**
	 * Property: Session
	 *
	 * Stores the session ID we use to make subsequent requests to Bullhorn.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	protected $session;

	/**
	 * Property: URL
	 *
	 * Nicely holds the formatted URL we make requests to.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	protected $url;

	/**
	 * Constructor
	 *
	 * Class constructor sets up some variables.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// This creates a "variable" in the variables array for us that isn't otherwise defined.
		add_filter( 'matador_variable_bullhorn_api_credentials_key', array( __CLASS__, 'define_credentials_key' ) );

		$this->get_credentials();

		try {
			$this->login();
		} catch ( Exception $e ) {

			new Event_Log( $e->getName(), $e->getMessage() );
			Admin_Notices::add( esc_html__( 'Login into Bullhorn failed see log for more info.', 'matador-jobs' ), 'warning', 'bullhorn-login-exception' );
		}
	}

	/**
	 * Define Credentials Key
	 *
	 * Allows us to use a filter to set a variable to the Matador::$variable object without
	 * having it pre-set in the variables defaults for security reasons.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public static function define_credentials_key() {

		return 'bullhorn_api_credentials';
	}

	/**
	 * Get Regional Datacenter URL
	 *
	 * Checks if a past Bullhorn cluster ID was determined and sets the datacenter based on the last known Bullhorn
	 * cluster for the user.
	 *
	 * @since 3.0.0
	 * @since 3.5.0  Updated datacenters
	 * @since 3.8.10 Updated to detect datacenter from cluster instead of setting (old behavior), added $type arg to
	 *               support datacenters for oauth (auth) and login (rest).
	 *
	 * @param string $type The action-specific datacenter, ie: 'auth' or 'rest' or 'api'.
	 *
	 * @return string
	 */
	private function get_data_center( $type = 'auth' ) {

		$cluster = (int) Matador::setting( 'bullhorn_api_cluster_id', 0 );

		switch ( $cluster ) {
			case 21:
			case 22:
			case 23:
				$url = "https://$type-emea.bullhornstaffing.com/";
				break;
			case 29:
				$url = "https://$type-emea9.bullhornstaffing.com/";
				break;
			case 30:
			case 31:
			case 32:
			case 33:
			case 34:
			case 35:
				$url = "https://$type-west.bullhornstaffing.com/";
				break;
			case 40:
			case 41:
			case 42:
			case 43:
				$url = "https://$type-east.bullhornstaffing.com/";
				break;
			case 50:
				$url = "https://$type-west50.bullhornstaffing.com/";
				break;
			case 60:
				$url = "https://$type-apac.bullhornstaffing.com/";
				break;
			case 66:
				$url = "https://$type-aus.bullhornstaffing.com/";
				break;
			case 70:
				$url = "https://$type-ger.bullhornstaffing.com/";
				break;
			case 71:
				$url = "https://$type-fra.bullhornstaffing.com/";
				break;
			case 91:
			case 99:
				$url = "https://$type-west9.bullhornstaffing.com/";
				break;
			default:
				$url = "https://$type.bullhornstaffing.com/";
				break;
		}

		/**
		 * @wordpress-filter Matador Bullhorn API Datacenter URL
		 *
		 * @since 3.0.0
		 * @since 3.8.10 Added $type. Second arg was changed from the datacenter setting, now deprecated, the last known
		 *               cluster
		 *
		 * @param string $url The datacenter URL determined from the cluster data, or the default
		 * @param string $cluster The last known cluster from previous login data
		 * @param string $type The type or URL being requested, ie: 'auth' or 'rest'
		 * @return string
		 */
		return esc_url( apply_filters( 'matador_bullhorn_data_center_url', $url, $cluster, $type ) );
	}

	/**
	 * Get API Credentials
	 *
	 * Gets the stored API Credentials from the WordPress
	 * database and assigns it to the variable.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function get_credentials() {

		$credentials = get_option( Matador::variable( 'bullhorn_api_credentials_key' ), array() );

		if ( is_array( $credentials )
			&& array_key_exists( 'refresh_token', $credentials )
			&& array_key_exists( 'access_token', $credentials ) ) {

			$this->credentials = $credentials;
		} else {
			// @todo: add a notice that credentials are missing?
			$this->credentials = array();
		}
	}

	/**
	 * Get API Credential
	 *
	 * Checks if a credential exists and returns it.
	 *
	 * @since 3.0.0
	 *
	 * @param (string) $key the name of the setting.
	 *
	 * @return string|null
	 */
	private function get_credential( $key ) {
		if ( array_key_exists( $key, $this->credentials ) ) {

			return $this->credentials[ $key ];
		} else {

			return null;
		}
	}

	/**
	 * Update API Credentials
	 *
	 * Takes an array of credentials, ads an expiry time,
	 * and updates the class variable and database option.
	 *
	 * The credentials request gives a value 'expires_in'
	 * time in seconds for the access token. Take a timestamp
	 * of the  time right now and add the expiration in second,
	 * then, to be safe, subtract 30 seconds to make sure all
	 * our future requests are made with plenty of time to spare.
	 *
	 * @since 3.0.0
	 *
	 * @param array $credentials array of credentials
	 *
	 * @return void
	 */
	private function update_credentials( $credentials = array() ) {
		if ( ! empty( $credentials ) ) {

			// Validate all four expected values came through the authorization request
			foreach ( array( 'token_type', 'access_token', 'expires_in', 'refresh_token' ) as $key ) {
				if ( ! array_key_exists( $key, $credentials ) ) {
					Logger::add( 'critical', 'bullhorn-update-credentials-invalid-data', __( 'Invalid credentials were provided to credentials update.', 'matador-jobs' ) );
					return;
				}
			}

			// Validate the token_type is "bearer"
			if ( 'Bearer' !== $credentials['token_type'] ) {
				Logger::add( 'critical', 'bullhorn-update-credentials-invalid-token-type', __( 'An invalid token type was provided to a credentials update.', 'matador-jobs' ) );
				return;
			}

			// Sanitize the values and toss unneeded ones
			$credentials['access_token']  = esc_attr( $credentials['access_token'] );
			$credentials['refresh_token'] = esc_attr( $credentials['refresh_token'] );
			unset( $credentials['token_type'] );
			unset( $credentials['expires_in'] );

			$this->credentials = $credentials;
			update_option( Matador::variable( 'bullhorn_api_credentials_key' ), $credentials );
		}
	}

	/**
	 * Destroy API Credentials
	 *
	 * Deletes existing API Credentials and unsets the class variable.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private function destroy_credentials() {
		$this->credentials = array();
		delete_option( Matador::variable( 'bullhorn_api_credentials_key' ) );
		delete_option( Matador::variable( 'bullhorn_session', 'transients' ) );
		delete_transient( Matador::variable( 'bullhorn_logged_in_user', 'transients' ) );
	}

	/**
	 * Is Client ID Valid
	 *
	 * As an aid to our users, Matador can checks that the ClientID is valid.
	 * This check attempts a behind-the-scenes call to Bullhorn as if we
	 * were attempting to authorize the site with credentials. If the result
	 * is an error page with the text "Invalid ClientID", the method returns
	 * false. The method also returns false if the connection attempt fails.
	 * Otherwise, the method assumes the ClientID is valid and returns true.
	 *
	 * @since 3.1.0
	 *
	 * @param string
	 * @return boolean
	 */
	public function is_client_id_valid( $client = null ) {

		Logger::add( 'notice', 'bullhorn-client-id-check', __( 'Checking Bullhorn Client ID.', 'matador-jobs' ) );

		$client = ! empty( $client ) ? $client : Matador::credential( 'bullhorn_api_client' );

		if ( ! $client ) {
			return false;
		}

		$url = $this->get_data_center() .  'oauth/authorize';

		$params = array(
			'client_id'     => $client,
			'response_type' => 'code',
		);

		$request = wp_remote_get( $url . '?' . http_build_query( $params ), array( 'timeout' => 30 ) );

		if ( $request && ! is_wp_error( $request ) ) {

			Logger::add( 'notice', 'bullhorn-client-id-response', __( 'Bullhorn Client ID Response Received' ) );

			// Strip the response of HTML tags
			$response = strip_tags( $request['body'] );

			// Replace all whitespace with single spaces.
			$response = preg_replace( '!\s+!', ' ', $response );

			// Ready an array to collect results of regex examination.
			$matches = array();

			// Examine response with regex, output results to $matches array
			preg_match( '/.*(\bInvalid Client Id\b).*/i', $response, $matches );

			// If the array has more than zero contents, it found the error that signifies
			// an invalid redirect URI
			if ( count( $matches ) > 0 ) {
				Logger::add( 'error', 'bullhorn-client-id-invalid', 'Bullhorn Client ID is Invalid' );

				return false;
			}
			Logger::add( 'notice', 'bullhorn-client-id-valid', __( 'Bullhorn Client ID Check returned no error. Try again.' ) );

			return true;
		} else {
			Logger::add( 'notice', 'bullhorn-client-id-response', __( 'Bullhorn Client ID Check was unable to connect. Try again.' ) );

			return false;
		}

	}

	/**
	 * Is Redirect URI Invalid
	 *
	 * For security purposes, Matador checks that the domain has a valid
	 * redirect URI. While Bullhorn doesn't require a valid redirect URI
	 * to permit API calls, the workflow of authorizing a site requires an
	 * expert knowledge of both Matador and Bullhorn to ensure credentials
	 * are properly recorded.
	 *
	 * This check attempts a behind-the-scenes call to Bullhorn as if we
	 * were attempting to authorize the site with credentials and the
	 * redirect URI created by Matador. If the result is an error page
	 * with the text "Invalid Redirect URI", the method returns true. If
	 * the method finds credentials are not yet saved and/or the site
	 * operator overrode the redirect URI to null, or if the attempt
	 * returns a log in page, the method returns false. False does not
	 * mean the Redirect URI is valid, only that it is not invalid.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean|null
	 */
	public function is_redirect_uri_invalid() {

		$redirect_uri = Matador::variable( 'api_redirect_uri' );

		if ( ! $redirect_uri ) {

			return null;
		}

		$client       = Matador::credential( 'bullhorn_api_client' );
		$client_valid = Matador::setting( 'bullhorn_api_client_is_valid' );
		$secret       = Matador::credential( 'bullhorn_api_secret' );

		if ( ! $client || ! $client_valid || ! $secret ) {
			new Event_Log( 'bullhorn-is-redirect-valid-missing-settings', __( 'A redirect URI check was indeterminate because of a missing valid Client ID or Client Secret.', 'matador-jobs' ) );

			return null;
		}

		$url = $this->get_data_center() .  'oauth/authorize';

		$params = array(
			'client_id'     => $client,
			'response_type' => 'code',
			'redirect_uri'  => $redirect_uri,
		);

		$request = wp_remote_get( $url . '?' . http_build_query( $params ) );

		if ( $request && ! is_wp_error( $request ) ) {

			// Strip the response of HTML tags
			$response = strip_tags( $request['body'] );

			// Replace all whitespace with single spaces.
			$response = preg_replace( '!\s+!', ' ', $response );

			// Ready an array to collect results of regex examination.
			$matches = array();

			// Examine response with regex, output results to $matches array
			preg_match( '/.*(\bInvalid Client Id\b).*/i', $response, $matches );

			// If the array has more than zero contents, it found the error that signifies
			// an invalid client ID. Return null to be handled as inderminate.
			if ( count( $matches ) > 0 ) {
				new Event_Log( 'bullhorn-is-redirect-valid-bad-client-id', __( 'A redirect URI check yielded a bad Client ID', 'matador-jobs' ) );

				return null;
			}

			// Examine response with regex, output results to $matches array
			preg_match( '/.*(\bInvalid Redirect URI\b).*/', $response, $matches );

			// If the array has more than zero contents, it found the error that signifies
			// an invalid redirect URI. Return true.
			if ( count( $matches ) > 0 ) {
				new Event_Log( 'bullhorn-is-redirect-valid-true', __( 'A redirect URI check yielded a valid Redirect URI.', 'matador-jobs' ) . print_r( $request['body'], true ) );

				return true;
			}
		} else {

			if( is_wp_error( $request ) ){
				new Event_Log( 'bullhorn-is-redirect-valid-wp_error', __( 'A redirect URI check yielded a WP error', 'matador-jobs' ) . print_r( $request, true ) );

			} else {

				new Event_Log( 'bullhorn-is-redirect-valid-null', __( 'A redirect URI check yielded an Error.', 'matador-jobs' ) . print_r( $request, true ) );
			}



			return null;
		}

		// If we made it this far, the redirect must be valid, so return false.
		return false;
	}

	/**
	 * Is Authorized
	 *
	 * Checks if Bullhorn credentials exist. If they do not, we can assume we cannot login.
	 * Does not determine if the credentials are valid.
	 *
	 * @since 3.0.0
	 *
	 * @param (string) $code the Bullhorn provided authorization code.
	 *
	 * @return bool whether we have existing authorization credentials.
	 */
	public function is_authorized() {
		if (
			! empty( $this->credentials )
			&& array_key_exists( 'access_token', $this->credentials )
			&& array_key_exists( 'refresh_token', $this->credentials )
		) {

			return true;
		}

		return false;
	}

	/**
	 * Authorize
	 *
	 * The first step to authorize a Bullhorn App is to request an authorization code.
	 * The authorization process can be done two ways.
	 *
	 * The first way, or the basic authorization, a user may send a request that redirects
	 * them to the Bullhorn Login screen where they must then enter their Bullhorn API
	 * user and password, after which they will be redirected back with an authorization code.
	 * This process must also be used if the API user has not accepted the terms and
	 * conditions.
	 *
	 * A second way, or advanced authorization, allows a user to send a plain-text username
	 * and password over HTTPS and Bullhorn will automatically redirect the user back with
	 * an authorization code.
	 *
	 * Note: the fast way requires an HTTPS site, and to check, we use WordPress's is_ssl()
	 * function. This has been known to not be accurate on load-balanced sites. See the link
	 * on a plugin for sites that are running SSL but load balanced servers are causing
	 * is_ssl() to return false.
	 *
	 * @since 3.0.0
	 * @param bool $advanced whether to attempt an advanced authorization
	 * @throws Exception
	 * @return void
	 */
	public function authorize( $advanced = true ) {

		Logger::add( 'notice', 'bullhorn-authorize-start', esc_html__( 'User initiated an authorization for Bullhorn.', 'matador-jobs' ) );

		// API Action URL
		$url = $this->get_data_center() . 'oauth/authorize';

		$redirect_uri   = Matador::variable( 'api_redirect_uri' );
		$client         = Matador::credential( 'bullhorn_api_client' );
		$secret         = Matador::credential( 'bullhorn_api_secret' );
		$user           = Matador::credential( 'bullhorn_api_user' );
		$pass           = Matador::credential( 'bullhorn_api_pass' );
		$has_authorized = Matador::setting( 'bullhorn_api_has_authorized' );

		if ( $client && $secret ) {

			$params = array(
				'client_id'     => $client,
				'response_type' => 'code',
			);

			// Skilled site operators may choose to use
			// a filter to set redirect URI to null.
			// This is not recommended in production.
			if ( $redirect_uri ) {
				$params['redirect_uri'] = $redirect_uri;
			}

			// An advanced authorization can be prevented by
			// passing the $advanced variable as false.
			// First-time log-ins to Bullhorn require the user to
			// accept terms and conditions, so $has_authorized
			// must be true.
			if ( $user && $pass && $advanced && $has_authorized ) {
				$params['username'] = $user;
				$params['password'] = $pass;
				$params['action']   = 'Login';
			}

			if ( ! isset( $params['action'] ) ) {
				$message = esc_html__( 'A manual authorization is required. User will be redirected.', 'matador-jobs' );
			} else {
				$message = esc_html__( 'A complete authorization is being sent to Bullhorn.', 'matador-jobs' );
			}

			Logger::add( 'notice', 'bullhorn-authorize-send', $message );

			$redirect = $url . '?' . http_build_query( $params );

		} else {

			$error = esc_html__( 'An authorization was attempted with missing or unsaved API credentials. At least Client ID and Client Secret are required.', 'matador-jobs' );
			throw new Exception( 'error', 'bullhorn-authorize-missing-credentials', $error );
		}

		wp_redirect( $redirect );
		die();
	}

	/**
	 * Reauthorize
	 *
	 * Occasionally, long after a site is authorized, especially during downtime at Bullhorn,
	 * a site may lose connection when a refresh_token is consumed but before a new refresh_token
	 * is granted. On unsecure sites and sites that do not provide a username and password in the
	 * settings, to reauthorize a site, user intervention is required. However, on secure sites
	 * that provide API user and passwords, we can attempt an automatic reauthorize.
	 *
	 * This should only be called when an API function fails. This will not run if a previous
	 * authorization does not exist, if the site is not secure, if the site is running in a no
	 * redirect uri mode, or if any of the required settings are not set.
	 *
	 * @since 3.0.0
	 * @throws Exception
	 */
	public function reauthorize() {

		Logger::add( 'notice', 'bullhorn-reauthorize-start', esc_html__( 'System initiated an automatic authorization attempt.', 'matador-jobs' ) );

		$redirect_uri = Matador::variable( 'api_redirect_uri' );
		$client       = Matador::credential( 'bullhorn_api_client' );
		$secret       = Matador::credential( 'bullhorn_api_secret' );
		$user         = Matador::credential( 'bullhorn_api_user' );
		$pass         = Matador::credential( 'bullhorn_api_pass' );

		if ( ! ( $redirect_uri && $client && $secret && $user && $pass && $this->is_authorized() ) ) {
			throw new Exception( 'notice', 'bullhorn-reauthorize-not-allowed', esc_html__( 'System determined settings are incomplete and we cannot support an automatic authorization attempt.', 'matador-jobs' ) );
		}

		$bad_redirect_or_client_id = $this->is_redirect_uri_invalid();

		if ( true === $bad_redirect_or_client_id ) {

			throw new Exception( 'notice', 'bullhorn-reauthorize-not-allowed-redirect-uri', esc_html__( 'System determined the redirect URI is not set at Bullhorn, cannot automatically reauthorize.', 'matador-jobs' ) );
		}

		if ( null === $bad_redirect_or_client_id ) {

			throw new Exception( 'notice', 'bullhorn-reauthorize-not-allowed-client-id', esc_html__( 'System determined the Client ID is invalid, cannot automatically reauthorize.', 'matador-jobs' ) );
		}

		Logger::add( 'notice', 'bullhorn-reauthorize-allowed', esc_html__( 'System determined site is able to support automatic reauthorization attempt.', 'matador-jobs' ) );

		set_transient( Matador::variable( 'bullhorn_auto_reauth', 'transients' ), true, 15 );

		$url = $this->get_data_center() .  'oauth/authorize';

		new Event_Log( 'bullhorn-reauthorize-remote-url', $url );

		$params = array(
			'client_id'     => $client,
			'response_type' => 'code',
			'redirect_uri'  => $redirect_uri,
			'username'      => $user,
			'password'      => $pass,
			'action'        => 'Login',
		);

		$request_args = array(
			'timeout'   => 10,
			// Because many sites are now using self-signed SSL, which doesn't verify
			// as easily, and given the nature of this action doing a multi-redirect loop
			// back to the host, we may want to disable SSL verify on this call.
			'sslverify' => apply_filters( 'matador_reauthorize_verify_ssl', true ),
		);

		$request = wp_remote_get( $url . '?' . http_build_query( $params ), $request_args );

		// Conditions to check for:
		// 1. are we getting stuck on Bullhorn with a bad username, redirect URL
		// 2. are we timing out?
		// 3. are we getting some sort of Bullhorn error?
		// 4. are we getting an error returning to our site? Can the site return an HTTP code instead?

		if ( is_wp_error( $request ) ) {

			// WP Errors on wp_remote_get can occur for a number of reasons but most commonly a timeout. Do not destroy credentials.

			new Event_Log( 'bullhorn-reauthorize-error-wordpress', __( 'Automatic reauthorization failed due to WordPress Remote error. Will retry later, but if this persists, please review your server configuration.', 'matador-jobs' ) );
			new Event_Log( 'bullhorn-reauthorize-error-wordpress-message', $request->get_error_message() );

		} else {

			$code = wp_remote_retrieve_response_code( $request );
			$body = $request['body'];

			// The Authorization Endpoint returns 401 when we get an authorization request but it is not expected
			if ( 401 === $code || 403 === $code ) {
				new Event_Log( 'bullhorn-reauthorize-error-forbidden', __( 'Automatic reauthorization failed due to site rejecting authorization callback. Will retry later, but if this persists, please review your server configuration.', 'matador-jobs' ) );
				return false;
			}

			if ( 200 === $code && str_starts_with( $body, '{' ) ) {

				$response = json_decode( $body );

				if ( $response->code ) {
					new Event_Log( 'bullhorn-reauthorize-code', __( 'Automatic reauthorization code retrieved. Will log in.', 'matador-jobs' ) );

					return $this->request_access_token( $response->code );
				} else {
					new Event_Log( 'bullhorn-reauthorize-code', __( 'Automatic reauthorization error code received from Bullhorn. Error: ', 'matador-jobs' ) . $response->error );
					return false;
				}
			}

			if ( 200 === $code ) {

				// 200 is returned by Bullhorn when a login error occurs.

				// Strip the response of HTML tags
				$response = strip_tags( $request['body'] );

				// Replace all whitespace with single spaces.
				$response = preg_replace( '!\s+!', ' ', $response );

				// Ready an array to collect results of regex examination.
				$matches = array();

				// Examine response with regex, output results to $matches array
				preg_match( '/.*(\bInvalid credentials\b).*/i', $response, $matches );

				// If the array has more than zero contents, it found the string.
				if ( count( $matches ) > 0 ) {

					Matador::setting( 'bullhorn_api_has_authorized', false );

					throw new Exception( 'notice', 'bullhorn-reauthorize-error-invalid-credentials', __( 'Automatic reauthorization failed due to invalid username or password.', 'matador-jobs' ) );
				}

				// Ready an array to collect results of regex examination.
				$matches = array();

				// Examine response with regex, output results to $matches array
				preg_match( '/.*(\bAccept\b).*/i', $response, $matches );

				// If the array has more than zero contents, it found the string.
				if ( count( $matches ) > 0 ) {

					Matador::setting( 'bullhorn_api_has_authorized', false );

					throw new Exception( 'notice', 'bullhorn-reauthorize-error-terms', __( 'Automatic reauthorization failed due to user action required at Bullhorn Login Screen.', 'matador-jobs' ) );
				}
			}

			new Event_Log( 'bullhorn-reauthorize-error-misc', __( 'Automatic reauthorization failed due to unknown reason. Will try again on next scheduled sync.', 'matador-jobs' ) );

			return false;
		}

	}

	/**
	 * Deauthorize
	 *
	 * In the event a site owner wishes to disconnect their site, we'll remove credentials
	 * for them with this function.
	 *
	 * @since 3.0.0
	 */
	public function deauthorize() {
		$this->destroy_credentials();
		Matador::setting( 'bullhorn_api_is_connected', false );
	}

	/**
	 * Get Access Token with Authorization Code
	 *
	 * The second step to authorize a Bullhorn App is to take the authorization code returned
	 * in step one and use it to authorize the site. This function will attempt to authorize the
	 * site, and if it can, will save the received credentials for future use.
	 *
	 * @since 3.0.0
	 *
	 * @param string $code the Bullhorn provided authorization code.
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function request_access_token( $code ) {

		// API Action URL
		$url = $this->get_data_center() .  'oauth/token';

		// Get info we need for this action
		$client       = Matador::credential( 'bullhorn_api_client' );
		$secret       = Matador::credential( 'bullhorn_api_secret' );
		$redirect_uri = Matador::variable( 'api_redirect_uri' );

		// Check if we have what we need
		if ( empty( $client ) || empty( $secret ) || empty( $code ) ) {
			throw new Exception( 'error', 'bullhorn-request-token-missing-credentials', esc_html__( 'Cannot authorize without credentials.', 'matador-jobs' ) );
		}

		// Send the request.
		$params = array(
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'client_id'     => $client,
			'client_secret' => $secret,
		);

		// Request Args
		$args = array(
			'timeout' => 15,
		);

		if ( $redirect_uri ) {
			$params['redirect_uri'] = $redirect_uri;
		}

		$request = wp_remote_post( add_query_arg( $params, $url ), $args );

		// Did the request itself work
		if ( $request && ! is_wp_error( $request ) ) {

			$body = json_decode( $request['body'] );

			// Okay, lets see if the content of the response is what we want
			if ( 200 === $request['response']['code'] && isset( $body->access_token ) ) {

				if ( ! Matador::setting( 'bullhorn_api_has_authorized' ) ) {
					Analytics::event( 'Bullhorn Authorized' );
				}

				Matador::setting( 'bullhorn_api_has_authorized', true );
				Matador::setting( 'bullhorn_api_is_connected', true );
				Matador::setting( 'matador_site_url', Helper::get_domain_md5() );

				$this->update_credentials( (array) $body );

			} elseif ( 400 === $request['response']['code'] ) {
				// Error 400 means we made a 'bad request', when really we provided invalid login
				// credentials. Of course, code 400 is totally and completely the wrong HTTP
				// code to use for a request with invalid credentials. (Should be 401). It means
				// we tried to send a used refresh token or a mismatched redirect URL. In the former,
				// it means we failed to update our state when we got a new code (unlikely) or a prior
				// request failed (very likely) and we didn't handle that request's error.

				$this->destroy_credentials();
				throw new Exception( 'error', 'bullhorn-authorization-bad-request', esc_html( __( 'Bullhorn could not authorize your site due to a bad request: ', 'matador-jobs' ) . $body->error_description ) );
			} elseif ( 500 === $request['response']['code'] ) {
				// Error 500 is new as of early 2022. It can be encountered when the first several digits (before a `:`)
				// do not match a valid "swimlane" aka Server Cluster ID, but could also represent any number of
				// internal errors. Check if a JSON response with a message exists, and serve an exception.

				$error = esc_html__( 'Bullhorn could not authorize your site due to an API Internal Server error.', 'matador-jobs' );

				if ( isset( $body->error_description ) ) {
					$error = substr( $error, 0, -1 ) . ': ' . $body->error_description;
				}

				$this->destroy_credentials();

				throw new Exception( 'error', 'bullhorn-authorization-internal-server-error', $error );
			}
		} else {

			throw new Exception( 'error', 'bullhorn-authorization-timeout', esc_html( __( 'Authorization failed due to a timeout when your site was accessing Bullhorn', 'matador-jobs' ) . print_r( $request, true ) ) );
		}

	}

	/**
	 * Refresh Access Token
	 *
	 * The third step to authorize a Bullhorn app is once per session, the login.
	 * Login requires a valid Access Token. The Bullhorn-provided API Credentials
	 * we got in step two include an access token which expires in 600 seconds.
	 * Login actions after that time limit need to first refresh the token, which
	 * this call does by re-authorizing the app using the refresh token provided
	 * by Bullhorn's most recent authorization.
	 *
	 * @since 3.0.0
	 * @return boolean
	 * @throws Exception
	 */
	private function refresh_access_token() {

		Logger::add( 'info', 'bullhorn-refresh-token-start', esc_html__( 'Starting Bullhorn Refresh Token.', 'matador-jobs' ) );

		// API Action URL
		$url = $this->get_data_center() .  'oauth/token';

		// Get info we need for this action
		$refresh_token = $this->get_credential( 'refresh_token' );
		$client        = Matador::credential( 'bullhorn_api_client' );
		$secret        = Matador::credential( 'bullhorn_api_secret' );

		// Are we missing something we need?

		if ( ! $refresh_token ) {
			// Not possible unless function is called improperly.
			throw new Exception( 'error', 'bullhorn-refresh-token-missing-credentials', esc_html__( 'Refreshing a token requires existing credentials. User intervention required.', 'matador-jobs' ) );
		}

		if ( ! $client ) {
			throw new Exception( 'error', 'bullhorn-refresh-token-missing-credentials', esc_html__( 'Refreshing a token requires API Client ID. User intervention required.', 'matador-jobs' ) );
		}

		if ( ! $secret ) {
			throw new Exception( 'error', 'bullhorn-refresh-token-missing-credentials', esc_html__( 'Refreshing a token requires API Secret. User intervention required.', 'matador-jobs' ) );
		}

		// Send the request
		$params = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refresh_token,
			'client_id'     => $client,
			'client_secret' => $secret,
		);

		$args = array(
			'timeout' => 15,
		);

		$request = wp_remote_post( add_query_arg( $params, $url ), $args );

		// Check if the request worked.
		if ( $request && ! is_wp_error( $request ) ) {
			$response = $request['response'];
			$body     = json_decode( $request['body'] );
		} else {
			throw new Exception( 'error', 'bullhorn-refresh-token-remote-error', esc_html__( 'Request to refresh token was rejected or timed out. Bullhorn may be down. Error: ', 'matador-jobs' ) . $request->get_error_message() );
		}

		$should_reconnect = false;

		// Check if the returned content is as expected.
		if ( 200 === $response['code'] && isset( $body->access_token ) ) {

			$this->update_credentials( (array) $body );
			// Translators: Placeholder for Token ID
			Logger::add( 'info', 'bullhorn-refresh-token-success', esc_html__( 'Bullhorn Refresh Token complete.', 'matador-jobs' ) );

		} elseif ( 400 === $response['code'] ) {
			// Error 400 means we made a 'bad request', when really we provided invalid login
			// credentials. Of course, code 400 is totally and completely the wrong HTTP
			// code to use for a request with invalid credentials. (Should be 401). It means
			// we tried to send a used refresh token. Either we failed to update our state when
			// we got a new one (unlikely) or a prior request failed (very likely) and we didn't
			// handle that request's error.
			//
			// The $body->error_description has error details. (often "Invalid Grant" )

			Logger::add( 'error', 'bullhorn-refresh-token-failed', esc_html__( 'Bullhorn refresh token failed with error: ', 'matador-jobs' ) . $body->error_description );
			$should_reconnect = true;

		} elseif ( 500 === $response['code'] ) {
			// Error 500 was first seen in 2022 when refresh_tokens with invalid "swimlanes" aka cluster IDs were sent
			// in the portion of the

			Logger::add( 'error', 'bullhorn-refresh-token-failed-internal-server-error', esc_html__( 'Bullhorn refresh token failed with error: ', 'matador-jobs' ) . $response['message'] );
			$should_reconnect = true;
		} else {
			Logger::add( 'error', 'bullhorn-refresh-token-failed-other-error', esc_html__( 'Bullhorn refresh token failed with other error: ', 'matador-jobs' ) . $response['message'] . ' ' . sprintf( '( %1$s: %2$s)', __( 'HTTP Code', 'matador-jobs' ), $response['code'] ) );
			$should_reconnect = true;
		}

		if ( $should_reconnect ) {
			// Here we are going to run reauthorize, which attempts a reauthorization request.
			try {

				$this->reauthorize();
				sleep( 1 );

			} catch ( Exception $e ) {

				Logger::add( $e->getLevel(), $e->getName(), $e->getMessage() );
				Admin_Notices::add( __( 'You are disconnected from Bullhorn. We were unable to refresh the token and could not recover the connection.', 'matador-jobs' ), 'error', 'bullhorn-refresh-token-disconnected' );
				Matador::setting( 'bullhorn_api_is_connected', false );
				$this->destroy_credentials();
				return false;
			}
		}

		// In late 2015, the server handling access tokens
		// and the regional servers were not syncing very
		// fast. We wait a half second and to give it
		// a moment to catch up.
		usleep( 500000 );

		return true;
	}

	/**
	 * Login to Bullhorn REST API
	 *
	 * If we've done everything right to this point, we are ready to
	 * log in and begin making calls to the API. The first thing we do
	 * is check our access token. If it is expired, we'll request a new
	 * one.
	 *
	 * @since 3.0.0
	 * @since 3.8.17 Updated TTL from 10 minutes to 5 hours.
	 *
	 * @throws Exception
	 * @return boolean
	 */
	public function login() {

		$transient = get_option( Matador::variable( 'bullhorn_session', 'transients' ), [] );

		if ( ! empty( $transient ) && $transient['expires'] > time() ) {
			$this->session   = $transient['id'];
			$this->url       = $transient['url'];
			$this->logged_in = true;

			// At this point, we have 60 seconds. @todo adapt based on PHP runtime limits?
			return true;
		}

		Logger::add( 'info', 'bullhorn-login-start', esc_html__( 'Logging into Bullhorn.', 'matador-jobs' ) );

		//remove any old admin notices
		Admin_Notices::remove( 'bullhorn-login-exception' );

		// Before we attempt login, check that we have authorized the site.
		// @todo: check if credentials change and remove has_authorized on setting change save
		if ( ! $this->is_authorized() && Matador::setting( 'bullhorn_api_has_authorized' ) ) {
			Logger::add( 'error', 'bullhorn-login-not-authorized', esc_html__( 'Site is not authorized to connect with Bullhorn. User intervention is required.', 'matador-jobs' ) );
			AdminNoticeDisconnectedMessage::message( [ 'error' => esc_html__( 'Site is not authorized to connect with Bullhorn. User intervention is required.', 'matador-jobs' ) ] );

			return false;
		}

		// @todo: if there are no credentials AND we have not authorized, we need to not authorize
		// above is confusing because an authorized site shouldn't attempt login

		// Before we attempt login, we need a refreshed token.
		// Despite what BH documentation may suggest, a token can be used only once.
		if ( ! $this->refresh_access_token() ) {
			throw new Exception( 'error', 'bullhorn-login-error-cant-refresh-token', esc_html__( 'Login failed to Bullhorn, unable to refresh token.', 'matador-jobs' ) );
		}

		// Send the request
		$url = $this->get_data_center( 'rest' ) . 'rest-services/login';

		$params = array(
			'version'      => '*',
			'access_token' => $this->get_credential( 'access_token' ),
			'ttl'          => 20100, // max is 20160 = 336 minutes or 5.6 hours
		);

		$request = wp_remote_get( add_query_arg( $params, $url ), array( 'timeout' => 15 ) );

		// Check if the request worked
		if ( $request && ! is_wp_error( $request ) ) {
			$body = json_decode( $request['body'] ); // can we use wp_json_retrieve_body here?
		} else {
			throw new Exception( 'error', 'bullhorn-login-timeout', esc_html__( 'Login failed due to timeout.', 'matador-jobs' ) );
		}

		// Review response from Bullhorn
		if ( isset( $body->BhRestToken ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$this->session   = $body->BhRestToken; // @codingStandardsIgnoreLine (SnakeCase)
			$this->url       = $body->restUrl; // @codingStandardsIgnoreLine (SnakeCase)

			if ( ! Matador::setting( 'bullhorn_api_cluster_id' ) ) {

				preg_match( '/rest(.*)\.bullhorn/U', $body->restUrl, $matches );

				if ( isset( $matches[1] ) ) {
					Matador::setting( 'bullhorn_api_cluster_id' , $matches[1] );
				}
			}

			$this->get_logged_in_username( true );

			update_option( Matador::variable( 'bullhorn_session', 'transients' ), [
				'id'      => $this->session,
				'url'     => $this->url,
				'expires' => time() + ( 5 * HOUR_IN_SECONDS ),
			] );

			$this->logged_in = true;

			Logger::add( 'info', 'bullhorn-login-success', esc_html__( 'Successfully logged into Bullhorn.', 'matador-jobs' ) );

		} else {

			if ( empty( $body->errorMessage ) ) { // @codingStandardsIgnoreLine (SnakeCase)
				$error = esc_html__( 'Error unknown', 'matador-jobs' );
			} else {
				$error = esc_html( $body->errorMessage ); // @codingStandardsIgnoreLine (SnakeCase)
			}

			throw new Exception( 'error', 'bullhorn-authorization-login-error', esc_html__( 'Login failed to Bullhorn error: ', 'matador-jobs' ) . $error );
		}

		return true;
	}

	/**
	 * API Request
	 *
	 * WHEW! We did it. We are logged into Bullhorn. This function handles our API
	 * calls and wraps wp_remote_request().
	 *
	 * @param string $api_method string Bullhorn API method, default null
	 * @param array $params array of API request parameters
	 * @param string $http_method http verb for request
	 * @param array|object $body data to be sent with request as JSON
	 * @param array $request_args array of arguments for wp_remote_request() function
	 *
	 * @uses wp_remote_request()
	 * @throws Exception
	 * @since 3.0.0
	 * @return bool|object|array of content from API
	 */
	public function request( $api_method = null, $params = array(), $http_method = 'GET', $body = null, $request_args = null ) {

		if ( is_null( $this->url ) ) {
			throw new Exception( 'error', 'bullhorn-request-not-logged-in', esc_html__( 'Bullhorn requests require a logged in instance.', 'matador-jobs' ) );
		}
		if ( is_null( $api_method ) || is_null( $params ) ) {
			throw new Exception( 'error', 'bullhorn-request-no-method', esc_html__( 'Bullhorn requests require a method and not null params array.', 'matador-jobs' ) );
		}
		if ( ! in_array( strtoupper( $http_method ), array( 'GET', 'POST', 'PUT' ), true ) ) {
			throw new Exception( 'error', 'bullhorn-request-invalid-http-method', esc_html__( 'Bullhorn requests require a valid HTTP method.', 'matador-jobs' ) );
		}

		// Translators: Placeholder is for API Method call.
		Logger::add( 'info', 'bullhorn-request-start', sprintf( esc_html__( 'Starting Bullhorn request to endpoint %s.', 'matador-jobs' ), $api_method ) );

		if ( is_array( $body ) || is_object( $body ) ) {
			$body = wp_json_encode( $body );
		}

		$params['BhRestToken'] = $this->session;

		$default_request_args = array(
			'method'      => strtoupper( $http_method ),
			'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'        => $body,
			'data_format' => 'body',
			'timeout'     => 45,
		);

		$args = ! empty( $request_args ) ? array_merge( $default_request_args, $request_args ) : $default_request_args;

		$request_url = add_query_arg( $params, $this->url . $api_method );

		// Translators: placeholder is for the API URL call
		Logger::add( 'info', 'bullhorn-request-url', sprintf( esc_html__( 'Initiating Bullhorn %s Request to URL %s', 'matador-jobs' ), $http_method, $request_url ) );

		$request = wp_remote_request( $request_url, $args );

		// Check if the request worked
		if ( $request && ! is_wp_error( $request ) ) {

			if ( isset( $request['response']['code'] ) && ! in_array( (int) $request['response']['code'], [ 200, 201, 202 ], true )  ) {
				Logger::add( 'info', 'bullhorn-request-bullhorn-error', (int) $request['response']['code'] . ' ' . print_r( $request['body'], true ) );

				// Rarely, we've observed that transients somehow saved without an expiration, likely due to a WordPress or PHP
				// error caused by a non-Matador function at the time of transient save. If this occurs, we need to recover. We
				// find that an error here is a safe place to cover this issue (otherwise, the check is taxing on performance).

				$session_cache = get_option( Matador::variable( 'bullhorn_session', 'transients' ), [] );

				if ( ! empty( $session_cache ) && $session_cache['expires'] > time() ) {
					delete_option( Matador::variable( 'bullhorn_session', 'transients' ) );
				}

				throw new Exception( 'error', 'bullhorn-request-bullhorn-error', esc_html( print_r( $request['body'], true ) ) );
			} else {
				Logger::add( 'info', 'bullhorn-request-success', sprintf( esc_html__( 'Completed Bullhorn request to endpoint %s.', 'matador-jobs' ), $request_url ) );
			}

			// Translators: Placeholder is for API Method call.
			return json_decode( $request['body'] );
		} else {
			throw new Exception( 'error', 'bullhorn-request-timed-out', esc_html__( 'Bullhorn request timed out.', 'matador-jobs' ) );
		}
	}

	/**
	 * Wrapper for submitting files around request().
	 *
	 *
	 * @param string $api_method string Bullhorn API call name
	 * @param array $params array of URL parameters
	 * @param string $http_method verb for request
	 * @param string $file path_to_file
	 *
	 * @return bool|array
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	protected function request_with_payload( $api_method = null, $params = array(), $http_method = 'POST', $file = null ) {

		// Check we got the right stuff
		if ( ! is_string( $api_method ) || ! is_array( $params ) || ! is_string( $http_method ) || ! is_string( $file ) || ! in_array( strtoupper( $http_method ), array( 'POST', 'PUT' ), true ) ) {
			$error_name = 'method-invalid-parameters';
			Logger::add( 'warning', $error_name, esc_html__( 'Method called with invalid parameters.', 'matador-jobs' ) );

			return array( 'error' => $error_name );
		}

		if ( ! is_array( $file ) ) {
			// Check the file exists
			if ( ! file_exists( $file ) ) {
				$error_name = 'file-does-not-exist';
				Logger::add( 'warning', $error_name, esc_html__( 'The file path was not set or invalid.', 'matador-jobs' ) );

				return array( 'error' => $error_name );
			}

			// Get the file type and format.
			list( $ext, $format ) = Helper::get_file_type( $file );

			// Get file contents without requiring get_file_contents or the URL to run wp_remote_get
			$contents = implode( '', file( $file ) );
			// Name to send.
			$name = substr( basename( $file ), 0, strrpos( basename( $file ), '.' ) ) . '.' . $ext;
		} else{
			if ( ! isset( $file['contents'] ) ) {
				$error_name = 'file-content-missing';
				Logger::add( '2', $error_name, esc_html__( 'The content is missing', 'matador-jobs' ) );

				return array( 'error' => $error_name );
			}
			// Get the file type and format.
			list( $ext, $format ) = Helper::get_file_type( $file['file'] );
			$name = substr( basename( $file ), 0, strrpos( basename( $file['file']  ), '.' ) ) . '.' . $ext;
			$contents = $file['contents'];
		}

		// Check the format is allowed.
		if ( ! $ext || ! $format ) {
			$error_name = 'file-format-invalid';
			Logger::add( '2', $error_name, esc_html__( 'The file type was invalid.', 'matador-jobs' ) );

			return array( 'error' => $error_name );
		}

		// Add to the params array file format;
		$params['format'] = $format;

		// Create a boundary. We'll need it as we build the payload.
		$boundary = md5( time() . $ext );

		// End of Line
		$eol = "\r\n";

		// Construct the payload in multipart/form-data format
		$payload  = '';
		$payload .= '--' . $boundary;
		$payload .= $eol;
		$payload .= 'Content-Disposition: form-data; name="submitted_file"; filename="' . $name . '"' . $eol;
		$payload .= 'Content-Type: ' . $format . $eol;
		$payload .= 'Content-Transfer-Encoding: binary' . $eol;
		$payload .= $eol;
		$payload .= $contents;
		$payload .= $eol;
		$payload .= '--' . $boundary . '--';
		$payload .= $eol . $eol;

		// Create args for wp_remote_request
		$args = array(
			'headers' => array(
				'accept'       => 'application/json',
				'content-type' => 'multipart/form-data;boundary=' . $boundary,
			),
		);

		// Call the standard request function and return it.
		return $this->request( $api_method, $params, $http_method, $payload, $args );
	}

    /**
     * Get Consent Object Name
     *
     * Fetches the object name for the Bullhorn Custom Object for GDPR Consent Tracking
     *
     * @since 3.6.0
     *
     * @return string|false $name
     *
     * @throws
     */
	public function get_consent_object_name() {

		$name = get_transient( Matador::variable( 'consent_object', 'transients' ) );

		/**
		 * Bullhorn Candidate Consent Custom Object Name
		 *
		 * Manually set the Bullhorn Candidate Consent Object custom object name. When present, will never result in a
		 * call out to Bullhorn while never relying on easy-to-remove transients to hold the object. Optional.
		 *
		 * @since 3.6.3
		 *
		 * @param string $name Default: false for no transient, 0 for object not found, or name of object as discovered
		 *
		 * @return mixed
		 */
		$name = apply_filters( 'matador-bullhorn-candidate-consent-object-name', $name );

		if ( false === $name ) {

			// create array of customObject1s to customObject10s
			$i = 0;
			$fields = array();
			while ( $i < 10 ) {
				$fields[] = 'customObject' . ++$i . 's';
			}

			// Call the endpoint for the fields
			$meta = $this->request( 'meta/Candidate', array( 'fields' => implode( ',', $fields ) ) );

			// Loop through the fields looking for the object
			foreach ( $meta->fields as $field ) {
				if ( property_exists( $field, 'associatedEntity' ) && property_exists( $field->associatedEntity, 'staticTemplateName' ) && 'consentMgmt' === $field->associatedEntity->staticTemplateName ) {
					set_transient( Matador::variable( 'consent_object', 'transients' ), $field->name );
					return $field->name;
				}
			}

			set_transient( Matador::variable( 'consent_object', 'transients' ), 0, DAY_IN_SECONDS );
			return 0;
		}

		return $name;
	}

	/**
	 * User has Private Candidate Entitlement
	 *
	 * @since 3.6.3
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function has_private_candidate_entitlement() {

		return $this->get_entitlement( 'ALLOW_PRIVATE', 'Candidate' );
	}

	/**
	 * Get Bullhorn User Entitlement for Entity
	 *
	 * @since 3.6.3
	 *
	 * @param string $entitlement
	 * @param string $entity
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function get_entitlement( $entitlement = '', $entity = 'Candidate' ) {

		if ( empty( $entitlement ) || ! is_string( $entitlement ) || empty( $entity ) || ! is_string( $entity ) ) {

			return false;
		}

		$entitlements = get_transient( Matador::variable( 'bullhorn_entitlements', 'transients' ) );

		if ( false === $entitlements || empty( $entitlements[ strtolower( $entity ) ] ) ) {

			// Call the endpoint for the fields
			$entity_entitlements = $this->request( 'entitlements/' . ucfirst( strtolower( $entity ) ) );

			$entity_entitlements = array_values( $entity_entitlements );

			if ( false === $entitlements ) {
				$entitlements = array();
			}

			$entitlements[ strtolower( $entity ) ] = $entity_entitlements;

			set_transient( Matador::variable( 'bullhorn_entitlements', 'transients' ), $entitlements, DAY_IN_SECONDS );

		}

		return in_array( strtoupper( $entitlement ), $entitlements[ strtolower( $entity ) ], true );
	}

	/**
	 * Get the Logged-In User
	 *
	 * Due to the 'Cookie Bug', users can log in with the username associated with their current Bullhorn cookie and not
	 * with the assigned 'API Username' from credentials. This routine, run on the `matador_bullhorn_login_success`
	 * action, will check for the API Username once per 24 hours and save it to a transient. The value from the
	 * transient can be used to perform checks for the cookie bug.
	 *
	 * @since 3.8.0
	 *
	 * @param bool $force
	 *
	 * @return string $username
	 */
	public function get_logged_in_username( $force = false ) {
	// public function get_logged_in_username( bool $force = false ) : string {

		if ( ! $force && get_transient( Matador::variable( 'bullhorn_logged_in_user', 'transients' ) ) ) {

			return get_transient( Matador::variable( 'bullhorn_logged_in_user', 'transients' ) );
		}

		try {

			// First, we need to get the current user integer ID from the current settings

			$request = 'settings/userId';

			$response = $this->request( $request );

			// Second, we need to get the current user text login name from the ID, if found

			$request = 'entity/CorporateUser/' . $response->userId;

			$response = $this->request( $request, [ 'fields' => 'username' ] );

			set_transient( Matador::variable( 'bullhorn_logged_in_user', 'transients' ), $response->data->username, DAY_IN_SECONDS );

			return $response->data->username;

		} catch ( Exception $error ) {}
	}
}
