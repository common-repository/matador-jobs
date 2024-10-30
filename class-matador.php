<?php
/**
 * Matador Jobs Plugin
 *
 * The one class that powers the plugin and makes it extendable.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2016-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Matador
 *
 * The one and only Matador.
 *
 * @since 3.0.0
 *
 * @access public
 * @final
 */
final class Matador {

	/**
	 * Constant: Matador Version
	 *
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @access public
	 * @constant
	 *
	 * @var string
	 */
	const VERSION = '3.8.21';

	/**
	 * Constant: ID
	 *
	 * Used to identify the remote ID for updates.
	 *
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @access public
	 * @constant
	 *
	 * @var int
	 */
	const ID = 10;

	/**
	 * Constant: LICENSES_HOST
	 *
	 * Used to identify the remote URL for updates and licenses (and info, if you're reading this).
	 *
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @access public
	 * @constant
	 *
	 * @var string
	 */
	const LICENSES_HOST = 'https://matadorjobs.com';

	/**
	 * Variable Path
	 *
	 * @access public
	 * @static
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @var string $path
	 */
	public static $path;

	/**
	 * Variable Directory
	 *
	 * @access public
	 * @static
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @var string $directory
	 */
	public static $directory;

	/**
	 * Variable Plugin File
	 *
	 * @access public
	 * @static
	 * @since 3.1.0 (converted from global constant)
	 *
	 * @var string $file
	 */
	public static $file;

	/**
	 * Variable Instance
	 *
	 * @since 3.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Matador instance
	 */
	private static $instance;

	/**
	 * Variable Settings
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @var Settings $settings to store instance of Matador::Settings
	 */
	public static $settings;

	/**
	 * Variable Variables
	 *
	 * @access public
	 * @static
	 *
	 * @var Variables $variables to store an instance of Matador::Variable
	 */
	public static $variables;

	/**
	 * Instance Builder
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Matador
	 */
	public static function instance() {

		if ( ! ( isset( self::$instance ) ) && ! ( self::$instance instanceof Matador ) ) {

			self::$instance = new Matador();

			self::$instance->setup_properties();

			require self::$directory . 'vendor/autoload.php';

			try {
				spl_autoload_register( array( __CLASS__, 'auto_loader' ) );
			} catch ( \Exception $error ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'There was an error initializing the Autoloader. Contact the developer.', 'matador-jobs' ), esc_attr( self::VERSION ) );
			}

			self::$instance->initialize();

