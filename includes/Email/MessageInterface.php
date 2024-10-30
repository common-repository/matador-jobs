<?php
/**
 * Matador / Email / Message Abstract Class
 *
 * @link        http://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017, Jeremy Scott
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
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function message( array $args = [] );
}
