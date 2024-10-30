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
use matador\{Event_Log, Matador, Updater};

/**
 * Trait: Extension
 *
 * @since 3.7.0
 */
trait ExtensionTrait {

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
	 * @since 3.7.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Class Constructor
	 *
	 * @since  3.7.0
	 */
	function __construct() {
	}

	/**
	 * Throw error on object clone.
	 *
	 * Singleton design pattern means is that there is a single object,
	 * and therefore, we don't want or allow the object to be cloned.
	 *
	 * @since  3.7.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not clone an instance of the plugin.', 'matador-jobs' ), esc_attr( self::VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * Unserializing of the class is also forbidden in the singleton pattern.
	 *
	 * @since  3.7.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not unserialize an instance of the plugin.', 'matador-jobs' ), esc_attr( self::VERSION ) );
	}

	/**
	 * Instance Builder
	 *
	 * Singleton pattern means we create only one instance of the class.
	 *
	 * @since  3.7.0
	 *
	 * @return object
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {

			$instance = new self();

			$instance->properties();

			try {
				spl_autoload_register( [ $instance, 'auto_loader' ] );
			} catch ( Exception $error ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'There was an error initializing the Autoloader. Contact the developer.', 'matador-jobs' ), esc_attr( self::VERSION ) );
			}

			$instance->initialize();

			/**
			 * Matador Extension [Extension] Initialized
			 *
			 * Action to run immediately after a Matador Extension's Initialization Code Runs
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador-extension-' . self::NAME . '-initialized' );

			add_action( 'plugins_loaded', [ $instance, 'textdomain' ] );

			add_filter( 'matador_locate_template_additional_directories',[ $instance, 'templates' ] );

			add_action( 'plugins_loaded', [ $instance, 'updater' ] );

			add_action( 'plugins_loaded', [ $instance, 'load' ] );

			/**
			 * Matador Extension [Extension] Loaded
			 *
			 * Action to run immediately after a Matador Extension's Loading Code Runs (during WordPress's
			 * plugins_loaded action).
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador-extension-' . self::NAME . '-loaded' );

			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 * Properties
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	protected function properties() : void {
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
	protected static function initialize() : void {}

	/**
	 * Load
	 *
	 * Stuff to call on the WordPress plugins_loaded action.
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public static function load() : void {}

	/**
	 * Auto Loader
	 *
	 * @since 3.7.0
	 *
	 * @param string $class
	 *
	 * @return void
	 */
	public static function auto_loader( string $class ) : void {

		$prefix = self::$namespace . '\\';

		$length = strlen( $prefix );

		if ( 0 !== strncmp( $prefix, $class, $length ) ) {

			return;
		}

		if ( strncmp( $prefix, $class, $length ) === 0 ) {
			// get the relative class name
			$relative_class = substr( $class, $length );

			// base directory for the namespace prefix
			$base_dir = self::$directory . 'src/';

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// if the file exists, require it
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	}

	/**
	 * Templates
	 *
	 * @since 3.7.0
	 *
	 * @param array $folders
	 *
	 * @return array
	 */
	public static function templates( array $folders ) : array {
		if ( file_exists( self::$directory . 'templates' ) ) {
			$folders[] = self::$directory;
		}
		return $folders;
	}

	/**
	 * Textdomain
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public static function textdomain() : void {
		load_plugin_textdomain( 'matador-extension-' . self::NAME, false, self::$directory . 'languages' );
	}

	/**
	 * Updater
	 *
	 * If an implementing class has a class constant ID defined and it is not zero, load the updater class which calls
	 * back to https://matadorjobs.com/ for updates in our Easy Digital Download's based web store.
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public static function updater() : void {

		if ( ! defined( 'self::ID' ) ) {

			return;
		}

		if ( 0 === self::ID ) {

			return;
		}

		new Updater( self::ID, self::$file, self::VERSION );
	}

	/**
	 * Setting
	 *
	 * @since 3.7.0
	 *
	 * @param string $setting
	 *
	 * @return mixed
	 */
	public static function setting( string $setting ) {
		return Matador::setting( $setting );
	}

	/**
	 * Translated Strings
	 *
	 * DO NOT USE.
	 *
	 * The only purpose to this function is to get these commonly used strings in Extension loaders into core so that we
	 * can use the translated strings before the extension's translation files are loaded.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	protected static function __ignore() : void {

		// String: Requires Matador Jobs Pro
		// Translators: the name of the plugin
		$unused1 = __( 'The plugin Matador Jobs Pro - %s Extension requires the Matador Jobs Pro plugin to be installed and active. Either activate the required plugin or deactivate the extension.', 'matador-jobs' );

		// String: Requires Matador Jobs Pro Version
		// Translators: the name of the plugin, the Matador version
		$unused4 = __( 'The plugin Matador Jobs Pro - %s Extension requires the Matador Jobs Pro plugin to be at least version %s. Please update your version of Matador Jobs Pro.', 'matador-jobs' );

		// String: Requires PHP Version
		// Translators: the name of the plugin, the PHP version
		$unused2 = __( 'The plugin Matador Jobs Pro - %s Extension requires PHP Version %s or better. Contact your web host to upgrade so you can use the features offered by this plugin.', 'matador-jobs' );

		// String: Requires PHP Extension
		// Translators: the name of the plugin, the name of the required extension(s)
		$unused3 = __( 'The plugin Matador Jobs Pro - %s Extension requires the following PHP extensions %s. Contact your web host to upgrade so you can use the features offered by this plugin.', 'matador-jobs' );

		die( __( 'You are not supposed to use this function.', 'matador-jobs' ) );
	}
}
