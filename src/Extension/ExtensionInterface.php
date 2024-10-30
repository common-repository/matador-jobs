<?php
/**
 * Matador / Extension / Extension Interface
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Extension
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class: Extension
 *
 * @since 3.7.0
 */
interface ExtensionInterface {

	/**
	 * Class Constructor
	 *
	 * @since  3.7.0
	 */
	function __construct();

	/**
	 * Throw error on object clone.
	 *
	 * Singleton design pattern means is that there is a single object,
	 * and therefore, we don't want or allow the object to be cloned.
	 *
	 * @since  3.7.0
	 */
	public function __clone();

	/**
	 * Disable unserializing of the class.
	 *
	 * Unserializing of the class is also forbidden in the singleton pattern.
	 *
	 * @since  3.7.0
	 */
	public function __wakeup();

	/**
	 * Instance Builder
	 *
	 * Singleton pattern means we create only one instance of the class.
	 *
	 * @since  3.7.0
	 *
	 * @return object
	 */
	public static function instance();

	/**
	 * Load
	 *
	 * Stuff to call on the WordPress plugins_loaded action.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function load() : void;

	/**
	 * Auto Loader
	 *
	 * @since 3.7.0
	 *
	 * @param string $class
	 *
	 * @return void
	 */
	public static function auto_loader( string $class ) : void;

	/**
	 * Templates
	 *
	 * @since  3.7.0
	 *
	 * @param array $templates
	 *
	 * @return array
	 */
	public static function templates( array $templates ) : array;

	/**
	 * Textdomain
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public static function textdomain() : void;

	/**
	 * Updater
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public static function updater() : void;

	/**
	 * Setting
	 *
	 * @since 3.7.0
	 *
	 * @param string $setting
	 *
	 * @return mixed
	 */
	public static function setting( string $setting );
}
