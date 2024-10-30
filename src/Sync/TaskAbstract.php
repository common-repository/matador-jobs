<?php
/**
 * Matador / Sync / Task Abstract
 *
 * Each "task" in a new sync (since 3.8.0 as beta) routine should have some common properties.
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

namespace matador\MatadorJobs\Sync;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;

/**
 * Sync Task Abstract Class
 *
 * @since 3.8.0
 */
abstract class TaskAbstract {

	/**
	 * Instance
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Tasks/Sub-Tasks
	 *
	 * @since 3.8.0
	 *
	 * @var array
	 */
	protected array $tasks;

	/**
	 * Task Data
	 *
	 * @since 3.8.0
	 *
	 * @param array
	 */
	protected array $data;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {
		$this->data();
	}

	/**
	 * Run
	 *
	 * Instantiates the singleton instance of the class, calls sync, returns bool.
	 *
	 * @param Sync $sync The instance of the Sync class so we can track the time.
	 *
	 * @return bool
	 */
	abstract public static function run( Sync $sync ) : bool;

	/**
	 * Sync
	 *
	 * The task's main sync routine runner. Everything happens here.
	 *
	 * @since 3.8.0
	 *
	 * @param Sync $sync The instance of the Sync class so we can track the time.
	 *
	 * @return bool
	 */
	abstract protected function sync( Sync $sync ) : bool;

	/**
	 * Tasks
	 *
	 * An array of sub-tasks to be run by this task routine, in order. Function should check transient first, or set up
	 * the array if not.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	abstract protected function tasks() : array;

	/**
	 * Data
	 *
	 * An array of data collected by this task routine.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function data() : array {

		$transient = get_transient( Matador::variable( 'doing_sync_task_data', 'transients' ) );

		$this->data = is_array( $transient ) ? $transient : [];

		return $this->data;
	}
}

