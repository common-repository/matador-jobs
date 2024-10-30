<?php
/**
 * Matador / REST API / Schema Interface
 *
 * An interface to document the creation and maintenance of WP REST API endpoint schema
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

namespace matador\MatadorJobs\Rest\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Endpoint Schema Interface
 *
 * @since 3.8.0
 */
interface SchemaInterface {

	/**
	 * Index
	 *
	 * Schema for GET calls to `/{endpoint}/`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function index();
	// public static function index() : array;

	/**
	 * Create
	 *
	 * Schema for PUT/POST calls to `/{endpoint}/`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function create();
	// public static function create() : array;

	/**
	 * Read
	 *
	 * Schema for GET calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function show();
	// public static function show() : array;

	/**
	 * Update
	 *
	 * Schema for PATCH calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function update();
	// public static function update() : array;

	/**
	 * Destroy
	 *
	 * Schema for DELETE calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function destroy();
	// public static function destroy() : array;
}
