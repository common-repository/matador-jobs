<?php
/**
 * Matador / Admin Event Log
 *
 * This powers the Matador Logger
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTimeImmutable;

/**
 * Class Event Log
 *
 * @final
 * @since 3.0.0
 */
final class Event_Log {

	/**
	 * Variable: File
	 *
	 * Stores the file name of the log file
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Variable: Now
	 *
	 * Stores the time now
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $now;

	/**
	 * Variable: Pointer
	 *
	 * Keeps the position in the current file
	 *
	 * @since 3.0.0
	 *
	 * @var resource
	 */
	private $pointer;

	/**
	 * Class Constructor
	 *
	 * Instantiates the class.
	 *
	 * @since 3.0.0
	 *
	 * @param string $code The log code/key.
	 * @param string $message The log message.
	 */
	public function __construct( string $code = '', string $message = '' ) {

		if ( ! $this->is_enabled() ) {

			return;
		}

		if ( ! file_exists( Matador::variable( 'log_file_path' ) ) ) {

			if ( ! wp_mkdir_p( Matador::variable( 'log_file_path' ) ) ) {

				Admin_Notices::add( __( 'Matador did not find a log file directory and was unable to create one. Please add the folder <kbd>wp-content/matador_uploads</kbd> with permissions <kbd>644</kbd>', 'matador-jobs' ), 'error', 'matador-folder-create-failed' );

				return;
			}
		}

		if ( ! file_exists( Matador::variable( 'log_file_path' ) . '/index.php' ) ) {
			touch( Matador::variable( 'log_file_path' ) . '/index.php' ); // @codingStandardsIgnoreLine
		}

		$this->now  = current_time( 'mysql' );
		$this->file = Matador::variable( 'log_file_path' ) . current_time( 'Y-m-d' ) . '-matador-log-' . $this->get_hash() . '.txt';

		if ( '' === $code ) {
			$this->delete_logs();

			return;
		}

		if ( ! file_exists( $this->file ) ) {
			$this->write_header();
		}

		$this->write( $code, $message );
	}

	/**
	 * Write File
	 *
	 * @since 3.0.0
	 *
	 * @param string $code The log code/key.
	 * @param string $message The log message.
	 *
	 * @return void
	 */
	private function write( $code = 'matador-meta-code-not-passed', $message = '' ) {

		$message = empty( $message ) ? __( 'Unknown Error.', 'matador-jobs' ) : $message;

		/**
		 * Action: Matador Event Log Before Write
		 *
		 * Action fires before an Event Log Write is completed.
		 *
		 * @since 3.5.0
		 *
		 * @param string $code
		 * @param string $message
		 */
		do_action( 'matador_event_log_before_write', $code, $message );

		$this->open();

		$line = sprintf( '%1$s: %2$s (%3$s)', $this->now, $message, esc_attr( $code ) ) . "\n";

		fwrite( $this->pointer, $line ); // @codingStandardsIgnoreLine

		$this->close();
	}

	/**
	 * Write File
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function write_header() {

		global $wp_version;

		$block_size = 80;

		$headers = [
			'Matador Jobs Log file created: ' . $this->now,
			'Matador version: ' . Matador::VERSION,
			'WordPress version: ' . $wp_version,
			'PHP version: ' . phpversion(),
		];

		/**
		 * Filter: Matador Log headers
		 *
		 * This filter allows you to add(remove) lines to the log header.
		 *
		 * @since 3.7.5
		 *
		 * @param array $headers one string per line
		 * @returns array
		 */
		$headers = apply_filters( 'matador_event_log_headers', $headers );

		$this->open();

		fwrite( $this->pointer, str_pad( '*', $block_size + 3, '*' ) . "*\n" ); // @codingStandardsIgnoreLine
		fwrite( $this->pointer, '*' . str_pad( '', $block_size + 1 ) . " *\n" ); // @codingStandardsIgnoreLine

