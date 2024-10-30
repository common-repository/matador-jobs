<?php
/**
 * Matador / Bullhorn API / Lead Entity
 *
 * Extends Bullhorn_Connection and Communicates with the Lead Entity
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \stdClass;

/**
 * Class Bullhorn Lead
 *
 * @since 3.0.0
 */
class Bullhorn_Lead extends Bullhorn_Connection {

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
	 * Create Lead
	 *
	 * @since 3.0.0
	 *
	 * @param array $lead
	 *
	 * @return object|null
	 */
	public function create( $lead = null ) {

		if ( is_null( $this->url ) ) {

			return null;
		}

		$method = 'entity/Lead';

		try {
			// API Request
			$response = $this->request( $method, [], 'PUT', $lead );
		} catch ( Exception $e ) {

			return null;
		}

		if ( is_object( $response ) && isset( $response->changedEntityId ) ) {

			return $response->changedEntityId;
		} else {
			if ( isset( $response->errorMessage ) ) {
				Logger::add( 'error', $response->errorMessageKey, $response->errorMessageKey );
			}
		}

		return null;
	}

	/**
	 * Read Lead
	 *
	 * Gets a Lead from the ID
	 *
	 * @since 3.0.0
	 *
	 * @param int $lead_id
	 *
	 * @return bool|object
	 */
	public function read( $lead_id ) {

		// API Method
		$method = 'entity/Lead/' . $lead_id;

		// API Params
		$params = array(
			'fields' => 'id,name,nickName,firstName,middleName,lastName,address,secondaryAddress,email,email2,email3,mobile,phone,phone2,phone3,description,status,dateLastModified',
		);

		try {
			// API Request
			$response = $this->request( $method, $params, 'GET' );
		} catch ( Exception $e ) {

			return false;
		}

		if ( is_object( $response ) && isset( $response->data ) ) {
			$return       = new stdClass();
			$return->data = $response->data;
		} else {

			return false;
		}

		return $return;
	}

	/**
	 * Update Client Contact
	 *
	 * @todo unused. Finish one day?
	 * @since 3.7.0
	 *
	 * @param stdClass $object
	 *
	 * @param int      $id
	 *
	 * @return bool
	 */
	public function update( $id, $object ) {
		unset( $object );

		return $id;
	}

	/**
	 * Delete Lead
	 *
	 * @todo unused. Finish one day?
	 * @since 3.0.0
	 *
	 * @param int $lead_id
	 *
	 * @return bool
	 */
	public function delete( $lead_id ) {

		unset( $lead_id );

		return false;
	}

	/**
	 * Find
	 *
	 * Looks up submitted email address and last name for matching entries
	 * in the candidates database.
	 *
	 * @since 3.0.0
	 *
	 * @param array $params
	 *
	 * @return integer|boolean
	 */
	public function find( $params ) {
		if ( empty( $params ) || empty( $params['query'] ) ) {

			return false;
		}

		// API Method
		$method = 'search/Lead';

		// API Params
		$params = shortcode_atts( [
			'count'  => '1',
			'query'  => 'isDeleted:0',
			'fields' => 'id,firstName,lastName,email,isDeleted',
		], $params );

		try {
			$request = $this->request( $method, $params, 'GET' );
		} catch ( Exception $e ) {

			return null;
		}


		if ( ! is_wp_error( $request ) && is_object( $request ) && ! isset( $request->errorMessage ) && 1 === $request->count ) {

			return (int) $request->data[0]->id;
		}

		return null;
	}

	//
	// @todo below move out helpers
	//

