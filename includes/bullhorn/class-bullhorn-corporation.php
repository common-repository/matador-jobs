<?php
/**
 * Matador / Bullhorn API / Corporation Submission
 *
 * Extends Bullhorn_Connection and submits candidates for jobs.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Bullhorn API
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

use \stdClass;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bullhorn_Corporation
 *
 * @since 3.0.0
 */
class Bullhorn_Corporation extends Bullhorn_Connection {

	/**
	 * Class Constructor
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get (Cached) Companies
	 *
	 * @since 3.7.0
	 *
	 * @return array
	 */
	public static function get_companies() {

		$transient = Matador::variable( 'bullhorn_companies_cache', 'transients' );
		$companies = get_transient( $transient );

		if ( false !== $companies ) {

			return $companies;
		}

		try {
			$bullhorn_corporation = new self();

			$companies = $bullhorn_corporation->get_all_companies();

			/**
			 * Filter : Matador Bullhorn Import Get all Companies
			 *
			 * Modify the imported companies object prior to performing actions on it.
			 *
			 * @since 3.5.1
			 *
			 * @param stdClass $companies
			 *
			 * @return stdClass
			 */
			$companies = apply_filters( 'matador_bullhorn_get_all_companies', $companies );

			set_transient( $transient, $companies, DAY_IN_SECONDS );

			return $companies;

		} catch ( Exception $e ) {

			return [ '-1', __( 'We were unable to fetch companies from Bullhorn. See your logs for more information', 'matador-jobs' ) ];
		}
	}

    /**
     * Get Companies
     *
     * This retrieves all available companies from Bullhorn.
     *
     * @since 3.0.0
     *
     * @param string $fields
     *
     * @return array
     *
     * @throws Exception
     */
    public function get_all_companies( $fields = '' ) {

        $companies   = array();

        while ( true ) {

            // Things we need
            $limit  = 100;
            $offset = isset( $offset ) ? $offset : 0;

            // API Method
            $request = 'query/ClientCorporation';

            // API Method Parameters
            $params = array(
                'fields' => ( empty( $fields ) ) ? 'id,name' : $fields,
                'where'  => 'id>0',
                'count'  => $limit,
                'start'  => $offset,
                'order'  => 'dateLastModified',
            );

            // API Call
            $response = $this->request( $request, $params );

            // Process API Response
            if ( isset( $response->data ) ) {

                // Merge Results Array with Return Array
                $companies = array_merge( $companies, $response->data );

                if ( count( $response->data ) < $limit ) {
                    // If the size of the result is less than the results per page
                    // we got all the companies, so end the loop
                    break;
                } else {
                    // Otherwise, increment the offset by the results per page, and re-run the loop.
                    $offset += $limit;
                }
            } elseif ( is_wp_error( $response ) ) {
                throw new Exception( 'error', 'bullhorn-corporation-request-companies-timeout', esc_html__( 'Operation timed out', 'matador-companies' ) );
            } else {
                break;
            }
        }

        if ( empty( $companies ) ) {
            new Event_Log( 'bullhorn-import-no-found-companies', esc_html__( 'Sync found no eligible companies for import.', 'matador-companies' ) );

            return [];
        } else {
            // Translators: Placeholder is for number of found companies.
            new Event_Log( 'bullhorn-import-found-companies-count', esc_html( sprintf( __( 'Sync found %1$s companies.', 'matador-companies' ), count( $companies ) ) ) );

            return $companies;
        }
    }
}
