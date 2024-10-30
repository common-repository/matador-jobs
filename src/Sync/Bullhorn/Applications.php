<?php
/**
 * Matador / Sync / Bullhorn / Import
 *
 * Class to handle the import of data from Bullhorn during a sync.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Sync
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Sync\Bullhorn;

use matador\MatadorJobs\Sync\Sync;
use matador\MatadorJobs\Sync\Abstracts\Task as SyncTask;

/**
 * Class Bullhorn Import
 *
 * @since 3.8.0
 */
class Applications extends SyncTask {

	/**
	 * Sync Task Name
	 *
	 * This is the name of the Sync Task (for filter generation in the Abstract routine).
	 *
	 * @since 3.8.0
	 *
	 * @param string
	 */
	protected static string $name = 'bullhorn_applications';

	/**
	 * Source
	 *
	 * This is our default 'source' for Jobs' trait functions.
	 *
	 * @since 3.8.0
	 *
	 * @param string
	 */
	private static string $source = 'bullhorn';

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {

		/**
		 * Sync Task Steps - Bullhorn Applications
		 *
		 * @wordpress-filter
		 *
		 * @since 3.8.0
		 *
		 * @param array $steps An array of class method names, filters to be  as strings (reserved), callables, or action names as
		 *                     strings.
		 * @return array
		 */
		$this->steps = apply_filters( 'matador_sync_task_steps_' . self::$name, [
			'get_unsynced',
			'prepare_sync',
			'sync_initial',
			'sync_retry'
		] );

		parent::__construct();
	}

	public function get_unsynced() {

	}

	public function prepare_sync() {

	}

	public function sync_initial() {

	}

	public function sync_retry() {

	}

}