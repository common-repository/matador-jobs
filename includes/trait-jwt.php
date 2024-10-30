<?php
/**
 * Matador / Trait / JWT
 *
 * Trait to extend classes that need to make JWT requests.
 *
 * @link        http://matadorjobs.com/
 *
 * @since       3.4.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Traits
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2018, Jeremy Scott, Paul Bearne
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\traits;

use \DomainException;
use \stdClass;

require_once 'trait-json.php';
require_once 'trait-base64.php';

trait JWT {

	use JSON, Base64;

	/**
	 * Accepted Algorithms
	 *
	 * @since  3.4.0
	 *
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $algorithms = array(
		'HS256' => array( 'hash_hmac', 'SHA256' ),
		'HS512' => array( 'hash_hmac', 'SHA512' ),
		'HS384' => array( 'hash_hmac', 'SHA384' ),
		'RS256' => array( 'openssl', 'SHA256' ),
		'RS384' => array( 'openssl', 'SHA384' ),
		'RS512' => array( 'openssl', 'SHA512' ),
	);

	/**
	 * Generate JSON Web Token (JWT)
	 *
	 * @since  3.4.0
	 *
	 * @access private
	 * @static
	 *
	 * @param stdClass|array $payload     A stdClass or array containing the payload. Required.
	 * @param string         $private_key A private key. Required.
	 * @param string         $algorithm   The hashing algorithm to use. Accepts algorithm in self::$algorithms array.
	 *                                    Default 'HS256'. Optional.
	 * @param string         $key_id      The key ID. Default null. Optional.
	 * @param array          $header      The API request headers. Default empty array. Optional.
	 *
	 * @return string JSON Web Token
	 */
	private static function jwt( $payload, $private_key, $algorithm = 'HS256', $key_id = null, $header = array() ) {
		$segments = array();

		$segments['header'] = static::base_64_encode( static::json_encode( static::jwt_header( $algorithm, $key_id, $header ) ) );

		$segments['payload'] = static::base_64_encode( static::json_encode( $payload ) );

		$segments['signature'] = static::base_64_encode( static::jwt_sign( implode( '.', $segments ), $private_key, $algorithm ) );

		return $segments['header'] . '.' . $segments['payload'] . '.' . $segments['signature'];
	}

	/**
	 * Generate JSON Web Token (JWT) Header
	 *
	 * @since  3.4.0
	 *
	 * @access private
	 * @static
	 *
	 * @param string $algorithm         The hashing algorithm to use. Accepts algorithm in self::$algorithms array.
	 *                                  Default 'HS256'. Optional.
	 * @param string $key_id            The key ID. Default null. Optional.
	 * @param array  $header            The API request headers. Default empty array. Optional.
	 *
	 * @return array JSON Web Token header
	 */
	private static function jwt_header( $algorithm = 'HS256', $key_id = null, $header = array() ) {
		$return = array(
			'typ' => 'JWT',
			'alg' => $algorithm,
		);

		if ( null !== $key_id && is_string( $key_id ) ) {
			$return['kid'] = $key_id;
		}

		if ( is_array( $header ) && ! empty( $header ) ) {
			$return = wp_parse_args( $header, $return );
		}

		return $return;
	}

	/**
	 * Sign JSON Web Token (JWT)
	 *
	 * @since  3.4.0
	 *
	 * @access private
	 * @static
	 *
	 * @param string $message     The message to be encoded.
	 * @param string $private_key A private key. Required.
	 * @param string $algorithm   The hashing algorithm to use. Accepts algorithm in self::$algorithms array. Default
	 *                            'HS256'. Optional.
	 *
	 * @return string Signed JSON Web Token
	 */
	private static function jwt_sign( $message, $private_key, $algorithm = 'HS256' ) {

		list( $function, $algorithm ) = static::$algorithms[ $algorithm ];

		switch ( $function ) {
			case 'hash_hmac':
				return hash_hmac( $algorithm, $message, $private_key, true );
			case 'openssl':
				$signature = '';

				$success = openssl_sign( $message, $signature, $private_key, $algorithm );

				if ( ! $success ) {
					throw new DomainException( __( 'OpenSSL unable to sign data', 'matador-jobs' ) );
				} else {
					return $signature;
				}
		}

		return false;
	}
}
