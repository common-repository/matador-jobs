<?php
/**
 * Matador / Settings
 *
 * This contains the settings structure and provides functions to manipulate saved settings.
 * This class is extended to create and validate field input on the settings page.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

class Settings {
	/**
	 * Variable: Key
	 *
	 * Stores the options key (name) of the Matador Settings option in WP
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public static $_key;

	/**
	 * Variable: Data
	 *
	 * Holds an array of options in between the class and WP.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	private $_data;

	/**
	 * Magic Method: Constructor
	 *
	 * Class constructor prepares 'key' and 'data' variables.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// @todo TEMPORARY WORK-AROUND
		add_filter( "matador_options_get_applications_sync", function( $value ) {
			if ( "-1" === $value ) {

				return "1";
			}

			return $value;
		} );

		new Settings_Sanitizers();
		new Settings_Validators();
		new Settings_Actions();

		$this->get_key();
		$this->get_data();
	}

	/**
	 * Magic Method: Get
	 *
	 * Magic method gets option from $_data
	 *
	 * @since 3.0.0
	 *
	 * @param string $key the name of the option
	 *
	 * @return mixed value of the option
	 */
	public function __get( $key ) {
		$raw = array_key_exists( $key, $this->_data ) ? $this->_data[ $key ] : null;

		/**
		 * Dynamic Filter: Get Options
		 *
		 * Filter the option after its been pulled out of the database.
		 *
		 * @since 3.0.0
		 */
		return apply_filters( "matador_options_get_$key", $raw );
	}

	/**
	 * Magic Method: Set
	 *
	 * Magic method sets option to $_data
	 *
	 * @since 3.0.0
	 * @since 3.1.0 do_action added
	 *
	 * @param string $key the name of the option
	 * @param mixed $value the value being given for the option
	 *
	 * @return mixed|false $value
	 */
	public function __set( $key, $value ) {

		$field = Settings_Fields::instance()->get_field( $key );

		// Check that Field Exists ( All fields must be added. )
		if ( ! is_array( $field ) ) {
			new Event_Log( 'options-option-not-allowed', __( 'All options must be declared before they can be saved. Option submitted: ', 'matador-jobs' ) . $key );
			return false;
		}

		// Grabs Current Value for Filter
		$current = $this->$key;

		/**
		 * Dynamic Filter: Set Option
		 *
		 * Lets us filter the option before saved to the database.
		 *
		 * @since 3.0.0
		 * @since 3.1.0 added the second parameter, $current
		 */
		$value = apply_filters( "matador_options_before_set_$key", $value, $current, $field );

		/**
		 * Filter: Set Option
		 *
		 * Lets us filter the option before saved to the database.
		 *
		 * @since 3.1.0
		 */
		$value = apply_filters( 'matador_options_before_set', $value, $key, $current, $field );

		// Sanitize
		$value = Settings_Sanitizers::sanitize( $value, $key, $field );

		// Validate
		$value = Settings_Validators::validate( $value, $key, $field );

		// Check if the value should be unset, but allow '0' values
		if ( empty( $value ) && ! is_numeric( $this->$key ) ) {
			unset( $this->$key );
			return false;
		}

		// If the option value is the same, no need to update
		if ( $value === $this->$key ) {
			return false;
		}

		// Set the option.
		$this->_data[ $key ] = $value;

		/**
		 * Dynamic Action: Set Option
		 *
		 * Allows us to perform an action on the new setting.
		 *
		 * @since 3.0.0
		 * @since 3.1.0 renamed action, added the second and third parameters, $key and $current
		 */
		do_action( "matador_options_after_set_$key", $value, $current );

		/**
		 * Action: Set Option
		 *
		 * Lets us to perform an action on the new setting.
		 *
		 * @since 3.1.0
		 */
		do_action( 'matador_options_after_set', $key, $value, $current );

		return $value;
	}

	/**
	 * Magic Method: Isset
	 *
	 * Magic method checks if option exists in $_data
	 *
	 * @since 3.0.0
	 *
	 * @param string $key the name of the option
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return ( array_key_exists( $key, $this->_data ) && isset( $this->_data[ $key ] ) );
	}

	/**
	 * Magic Method: Unset
	 *
	 * Magic method sets option to $_data
	 *
	 * @since 3.0.0
	 *
	 * @param string $key the name of the option
	 *
	 * @return void
	 */
	public function __unset( $key ) {

		$previous = isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : false;

		/**
		 * Action: Before Unset Option
		 *
		 * Lets us perform an action on a soon to be unset setting.
		 *
		 * @param string $key
		 * @param mixed  $previous
		 *
		 * @since 3.8.0
		 */
		do_action( 'matador_options_before_unset', $key, $previous );

		/**
		 * Dynamic Action: Before Unset Option
		 *
		 * Allows us to perform an action on the newly unset setting.
		 *
		 * @param mixed  $previous
		 *
		 * @since 3.8.0
		 */
		do_action( "matador_options_before_unset_$key", $previous );

		unset( $this->_data[ $key ] );

		/**
		 * Dynamic Action: Unset Option
		 *
		 * Allows us to perform an action on the newly unset setting.
		 *
		 * @param mixed  $previous
		 *
		 * @since 3.1.0
		 */
		do_action( "matador_options_after_unset_$key", $previous );

		/**
		 * Action: Unset Option
		 *
		 * Lets us to perform an action on the newly unset setting.
		 *
		 * @param string $key
		 * @param mixed  $previous
		 *
		 * @since 3.1.0
		 */
		do_action( 'matador_options_after_unset', $key, $previous );
	}

	/**
	 * Save $_data to WP Options DB
	 *
	 * @since 3.0.0
	 * @return boolean
	 */
	private function save() {

		if ( update_option( self::$_key, $this->_data ) ) {

			Admin_Notices::add( esc_html__( 'Matador Settings saved.', 'matador-jobs' ), 'success', 'settings-save-completed' );

			do_action( 'matador_options_saved' );

			Matador::instance()->reset();

			return true;
		} else {
			// new Event_Log( 'settings-save-without-change', esc_html__( 'Matador Settings were saved, but no changes were detected.', 'matador-jobs' ) );

			return false;
		}
	}

	/**
	 * Update Options
	 *
	 * As opposed to setting single settings one at a time,
	 * and separately saving, batch update by passing an array.
	 * Will include sanitization and validation.
	 *
	 * @since 3.0.0
	 *
	 * @param array $inputs
	 *
	 * @return bool
	 */
	public function update( $inputs ) {

		delete_transient( Matador::variable( 'settings_fields_errors', 'transients' ) );

		/**
		 * Filter: Filter Inputed Settings
		 *
		 * Filter all the settings after they've been pulled out of the database.
		 *
		 * @since 3.0.0
		 * @since 3.1.0 moved load order
		 */
		$inputs = apply_filters( 'matador_options_pre_set', $inputs );

		// Loop through submitted values
		foreach ( $inputs as $key => $value ) {
			$this->$key = $value;
		}

		return $this->save();
	}

	/**
	 * Field Error
	 *
	 * Saves sanitization, validation, or action errors
	 * to a transient for retrieval on the front-end.
	 * @since 3.1.0
	 *
	 * @param string $key
	 * @param string $error
	 *
	 * @return void
	 */
	public static function field_error( $key = '', $error = '' ) {

		if ( ! $key && ! $error ) {
			return;
		}

		$errors = array();

		$errors[ $key ] = $error;

		$existing = get_transient( Matador::variable( 'settings_fields_errors', 'transients' ) );

		if ( is_array( $existing ) ) {
			$errors = array_merge( $errors, $existing );
		}

		set_transient( Matador::variable( 'settings_fields_errors', 'transients' ), $errors, 30 );
	}

	/**
	 * Get $_key
	 *
	 * Assigns the $_key variable, runs a default through a filter.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private static function get_key() {
		/**
		 * Filter: Options Key
		 *
		 * Very important that the default here and the default in class-matador-variables.php are the same.
		 *
		 * @since 3.0.0
		 */
		self::$_key = apply_filters( 'matador_options_key', 'matador-settings' );
	}

	/**
	 * Get $_data from WP Options DB
	 *
	 * Assigns the $_data variable, runs through default through a filter.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private function get_data() {
		/**
		 * Filter: Get All Options/Settings
		 *
		 * Filter all the settings after they've been pulled out of the database.
		 *
		 * @since 3.0.0
		 */
		$this->_data = apply_filters( 'matador_options_get__all', get_option( self::$_key, array() ) );
	}

	/**
	 * Has Settings
	 *
	 * Determines if Matador has settings. Used during install.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function has_settings() {
		return is_array( $this->_data ) && ! empty( $this->_data );
	}
}