	/**
	 * Find Contact By Email and Last Name
	 *
	 * Looks up submitted email address and last name for matching entries
	 * in the candidates database.
	 *
	 * @since 3.7.0
	 *
	 * @param string $email
	 * @param string $last_name
	 *
	 * @return integer|null
	 */
	public function find_by_email_and_last_name( $email, $last_name ) {

		if ( ! $email ) {

			return null;
		}

		$params = [];

		if ( is_string( $last_name ) && ! empty( $last_name ) ) {
			$params['query'] = sprintf( 'email: "%s" AND lastName: "%s" AND isDeleted:0', sanitize_email( $email ), sanitize_text_field( $last_name ) );
		} else {
			$params['query'] = sprintf( 'email: "%s" AND isDeleted:0', sanitize_email( $email ) );
		}

		return self::find( $params );
	}

	//
	// @todo below this point refactor
	//

	/**
	 * Find Client Corporation
	 *
	 * @todo once we figure out singleton Bullhorn connections, this should be in a ClientCorporation class
	 * @since 3.7.0
	 *
	 * @param string $name
	 *
	 * @return int|null
	 */
	public function find_company_by_name( $name ) {

		if ( empty( $name ) ) {

			return null;
		}

		// API Method
		$method = 'search/ClientCorporation';

		// API Params
		$params = [
			'fields' => 'id',
			'count'  => '1',
			'query'  => sprintf( 'name:"%s"', sanitize_text_field( $name ) ),
		];

		// API Request
		try {
			$response = $this->request( $method, $params, 'GET' );
		} catch ( Exception $e ) {

			return null;
		}

		if ( is_object( $response ) && isset( $response->count ) && 1 === $response->count ) {

			return $response->data[0]->id;
		}

		return null;
	}

	/**
	 * Check is the current account has leads enabled
	 * returns false if not enabled
	 *
	 * @since 3.0.0
	 *
	 * @return bool;
	 */
	public function leads_enabled() {

		if ( ! $this->is_authorized() ) {

			return false;
		}

		$cache_key = 'matador_extension_leads_enabled';

		$enabled = get_transient( 'matador_extension_leads_enabled' );

		if ( false === $enabled ) {

			// API Method
			$method = 'settings/leadAndOpportunityEnabled';

			try {
				// API Request
				$response = $this->request( $method, [], 'GET' );
			} catch ( Exception $e ) {

				return false;
			}

			if ( is_object( $response ) && isset( $response->leadAndOpportunityEnabled ) ) {
				$enabled = $response->leadAndOpportunityEnabled;

				set_transient( $cache_key, $enabled, $enabled ? DAY_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
			} else {

				return false;
			}
		}

		return (bool) $enabled;
	}

	/**
	 * Create Note
	 *
	 * @since 3.0.0
	 *
	 * @param array $note
	 *
	 * @return bool
	 */
	public function create_note( $note = [] ) {

		if ( empty( $note ) ) {

			return false;
		}

		// API Method
		$method = 'entity/Note';

		try {
			$response= $this->request( $method, [], 'PUT', $note );
		} catch ( Exception $e ) {
			new Event_Log( 'matador-create-note-error',  esc_html__( 'Call to create note returned an error: ', 'matador-jobs' ) . '  ' . print_r( $e , true ) );

			return false;
		}

		if ( isset( $response->changedEntityId ) ) {

			return $response->changedEntityId;
		}

		return false;
	}

	/**
	 * Get The User ID from Settings
	 *
	 * @since 3.0.0
	 *
	 * @return int|null
	 */
	public function get_settings_user_id() {

		if ( is_null( $this->url ) ) {
			return false;
		}

		$cache_key = 'matador_default_user_id';

		$cache = get_transient( $cache_key );

		if ( ! $cache ) {

			// API Method
			$method = 'settings/userId';

			try {
				// API Request
				$response = $this->request( $method, [], 'GET' );
			} catch ( Exception $e ) {

				return null;
			}

			if ( ! is_object( $response ) || ! isset( $response->userId ) ) {

				return null;

			}

			$user_id = absint( $response->userId );

			set_transient( $cache_key, $user_id, DAY_IN_SECONDS );

			$cache = $user_id;
		}

		return $cache;
	}
}
