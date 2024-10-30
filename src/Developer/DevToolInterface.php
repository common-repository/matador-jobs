<?php
/**
 * Matador / Developer / Developer Tool Interface
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.7
 *
 * @package     Matador Jobs Board
 * @subpackage  Developer
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2023, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Developer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface: DevTool
 *
 * @since 3.8.7
 */
interface DevToolInterface {

	/**
	 * Class Constructor
	 *
	 * @since 3.8.7
	 */
	function __construct();

	/**
	 * Register
	 *
	 * @since 3.8.7
	 *
	 * @param array
	 *
	 * @return array
	 */
	public static function register( $tools );
	// public static function register( array $tools ) : array;

	/**
	 * Name
	 *
	 * @since 3.8.7
	 *
	 * @return string
	 */
	public static function name();
	// static function name() : string;

	/**
	 * Menu Name
	 *
	 * @since 3.8.7
	 *
	 * @return string
	 */
	public static function menu_name();
	//public static function menu_name() : string;

	/**
	 * Render
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function render();
	//public static function render() : void;
}