			/**
			 * Action: Matador Initialized
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador_initialized' );

			add_action( 'plugins_loaded', array( self::$instance, 'textdomain' ) );

			add_action( 'plugins_loaded', array( self::$instance, 'load' ) );

			/**
			 * Action: Matador Loaded
			 *
			 * @since 3.7.0
			 */
			do_action( 'matador_loaded' );
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Throw error on object clone.
	 *
	 * Singleton design pattern means is that there is a single object,
	 * and therefore, we don't want or allow the object to be cloned.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not clone an instance of the plugin.', 'matador-jobs' ), esc_attr( self::VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * Unserializing of the class is also forbidden in the singleton pattern.
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No can do! You may not unserialize an instance of the plugin.', 'matador-jobs' ), esc_attr( self::VERSION ) );
	}

	/**
	 * Setup Properties
	 *
	 * @since 3.0.0
	 * @since 3.1.0 renamed to reflect that it now sets up properties, not constants
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	private static function setup_properties() {
		self::$directory = plugin_dir_path( __FILE__ );
		self::$file      = self::$directory . 'matador.php';
		self::$path      = plugin_dir_url( self::$file );
	}

	/**
	 * Load Plugin
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function initialize() {
		self::reset();
		new MatadorJobs\Core\Activate();
		new MatadorJobs\Core\Install();
		new MatadorJobs\Core\Update();
		new MatadorJobs\Core\Deactivate();
		new MatadorJobs\Core\Monitor();
		new MatadorJobs\CommandLine\Matador();
	}

	/**
	 * Load Plugin
	 *
	 * @since 3.0.1
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function load() {

		require_once self::$directory . 'includes/compatability.php';

		/**
		 * Filter: Should Load Updater Class
		 *
		 * @since 3.0.1
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_updater', true )
			&& file_exists( self::$directory . 'includes/admin/class-updater.php' ) ) {
			new Updater();
		}

		/**
		 * Filter: Should Load Endpoints Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_endpoints', true ) ) {
			new Endpoints();
		}

		/**
		 * Filter: Should Load Scripts Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_scripts', true ) ) {
			new Scripts();
		}

		/**
		 * Filter: Should Load Shortcodes Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_shortcodes', true ) ) {
			new Shortcodes();
		}

		/**
		 * Filter: Should Load Scheduled Events Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_scheduled_events', true ) ) {
			new Scheduled_Events();
		}

		/**
		 * Filter: Should Load Job Taxonomies Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_job_taxonomies', true ) ) {
			new Job_Taxonomies();
		}

		/**
		 * Filter: Should Load Job Listings Post Type Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_job_listing', true ) ) {
			new Job_Listing();
		}

		/**
		 * Filter: Should Load Application Class
		 *
		 * @since 3.0.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_application', true )
			&& '1' === self::setting( 'applications_accept' ) ) {
			new Application();
		}

		/**
		 * Filter: Should Load Honeypot Class
		 *
		 * @since 3.3.0
		 * @param bool
		 */
		if ( apply_filters( 'matador_load_class_honeypot', true ) ) {
			new Honeypot();
		}

		/**
		 * Filter: Should Load Google Indexing API Class
		 *
		 * @since 3.4.0
		 * @param bool
		 */
		if (
			apply_filters( 'matador_load_module_google_indexing', true ) &&
			file_exists( self::$directory . 'includes/modules/class-google-indexing-module.php' )
		) {
			new Google_Indexing_Module();
		}

		/**
		 * Filter: Should Load Campaign Tracking Class
		 *
		 * @since 3.5.0
		 * @param bool
		 */
		if (
			apply_filters( 'matador_load_module_campaign_tracking', true ) &&
			file_exists( self::$directory . 'includes/modules/class-campaign-tracking.php' ) &&
			version_compare( PHP_VERSION, '5.6', '>' )
		) {
			new Campaign_Tracking();
		}

		if ( is_admin() ) {
			new Admin_Tasks();
			new Options();
			new Bullhorn_Connection_Assistant();
			new Admin_Notices();
			new Upgrade();
			new MatadorJobs\Developer\DeveloperTools();
			// Deprecated 3.8.7
			new MatadorJobs\Developer\BullhornAPIDebugger();
		}

		new MatadorJobs\Core\Analytics();
		new MatadorJobs\Rest\Api();

		new MatadorJobs\ThirdParty\Akismet\Akismet();
		new MatadorJobs\ThirdParty\WPML\WPML();
		new MatadorJobs\ThirdParty\AllInOneSeo\AllInOneSeo();
		new MatadorJobs\ThirdParty\RankMath\RankMath();
		new MatadorJobs\ThirdParty\Yoast\Yoast();



		require_once self::$directory . 'includes/template-functions.php';
		require_once self::$directory . 'templates/template-actions-filters.php';

		if ( file_exists( self::$directory . 'includes/deprecated.php' ) ) {
			require_once self::$directory . 'includes/deprecated.php';
		}
	}

	/**
	 * Textdomain
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function textdomain() {
		load_plugin_textdomain( 'matador-jobs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Class Autoloader
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param $class
	 *
	 * @return void
	 */
	public static function auto_loader( $class ) {

		// New PSR-4 Autoloader Scheme

		$prefix = 'matador\\MatadorJobs\\';
		$len    = strlen( $prefix );

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) === 0 ) {
			// get the relative class name
			$relative_class = substr( $class, $len );

			// base directory for the namespace prefix
			$base_dir = self::$directory . 'src/';

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

		$prefix = 'matador\\';
		$len    = strlen( $prefix );

		if ( 0 === strncmp( $prefix, $class, $len ) ) {

			// Old Matador Jobs 3.0.0 Autoloader (until phased out)
			$class   = lcfirst( str_replace( '\\', '', str_replace( __NAMESPACE__, '', $class ) ) );
			$classes = preg_split( '#([A-Z][^A-Z]*)#', $class, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

			if ( 1 <= count( $classes ) ) {
				$class = implode( '-', $classes );
			}
			$class = str_replace( '_-', '-', $class );

			$file_name = strtolower( $class ) . '.php';
			$root_path = self::$directory . 'includes/';

			$folders_used = array(
				'admin/',
				'modules/',
				'bullhorn/',
				'plugin-support/',
				'theme-support/',
				'', // root of includes
			);

			foreach ( $folders_used as $folder ) {
				if ( file_exists( $root_path . $folder . 'class-' . $file_name ) ) {
					include $root_path . $folder . 'class-' . $file_name;

					return;
				}
			}
		}
	}

	/**
	 * Is Pro?
	 *
	 * Simple function to check if the user is using Matador Jobs Pro or not.
	 *
	 * @since 3.3.5
	 *
	 * @access public
	 * @static
	 *
	 * @return bool
	 */
	public static function is_pro() {
		if ( file_exists( self::$directory . '/matador.php' ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Fetch Variable Value
	 *
	 * @since 3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param string $key
	 * @param string $group (optional)
	 *
	 * @return mixed
	 */
	public static function variable( $key, $group = '' ) {
		if ( ! self::$variables ) {
			self::$variables = new Variables();
		}
		if ( $group && self::variable( $group ) ) {
			$group = self::$variables->$group;
			if ( array_key_exists( $key, $group ) ) {

				return $group[ $key ];
			}
		} else {

			return wp_unslash( self::$variables->$key );
		}

		return false;
	}

	/**
	 * Fetch Setting Value
	 *
	 * @since 3.0.0
	 * @since 3.0.4 setter argument was added
	 *
	 * @access public
	 * @static
	 *
	 * @param string $key
	 * @param mixed $new (optional)
	 *
	 * @return mixed
	 */
	public static function setting( $key, $new = null ) {
		if ( ! is_null( $new ) ) {
			self::$settings->update( array( $key => $new ) );
		}

		return wp_unslash( self::$settings->$key );
	}

	/**
	 * Fetch Credential
	 *
	 * A credential can be overridden in the wp-config file by prepending 'matador_' to the key.
	 *
	 * @since 3.7.0
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function credential( $key ) {

		if ( ! $key && ! is_string( $key ) ) {

			return null;
		}

		if ( defined( strtoupper( 'matador_' . $key ) ) ) {

			return constant( strtoupper( 'matador_' . $key ) );
		}

		return self::setting( $key );
	}

	/**
	 * Refresh Settings & Variables
	 *
	 * @since 3.1.0
	 *
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function reset() {
		self::$settings  = new Settings();
		self::$variables = new Variables();
	}
}
