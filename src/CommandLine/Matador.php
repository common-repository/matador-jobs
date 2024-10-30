<?php
/**
 * Matador / CLI / Matador CLI
 *
 * This class will initialize the Matador Command Line Commands for WP CLI
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\CommandLine;

use WP_CLI;

/**
 * Class Matador [CLI]
 *
 * @since 3.8.0
 */
final class Matador {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'cli_init', [ __CLASS__, 'commands' ] );
	}

	/**
	 * Add Command
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function commands() {
	// public static function commands() : void {

		if ( ! class_exists( 'WP_CLI' ) ) {

			return;
		}

		$args = [
			'shortdesc' => __( 'Run tasks and automation for Matador Jobs Pro plugin.', 'matador-jobs' ),
		];

		WP_CLI::add_command( 'matador', 'matador\MatadorJobs\CommandLine\Commands', $args );

	}
}