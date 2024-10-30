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

namespace matador\MatadorJobs\Sync\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;
use matador\Event_Log;
use matador\MatadorJobs\Sync\Sync;

/**
 * Sync Task Abstract Class
 *
 * @since 3.8.0
 */
abstract class Task {

	/**
	 * Name/Key
	 *
	 * This is the key/name/identifier of the Sync Task (for filters, etc).
	 *
	 * @since 3.8.0
	 *
	 * @var string
	 */
	protected static string $name = 'task';

	/**
	 * Instance
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Task Steps
	 *
	 * @since 3.8.0
	 *
	 * @var array
	 */
	protected array $steps = [];

	/**
	 * Task Data
	 *
	 * @since 3.8.0
	 *
	 * @var array
	 */
	protected array $data;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	public function __construct() {
		$this->data();
	}

	/**
	 * Run
	 *
	 * Instantiates the singleton instance of the class, calls sync, returns true if complete, false if it needs to
	 * continue on a subsequent run.
	 *
	 * @since 3.8.0
	 *
	 * @param Sync $sync The instance of the Sync class so we can track the time.
	 *
	 * @return bool
	 */
	public static function run( Sync $sync ): bool {

		if ( ! ( isset( static::$instance ) ) && ! ( static::$instance instanceof static ) ) {

			static::$instance = new static();
		}

		return static::$instance->sync( $sync );
	}

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
	protected function sync( Sync $sync ): bool {

		$should_continue = false;

		$transient_key_sync_data = Matador::variable( 'doing_sync_task_data', 'transients' );
		$transient_key_sync_step = Matador::variable( 'doing_sync_task_step', 'transients' );

		$progress = get_transient( $transient_key_sync_step ) ?: 0;

		if ( 0 === $progress ) {
			$this->reset();
		}

		if ( $progress > 0 ) {
			// Translators: placeholder for the name of the 1: Sync Task Name.
			new Event_Log( 'matador-experimental-sync-task-continue', sprintf( __( 'Continuing a Matador Sync (Experimental) Task: %1$s', 'matador-jobs' ), static::$name ) );
		} else {
			// Translators: placeholder for the name of the 1: Sync Task Name.
			new Event_Log( 'matador-experimental-sync-task-start', sprintf( __( 'Starting Matador Sync (Experimental) Task: %1$s', 'matador-jobs' ), static::$name ) );
		}

		while ( $progress < count( $this->steps ) ) {

			if ( ! $sync->has_execution_time_left() ) {

				// We are out of time, so tell the sync routine to fire off a resume/loopback.
				$should_continue = true;

				break;
			}

			// Translators: placeholder for the name of the 1: Sync Task Name and 2: Sync Subtask Name.
			new Event_Log( 'matador-experimental-task', sprintf( __( 'Running Sync Task "%1$s" SubTask "%2$s"', 'matador-jobs' ), static::$name, $this->steps[ $progress ] ) );

			// Each function should return truthy or false. False if we need to repeat the task.
			if ( method_exists( $this, $this->steps[ $progress ] ) ) {
				$step_complete = $this->{$this->steps[ $progress ]}();
			} elseif ( is_callable( $this->steps[ $progress ] ) ) {
				$step_complete = call_user_func( $this->steps[ $progress ], $this, $sync );
			} else {
				// Translators: placeholder for the name of the 1: Sync Task Name and 2: Sync Subtask Name.
				new Event_Log( 'matador-experimental-task-not-found', sprintf( __( 'Sync Task "%1$s" SubTask "%2$s" cannot run due to missing callable. Will skip.', 'matador-jobs' ), static::$name, $this->steps[ $progress ] ) );
				$step_complete = null;
			}

			/**
			 * @wordpress-filter Matador Sync Task Step Is Complete
			 *
			 * `matador_sync_task_{$task}_step_{$step}_complete`, $task = task name, $step = step name
			 *
			 * @since 3.8.0
			 *
			 * @param bool $step_complete Default is value back from callable/method
			 * @param Task $this The current calling class, instance of matador\MatadorJobs\Sync\Abstracts\Task
			 * @param Sync $sync The current calling parent Sync class, instance of matador\MatadorJobs\Sync\Sync
			 *
			 * @return bool
			 */
			$step_complete = apply_filters( 'matador_sync_task_' . static::$name . '_step_' . $this->steps[ $progress ] . '_complete', $step_complete, $this, $sync );

			set_transient( $transient_key_sync_data, $this->data );

			// If the value of $step_complete is true or 'truthy', increment progress and repeat the loop.
			if ( false !== $step_complete ) {
				set_transient( $transient_key_sync_step, ++$progress, 2 * MINUTE_IN_SECONDS );
			}
		}

		if ( $should_continue ) {

			// False, when returned to the Sync Runner,
			// means we need to continue after it kicks
			// off a continuation.
			return false;
		}

		// If we got this far, this Sync Task is done.
		// Clear its transients and tell the Sync Runner
		// to move on.
		delete_transient( $transient_key_sync_data );
		delete_transient( $transient_key_sync_step );

		// Translators: Placeholder 1 for the name of the completed sync task.
		new Event_Log( 'matador-experimental-task-complete', sprintf( __( 'Completed Matador Sync (Experimental) Task: %1$s', 'matador-jobs' ), static::$name ) );

		return true;
	}

	/**
	 * Data
	 *
	 * An array of data collected by this task routine.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function data(): array {

		$transient = get_transient( Matador::variable( 'doing_sync_task_data', 'transients' ) );

		$this->data = is_array( $transient ) ? $transient : [];

		return $this->data;
	}

	/**
	 * Reset Data
	 *
	 * Resets the data array. Useful if tasks are ever reset, so we don't have runaway data arrays in a loop.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	protected function reset(): void {
		$this->data = [];
	}
}
