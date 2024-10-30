<?php
/**
 * Matador / ThirdParty / Yoast (SEO Plugin)
 *
 * Disables certain core Matador features and integrates with Yoast SEO plugin.
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 * @since       3.8.0 Renamed/Moved from includes/class-json-schema-job-posting.php
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\Yoast;

if ( ! defined( 'WPINC' ) ) {
	die;
}

use matador\Matador;
use matador\Job_Listing;
use WPSEO_Schema_Context;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

/**
 * Class Json_Schema_Job_Posting
 *
 * @package matador
 *
 * @since 3.6.0
 */
class JobPosting extends Abstract_Schema_Piece {

	/**
	 * A value object with context variables.
	 *
	 * @since 3.6.0
	 *
	 * @var WPSEO_Schema_Context
	 */
	public $context;

	/**
	 * WPSEO_Schema_Organization constructor.
	 *
	 * @since 3.6.0
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->context = $context;
	}

	/**
	 * Check if Schema is needed
	 *
	 * @since 3.6.0
	 *
	 * @return bool
	 */
	public function is_needed() {

		if ( ! Matador::setting( 'jsonld_enabled' ) ) {

			return false;
		}

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {

			return false;
		}

		return true;
	}

	/**
	 * Generates the Schema Data
	 *
	 * @return array $data The Organization schema.
	 */
	public function generate() {

		$data = (array) json_decode( Job_Listing::get_jsonld( get_the_ID() ) );

		if ( ! function_exists( 'YoastSEO' ) ) {

			return $data;
		}

		$data['mainEntityOfPage'] = [ '@id' => YoastSEO()->meta->for_current_page()->canonical ];

		unset( $data['@context'] );

		return $data;
	}
}
