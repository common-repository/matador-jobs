<?php
/**
 * Matador Logger
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Bullhorn API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

/*
 *
 */
final class Logger {

	/**
	 *
	 * 1 = Info. 2 = Notice 3 = Warning 4 = Error, Tell User, 5 = Critical, Tell Matador
	 *
	 * @param string $level
	 * @param string $code name of log item
	 * @param string $message
	 *
	 * @return bool
	 */
	public static function add( $level, $code = '', $message = 'An Error Occurred.' ) {

		new Event_Log( $code, $message );

		do_action( 'matador_log', $level, $message, $code );
		__return_true();
	}
}
