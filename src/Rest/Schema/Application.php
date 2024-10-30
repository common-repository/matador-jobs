<?php
/**
 * Matador /  REST API / "Sync" Endpoint Schema
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
 * Sync Endpoint Schema
 *
 * @since 3.8.0
 *
 * @final
 */
final class Application extends SchemaAbstract {

	/**
	 * Preload
	 *
	 * Schema for GET calls to `application/preload/`
	 *
	 * @uses https://developer.wordpress.org/reference/functions/sanitize_text_field/
	 * @uses https://developer.wordpress.org/reference/functions/rest_sanitize_boolean/
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 *
	 * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
	 */
	public static function preload() {
	// public static function index() : array {
		return [];
	}
}