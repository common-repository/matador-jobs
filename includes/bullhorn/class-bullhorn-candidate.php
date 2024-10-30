<?php
/**
 * Matador / Bullhorn API / Candidate Submission
 *
 * Extends Bullhorn_Connection and submits candidates for jobs.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use stdClass;

/**
 * This class is an extension of Bullhorn_Connection.  Its purpose
 * is to allow for resume and candidate posting
 *
 * Class Bullhorn_Candidate_Processor
 */
class Bullhorn_Candidate extends Bullhorn_Connection {

	/**
	 * Class Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Find Candidate
	 *
	 * Looks up submitted email address and last name for matching entries
	 * in the candidates database.
	 *
	 * @since 3.0.0
	 * @since 3.7.0 All records for a given email are now downloaded and last name compares are made locally.
	 *
	 * @param string $email
	 * @param string $last_name
	 *
	 * @return integer|boolean
	 *
	 * @throws Exception
	 */
	public function find_candidate( $email, $last_name ) {

		if ( ! $email ) {

			return false;
		}

		$email = Helper::escape_lucene_string( $email );

		// API Method
		$method = 'search/Candidate';

		$query = '( email:' . $email . ' OR email2: ' . $email . ' OR email3: ' . $email . ' ) AND isDeleted:0';

		/**
		 * Matador Bullhorn Candidate Find Candidate Query Filter
		 *
		 * WARNING: Use with caution. Failing to make a proper query can result in Matador failing to complete a sync
		 * and/or other unintended consequences.
		 *
		 * @since 3.7.7
		 *
		 * @param string $query     The default query
		 * @param string $email     The email being searched upon
		 * @param string $last_name The candidate's last name
		 *
		 * @return string
		 */
		$query =  apply_filters( 'matador_bullhorn_candidate_find_candidate_query', $query, $email, $last_name );

		// API Params
		$params = [
			'query'  => $query,
			'fields' => 'id,lastName',
			'sort'   => '-id',
		];

		/**
		 * Matador Bullhorn Candidate Find Candidate Request Params
		 *
		 * WARNING: Use with caution. Failing to make a proper query can result in Matador failing to complete a sync
		 * and/or other unintended consequences.
		 *
		 * @since 3.7.7
		 *
		 * @param array  $params    Default parameters
		 * @param string $email     The email being searched upon
		 * @param string $last_name The candidate's last name
		 *
		 * @return string
		 */
		$params =  apply_filters( 'matador_bullhorn_candidate_find_candidate_request_params', $params, $email, $last_name );

		$request = $this->request( $method, $params );

		if (
			! is_wp_error( $request )
			&& is_object( $request )
			&& ! isset( $request->errorMessage )
			&& 0 < $request->count
		) {
			foreach ( $request->data as $candidate ) {
				if ( strtolower( $last_name ) === strtolower( $candidate->lastName ) ) {

					return (int) $candidate->id;
				}
			}
		}

		return false;
	}

