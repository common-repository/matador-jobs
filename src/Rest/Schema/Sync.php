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
final class Sync extends SchemaAbstract {

	/**
	 * Index
	 *
	 * Schema for GET calls to `/sync/`
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
	public static function index() {
	// public static function index() : array {
		return [
			'action' => [
				'description' => __( 'Call a specific sync, or leave blank to run next scheduled sync or continuation.', 'matador-jobs' ),
				'type' => 'string',
				'default' => '',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $field ) {
					return in_array( strtolower( $field ), [ '', 'all', 'jobs', 'applications' ], true );
				}
			],
			'cache' => [
				'description' => __( 'Whether to break cache on certain syncs. Default `false`.', 'matador-jobs' ),
				'type' => 'boolean',
				'default' => 'false',
				'sanitize_callback' => 'rest_sanitize_boolean',
			]
		];
	}
}