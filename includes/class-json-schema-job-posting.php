<?php
/**
 * Matador / WPSEO_Graph_Piece
 *
 * Manages create the JSON object needed by WPSEO.
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'WPINC' ) ) {
	die;
}

use \WPSEO_Graph_Piece;
use \WPSEO_Schema_Context;
use \WPSEO_Schema_IDs;

/**
 * Class Json_Schema_Job_Posting
 *
 * @package matador
 *
 * @since 3.6.0
 */
class Json_Schema_Job_Posting implements WPSEO_Graph_Piece {

	/**
	 * A value object with context variables.
	 *
	 * @since 3.6.0
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

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

		$data['mainEntityOfPage'] = [ '@id' => $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH ];

		unset( $data['@context'] );

		return $data;
	}
}