	/**
	 * Search by Email Address
	 *
	 * Looks up submitted email address for matching entries
	 * in the candidates database.
	 *
	 * @param integer $bhid
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function get_candidate( $bhid = null ) {

		if ( ! is_integer( $bhid ) ) {
			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $bhid;

		// API Params
		$params = array(
			/**
			 * Matador Bullhorn Candidate Get Candidate Method Fields Filter
			 *
			 * NOTE: Use this with caution. Exposing fields for edit can allow unintended changes to candidate record.
			 *
			 * WARNING: If field does not exist in the Bullhorn Candidate entity, your application sync will break.
			 *
			 * @since 3.6.0
			 *
			 * @param string $fields Comma separated, without spaces, list of Candidate Fields per Bullhorn API Candidate Entity
			 *
			 * @return string
			 */
			'fields' => apply_filters( 'matador_bullhorn_candidate_get_candidate_fields', 'id,name,nickName,firstName,middleName,lastName,address,secondaryAddress,email,email2,email3,mobile,phone,phone2,phone3,description,status,dateLastModified' ),
		);


		// API Request
		$response = $this->request( $method, $params, 'GET' );

		if ( is_object( $response ) && isset( $response->data ) && isset( $response->data->id ) && $response->data->id === $bhid ) {

			$return = new stdClass();

			$return->candidate = $response->data;
		} else {
			$return = false;
		}

		return $return;
	}

	/**
	 * Save Candidate
	 *
	 * @param stdClass $candidate
	 *
	 * @return stdClass|boolean
	 *
	 * @throws Exception
	 * @since 3.0
	 */
	public function save_candidate( $candidate = null ) {

		if ( ! $candidate->candidate || ! is_object( $candidate->candidate ) ) {
			Logger::add( 'error', 'matador-error-bad-candidate-data', esc_html__( 'We passed bad data to the save candidate function the data was: ', 'matador-jobs' ) . ' ' . print_r( $candidate, true ) );

			return false;
		}

		// API Method
		if ( isset( $candidate->candidate->id ) ) {
			$method = 'entity/Candidate/' . $candidate->candidate->id;
			// API Request
			$response = $this->request( $method, array(), 'POST', $candidate->candidate );
		} else {
			$method = 'entity/Candidate';
			// API Request
			$response = $this->request( $method, array(), 'PUT', $candidate->candidate );
		}

		if ( is_object( $response ) && isset( $response->changedEntityId ) ) {
			$candidate->candidate->id = $response->changedEntityId;

			return $candidate;
		} else {

			if ( isset( $candidate->candidate->id ) ) {
				Logger::add( 'error', 'matador-error-updating-candidate', sprintf( esc_html__( 'We got an error when updating a remote candidate (%s) the returned error was: ', 'matador-jobs' ), $candidate->candidate->id )  . ' ' . print_r( $response, true ) );
			} else {
				Logger::add( 'error', 'matador-error-creating-candidate', esc_html__( 'We got an error when creating a remote candidate the returned error was: ', 'matador-jobs' ) . ' ' . print_r( $response, true ) );
			}

			return false;
		}
	}

	/**
	 * Save basic Candidate Certification
	 *
	 * @since 3.7.0
	 *
	 * @param stdClass|array $candidate
	 *
	 * @return bool
	 */
	public function save_candidate_certification_list( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->certificationList ) || ! is_array( $candidate->certificationList )) {

			return false;
		}

		// API Method
		$method = 'entity/CandidateCertification';

		// HTTP Action
		$http = 'PUT';

		foreach ( $candidate->certificationList as $certification_id ) { // @codingStandardsIgnoreLine (SnakeCase)

			$body = array(
				'certification' => array(
					'id' => (int) $certification_id,
				),
				'candidate'     => array(
					'id' => (int) $candidate->candidate->id,
				),
				'status'        => 'current',
				'name'          => $this->get_certification_name( (int) $certification_id ),
			);

			try {
				// API Call
				$return[] = $this->request( $method, array(), $http, $body );
			} catch ( Exception $e ) {

				return false;
			}
		}

		return empty( $return ) ? false : true;
	}

	/**
	 * Get Certification Name
	 *
	 * @since 3.7.0
	 *
	 * @param int $certification_id
	 *
	 * @return string
	 */
	private function get_certification_name( $certification_id ) {

		// try to get from local saved settings
		$option_fields = get_option( 'matador_advanced_applications' );

		if (
			$option_fields !== false &&
			! empty( $option_fields['certificationList']->options ) &&
			array_key_exists( (int) $certification_id, $option_fields['certificationList']->options )
		) {
			return $option_fields['certificationList']->options[ (int) $certification_id ];
		}

		try {
			$options = $this->request( 'options/Certification', array( 'fields' => 'value,label', 'count' => '300' ) );
		} catch ( Exception $e ) {

			return (string) $certification_id;
		}

		if ( ! empty( $options->options ) ) {
			foreach ( $options->options as $option ) {
				if ( $certification_id = $option->value ) {
					return $option->label;
				}
			}
		}

		return (string) $certification_id;
	}

	/**
	 * Save Candidate Education
	 *
	 * @param stdClass|array $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function save_candidate_education( $candidate = null ) {

		if ( ! $candidate ) {
			return false;
		}

		// API Method
		$method = 'entity/CandidateEducation';

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		if ( isset( $candidate->candidateEducation ) && is_array( $candidate->candidateEducation ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			$return = array();

			foreach ( $candidate->candidateEducation as $education ) { // @codingStandardsIgnoreLine (SnakeCase)

				$education->candidate     = new stdClass();
				$education->candidate->id = $candidate->candidate->id;

				$education->certification  = isset( $education->certification ) ? substr( $education->certification, 0, 100 ) : '';
				$education->major          = isset( $education->major ) ? substr( $education->major, 0, 100 ) : '';
				$education->degree         = isset( $education->degree ) ? substr( $education->degree, 0, 100 ) : '';
				$education->school         = isset( $candidate->school ) ? substr( $candidate->school, 0, 100 ) : '';
				$education->city           = isset( $education->city ) ? substr( $education->city, 0, 40 ) : '';
				$education->state          = isset( $education->state ) ? substr( $education->state, 0, 50 ) : '';

				// API Call
				$return[] = $this->request( $method, $params, $http, $education );

			}
		}

		return isset( $return ) ? true : false;
	}

	/**
	 * Save Candidate Work History
	 *
	 * @param stdClass|array $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function save_candidate_work_history( $candidate = null ) {

		if ( empty( $candidate ) ) {
			return false;
		}

		// API Method
		$method = 'entity/CandidateWorkHistory';

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		if ( isset( $candidate->candidateWorkHistory ) && is_array( $candidate->candidateWorkHistory ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			// Return Array
			$return = array();

			foreach ( $candidate->candidateWorkHistory as $job ) { // @codingStandardsIgnoreLine (SnakeCase)

				$job->candidate     = new stdClass();
				$job->candidate->id = $candidate->candidate->id;
				$job->title         = isset( $candidate->title ) ? substr( $candidate->title, 0, 50 ) : '';
				$job->companyName   = isset( $candidate->companyName ) ? substr( $candidate->companyName, 0, 100 ) : '';

				// API Call
				$return[] = $this->request( $method, $params, $http, $job );

			}
		}

		return ! empty( $return ) ? true : false;
	}

	/**
	 * Save Candidate primary
	 *
	 * @param stdClass $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function save_candidate_primary_skills( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->primarySkills ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			return false;
		}

		$bullhorn_skills      = $this->get_skills_list();
		$candidate_skills     = $candidate->primarySkills; // @codingStandardsIgnoreLine (SnakeCase)
		$candidate_skills_ids = array();

		if ( ! empty( $bullhorn_skills ) ) {
			foreach ( $candidate_skills as $skill ) {
				if ( isset( $skill->id ) ) {
					if ( array_key_exists( $skill->id, $bullhorn_skills ) ) {
						$candidate_skills_ids[] = $skill->id;
					}
				} elseif ( isset( $skill->name ) ) {
					$key = array_search( strtolower( $skill->name ), $bullhorn_skills, true );
					if ( $key ) {
						$candidate_skills_ids[] = $key;
					}
				} else {
					$key = array_search( strtolower( $skill ), $bullhorn_skills, true );
					if ( $key ) {
						$candidate_skills_ids[] = $key;
					} elseif ( array_key_exists( $skill, $bullhorn_skills ) ) {
						$candidate_skills_ids[] = $skill;
					} else {
						Logger::add( 'info', 'matador-skill-missing', esc_html__( 'We didn\'t find the id passed to primarySkills', 'matador-jobs' ) . ' - ' . print_r( $skill, true ) );
						return false;
					}
				}
			}
			$candidate_skills_ids = array_unique( $candidate_skills_ids );
		}

		if ( empty( $candidate_skills_ids ) ) {
			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/primarySkills/' . implode( ',', $candidate_skills_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return ) ? true : false;
	}

	/**
	 * Save Candidate secondary Skills
	 *
	 * @param stdClass $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function save_candidate_secondary_skills( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->secondarySkills ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			return false;
		}

		$bullhorn_skills      = $this->get_skills_list();
		$candidate_skills     = $candidate->secondarySkills; // @codingStandardsIgnoreLine (SnakeCase)
		$candidate_skills_ids = array();

		if ( ! empty( $bullhorn_skills ) ) {
			foreach ( $candidate_skills as $skill ) {

				if ( is_string( $skill ) ) {
					$skill_name = $skill;
				} elseif ( is_array( $skill ) ) {
					$skill_name = $skill['name'];
				} elseif ( is_object( $skill ) ) {
					$skill_name = $skill->name;
				} else {
					continue;
				}

				$key = array_search( strtolower( $skill_name ), $bullhorn_skills, true );

				if ( $key ) {
					$candidate_skills_ids[] = $key;
				} elseif ( array_key_exists( $skill, $bullhorn_skills ) ) {
					$candidate_skills_ids[] = $skill;
				}
			}
			$candidate_skills_ids = array_unique( $candidate_skills_ids );
		}

		if ( empty( $candidate_skills_ids ) ) {

			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/secondarySkills/' . implode( ',', $candidate_skills_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return );
	}

	/**
	 * Save Candidate Secondary Owners
	 *
	 * @since 3.5.0
	 *
	 * @param stdClass $candidate
	 *
	 * @return bool
	 */
	public function save_candidate_secondary_owners( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->secondaryOwners ) ) {

			return false;
		}

		$bullhorn_users = $this->get_all_corporate_user_ids();

		if ( empty( $bullhorn_users ) ) {

			return false;
		}

		$secondary_owners = [];

		foreach ( $candidate->secondaryOwners as $owner_id ) {

			if ( ! in_array( (int) $owner_id, $bullhorn_users, true ) ) {

				Logger::add( 'info', 'matador-secondary-owner-not-exists', esc_html__( 'The secondaryOwner(s) ID(s) do not match Bullhorn users. Will skip.', 'matador-jobs' ) );
				continue;
			}

			if ( isset( $candidate->candidate->owner->id ) && (int) $owner_id === $candidate->candidate->owner->id ) {

				Logger::add( 'info', 'matador-secondary-owner-matches-owner', esc_html__( 'The secondaryOwner ID matches the primary owner ID. Will skip.', 'matador-jobs' ) );
				continue;
			}

			$secondary_owners[] = $owner_id;
		}

		$secondary_owners = array_unique( $secondary_owners );

		if ( empty( $secondary_owners ) ) {

			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/secondaryOwners/' . implode( ',', $secondary_owners );

		try {
			$response = $this->request( $method, array(), 'PUT' );
		} catch ( Exception $error ) {

			return false;
		}

		return ! empty( $response );
	}

	/**
	 * Get Corporate User IDs
	 *
	 * Gets an array of the Corporate User's IDs. Used to verify calls to set Candidate Owners.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	private function get_all_corporate_user_ids() {

		$cache_key = 'matador_corporate_users';

		$corporate_users = get_transient( $cache_key );

		if ( false === $corporate_users ) {

			new Event_Log( 'matador-bullhorn-import-corporate-users-start', esc_html__( 'Requesting Bullhorn users data.', 'matador-jobs' ) );

			$corporate_users = array();
			$data = array();

			while ( true ) {

				// Things we need
				$limit  = 100;
				$offset = isset( $offset ) ? $offset : 0;

				// API Method
				$request = 'query/CorporateUser';

				// API Method Parameters
				$params = array(
					'fields' => 'id',
					'where'  => 'id>0',
					'count'  => $limit,
					'start'  => $offset,
					'order'  => 'dateLastModified',
				);

				// API Call
				try {
					$response = $this->request( $request, $params );

					// Process API Response
					if ( isset( $response->data ) ) {

						// Merge Results Array with Return Array
						foreach ( $response->data as $user ) {
							$data[] = $user->id;
						}

						$corporate_users = array_merge( $corporate_users, $data );

						if ( count( $response->data ) < $limit ) {
							// If the size of the result is less than the results per page
							// we got all the companies, so end the loop
							break;
						} else {
							// Otherwise, increment the offset by the results per page, and re-run the loop.
							$offset += $limit;
						}
					} elseif ( is_wp_error( $response ) ) {

						throw new Exception( 'error', 'matador-bullhorn-import-corporate-users-timeout', esc_html__( 'Bullhorn request corporate users operation timed out', 'matador-jobs' ) );
					} else {
						break;
					}
				} catch ( Exception $error ) {

					new Event_Log( 'matador-bullhorn-import-corporate-error', esc_html__( 'Bullhorn request corporate users encountered an error. This may impact the remainder of the sync routine.', 'matador-jobs' ) );

					return $corporate_users;
				}
			}

			if ( empty( $corporate_users ) ) {

				new Event_Log( 'matador-bullhorn-import-corporate-users-not-found', esc_html__( 'Bullhorn request corporate users found no corporate users.', 'matador-jobs' ) );

				return [];

			} else {
				// Translators: Placeholder is for number of found companies.
				new Event_Log( 'matador-bullhorn-import-corporate-users-count', esc_html( sprintf( __( 'Bullhorn request corporate users found %1$s corporate users.', 'matador-jobs' ), count( $corporate_users ) ) ) );

				set_transient( $cache_key, $corporate_users, DAY_IN_SECONDS );
			}
		}

		return $corporate_users;
	}

	/**
	 * Save Candidate Categories
	 *
	 * @param stdClass $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function save_candidate_categories( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->categories ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			return false;
		}

		$bullhorn_categories      = $this->get_categories_list();
		$candidate_categories     = $candidate->categories; // @codingStandardsIgnoreLine (SnakeCase)
		$candidate_categories_ids = array();

		if ( ! empty( $bullhorn_categories ) ) {
			foreach ( $candidate_categories as $category ) {
				$key = array_search( strtolower( $category ), $bullhorn_categories, true );
				if ( $key ) {
					$candidate_categories_ids[] = $key;
				} elseif ( array_key_exists( $category, $bullhorn_categories ) ) {
					$candidate_categories_ids[] = $category;
				} else {
					Logger::add( 'info', 'matador-category-missing', esc_html__( 'We didn\'t find the id passed to categories', 'matador-jobs' ) . ' - ' . print_r( $category, true ) );
					return false;
				}
			}
			$candidate_categories_ids = array_unique( $candidate_categories_ids );
		}

		if ( empty( $candidate_categories_ids ) ) {

			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/categories/' . implode( ',', $candidate_categories_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return ) ? true : false;
	}

	/**
	 * Save Candidate Categories
	 *
	 * @param stdClass $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function save_candidate_specialties( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->specialties ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			return false;
		}

		$bullhorn_specialties  = $this->get_specialties_list();
		$candidate_specialties = $candidate->specialties; // @codingStandardsIgnoreLine (SnakeCase)

		$candidate_specialties_ids = array();

		if ( ! empty( $bullhorn_specialties ) ) {
			foreach ( $candidate_specialties as $specialty ) {
				$key = array_search( strtolower( $specialty ), $bullhorn_specialties, true );
				if ( $key ) {
					$candidate_specialties_ids[] = $key;
				} elseif ( array_key_exists( $specialty, $bullhorn_specialties ) ) {
					$candidate_specialties_ids[] = $specialty;
				} else {
					Logger::add( 'info', 'matador-specialty-missing', esc_html__( 'We didn\'t find the id passed to specialties', 'matador-jobs' ) . ' - ' . print_r( $specialty, true ) );
					return false;
				}
			}
			$candidate_specialties_ids = array_unique( $candidate_specialties_ids );
		}

		if ( empty( $candidate_specialties_ids ) ) {

			return false;
		}
		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/specialties/' . implode( ',', $candidate_specialties_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return ) ? true : false;
	}


	/**
	 * Save Candidate Categories
	 *
	 * @param stdClass $candidate
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function save_candidate_business_sectors( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->businessSectors ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			return false;
		}

		$bullhorn_business_sectors  = $this->get_business_sectors_list();
		$candidate_business_sectors = $candidate->businessSectors; // @codingStandardsIgnoreLine (SnakeCase)

		$candidate_business_sectors_ids = array();

		if ( ! empty( $bullhorn_business_sectors ) ) {
			foreach ( $candidate_business_sectors as $sector ) {
				$key = array_search( strtolower( $sector ), $bullhorn_business_sectors, true );
				if ( $key ) {
					$candidate_business_sectors_ids[] = $key;
				} elseif ( array_key_exists( $sector, $bullhorn_business_sectors ) ) {
					$candidate_business_sectors_ids[] = $sector;
				} else {
					Logger::add( 'info', 'matador-business-sectors-missing', esc_html__( 'We didn\'t find the id passed to businessSectors', 'matador-jobs' ) . ' - ' . print_r( $sector, true ) );
					return false;
				}
			}
			$candidate_business_sectors_ids = array_unique( $candidate_business_sectors_ids );
		}

		if ( empty( $candidate_business_sectors_ids ) ) {

			return false;
		}
		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/businessSectors/' . implode( ',', $candidate_business_sectors_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return ) ? true : false;
	}

	/**
	 * Attach Note to a candidate
	 *
	 * @since 3.0.0
	 * @since 3.8.17 Added $application arg.
	 *
	 * @param stdClass $candidate   The Bullhorn Candidate object. Required.
	 * @param string   $note        The generated note from the form and sync process so far. Optional, default empty
	 *                              string.
	 * @param array    $application The array of application data from the form. Optional, default empty array.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function save_candidate_note( $candidate, $note = '', $application = [] ) {

		if ( ! $candidate && ! $note ) {
			return false;
		}

		/**
		 * Bullhorn Candidate Note
		 *
		 * @wordpress-filter `matador_bullhorn_candidate_note`
		 *
		 * Filter and modify the note string before submitting to Bullhorn on the Candidate.
		 *
		 * @since 3.8.17
		 *
		 * @param string   $note        The candidate note as generated by the Application and Sync routine so far.
		 * @param stdClass $candidate   Object containing a Bullhorn Candidate.
		 * @param array    $application Array containing the application data from the form or API call. Can be empty
		 *                              array if the create Candidate note is called from a non-Application use case.
		 *
		 * @return string The (maybe) modified string for the candidate note.
		 */
		$note = apply_filters( 'matador_bullhorn_candidate_note', $note, $candidate, $application );

		$note_args = [
			'personReference'  => [ 'id' => $candidate->candidate->id ],
			'comments'         => $note,
			'action'           => 'Other',
		];

		if ( isset( $candidate->candidate->owner->id ) ) {
			$note_args['commentingPerson'] = [ 'id' => $candidate->candidate->owner->id ];
		}

		/**
		 * Bullhorn Candidate Note Arguments
		 *
		 * @wordpress-filter `matador_bullhorn_candidate_note_args`
		 *
		 * Filter and modify the note args array before submitting to Bullhorn on the Candidate.
		 *
		 * @since 3.8.17
		 *
		 * @param string   $note_args   The candidate note as generated by the Application and Sync routine so far.
		 * @param stdClass $candidate   Object containing a Bullhorn Candidate.
		 * @param array    $application Array containing the application data from the form or API call. Can be empty
		 *                              array if the create Candidate note is called from a non-Application use case.
		 *
		 * @return array   The (maybe) modified note args array.
		 */
		$body = apply_filters( 'matador_bullhorn_candidate_note_args', $note_args, $candidate, $application );

		// API Method
		$method = 'entity/Note';

		// Request
		$response = $this->request( $method, [], 'PUT', $body );

		return (bool) $response;
	}

	/**
	 * Attach file to a candidate.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 added ID parameter to begin support of multiple file uploads per candidate.
	 *
	 * @param stdClass $candidate Candidate Object
	 * @param string   $path      Path to the file (temp or saved)
	 * @param string   $id        The form field name, $_FILES array index key, or file ID
	 *
	 * @return bool
	 *
	 * @throws Exception
	 **/
	public function save_candidate_file( $candidate = null, $path = '', $id = '' ) {

		if ( ! $candidate || ! $path ) {
			return false;
		}

		// API Method
		$method = 'file/Candidate/' . $candidate->candidate->id . '/raw';

		switch ( strtolower( $id ) ) {
			case 'resume';
				$type = 'Resume';
				break;
			case 'letter';
				$type = 'Cover Letter';
				break;
			default:
				$type = 'Other';
		}

		$params = array(
			'externalID' => 'Portfolio', // PER BULLHORN
			'fileType'   => 'RESUME', // PER BULLHORN
			/**
			 * Filter: Matador Value sent to bullhorn for the file type
			 *
			 * @since 3.8.0
			 *
			 * @param string $term Default "Other"
			 *
			 * @return string
			 */
			'type'       => apply_filters( 'matador_candidate_file_type', $type, $id, $candidate ),
		);

		// API Request
		$request = $this->request_with_payload( $method, $params, 'PUT', $path );

		return (bool) $request;
	}

	/**
	 * Get Candidate Job Submissions
	 *
	 * For a given Candidate ID, get the history of job submissions for the candidate.
	 *
	 * @param int $candidate_id
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function get_candidate_submissions( $candidate_id = null ) {

		if ( empty( $candidate_id ) || ! is_integer( $candidate_id ) ) {

			return false;
		}

		$transient = 'JobSubmission_' . $candidate_id;

		$applications = get_transient( $transient );

		if ( false === $applications ) {

			$method   = 'search/JobSubmission?query=candidate.id:' . $candidate_id;
			$params   = array( 'fields' => 'id,status' );
			$response = $this->request( $method, $params, 'GET' );

			$applications = array();

			foreach ( $response->data as $application ) {

				$applications[ $application->id ] = $application->status;
			}

			set_transient( $transient, $applications, HOUR_IN_SECONDS );
		}

		return $applications;
	}

	/**
	 * Get Job Submission History
	 *
	 * For a given job submission, get the history of actions on the submission.
	 *
	 * @param int $submission_id the ID of the Job Submission
	 *
	 * @return array|bool
	 * @throws Exception
	 * @since 3.5.0
	 *
	 * @todo rename to get_job_submission_history
	 */
	public function get_job_application_status( $submission_id = null ) {
		if ( empty( $candidate_id ) || ! is_integer( $submission_id ) ) {

			return false;
		}

		$transient    = 'job_submission_status_' . $submission_id;
		$applications = get_transient( $transient );

		if ( false === $applications ) {

			$method = 'entity/JobSubmissionHistory/' . $submission_id;
			$params = array( 'fields' => 'id,comments,dateAdded,jobSubmission,status,transactionID' );

			$response = $this->request( $method, $params, 'GET' );

			$applications = $response->data;

			set_transient( $transient, $applications, HOUR_IN_SECONDS );
		}

		return $applications;
	}

	/**
	 * Attach Found Candidate to Job
	 *
	 * Looks up submitted email address for matching entries
	 * in the candidates database.
	 *
	 * @param stdClass $candidate
	 * @param integer  $job_id
	 * @param array    $application
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function submit_candidate_to_job( $candidate = null, $job_id = null, $application = [] ) {

		if ( ! is_object( $candidate ) && ! is_int( $job_id ) ) {
			return false;
		}

		$status              = 'New Lead';
		$mark_application_as = Matador::setting( 'bullhorn_mark_application_as' );
		if ( ! empty( $mark_application_as ) ) {
			switch ( $mark_application_as ) {
				case 'submitted':
					$status = 'Submitted';
					break;
				case 'lead':
				default:
					$status = 'New Lead';
					break;
			}
		}

		// API Method
		$method = 'entity/JobSubmission';

		// Request Body
		$body = array(
			'candidate'       => array( 'id' => $candidate->candidate->id ),
			'jobOrder'        => array( 'id' => $job_id ),
			/**
			 * Matador Data Source Description
			 *
			 * Modify the string value passed to the "source" field of an external resource, ie: Bullhorn, during a sync
			 * that creates or updates remote records. Default is "{Site Name} Website", ie: "ACME Staffing Website".
			 * Use the $context argument to narrow the modification to only certain types of external data.
			 *
			 * @since 3.1.1
			 * @since 3.4.0 added $data parameter
			 * @since 3.5.0 added $submission parameter
			 *
			 * @var string    $source The value for Source. Limit of 200 characters for Candidates, 100 for
			 *                            JobSubmissions. Default is the value of the WordPress "website name" setting.
			 * @var string    $context    Limit scope of filter in filtering function
			 * @var stdClass  $data       The associated data with the $context. Should not be used without $context first.
			 * @var array     $submission The associated data with the $context's submission.
			 *
			 * @return string The modified value for Source. Warning! Limit of 200 characters for Candidates, 100 for JobSubmissions.
			 */
			'source'          => substr( apply_filters( 'matador_data_source_description', get_bloginfo( 'name' ), 'submission', $candidate->candidate, $application ), 0, 100 ),
			/**
			 * Matador Data Status Description
			 *
			 * Adjusts the value of the status for the Bullhorn data item. IE: "New Lead"
			 *
			 * @since 3.5.1
			 *
			 * @var string    $status     The value of status. Set initially by default or by settings.
			 * @var string    $context    Limit scope of filter in to an entity
			 * @var stdClass  $data       The associated data with the $context. Should not be used without $context first.
			 * @var array     $submission The associated data with the $context's submission.
			 *
			 * @return string The filtered value of status.
			 */
			'status'          => apply_filters( 'matador_data_source_status', $status, 'submission', $candidate->candidate, $application ),
			'dateWebResponse' => (int) ( microtime( true ) * 1000 ),
		);

		if ( isset( $candidate->candidate->owner->id ) ) {
			$body['owners']      = [ 'id' => $candidate->candidate->owner->id ];
			$body['sendingUser'] = [ 'id' => $candidate->candidate->owner->id ];
		}

		$response = $this->request( $method, array(), 'PUT', $body );

		/**
		 * Action After Submit Candidate to Job
		 *
		 * @wordpress-action matador_bullhorn_after_submit_candidate_to_job
		 *
		 * @since 3.5.0
		 * @since 3.8.18 Added $application parameter.
		 *
		 * @param stdClass $candidate   The Candidate object
		 * @param int      $job_id      Integer ID of the Job.
		 * @param stdClass $response    Array of API respose data from the Bullhorn entity/JobSubmission API call.
		 * @param array    $application Array of application data.
		 */
		do_action( 'matador_bullhorn_after_submit_candidate_to_job', $candidate, $job_id, $response, $application );

		return is_object( $response ) ? $response->changedEntityId : false;
	}

	/**
	 * Parse Resume
	 *
	 * Takes an application data array, checks for the file info.
	 *
	 * @param string $file Path to file for resume.
	 * @param string $content Text-based resume content.
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 * @since 3.0.0
	 * @since 3.4.0 added $context parameter
	 *
	 */
	public function parse_resume( $file = null, $content = null ) {

		if ( ! $file && ! $content ) {
			throw new Exception( 'warning', 'bullhorn-parse-resume-no-file', esc_html__( 'Parse resume cannot be called without a file.', 'matador-jobs' ) );
		}

		// API Method
		if ( ! $file && $content ) {
			$method = 'resume/parseToCandidateViaJson';
		} else {

			$file_size = filesize( $file ) / MB_IN_BYTES;

			Logger::add( 'info', 'bullhorn-cv-size', __( 'Resume/CV File Size: ', 'matador-jobs' ) . round( $file_size, 2 ) . 'mb' );

			/**
			 * File size limit for the Bullhorn Resume Parser
			 *
			 * Adjusts the max size that we attempt to send to Bullhorn. This number should be based on what the
			 * Bullhorn API accepts (or rejects) and is not a per-se user option. Modify the global variable to adjust
			 * user form submissions, but they should not exceed this number.
			 *
			 * Based on testing 2019, the Bullhorn API accepted up to 5mb.
			 *
			 * @todo Review in 4.0.0 updates if this should even be filterable.
			 *
			 * @since 3.5.0
			 * @since 3.8.0 Default was value of Variable `accepted_file_size_limit`, now is 5(mb)
			 *
			 * @var int File size in Mb.
			 */
			$file_size_limit = apply_filters( 'matador_bullhorn_file_size_limit', 5 );

			if ( $file_size < $file_size_limit ) {

				$method = 'resume/parseToCandidate';

			} else {

				// Translators: 1. Submitted file size in mb. 2. Max allowed file size in mb.
				$error = __( 'Resume/CV file size exceeds Bullhorn limit of %1$smb. Will not submit resume file to Bullhorn for processing.', 'matador-jobs' );

				Logger::add( 'info', 'bullhorn-file-size-exceeds-limit', sprintf( $error, $file_size_limit ) );

				if ( $content ) {
					$method = 'resume/parseToCandidateViaJson';
				} else {
					return false;
				}
			}
		}

		// API Params
		$params = array(
			'populateDescription' => apply_filters( 'matador_bullhorn_candidate_parse_resume_description_format', 'html' ),
		);

		// while ( true ) is ambiguous, but the loop is broken upon a return, which occurs by the fifth cycle.
		while ( true ) {

			$count = isset( $count ) ? ++ $count : 1;

			if ( 'resume/parseToCandidateViaJson' === $method ) {
				$body = array(
					'resume' => $content,
				);

				$request_args = array(
					'headers' => array( 'Content-Type' => 'application/json' ),
				);

				if ( strip_tags( $content ) !== $content ) {
					$params['format'] = 'html';
				} else {
					$params['format'] = 'text';
				}

				try {
					$return = $this->request( $method, $params, 'POST', $body, $request_args );
				} catch ( Exception $error ) {
					return false;
				}

			} else {
				try {
					$return = $this->request_with_payload( $method, $params, 'POST', $file );
				} catch ( Exception $error ) {
					return false;
				}
			}

			if ( isset( $return->errorMessage ) ) {

				if (
					isset( $return->errorMessageKey ) &&
					substr( $return->errorMessageKey, 0, 'errors.resumeParser' ) === 'errors.resumeParser'
				) {
					Logger::add( 'error', 'bullhorn-resume-file-error', print_r( $return->errorMessage, true ) );
				} else {
					Logger::add( 'error', 'bullhorn-resume-error', print_r( $return->errorMessage, true ) );
				}

				return false;
			}

			// Success condition
			if ( ! isset( $return->errorMessage ) ) { // @codingStandardsIgnoreLine (SnakeCase)
				return $return;
			}

			// Try Again Condition
			if ( $count >= 5 ) {
				return array( 'error' => 'attempted-five-and-failed' );
			}
		}

		return false;
	}

	/**
	 * Get Skills List
	 *
	 * Gets all the Skills terms.
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function get_skills_list() {

		$transient = Matador::variable( 'bullhorn_skills_cache', 'transients' );
		$skills    = get_transient( $transient );

		if ( ! $skills ) {
			// Things we need
			$limit      = 300;
			$offset     = isset( $offset ) ? $offset : 0;
			$new_skills = array();

			// API Method
			$method = 'options/Skill';

			// HTTP Action
			$http = 'GET';

			while ( true ) {

				// Return Array
				$request = $this->request( $method, array(
					'count' => $limit,
					'start' => $offset,
				), $http );

				if ( isset( $request->data ) ) {

					foreach ( $request->data as $skill ) {
						$new_skills[ $skill->value ] = strtolower( trim( $skill->label ) );
					}

					if ( count( $request->data ) < $limit ) {
						// If the size of the result is less than the results per page
						// we got all the jobs, so end the loop
						break;
					} else {
						// Otherwise, increment the offset by the results per page, and re-run the loop.
						$offset += $limit;
					}
				} else {

					break;
				}
			}// while
			$skills = array_unique( $new_skills );
			set_transient( $transient, $skills, HOUR_IN_SECONDS * 6 );
		}

		return $skills;
	}


	/**
	 * Get Categories List
	 *
	 * Gets all the Categories terms.
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function get_categories_list() {

		$transient  = Matador::variable( 'bullhorn_categories_cache', 'transients' );
		$categories = get_transient( $transient );

		if ( ! $categories ) {

			// Things we need
			$limit          = 300;
			$offset         = isset( $offset ) ? $offset : 0;
			$new_categories = array();
			// API Method
			$method = 'options/Category';

			// HTTP Action
			$http = 'GET';

			while ( true ) {
				// Return Array
				$request = $this->request( $method, array(
					'count' => $limit,
					'start' => $offset,
				), $http );

				if ( isset( $request->data ) ) {

					foreach ( $request->data as $category ) {
						$new_categories[ $category->value ] = strtolower( trim( $category->label ) );
					}
					if ( count( $request->data ) < $limit ) {
						// If the size of the result is less than the results per page
						// we got all the jobs, so end the loop
						break;
					} else {
						// Otherwise, increment the offset by the results per page, and re-run the loop.
						$offset += $limit;
					}
				} else {

					break;
				}
			}
			$categories = array_unique( $new_categories );
			set_transient( $transient, $categories, HOUR_IN_SECONDS * 6 );
		}

		return $categories;
	}

	/**
	 * Get Categories List
	 *
	 * Gets all the Categories terms.
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function get_specialties_list() {

		$transient   = Matador::variable( 'bullhorn_specialties_cache', 'transients' );
		$specialties = get_transient( $transient );

		if ( ! $specialties ) {
			// Things we need
			$limit           = 300;
			$offset          = isset( $offset ) ? $offset : 0;
			$new_specialties = array();
			// API Method
			$method = 'options/Specialty';

			// HTTP Action
			$http = 'GET';

			while ( true ) {

				// Return Array
				$request = $this->request( $method, array(
					'count' => $limit,
					'start' => $offset,
				), $http );

				if ( isset( $request->data ) ) {

					foreach ( $request->data as $specialty ) {
						$new_specialties[ $specialty->value ] = strtolower( trim( $specialty->label ) );
					}

					if ( count( $request->data ) < $limit ) {
						// If the size of the result is less than the results per page
						// we got all the jobs, so end the loop
						break;
					} else {
						// Otherwise, increment the offset by the results per page, and re-run the loop.
						$offset += $limit;
					}
				} else {

					break;
				}
			}// while
			$specialties = array_unique( $new_specialties );
			set_transient( $transient, $specialties, HOUR_IN_SECONDS * 6 );
		}

		return $specialties;
	}


	/**
	 * Get Categories List
	 *
	 * Gets all the Categories terms.
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 3.5.0
	 */
	public function get_business_sectors_list() {

		$transient        = Matador::variable( 'bullhorn_business_sectors_cache', 'transients' );
		$business_sectors = get_transient( $transient );

		if ( ! $business_sectors ) {
			// Things we need
			$limit                = 300;
			$offset               = isset( $offset ) ? $offset : 0;
			$new_business_sectors = array();

			// API Method
			$method = 'options/BusinessSector';

			// HTTP Action
			$http = 'GET';

			while ( true ) {

				// Return Array
				$request = $this->request( $method, array(
					'count' => $limit,
					'start' => $offset,
				), $http );

				if ( isset( $request->data ) ) {

					foreach ( $request->data as $business_sector ) {
						$new_business_sectors[ $business_sector->value ] = strtolower( trim( $business_sector->label ) );
					}

					if ( count( $request->data ) < $limit ) {
						// If the size of the result is less than the results per page
						// we got all the jobs, so end the loop
						break;
					} else {
						// Otherwise, increment the offset by the results per page, and re-run the loop.
						$offset += $limit;
					}
				} else {
					// we got all the jobs, so end the loop
					break;
				}
			}// while
			$business_sectors = array_unique( $new_business_sectors );
			set_transient( $transient, $business_sectors, HOUR_IN_SECONDS * 6 );
		}

		return $business_sectors;
	}


	/**
	 * @param stdClass $candidate
	 *
	 * @return mixed
	 */
	public function delete_candidate(
		$candidate
	) {
		//TODO: add call to do this in bullhorn
		return $candidate;
	}

	/**
	 * Get Country ID from Name
	 *
	 *
	 */

	/**
	 * Get Countries
	 *
	 * Bullhorn stores country as an ID and not as a name.
	 * So we need to format country data into an array of
	 * IDs and names.
	 *
	 * @since 3.8.0
	 *
	 * @return array|boolean;
	 *
	 * @throws Exception
	 */
	public function get_countries() {

		$cache_key = 'matador_bullhorn_countries_list';
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		// API Method
		$request = 'options/Country';

		// API Call
		$response = $this->request( $request, [ 'count'  => '300' ] );

		if ( ! is_wp_error( $response ) ) {

			$country_list = array();
			foreach ( $response->data as $country ) {
				$country_list[ $country->value ] = $country->label;
			}
		} else {

			return false;

		}

		set_transient( $cache_key, $country_list, DAY_IN_SECONDS );

		return $country_list;
	}

}
