<?php
/**
 * Matador / Utilities / Base64
 *
 * Trait to extend classes that need to handle URL-safe Base64 encoding and decoding.
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

trait Base64 {

	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @since 3.4.0
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function base_64_decode( $input ) {

		$remainder = strlen( $input ) % 4;

		if ( $remainder ) {
			$pad    = 4 - $remainder;
			$input .= str_repeat( '=', $pad );
		}

		return base64_decode( strtr( $input, '-_', '+/' ) );
	}

	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @since 3.4.0
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function base_64_encode( $input ) {

		return str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $input ) );
	}
}
