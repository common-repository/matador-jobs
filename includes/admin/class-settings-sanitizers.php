<?php
/**
 * Matador / Settings / Sanitizer
 *
 * This contains the settings structure and provides functions to manipulate saved settings.
 * This class is extended to create and validate field input on the settings page.
 *
 * @link        https://matadorjobs.com/
 * @since       3.1.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

final class Settings_Sanitizers {

	/**
	 * Magic Method: Constructor
	 *
	 * Class constructor prepares 'key' and 'data' variables.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {}

	/**
	 * Sanitize Fields
	 *
	 * Sanitizes Form Input from submission
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Sanitization and Validation Split Apart
	 *
	 * @param string|array $value
	 * @param string $key
	 * @param array $field
	 *
	 * @return mixed|false
	 */
	public static function sanitize( $value = '', $key = '', $field = array() ) {

		if ( empty( $key ) || empty( $field ) ) {

			return null;
		}

		if ( has_filter( "matador_options_sanitize_field_$key" ) ) {
			/**
			 * Dynamic Filter: Sanitize by Field
			 *
			 * Allows us to perform a sanitize action on a per-field basis.
			 *
			 * @since 3.1.0
			 */
			return apply_filters( "matador_options_sanitize_field_$key", $value );
		}

		if ( array_key_exists( 'sanitize', $field ) && ! empty( $field['sanitize'] ) ) {
			$method = $field['sanitize'];

			// Check for filter and use it.
			if ( has_filter( "matador_options_sanitize_$method" ) ) {
				/**
				 * Filter: Sanitize by Method
				 *
				 * Allows us to perform a sanitize action on the basis of the "method"
				 * or "type" defined in the option instantiation.
				 *
				 * @since 3.0.0
				 * @since 3.1.0 added $field variable to the call
				 */
				return apply_filters( "matador_options_sanitize_$method", $value, $field );
			}

			// Check for class method and use it.
			if ( method_exists( __CLASS__, $method ) ) {
				return self::$method( $value );
			}

			// Fallback to WP method and use it.
			if ( function_exists( $method ) ) {
				return call_user_func( $method, $value );
			}
		}

		$method = $field['type'];

		// Check for filter and use it.
		if ( has_filter( "matador_options_sanitize_$method" ) ) {
			/**
			 * Filter: Sanitize by Method
			 *
			 * Allows us to perform a sanitize action on the basis of the "method"
			 * or "type" defined in the option instantiation.
			 *
			 * @since 3.0.0
			 * @since 3.1.0 added $field variable to the call
			 */
			return apply_filters( "matador_options_sanitize_$method", $value, $field );
		}

		// Check for class method and use it.
		if ( method_exists( __CLASS__, $method ) ) {
			return self::$method( $value, $field );
		}

		return self::text( $value );
	}

	/**
	 * Sanitize Text Field
	 *
	 * Simple wrapper for sanitize_text_field()
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function text( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize Textarea Field
	 *
	 * Simple wrapper for sanitize_textarea_field()
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function textarea( $value ) {
		return sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize Slug Field
	 *
	 * Simple wrapper for sanitize_title() to sanitize
	 * input for use as URL slugs
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function slug( $value ) {
		return sanitize_title( $value );
	}

	/**
	 * Password
	 *
	 * Some fields shouldn't pass through a WordPress bulk sanitize
	 * action like sanitize_text_field. This sanitization only trims
	 * whitespace but leaves the rest. (Good for license keys, API
	 * fields, and passwords).
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function password( $value = null ) {
		return self::trim( $value );
	}

	/**
	 * Just Trim Whitespace
	 *
	 * Some fields shouldn't pass through a WordPress bulk sanitize
	 * action like sanitize_text_field. This sanitization only trims
	 * whitespace but leaves the rest. (Good for license keys, API
	 * fields, and passwords).
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function trim( $value = null ) {
		return trim( $value );
	}

	/**
	 * Email
	 *
	 * Simple wrapper for sanitize_email() to sanitize
	 * input to an email address
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function email( $value = null ) {
		return sanitize_email( $value );
	}

	/**
	 * Email List
	 *
	 * Simple wrapper for sanitize_email() to sanitize
	 * input to an email address
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function email_list( $value = null ) {

		if ( empty( $value ) ) {
			return $value;
		}

		$value = preg_replace( '/\s+/', '', $value );

		$emails = explode( ',', $value );

		if ( count( $emails ) > 1 ) {
			foreach ( $emails as &$email ) {
				$email = self::email( $email );
			}
			return implode( ', ', $emails );
		}

		return self::email( $value );

	}

	/**
	 * Attribute
	 *
	 * Simple wrapper for esc_attr() to sanitize
	 * input to be safe for HTML attributes
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function attribute( $value = null ) {
		return esc_attr( $value );
	}

	/**
	 * Integer
	 *
	 * Simple wrapper for intval() to sanitize
	 * input to an integer.
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function integer( $value = null ) {
		return intval( $value );
	}

	/**
	 * Number List
	 *
	 * Sanitizes a string of comma-separated values into a comma-separated string of absolute value numbers
	 *
	 * @since 3.4.0 (or 1.0.0 of Import by Client Extension)
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function number_list( $value ) {

		$clean_array = array_diff( array_map( 'absint', array_map( 'trim', explode( ',', $value ) ) ), [ 0 ] );

		return implode( ', ', $clean_array );
	}

	/**
	 * Sanitize Page Field
	 *
	 * Sanitizes input for page type. Does not validate
	 * the integer represents a page. Returns a -1 when
	 * input is not an integer greater than 0.
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private static function page( $value ) {
		if ( 0 < (int) $value ) {
			return (int) $value;
		} else {
			return -1;
		}
	}

	/**
	 * Sanitize Radio Field
	 *
	 * Sanitizes input for radio type. Makes sure
	 * the input is one of the allowed options.
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 * @param array $field
	 *
	 * @return string
	 */
	private static function radio( $value, $field = array() ) {

		if ( empty( $field ) || ! is_array( $field ) ) {
			return null;
		}

		if ( array_key_exists( 'options', $field ) && ! empty( $field['options'] ) ) {
			// options array exists, so value should be a key of the array
			if ( array_key_exists( $value, $field['options'] ) ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Sanitize Toggle Field
	 *
	 * Sanitizes input for toggle type.
	 *
	 * @since 3.4.0
	 *
	 * @param string $value
	 * @param array $field
	 *
	 * @return string
	 */
	private static function toggle( $value, $field = array() ) {

		if ( empty( $field ) || ! is_array( $field ) ) {
			return null;
		}

		if ( is_numeric( $value ) && in_array( intval( $value ), array( 0, 1 ), true ) ) {
			return (string) intval( $value );
		}

		return null;
	}

	/**
	 * Sanitize Checkbox Field
	 *
	 * Sanitizes input(s) from a checkbox type. Makes sure
	 * the input(s) is(are) in the allowed options.
	 *
	 * @since 3.1.0
	 *
	 * @param array $value
	 * @param array $field
	 *
	 * @return array
	 */
	private static function checkbox( $value, $field = array() ) {

		if ( empty( $field ) ) {
			return null;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		$values = array();

		foreach ( $value as $selected_value ) {
			// check if the options array exists
			if ( array_key_exists( 'options', $field ) && ! empty( $field['options'] ) ) {
				// options array exists, so value should be a key of the array
				if ( array_key_exists( $selected_value, $field['options'] ) ) {
					$values[] = $selected_value;
				}
			}
		}

		return $values;
	}

	/**
	 * Sanitize Select Field
	 *
	 * Sanitizes input(s) from a select type. Makes sure
	 * the input(s) is(are) in the allowed options.
	 *
	 * @since 3.1.0
	 *
	 * @param string $value
	 * @param array $field
	 *
	 * @return string
	 */
	private static function select( $value, $field = array() ) {

		if ( empty( $field ) ) {
			return null;
		}

		if ( array_key_exists( 'options', $field ) && ! empty( $field['options'] ) ) {
			// options array exists, so value should be a key of the array
			if ( array_key_exists( $value, $field['options'] ) ) {
				return $value;
			}
		}

		return null;
	}

}
