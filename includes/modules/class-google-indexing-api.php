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
 * @subpackage  Google Indexing API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

use stdClass;
use matador\MatadorJobs\Utilities\Jwt;

class Google_Indexing_Api {

	use Jwt;

	/**
	 * Variable Keys
	 *
	 * @since 3.4.0
	 *
	 * @var stdClass JSON object with keys
	 */
	private $keys;

	/**
	 * Variable Grant
	 *
	 * @since 3.4.0
	 *
	 *
	 * @var array|string Holds the current valid Grant for the Indexing API
	 */
	private $token = null;

	/**
	 * Google Indexing API URL
	 *
	 * @since 3.4.0
	 *
	 *
	 * @var string
	 */
	private static $google_index_url = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

	/**
	 * Constructor
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function __construct() {

		$keys = array();

		$keys['private_key'] = Matador::credential( 'google_indexing_api_key' );

		$keys['client_email'] = Matador::credential( 'google_indexing_api_email' );

		if ( ! empty( $keys ) ) {
			$this->keys = $keys;
		} else {
			$this->keys = false;
		}
	}

	/**
	 * Login to Google Indexing API
	 *
	 * @since 3.4.0
	 *
	 * @param bool $debug
	 *
	 * @return bool|mixed
	 */
	private function login( $debug = false ) {

		$transient = Matador::variable( 'google_indexing_api_grant', 'transients' );

		// don't use transient so test the login
		if ( ! $debug ) {

			// If not in class cache, restore transient cache
			if ( ! $this->token ) {
				$this->token = get_transient( $transient );
			}

			// If in class or transient cache, return cached grant token
			if ( $this->token ) {

				return $this->token;
			}
		}

		// Not cached, so get new grant token
		if ( ! $this->keys ) {

			Logger::add( 'error', 'google-indexing-token-missing-keys', __( 'Unable to connect to Google API Login due to missing keys. Set them in your settings file.', 'matador-jobs' ) );
			if ( $debug ) {
				echo 'Unable to connect to Google API Login due to missing keys' . PHP_EOL;
			}

			return false;
		}

		$json = $this->keys;

		// Create token payload as a JSON string
		$payload = array(
			'iss'   => $json['client_email'],
			'scope' => 'https://www.googleapis.com/auth/indexing',
			'aud'   => 'https://www.googleapis.com/oauth2/v4/token',
			'exp'   => strtotime( 'now + 1 hour' ),
			'iat'   => strtotime( 'now' ),
		);

		$private_key    = $json['private_key'];
		$private_key_id = preg_replace( "/\n/m", '\n', $private_key );

		if ( false === openssl_pkey_get_private( $private_key ) ) {

			Logger::add( 'error', 'google-indexing-bad-key', __( 'The key file is bad', 'matador-jobs' ) );

			return false;
		}


		$assertion = self::jwt( $payload, $private_key, 'RS256', $private_key_id );

		$body = http_build_query( array(
			'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
			'assertion'  => $assertion,
		) );

		if ( $debug ) {

			echo esc_html__( 'Calling Google API with:', 'matador-jobs' ) . PHP_EOL . print_r( $body, true ) . PHP_EOL;
		}

		$response = wp_remote_post( 'https://www.googleapis.com/oauth2/v4/token', array( 'body' => $body ) );

		if ( $debug ) {
			echo esc_html__( 'Got back from Google APIs:', 'matador-jobs' ) . PHP_EOL . print_r( wp_remote_retrieve_body( $response ), true ) . PHP_EOL;
		}

		if ( is_wp_error( $response ) ) {
			Logger::add( 'info', 'google-indexing-token-timeout', __( 'Unable to reach Google API Login servers for new login token. Error: ', 'matador-jobs' ) . $response->get_error_message() );

			if ( $debug ) {

				echo esc_html__( 'Got back an Error with this message:', 'matador-jobs' ) . $response->get_error_message() . PHP_EOL;
			}

			return false;
		}

		$response = self::json_decode( wp_remote_retrieve_body( $response ) );

		if( ! isset( $response->access_token ) ) {
			Logger::add( 'error', 'google-indexing-token-failed no token', __( 'We failed to get a token from the Google API Error: ', 'matador-jobs' ) . print_r( $response, true ) );

			if ( $debug ) {

				echo esc_html__( 'We failed to get a token from the Google API check for an error message in the data returned from Google above.', 'matador-jobs' ) . PHP_EOL;
			}

			return false;
		}

		$token = $response->access_token;

		Logger::add( 'info', 'google-indexing-token-new', __( 'Google API Login request complete. Saved new login token.', 'matador-jobs' ) );

		if ( $debug ) {

			echo esc_html__( 'Google API Login request complete. Saved new login token.', 'matador-jobs' ) . PHP_EOL;
		}

		// Cache to Transient
		set_transient( $transient, $token, MINUTE_IN_SECONDS * 55 );

		// Cache to Class
		$this->token = $token;

		// Return
		return $this->token;
	}

