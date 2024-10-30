<?php
/**
 * Matador / ThirdParty / WPML
 *
 * Disables certain core Matador features and integrates with WordPress Multi-Language Plugin.
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty/WPML
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\WPML;

use matador\Matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPML
 *
 * @since 3.8.0
 */
final class WPML {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {
		add_filter( 'wpml_get_translated_slug', array( __CLASS__, 'wpml_get_translated_slug' ), 5, 2 );
	}

	/**
	 * WPML Translate Slug
	 *
	 * Runs on the WPML filter `wpml_get_translated_slug` and modifies the translated URL-safe "slug" based on Matador's
	 * settings.
	 *
	 * @since 3.8.0
	 *
	 * @see https://wpml.org/wpml-hook/wpml_get_translated_slug/
	 * @see https://developer.wordpress.org/reference/functions/sanitize_title/
	 * @see https://developer.wordpress.org/reference/classes/wp_query/
	 *
	 * @param string $slug
	 * @param string $post_type
	 *
	 * @return string
	 */
	public static function wpml_get_translated_slug( $slug, $post_type ) {

		if ( Matador::variable( 'post_type_key_job_listing' ) !== $post_type ) {

			return $slug;
		}

		$setting = Matador::setting( 'post_type_slug_job_listing_each' );

		if ( 'title' === $setting ) {

			return $slug;
		}

		// Query to get post by $slug aka "name"
		$query = array(
			'name'           => $slug,
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields' => 'ids',
		);

		$posts = get_posts( $query );

		if ( ! $posts || is_wp_error( $posts ) ) {

			return $slug;
		}

		$bhid = get_post_meta( $posts[0], 'bullhorn_job_id', true );

		switch ( $setting ) {
			case 'title_id':
				$slug = $slug . ' ' . $bhid;
				break;
			case 'id_title':
				$slug = $bhid . ' ' . $slug;
				break;
			default:
				break;
		}

		return sanitize_title( $slug );
	}
}