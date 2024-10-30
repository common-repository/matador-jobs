<?php
/**
 * Scripts & Styles
 *
 * @package     Matador Jobs
 * @subpackage  Functions
 * @copyright   (c) 2018-2021 Matador Software, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1.0
 */

namespace matador;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scripts {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
	}

	/**
	 * Register Styles
	 *
	 * Checks the styles option and hooks the required filter.
	 *
	 * @since 2.1.0
	 */
	public static function styles() {
		/**
		 * Filter: Matador Enable Styles
		 *
		 * @since 3.7.0
		 *
		 * @param boolean
		 * @return boolean
		 */
		if ( apply_filters( 'matador_enable_styles', true ) && Matador::setting( 'enable_styles' ) ) {
			wp_register_style( 'matador-styles', Matador::$path . 'assets/css/matador-styles'. self::min() . '.css', array(), self::version(), 'all' );
			wp_enqueue_style( 'matador-styles' );
		} elseif ( apply_filters( 'matador_enable_styles', true ) ) {
			wp_register_style( 'matador-basic-style', Matador::$path . 'assets/css/matador-basic-styles'. self::min() . '.css', array(), self::version(), 'all' );
			wp_enqueue_style( 'matador-basic-style' );
		}
	}

	/**
	 * Register Scripts
	 *
	 * Checks the styles option and hooks the required filter.
	 *
	 * @since 1.0.0
	 */
	public static function scripts() {

		if ( is_admin() ) {
			return;
		}

		wp_register_script( 'jquery_validate', Matador::$path . 'assets/scripts/vendor/jquery.validate.min.js', array( 'jquery-core' ), '1.19.5', true );
		wp_register_script( 'jquery_validate_localization', Matador::$path . 'assets/scripts/jquery.validate.localization'. self::min() . '.js', array( 'jquery-core', 'jquery_validate' ), self::version(), true );
		wp_localize_script( 'jquery_validate_localization', 'jquery_validate_localization', self::jquery_validate_l10n() );

		wp_register_script( 'matador_javascript', Matador::$path . 'assets/scripts/matador'. self::min() . '.js', array( 'jquery_validate_localization' ), self::version(), true );
		wp_localize_script( 'matador_javascript', 'matador_javascript_localize', self::matador_javascript_localize() ); // @since 3.8.18
	}

	/**
	 * Admin Styles
	 *
	 * @since 3.0.0
	 */
	public static function admin_styles() {
		wp_register_style( 'matador_admin_styles', Matador::$path . 'assets/css/matador-admin-styles'. self::min() . '.css', array(), self::version() );
		wp_enqueue_style( 'matador_admin_styles' );
	}

	/**
	 * Admin Scripts
	 *
	 * @since 3.0.0
	 */
	public static function admin_scripts() {
		wp_register_script( 'matador_admin_scripts', Matador::$path . 'assets/scripts/matador-admin'. self::min() . '.js', array( 'jquery' ), self::version(), true );
		wp_enqueue_script( 'matador_admin_scripts' );
	}

	/**
	 * Matador Localize
	 *
	 * Localizes the Matador Javascript file.
	 *
	 * @since 3.8.18
	 *
	 * @return array of localized strings.
	 */
	public static function matador_javascript_localize() {
		return [
			'wp_rest_base' => esc_url_raw( rest_url() ),
		];
	}

	/**
	 * Localize jQuery Validate
	 *
	 * Checks the styles option and hooks the required filter.
	 *
	 * @since 3.4.0
	 *
	 * @return array of localized strings.
	 */
	public static function jquery_validate_l10n() {

		$filtered = array();

		// Get the Matador variable "accepted_file_extensions" which is an array of extensions including the leading
		// period. Convert to a format of a pipe-separated string without leading period.

		$extensions = Matador::variable( 'accepted_file_extensions' );

		$extensions = array_map( function( $extension ) {
			// remove the `.` period from the start of each extension.
			return substr( $extension, 1 );
		}, $extensions );

		$extensions = implode( '|', $extensions );

		// Get the Matador variable "accepted_file_size_limit" and convert from MegaBytes to Bytes
		// If larger than the wp_max_upload_size(), use that instead.

		if ( wp_convert_hr_to_bytes( Matador::variable( 'accepted_file_size_limit' ) . 'm' ) <= wp_max_upload_size() ) {
			$max_upload_size = Matador::variable( 'accepted_file_size_limit' );
		} else {
			$max_upload_size = wp_max_upload_size() / MB_IN_BYTES;
		}

		$strings = array(
			'required'       => __( 'This field is required.', 'matador-jobs' ),
			'remote'         => __( 'Please fix this field.', 'matador-jobs' ),
			'email'          => __( 'Please enter a valid email address.', 'matador-jobs' ),
			'url'            => __( 'Please enter a valid URL.', 'matador-jobs' ),
			'date'           => __( 'Please enter a valid date.', 'matador-jobs' ),
			'dateISO'        => __( 'Please enter a valid date (ISO).', 'matador-jobs' ),
			'number'         => __( 'Please enter a valid number.', 'matador-jobs' ),
			'digits'         => __( 'Please enter only digits.', 'matador-jobs' ),
			'equalTo'        => __( 'Please enter the same value again.', 'matador-jobs' ),
			// Translators: {0} is a javascript number placeholder
			'maxlength'      => __( 'Please enter no more than {0} characters.', 'matador-jobs' ),
			// Translators: {0} is a javascript number placeholder
			'minlength'      => __( 'Please enter at least {0} characters.', 'matador-jobs' ),
			// Translators: {0} and {1} are javascript number placeholders
			'rangelength'    => __( 'Please enter a value between {0} and {1} characters long.', 'matador-jobs' ),
			// Translators: {0} and {1} are javascript number placeholders
			'range'          => __( 'Please enter a value between {0} and {1}.', 'matador-jobs' ),
			// Translators: {0} is a javascript number placeholder
			'max'            => __( 'Please enter a value less than or equal to {0}.', 'matador-jobs' ),
			// Translators: {0} is a javascript number placeholder
			'min'            => __( 'Please enter a value greater than or equal to {0}.', 'matador-jobs' ),
			// Translators: {0} is a javascript number placeholder
			'maxsize'        => __( 'Please submit files smaller than {0} megabytes.', 'matador-jobs' ),
			'extension'      => __( 'Please submit a file with a file extension from the list.', 'matador-jobs' ),
			'reCaptcha'      => __( 'Please mark the reCAPTCHA checkbox.', 'matador-jobs' ),
			'maxFileSize'    => $max_upload_size,
			'fileExtensions' => $extensions,
		);

		foreach ( $strings as $key => $value ) {
			/**
			 * Dynamic Filter: Matador Application Validation Error {Error}
			 *
			 * Change the string used in the validation to a custom line.
			 *
			 * @since 3.4.0
			 *
			 * @param string
			 * @return string
			 */
			$filtered[ $key ] = apply_filters( "matador_application_validator_error_{ $key }", $value );
		}

		/**
		 * Filter: Matador Application Validator Errors
		 *
		 * Change the strings used in the validation errors.
		 *
		 * @since 3.7.7
		 *
		 * @param string
		 * @return string
		 */
		return apply_filters( 'matador_application_validator_errors', $filtered );
	}

	/**
	 * Debug?
	 *
	 * @since 3.7.0
	 *
	 * @return bool
	 */
	private static function debug() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

			return true;
		} elseif ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {

			return true;
		} else {

			return false;
		}
	}

	/**
	 * Min?
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public static function min() {

		return self::debug() ? '' : '.min';
	}

	/**
	 * Version
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function version() {
		if ( self::debug() ) {

			return strtotime( 'now' );
		} else {

			return Matador::VERSION;
		}
	}
}
