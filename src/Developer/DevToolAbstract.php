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

use matador\Template_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class DevTool
 *
 * @since 3.8.7
 */
abstract class DevToolAbstract implements DevToolInterface {

	/**
	 * Menu Position
	 *
	 * @var int
	 */
	static public int $menu_position = 10;

	/**
	 * Constructor
	 *
	 * @since 3.8.7
	 */
	function __construct() {

		// Check that the interiting class has the required Class Constant.
		if ( ! defined( 'static::SLUG' ) ) {

			die(
				printf(
					__( 'Classes inheriting from %1$s must have the constant %2$s defined in the child class.', 'matador-jobs' ),
			'`matador\MatadorJobs\Developer\DevTool`',
					'SLUG'
				)
			);
		}

		add_filter( 'matador_admin_developer_tools', [ get_called_class(), 'register' ] );
	}

	/**
	 * Register
	 *
	 * @since 3.8.7
	 *
	 * @param array
	 *
	 * @return array
	 */
	public static function register( $tools ) {
	// public static function register( array $tools ) : array {

		$tools[ static::SLUG ] = [
			'slug'      => static::SLUG,
			'name'      => static::name(),
			'menu_name' => static::menu_name(),
			'class'     => get_called_class(),
			'position'  => static::$menu_position,
		];

		return $tools;
	}

	/**
	 * Name
	 *
	 * @since 3.8.7
	 *
	 * @return string
	 */
	public static function name() {
	// public static function name() : string {

		return ucwords( trim( str_replace( '-', ' ', static::SLUG ) ) );
	}

	/**
	 * Menu Name
	 *
	 * @since 3.8.7
	 *
	 * @return string
	 */
	public static function menu_name() {
	// public static function name() : string {

		return self::name();
	}

	/**
	 * Render
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function render() {
	// public static function render(): void {

		Template_Support::get_template( 'developer-' . static::SLUG . '.php', [], 'admin', '', true );
	}
}