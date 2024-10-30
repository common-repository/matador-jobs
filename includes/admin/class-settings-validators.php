<?php
/**
 * Matador / Settings
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

final class Settings_Validators {

	/**
	 * Magic Method: Constructor
	 *
	 * Class constructor prepares 'key' and 'data' variables.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_filter( 'matador_options_validate', array( __CLASS__, 'validate_client' ), 10, 2 );
		add_filter( 'matador_options_validate', array( __CLASS__, 'validate_page' ), 10, 3 );
		add_filter( 'matador_options_validate', array( __CLASS__, 'validate_bullhorn_api_assistant' ), 10, 2 );
	}

	/**
	 * Validate
	 *
	 * Split out of sanitize in version 3.1.0. Filters must be added in
	 * constructor or elsewhere in the app.
	 * @since 3.1.0
	 *
	 * @param string|array $value
	 * @param string $key
	 * @param array $field
	 *
	 * @return array
	 */
	public static function validate( $value = '', $key = '', $field = array() ) {

		// We need a $key and $field array
		if ( empty( $key ) && empty( $field ) ) {

			return null;
		}

		$validated = apply_filters( 'matador_options_validate', $value, $key, $field );

		if ( $value !== $validated ) {
			return Settings_Sanitizers::sanitize( $validated, $key, $field );
		}

		return $value;
	}

	/**
	 * Validate Client ID
	 *
	 * When a new Client ID is submitted, make a call to Bullhorn to
	 * check it is valid.
	 *
	 * @since 3.1.0
	 *
	 * @param int $value
	 * @param string $key
	 *
	 * @return bool|string
	 */
	public static function validate_client( $value, $key ) {

		if ( 'bullhorn_api_client' !== $key ) {
			return $value;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		$existing = Matador::$settings->bullhorn_api_client;
		$valid    = Matador::$settings->bullhorn_api_client_is_valid;

		if ( $existing !== $value || ! $valid ) {

			$bullhorn           = new Bullhorn_Connection();
			$is_client_id_valid = $bullhorn->is_client_id_valid( $value );

			if ( $is_client_id_valid ) {
				Matador::$settings->bullhorn_api_client_is_valid = true;
			} else {
				if ( $valid ) {
					Matador::$settings->bullhorn_api_client_is_valid = false;
				}
				$error = __(
					'Your Bullhorn Client ID is invalid. Double check you entered it correctly, and if so, you may need to submit a support ticket to Bullhorn.', 'matador-jobs' );
				Settings::field_error( $key, $error );
			}
		}

		return $value;
	}

	/**
	 * Validate Page Type Inputs
	 *
	 * Checks that "Page" type options are set to actual
	 * pages that both exist as "page" post type and have
	 * status of "publish"/published.
	 * @since 3.1.0
	 *
	 * @param int $value
	 * @param string $key
	 * @param array $field
	 *
	 * @return int
	 */
	public static function validate_page( $value, $key, $field ) {

		// Only Run on 'Page' Type Fields
		if ( ! empty( $field['type'] ) && 'page' !== $field['type'] ) {
			return $value;
		}

		// If the field value is 0 (unset) or -1 (alternate),
		// ignore.
		if ( ( 0 === $value ) || ( -1 === $value ) ) {
			return $value;
		}

		// Try to get a post with the ID.
		$page = get_post( $value );

		// A post exists
		if ( $page && ! is_wp_error( $page ) ) {

			// Check if the post is 'page' post type
			if ( 'page' !== $page->post_type ) {

				Settings::field_error( $key, __( 'The submitted field was not a page.', 'matador-jobs' ) );

				return 0;
			}

			// Check if the 'page' is published
			if ( 'publish' !== $page->post_status ) {

				Settings::field_error( $key, __( 'This page is not published. Publish it first.', 'matador-jobs' ) );

				return -1;
			}
		} else {

			// A post does not exist.
			Settings::field_error( $key, __( 'The page does not exist.', 'matador-jobs' ) );

			return -1;
		}

		// Otherwise return a valid page ID
		return $page->ID;
	}

	/**
	 * Validate Page Type Inputs
	 *
	 * Checks that "Page" type options are set to actual
	 * pages that both exist as "page" post type and have
	 * status of "publish"/published.
	 * @since 3.1.0
	 *
	 * @param int $value
	 * @param string $key
	 *
	 * @return int
	 */
	public static function validate_bullhorn_api_assistant( $value, $key ) {

		if ( 'bullhorn_api_assistant' !== $key ) {
			return $value;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		if ( ! in_array( strtolower( $value ), Bullhorn_Connection_Assistant::get_progress_steps(), true ) ) {
			return 'prepare';
		}

		return strtolower( $value );
	}
}
