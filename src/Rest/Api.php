<?php
/**
 * Matador / Rest / API Instantiator Class
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Rest
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Type Abstract Class
 *
 * @since 3.8.0
 */
final class Api {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	public function __construct() {
		new Endpoint\Sync();
		new Endpoint\Application();
	}
}
