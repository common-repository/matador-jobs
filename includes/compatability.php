<?php
/**
 * Matador / Compatability
 *
 * Add Polyfills and other compatability so we can use modern PHP or modern WordPress without requiring it.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.8
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2023 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'array_key_first' ) ) {
	/**
	 * Polyfill for array_key_first() function added in PHP 7.3, WordPress Polyfilled 5.9
	 *
	 * Get the first key of the given array without affecting
	 * the internal array pointer.
	 *
	 * Remove with minimum PHP 7.3, WordPress 5.9
	 * @since 3.8.8
	 *
	 * @author WordPress Team
	 *
	 * @param array $array An array.
	 * @return string|int|null The first key of array if the array
	 *                         is not empty; `null` otherwise.
	 */
	function array_key_first( array $array ) {
		foreach ( $array as $key => $value ) {
			return $key;
		}
	}
}

if ( ! function_exists( 'array_key_last' ) ) {
	/**
	 * Polyfill for `array_key_last()` function added in PHP 7.3, WordPress Polyfilled 5.9
	 *
	 * Get the last key of the given array without affecting the
	 * internal array pointer.
	 *
	 * Remove with minimum PHP 7.3, WordPress 5.9
	 * @since 3.8.8
	 *
	 * @author WordPress Team
	 *
	 * @param array $array An array.
	 * @return string|int|null The last key of array if the array
	 *.                        is not empty; `null` otherwise.
	 */
	function array_key_last( array $array ) {
		if ( empty( $array ) ) {
			return null;
		}

		end( $array );

		return key( $array );
	}
}

if ( ! function_exists( 'str_contains' ) ) {
	/**
	 * Polyfill for `str_contains()` function added in PHP 8.0, WordPress Polyfilled 5.9
	 *
	 * Performs a case-sensitive check indicating if needle is
	 * contained in haystack.
	 *
	 * Remove with minimum PHP 8.0, WordPress 5.9
	 * @since 3.8.8
	 *
	 * @author WordPress Team
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the haystack.
	 * @return bool True if `$needle` is in `$haystack`, otherwise false.
	 */
	function str_contains( $haystack, $needle ) {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( 'str_starts_with' ) ) {
	/**
	 * Polyfill for `str_starts_with()` function added in PHP 8.0, WordPress Polyfilled 5.9
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack begins with needle.
	 *
	 * Remove with minimum PHP 8.0, WordPress 5.9
	 * @since 3.8.8
	 *
	 * @author WordPress Team
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` starts with `$needle`, otherwise false.
	 */
	function str_starts_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}

		return 0 === strpos( $haystack, $needle );
	}
}

if ( ! function_exists( 'str_ends_with' ) ) {
	/**
	 * Polyfill for `str_ends_with()` function added in PHP 8.0, WordPress Polyfilled 5.9
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack ends with needle.
	 *
	 * Remove with minimum PHP 8.0, WordPress 5.9
	 * @since 3.8.8
	 *
	 * @author WordPress Team
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` ends with `$needle`, otherwise false.
	 */
	function str_ends_with( $haystack, $needle ) {
		if ( '' === $haystack && '' !== $needle ) {
			return false;
		}

		$len = strlen( $needle );

		return 0 === substr_compare( $haystack, $needle, -$len, $len );
	}
}