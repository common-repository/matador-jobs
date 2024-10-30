<?php
/**
 * Matador / REST API / Schema Abstract
 *
 * An abstract class to aid in the creation and maintenance of WP REST API endpoints
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  REST API
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
 * REST API Endpoint Schema Abstract
 *
 * @since 3.8.0
 */
abstract class SchemaAbstract implements SchemaInterface {

	/**
	 * Index
	 *
	 * Schema for GET calls to `/{endpoint}/`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function index() {
	// public static function index() : array {

		return [];
	}

	/**
	 * Create
	 *
	 * Schema for PUT/POST calls to `/{endpoint}/`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function create() {
	// public static function create() : array {

		return [];
	}

	/**
	 * Read
	 *
	 * Schema for GET calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function show() {
	// public static function show() : array {

		return [];
	}

	/**
	 * Update
	 *
	 * Schema for PATCH calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function update() {
	// public static function update() : array {

		return [];
	}

	/**
	 * Destroy
	 *
	 * Schema for DELETE calls to `/{endpoint}/{ID}`
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	public static function destroy() {
	// public static function destroy() : array {

		return [];
	}
}