	/**
	 * Report New Resource
	 *
	 * @since 3.4.0
	 *
	 * @param string $url
	 * @param bool $debug
	 *
	 * @return bool
	 */
	public function add( $url, $debug = false ) {

		$token = $this->login( $debug );

		if ( false === $token ) {

			return false;
		}

		$body = self::json_encode( array(
			'url'  => $url,
			'type' => 'URL_UPDATED',
		) );

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => $body,
		);


		if ( $debug ) {

			echo esc_html__( 'Calling Google API with:', 'matador-jobs' ) . PHP_EOL . print_r( $args, true ) . PHP_EOL;
		}

		$response = wp_remote_post( self::$google_index_url, $args );

		if ( $debug ) {
			echo esc_html__( 'Got back from Google APIs:', 'matador-jobs' ) . PHP_EOL . print_r( wp_remote_retrieve_body( $response ), true ) . PHP_EOL;
		}



		if ( is_wp_error( $response ) ) {
			new Event_Log( 'google-indexing-add-request-failed', __( 'Unable to reach Google Indexing API. Error: ', 'matador-jobs' ) . $response->get_error_message() );

			return false;
		} else {
			$body = self::json_decode( wp_remote_retrieve_body( $response ) );

			if ( isset( $body->error ) ) {
				$error = '[' . $body->error->code . '] ' . $body->error->status . ': ' . $body->error->message;
				new Event_Log( 'google-indexing-add-error', __( 'Google Indexing API request failed and responded with an error. Error: ', 'matador-jobs' ) . $error );

				if ( $debug ) {

					echo esc_html__( 'We failed to add the URL to the index. Please look at the error message and check our Doc for fixes for common errors ', 'matador-jobs' ) . PHP_EOL;

					echo '<H1>' . esc_html__( 'DO NOT turn Google Indexing on until you have solved this error!!', 'matador-jobs' ) . '</H1>' . PHP_EOL;
				}

			} else {
				new Event_Log( 'google-indexing-add-successful', __( 'Successfully reported new URL to Google Indexing API.', 'matador-jobs' ) );

				if ( $debug ) {

					echo '<H1>' . esc_html__( 'It worked you can now turn Google Indexing API on :-)', 'matador-jobs' ) . '</H1>' . PHP_EOL;
				}
			}

			return true;
		}
	}

	/**
	 * Report Expired Resource
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function remove( $url ) {

		$token = $this->login();

		if ( false === $token ) {
			return false;
		}

		$body = self::json_encode( array(
			'url'  => $url,
			'type' => 'URL_DELETED',
		) );

		$args = array(
			'body'    => $body,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
		);

		$response = wp_remote_post( self::$google_index_url, $args );

		if ( is_wp_error( $response ) ) {
			new Event_Log( 'google-indexing-remove-request-failed', __( 'Unable to reach Google Indexing API. Error: ', 'matador-jobs' ) . $response->get_error_message() );

			return false;
		} else {
			$body = self::json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $body->error ) ) {
				$error = '[' . $body->error->code . '] ' . $body->error->status . ': ' . $body->error->message;
				new Event_Log( 'google-indexing-remove-error', __( 'Google Indexing API request failed and responded with an error. Error: ', 'matador-jobs' ) . $error );
			} else {
				new Event_Log( 'google-indexing-remove-successful', __( 'Successfully reported expired URL to Google Indexing API.', 'matador-jobs' ) );
			}

			return true;
		}
	}
}
