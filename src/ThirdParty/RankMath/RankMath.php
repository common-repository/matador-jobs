<?php
/**
 * Matador / ThirdParty / RankMath (SEO Plugin)
 *
 * Disables certain core Matador features and integrates with RankMath's SEO plugin.
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Job_Listing;
use matador\Matador;

/**
 * Class RankMath
 *
 * @since 3.8.0
 */
final class RankMath {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {
		add_filter( 'rank_math/json_ld', array( __CLASS__, 'json_ld' ), 99 );
	}

	/**
	 * Attaches JSON+LD to RankMath Graph
	 *
	 * This function is only called if the filter `rank_math/json_ld` is run. If the filter doesn't run, therefore,
	 * RankMath is not present.
	 *
	 * @since 3.6.0
	 * @since 3.8.0 Extracted from matador/Job_Listing into dedicated class
	 *
	 * @param array  $data   The current generated Graph by RankMath
	 *
	 * @return array
	 */
	public static function json_ld( $data ) {

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {
			return $data;
		}

		// Disable Matador JSON+LD rendering.
		remove_filter( 'wp_head', array( 'matador\Job_Listing', 'jsonld' ), 5 );

		// Get Our JSON+LD Job Listing Object
		$jsonld = json_decode( Job_Listing::get_jsonld( get_the_ID() ), true );

		// Fix RankMath handling of boolean type values, which are cast as integer 0 or 1, into literal strings of
		// 'true' or 'false'
		if ( isset( $jsonld['directApply'] ) ) {
			$jsonld['directApply'] = ( $jsonld['directApply'] ) ? 'true' : 'false';
		}

		// Attach our JSON+LD to the RankMath Graph Object
		if ( $jsonld ) {
			$data['JobPosting'] = $jsonld;
		}

		return $data;
	}
}