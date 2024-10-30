<?php
/**
 * Matador / Utilities / JSON
 *
 * Trait to extend classes that handle JSON objects.
 *
 * @link        https://matadorjobs.com/
 *
 * @since       3.4.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Utilities
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Utilities;

use \DomainException;
use \stdClass;

trait Json {

	/**
	 * Encode a PHP object into a JSON string.
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param stdClass|array $input   A PHP object or array
	 * @param int            $options Options to be passed to json_encode(). Default 0. Optional.
	 * @param int            $depth   Maximum depth to walk through $data. Must be greater than 0. Default 512. Optional.
	 *
	 * @return string JSON representation of the PHP object or array
	 *
	 * @throws DomainException Provided object could not be encoded to valid JSON
	 */
	public static function json_encode( $input, $options = 0, $depth = 512 ) {

		return wp_json_encode( $input, $options, $depth );
	}

	/**
	 * Decode a JSON string into a PHP object.
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param string $input   A PHP object or array
	 * @param int    $options Options to be passed to json_encode(). Default 0. Optional.
	 * @param int    $depth   Maximum depth to walk through $data. Must be greater than 0. Default 512. Optional.
	 *
	 * @return stdClass Object representation of JSON string
	 *
	 * @throws DomainException Provided string was invalid JSON
	 */
	public static function json_decode( $input, $options = 0, $depth = 512 ) {

		if (
			version_compare( PHP_VERSION, '5.4.0', '>=' ) &&
			! ( defined( 'JSON_C_VERSION' ) && PHP_INT_SIZE > 4 )
		) {
			/* In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
			 * to specify that large ints (like Steam Transaction IDs) should be treated as
			 * strings, rather than the PHP default behaviour of converting them to floats.
			 */
			$obj = json_decode( $input, $options, $depth, JSON_BIGINT_AS_STRING );
		} else {
			/* Not all servers will support that, however, so for older versions we must
			 * manually detect large ints in the JSON string and quote them (thus converting
			 * them to strings) before decoding, hence the preg_replace() call.
			 */
			$max_int_length       = strlen( (string) PHP_INT_MAX ) - 1;
			$json_without_bigints = preg_replace( '/:\s*(-?\d{' . $max_int_length . ',})/', ': "$1"', $input );
			$obj                  = json_decode( $json_without_bigints );
		}
		if ( function_exists( 'json_last_error' ) && json_last_error() ) {
			static::handle_json_error( json_last_error() );
		} elseif ( null === $obj && 'null' !== $input ) {
			throw new DomainException( 'Null result with non-null input' );
		}

		return $obj;
	}

	/**
	 * Helper method to create a JSON error.
	 *
	 * @param string $error An error from json_last_error()
	 *
	 * @return void
	 */
	private static function handle_json_error( $error = '' ) {

		$messages = array(
			JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
			JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
			JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
			JSON_ERROR_UTF8           => 'Malformed UTF-8 characters', //PHP >= 5.3.3
		);

		throw new DomainException(
			isset( $messages[ $error ] ) ? $messages[ $error ] : 'Unknown JSON error: ' . $error
		);
	}

}
