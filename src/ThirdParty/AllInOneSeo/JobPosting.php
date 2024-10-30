<?php
/**
 * Matador / ThirdParty / All-in-One SEO (SEO Plugin) / Job Posting
 *
 * Generates the Job Posting object for the All-in-One SEO Graph.
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty / All-in-One SEO
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\AllInOneSeo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Helper;
use matador\Job_Listing;
use AIOSEO\Plugin\Common\Schema\Graphs\Graph;

/**
 * Call AllInOneSEO Job Posting Graph
 *
 * @since 3.8.0
 */
class JobPosting extends Graph {

	/**
	 * Returns the graph data.
	 *
	 * @since 3.8.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {

		$data = Helper::object_to_array( json_decode( Job_Listing::get_jsonld( get_the_ID() ) ) );

		unset( $data['@context'] );

		return $data;
	}
}
