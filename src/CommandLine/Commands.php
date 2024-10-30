<?php
/**
 * Matador / CLI / Matador CLI Commands
 *
 * This class holds the Matador Command Line Commands for WP CLI
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

use matador\Bullhorn_Import;
use matador\Exception;
use matador\MatadorJobs\Sync\Sync;
use WP_CLI;

/**
 * Class [CLI] Commands
 *
 * @since 3.8.0
 */
final class Commands {

	/**
	 * Triggers a Bullhorn Sync.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function sync() : void {

		WP_CLI::log( __( 'Starting sync', 'matador-jobs' ) );

		try{
			if ( apply_filters( 'matador_experimental_sync', false ) ) {

				Sync::run();

			} else {
				$bullhorn = new Bullhorn_Import();

				$bullhorn->sync();
			}

		} catch( Exception $e ){

			WP_CLI::log( __( 'Sync errored ', 'matador-jobs' ) . print_r( $e, true ) );

		}

		WP_CLI::log( __( 'Sync finished', 'matador-jobs' ) );
	}
}