		foreach ( $headers as $header ) {
			fwrite( $this->pointer, '* ' . str_pad( $header, $block_size ) . " *\n" ); // @codingStandardsIgnoreLine
		}

		fwrite( $this->pointer, '*' . str_pad( '', $block_size + 1 ) . " *\n" ); // @codingStandardsIgnoreLine
		fwrite( $this->pointer, str_pad( '*', $block_size + 3, '*' ) . "*\n" ); // @codingStandardsIgnoreLine

		$this->close();
	}
	/**
	 * Open File
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function open() {
		$this->pointer = fopen( $this->file, 'a+' ); // @codingStandardsIgnoreLine
	}

	/**
	 * Close File
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function close() {
		fclose( $this->pointer ); // @codingStandardsIgnoreLine
	}

	/**
	 * Is Enabled
	 *
	 * Checks that logging is enabled in the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	private static function is_enabled() {
		return 0 < Matador::setting( 'logging' );
	}

	/**
	 * Get Hash
	 *
	 * Appends a hash to the file names to obfuscate
	 * log file names for security purposes.
	 *
	 * @return string
	 */
	private function get_hash() {

		return md5( Matador::variable( 'log_file_path' ) );
	}

	/**
	 * List Logs
	 *
	 * @since 3.0.0
	 *
	 * @access static
	 *
	 * @return string
	 */
	public static function list_logs() {

		$files    = array_reverse( glob( Matador::variable( 'log_file_path' ) . '*-matador-log*.txt' ) );
		$out      = '';
		$base_url = Matador::variable( 'uploads_base_url' );

		if ( (int) Matador::setting( 'logging' ) < count( $files ) || '-1' === Matador::setting( 'logging' ) ) {
			new Event_Log(); // deletes logs
			// re-call list of file as we have just removed one(some).
			$files = array_reverse( glob( Matador::variable( 'log_file_path' ) . '*-matador-log*.txt' ) );
		}

		if ( empty( $files ) ) {
			return '<br />' . esc_html__( 'No Log files found', 'matador-jobs' );
		}

		foreach ( $files as $file ) {
			$fullpath = explode( wp_normalize_path( DIRECTORY_SEPARATOR ), $file );
			$filename = end( $fullpath );
			$out     .= sprintf( '<br /><a href="%1$s" target="_blank">%2$s</a>', esc_url( $base_url . $filename ), $filename );
		}

		return $out;
	}

	/**
	 * Delete Log Files
	 *
	 * @since 3.0.0
	 */
	public function delete_logs() {

		$files       = glob( Matador::variable( 'log_file_path' ) . '*-matador-log*.txt' );
		$date_offset = (int) Matador::setting( 'logging' );

		if ( 0 === $date_offset ) {

			return;
		}

		foreach ( $files as $file ) {
			$fullpath = explode( DIRECTORY_SEPARATOR, $file );
			$filename = end( $fullpath );
			$filedate = str_replace( '-matador-log.txt', '', str_replace( '-matador-log-' . $this->get_hash() . '.txt', '', $filename ) );

			if ( strtotime( $filedate ) ) {
				try {
					$filedate          = new DateTimeImmutable( $filedate );
					$date_file_created = new DateTimeImmutable( $this->now );
					$age_of_file       = $filedate->diff( $date_file_created );
				} catch ( \Exception $error ) {
					return;
				}

				if ( $date_offset < $age_of_file->days && file_exists( $file ) ) {
					unlink( $file ); // @codingStandardsIgnoreLine
					new Event_Log( 'matador-logger-delete-old-log', __( 'Expired log removed. File: ', 'matador-jobs' ) . $file );
				}
			} else {
				unlink( $file ); // @codingStandardsIgnoreLine
				new Event_Log( 'matador-logger-delete-bad-date-log', __( 'Bad hashed log file removed. File: ', 'matador-jobs' ) . $file );
			}
		}
	}
}
