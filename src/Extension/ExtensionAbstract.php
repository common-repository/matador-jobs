<?php
/**
 * Matador / Extension / Extension Abstract Class
 *
 * An abstract class to aid in the production and management of Matador Jobs extensions.
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

use \Exception;
use matador\Matador;
use matador\Updater;

/**
 * Abstract Class: Extension
 *
 * @since 3.7.0
 */
abstract class ExtensionAbstract implements ExtensionInterface {

	/**
	 * TEXT DOMAIN
	 *
	 * @since 3.7.0
	 *
	 * @var int
	 */
	const NAME = 'matador-extension-abstract';

	/**
	 * Version
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * ID
	 *
	 * The ID on the MatadorJobs License server for this software item.
	 *
	 * @since 3.7.0
	 *
	 * @var int
	 */
	const ID = 1;

	/**
	 * Path
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	public static $path = '';

	/**
	 * Directory
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	public static $directory = '';

	/**
	 * File
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	public static $file = '';

	/**
	 * Namespace
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	public static $namespace = '';

	/**
	 * Variable Instance
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 *
	 * @var ExtensionAbstract $instance
	 */
	private static $instance;

	/**
	 * Class Constructor
	 *
	 * @access public
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public function __construct() {
		// Silence is Golden
	}

	/**
	 * Throw error on object clone.
	 *
	 * Singleton design pattern means is that there is a single object,
	 * and therefore, we don't want or allow the object to be cloned.
	 *
	 * @access public
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not clone an instance of the plugin.', 'matador-jobs' ), esc_attr( static::VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * Unserializing of the class is also forbidden in the singleton pattern.
	 *
	 * @access public
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not unserialize an instance of the plugin.', 'matador-jobs' ), esc_attr( static::VERSION ) );
	}

	/**
	 * Instance Builder
	 *
	 * Singleton pattern means we create only one instance of the class.
	 *
	 * @access public
	 * @static
	 * @since  1.0.0
	 *
	 * @return ExtensionAbstract
	 */
	public static function instance() {

		$class = get_called_class();

		if ( ! ( isset( self::$instance ) ) && ! ( self::$instance instanceof $class ) ) {

			self::$instance = new $class();

			self::$instance->properties();

			try {
				spl_autoload_register( array( __CLASS__, 'auto_loader' ) );
			} catch ( Exception $error ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'There was an error initializing the Autoloader. Contact the developer.', 'matador-jobs' ), esc_attr( self::VERSION ) );
			}

			self::$instance->initialize();

			/**
			 * Matador Extension [Extension] Initialized
			 *
			 * Action to run immediately after a Matador Extension's Initialization Code Runs
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador-extension-' . static::NAME . '-initialized' );

			add_action( 'plugins_loaded', array( self::$instance, 'textdomain' ) );

			add_action( 'plugins_loaded', array( self::$instance, 'updater' ) );

			add_action( 'plugins_loaded', array( self::$instance, 'load' ) );

			/**
			 * Matador Extension [Extension] Loaded
			 *
			 * Action to run immediately after a Matador Extension's Loading Code Runs (during WordPress's
			 * plugins_loaded action).
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador-extension-' . static::NAME . '-loaded' );
		}

		return self::$instance;
	}

	/**
	 * Properties
	 *
	 * @access private
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	protected function properties() {
		// Translators: Placeholder is the fully-qualified function name.
		die( sprintf( esc_html__( 'Function %s must be overridden in child class.', 'matador-jobs' ), 'matador\MatadorJobs\Extension::properties()' ) );
	}

	/**
	 * Initialize
	 *
	 * Stuff to call when files are initially loaded. Be careful.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	protected static function initialize() {}

	/**
	 * Load
	 *
	 * Stuff to call on the WordPress plugins_loaded action.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function load() {}

	/**
	 * Auto Loader
	 *
	 * @since 3.7.0
	 *
	 * @param string $class
	 *
	 * @return void
	 */
	public static function auto_loader( $class ) {

		$prefix = static::$namespace . '\\';

		$length = strlen( $prefix );

		if ( 0 !== strncmp( $prefix, $class, $length ) ) {

			return;
		}

		if ( strncmp( $prefix, $class, $length ) === 0 ) {
			// get the relative class name
			$relative_class = substr( $class, $length );

			// base directory for the namespace prefix
			$base_dir = static::$directory . 'src/';

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// if the file exists, require it
			if ( file_exists( $file ) ) {
				require $file;
				return;
			}
		}
	}

	/**
	 * Plugin Textdomain
	 *
	 * @access public
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'matador-extension-' . static::NAME, false, static::$path . '/languages' );
	}

	/**
	 * Plugin Updater
	 *
	 * @access public
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public static function updater() {
		new Updater( static::ID, static::$file, static::VERSION );
	}

	/**
	 * Setting
	 *
	 * @access public
	 *
	 * @since 3.7.0
	 *
	 * @param string $setting
	 *
	 * @return mixed $setting
	 */
	public static function setting( $setting ) {
		return Matador::setting( $setting );
	}
}
