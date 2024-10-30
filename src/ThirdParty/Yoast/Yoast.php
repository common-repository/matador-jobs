<?php
/**
 * Matador / ThirdParty / Yoast (SEO Plugin)
 *
 * Disables certain core Matador features and integrates with the Yoast SEO plugin.
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty/Yoast
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;

/**
 * Class Yoast
 *
 * @since 3.8.0
 */
final class Yoast {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {
		add_filter( 'wpseo_schema_graph_pieces', array( __CLASS__, 'wpseo_schema_graph_pieces' ), 5, 2 );
	}

	/**
	 * Attaches JSON+LD to Yoast Graph
	 *
	 * This function is only called if the filter `wpseo_schema_graph_pieces` is run. If the filter doesn't run,
	 * therefore, WPSEO (Yoast) is not present.
	 *
	 * @since 3.6.0
	 * @since 3.8.0 Extracted from matador/Job_Listing into dedicated class
	 *
	 * @param array  $pieces  The current generated Graph by WPSEO
	 * @param string $context The context of the JSON being provided
	 *
	 * @return array
	 */
	public static function wpseo_schema_graph_pieces( $pieces, $context ) {

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {
			return $pieces;
		}

		// Disable Matador JSON+LD rendering.
		remove_filter( 'wp_head', array( 'matador\Job_Listing', 'jsonld' ), 5 );

		$pieces[] = new JobPosting( $context );

		return $pieces;
	}
}