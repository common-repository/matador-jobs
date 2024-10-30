<?php
/**
 * Matador / ThirdParty / All-in-One SEO (SEO Plugin)
 *
 * Disables certain core Matador features and integrates with All-in-One SEO's SEO plugin.
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

namespace matador\MatadorJobs\ThirdParty\AllInOneSeo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Matador;

/**
 * Class AllInOneSeo
 *
 * @since 3.8.0
 */
final class AllInOneSeo {

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 */
	function __construct() {
		add_filter( 'aioseo_schema_output', array( __CLASS__, 'aioseo_schema_output' ), 99 );
	}

	/**
	 * Attaches JSON+LD to All in One SEO (AIOSEO) Graph
	 *
	 * @since 3.8.0
	 *
	 * @param array $graphs
	 *
	 * @return array
	 */
	public static function aioseo_schema_output( $graphs ) {

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {
			return $graphs;
		}

		// Disable Matador JSON+LD rendering.
		remove_filter( 'wp_head', array( 'matador\Job_Listing', 'jsonld' ), 5 );

		$graphs[] = ( new JobPosting() )->get();

		return $graphs;
	}
}