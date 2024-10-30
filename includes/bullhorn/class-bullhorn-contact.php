<?php
/**
 * Matador / Bullhorn API / Client Contact Entity
 *
 * Extends Bullhorn_Connection and Communicates with the Contact Entity
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.0
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
 * Class Bullhorn_Contact
 *
 * @since 3.7.0
 */
final class Bullhorn_Contact extends Bullhorn_Connection {

	/**
	 * Class Constructor
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * Create
	 *
	 * Create a new contact
	 *
	 * @since 3.7.0
	 *
	 * @param array $submission
	 *
	 * @return object|null
	 */
	public function create( $submission = [] ) {

		if ( is_null( $this->url ) ) {

			return null;
		}

		if ( empty( $submission ) ) {

			return null;
		}

		$method = 'entity/ClientContact';

		try {
			$response = $this->request( $method, [], 'PUT', $submission );
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
	 * Read
	 *
	 * Retrieve Contact
	 *
	 * @since 3.7.0
	 *
	 * @param $id
	 *
	 * @return bool|object
	 *
	 * @throws Exception
	 */
	public function read( $id ) {

		// API Method
		$method = 'entity/ClientContact/' . $id;

		// API Params
		$params = array(
			'fields' => 'id,name'
		);

		// API Request
		$response = $this->request( $method, $params, 'GET' );

		if ( is_object( $response ) && isset( $response->data ) ) {
			$return       = new stdClass;
			$return->data = $response->data;
		} else {
			$return = false;
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
	 * Delete Client Contact
	 *
	 * @todo unused. Finish one day?
	 * @since 3.7.0
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete( $id ) {

		return $id;
	}

	//
	// @todo this is where this class should END
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

		if ( is_string( $last_name ) && ! empty( $last_name ) ) {
			$query = sprintf( 'email: "%s" AND lastName: "%s" AND isDeleted:0', sanitize_email( $email ), sanitize_text_field( $last_name ) );
		} else {
			$query = sprintf( 'email: "%s" AND isDeleted:0', sanitize_email( $email ) );
		}

		// API Method
		$method = 'search/ClientContact';

		// API Params
		$params = array(
			'count'  => '1',
			'query'  => $query,
			'fields' => 'id,lastName,email',
		);

		try {
			$request = $this->request( $method, $params, 'GET' );
		} catch ( Exception $e ) {

			return null;
		}

		if ( ! is_wp_error( $request ) && is_object( $request ) && ! isset( $request->errorMessage ) && 0 < $request->count ) {

			return (int) $request->data[0]->id;
		}

		return null;
	}

	/**
	 * Create Note
	 *
	 * @since 3.7.0
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
			$response = $this->request( $method, [], 'PUT', $note );
		} catch ( Exception $e ) {

			return false;
		}

		if ( isset( $response->changedEntityId ) ) {

			return $response->changedEntityId;
		}

		return false;
	}

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
	 * Create ClientCorporation from Name
	 *
	 * @todo once we figure out singleton Bullhorn connections, this should be in a ClientCorporation class
	 * @since 3.7.0
	 *
	 * @param string $name
	 *
	 * @return int|null
	 */
	public function create_company_from_name( $name ) {

		if ( empty( $name ) ) {

			return null;
		}

		$name = substr( sanitize_text_field( $name ), 0, 100 );

		// API Method
		$method = 'entity/ClientCorporation';

		// API Request
		try {
			$response = $this->request( $method, [], 'PUT', [ 'name' => $name ] );
		} catch ( Exception $e ) {

			return null;
		}

		if ( is_object( $response ) && isset( $response->changedEntityId ) ) {

			return $response->changedEntityId;
		}

		return null;
	}

	/**
	 * Get The User ID from Settings
	 *
	 * @since 3.7.0
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

			set_transient( $cache_key, $user_id, 24 * HOUR_IN_SECONDS );

			$cache = $user_id;
		}

		return $cache;
	}
}
