<?php
/**
 * Matador / Email / Message Interface
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Message Interface
 *
 * @since 3.6.0
 *
 * @package matador\MatadorJobs\Email
 */
interface MessageInterface {

	/**
	 * Message
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function message( array $args = [] );
}
