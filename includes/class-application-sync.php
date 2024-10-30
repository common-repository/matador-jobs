<?php
/**
 * Matador Submit Candidate
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

use DateTime;
use stdClass;
use matador\MatadorJobs\Email\ApplicationApplicantMessage;
use matador\MatadorJobs\Email\ApplicationRecruiterMessage;
use matador\MatadorJobs\Email\AdminNoticeGeneralMessage;
use matador\MatadorJobs\Email\AdminNoticeConsentObjectPermissionMessage;

class Application_Sync {

	/**
	 * Application ID
	 *
	 * ID of the WordPress Application Custom Post Type post.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	private $application_id;

	/**
	 * Application Data
	 *
	 * The application data object from the application.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $application_data;

	/**
	 * Application Sync Status
	 *
	 * The status of the application status.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $candidate_sync_status;

	/**
	 * Application Sync Step
	 *
	 * The step of the application sync for the current application status
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $candidate_sync_step;

	/**
	 * Candidate Bullhorn ID
	 *
	 * The ID of the Bullhorn Candidate Entity
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $candidate_bhid;

	/**
	 * Candidate Data
	 *
	 * The candidate data object from Bullhorn or created by this class
	 *
	 * @since 3.0.0
	 *
	 * @var stdClass
	 */
	private $candidate_data;

	/**
	 * Candidate Resume
	 *
	 * The parsed resume object returned from Bullhorn's resume parser
	 *
	 * @since 3.0.0
	 *
	 * @var stdClass
	 */
	private $candidate_resume;

	/**
	 * Constructor
	 * @since 3.0.0
	 *
	 * @param int $application_id
	 */
	public function __construct( $application_id = null ) {
        new ApplicationRecruiterMessage();
        new ApplicationApplicantMessage();

		if ( null !== $application_id && is_int( $application_id ) && get_post_status( intval( $application_id ) ) ) {
			$this->sync( $application_id );
		}
	}

	/**
	 * Sync Application
	 *
	 * Begins the sync of the application.
	 * @since 3.0.0
	 *
	 * @param int $application_id
	 *
	 * @return bool
	 */
	public function sync( $application_id ) {

		$this->application_id  = $application_id;
		$application_post_data = get_post_meta( $this->application_id, Matador::variable( 'application_data' ), true );

		if ( false !== $application_post_data && ! empty( $application_post_data ) ) {
			$this->application_data = (array) $application_post_data;
		} else {
			update_post_meta( $application_id, Matador::variable( 'candidate_sync_status' ), '3' );
			return false;
		}

		$this->candidate_sync_status = (array) get_post_meta( $this->application_id, Matador::variable( 'candidate_sync_status' ), true );
		$this->candidate_sync_step   = (array) get_post_meta( $this->application_id, Matador::variable( 'candidate_sync_step' ), true );

		return $this->add_bullhorn_candidate();
	}

	/**
	 * Clear Too Many Values
	 *
	 *
	 * @param array     $application
	 * @param \stdClass $candidate
	 * @param bool      $update
	 *
	 * @return \stdClass $candidate
	 */
	private static function clear_too_many_values( $application, stdClass $candidate, $update = false ) {
		// Skill List
		// @todo should not exist in core
		if ( isset( $application['skillList'] ) && ! empty( $application['skillList'] ) ) {

			if ( is_string( $application['skillList'] ) ) {
				$application['skillList'] = [ $application['skillList'] ];
			}

			if ( is_array( $application['skillList'] ) ) {

				foreach ( $application['skillList'] as $key => $value ) {

					$application['skillList'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->skillList ) || ! is_array( $candidate->skillList ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$candidate->skillList = $application['skillList']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->skillList = array_merge( $candidate->skillList, $application['skillList'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}
		}
		unset( $candidate->candidate->skillList );

		// Categories List
		// @todo should not exist in core
		if ( isset( $application['categories'] ) && ! empty( $application['categories'] ) ) {

			if ( is_string( $application['categories'] ) ) {
				$application['categories'] = [ $application['categories'] ];
			}

			if ( is_array( $application['categories'] ) ) {

				$application['categories'] = array_filter( $application['categories'] );

				foreach ( $application['categories'] as $key => $value ) {

					$application['categories'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->categories ) || ! is_array( $candidate->categories ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName

					$candidate->categories = $application['categories']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->categories = array_merge( $candidate->categories, $application['categories'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}

				if ( ! $update && ! empty( $candidate->categories ) && ! isset( $candidate->candidate->category ) ) {
					$candidate->candidate->category     = new stdClass();
					$candidate->candidate->category->id = array_shift( $candidate->categories );
				}
			}
		}
		unset( $candidate->candidate->categories );

		// businessSectors List
		// @todo should not exist in core
		if ( isset( $application['businessSectors'] ) && ! empty( $application['businessSectors'] ) ) {

			if ( is_string( $application['businessSectors'] ) ) {
				$application['businessSectors'] = [ $application['businessSectors'] ];
			}

			if ( is_array( $application['businessSectors'] ) ) {

				$application['businessSectors'] = array_filter( $application['businessSectors'] );

				foreach ( $application['businessSectors'] as $key => $value ) {

					$application['businessSectors'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->businessSectors ) || ! is_array( $candidate->businessSectors ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$candidate->businessSectors = $application['businessSectors']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->businessSectors = array_merge( $candidate->businessSectors, $application['businessSectors'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}
		}
		unset( $candidate->candidate->businessSectors );

		// specialties List
		// @todo should not exist in core
		if ( isset( $application['specialties'] ) && ! empty( $application['specialties'] ) ) {

			if ( is_string( $application['specialties'] ) ) {
				$application['specialties'] = [ $application['specialties'] ];
			}

			if ( is_array( $application['specialties'] ) ) {

				$application['specialties'] = array_filter( $application['specialties'] );

				foreach ( $application['specialties'] as $key => $value ) {

					$application['specialties'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->specialties ) || ! is_array( $candidate->specialties ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$candidate->specialties = $application['specialties']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->specialties = array_merge( $candidate->specialties, $application['specialties'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}
		}
		unset( $candidate->candidate->specialties );

		// primarySkills List
		// @todo should not exist in core
		if ( isset( $application['primarySkills'] ) && ! empty( $application['primarySkills'] ) ) {

			if ( is_string( $application['primarySkills'] ) ) {
				$application['primarySkills'] = [ $application['primarySkills'] ];
			}

			if ( is_array( $application['primarySkills'] ) ) {

				$application['primarySkills'] = array_filter( $application['primarySkills'] );

				foreach ( $application['primarySkills'] as $key => $value ) {

					$application['primarySkills'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->primarySkills ) || ! is_array( $candidate->primarySkills ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$candidate->primarySkills = $application['primarySkills']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->primarySkills = array_merge( $candidate->primarySkills, $application['primarySkills'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}
		}
		unset( $candidate->candidate->primarySkills );

		// secondarySkills List
		// @todo should not exist in core
		if ( isset( $application['secondarySkills'] ) && ! empty( $application['secondarySkills'] ) ) {

			if ( is_string( $application['secondarySkills'] ) ) {
				$application['secondarySkills'] = [ $application['secondarySkills'] ];
			}

			if ( is_array( $application['secondarySkills'] ) ) {

				$application['secondarySkills'] = array_filter( $application['secondarySkills'] );

				foreach ( $application['secondarySkills'] as $key => $value ) {

					$application['secondarySkills'][ $key ] = esc_html( $value );
				}

				if ( ! isset( $candidate->secondarySkills ) || ! is_array( $candidate->secondarySkills ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$candidate->secondarySkills = $application['secondarySkills']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				} else {
					$candidate->secondarySkills = array_merge( $candidate->secondarySkills, $application['secondarySkills'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}
		}
		unset( $candidate->candidate->secondarySkills );

        if ( isset( $application['certificationList'] ) && ! empty( $application['certificationList'] ) ) {

	        if ( is_string( $application['certificationList'] ) ) {
		        $application['certificationList'] = [ $application['certificationList'] ];
	        }

	        if ( is_array( $application['certificationList'] ) ) {

		        $application['certificationList'] = array_filter( $application['certificationList'] );

		        foreach ( $application['certificationList'] as $key => $value ) {
			        $application['certificationList'][ $key ] = esc_html( $value );
		        }
		        if ( empty( $candidate->certificationList ) || ! is_array( $candidate->certificationList ) ) {
			        $candidate->certificationList = $application['certificationList'];
		        } else {
			        $candidate->certificationList = array_merge( $candidate->certificationList, $application['certificationList'] );
		        }
	        }
        }
        unset( $candidate->candidate->certificationList );

		return $candidate;
	}

	/**
	 * Add Candidate from Resume
	 *
	 * This acts as an alternate constructor. Takes a file path to a resume/cv and
	 * sets up an alternate instance of the class to then run a candidate sync/submit routine.
	 *
	 * @param string $file_path
	 * @param int    $bhid
	 *
	 * @return stdClass
	 * @todo: this function needs to be removed after jobboard is refactored to have its applicant/candidate
	 * @todo: post type work like Matador's and WPJM's
	 * @since  3.5.0
	 *
	 */
	public function add_candidate_from_cv( $file_path, $bhid = null ) {
		$application['files']['resume']['path'] = $file_path;

		$this->application_data = (array) $application;

		if ( null !== $bhid ) {
			$this->candidate_bhid = $bhid;
		}

		$this->add_bullhorn_candidate();

		return $this->candidate_data;
	}

	/**
	 * Add Bullhorn Candidate
	 *
	 * Takes a candidate application and creates and submits a Bullhorn candidate
	 * @since 3.0.0
	 */
	public function add_bullhorn_candidate() {

		add_action( 'matador_log', array( $this, 'add_to_log' ), 10, 2 );

		/**
		 * Filter : Should Add Bullhorn Candidate
		 *
		 * Check if the application should processed (useful to bypass, ie: into a 3rd party first)
		 *
		 * @since 3.6.0
		 *
		 * @param bool  $should         default true
		 * @param int   $application_id WordPress application ID
		 * @param array $application    application data array
		 *
		 * @return bool
		 */
		if ( ! apply_filters( 'matador_application_sync_should_add_bullhorn_candidate', true, $this->application_id, $this->application_data ) ) {

			Logger::add( 'info', 'matador-app-sync-alternate', esc_html__( 'Application will not be synced to Bullhorn, but to 3rd-party application processor. Application ID: ', 'matador-jobs' ) . ' ' . $this->application_id );

			/**
			 * Filter : Sync Candidate Alternate Handler
			 *
			 * Behaves as an action, but returns boolean. True sets labels for user interface.
			 *
			 * @since 3.6.0
			 *
			 * @param bool  $should         default true
			 * @param int   $application_id WordPress application ID
			 * @param array $application    application data array
			 *
			 * @return bool
			 */
			if ( apply_filters( 'matador_application_sync_alternate_candidate', false, $this->application_id, $this->application_data ) ) {

				Logger::add( 'info', 'matador-app-sync-alternate-success', esc_html__( 'Application synced to 3rd-party application processor. Application ID: ', 'matador-jobs' ) . ' ' . $this->application_id );

				if ( Matador::setting( 'application_delete_local_on_sync' ) ) {
					Logger::add( 'info', 'matador-app-sync-alternate-success-delete', esc_html__( 'Application synced to 3rd-party application processor will now be deleted due to Privacy and Data Storage settings. Removing local candidate ', 'matador-jobs' ) . ' ' . $this->application_id );
					wp_delete_post( $this->application_id, true );
				} else {
					$this->candidate_sync_status = '1';
					$this->save_data();
				}

			} else {
				Logger::add( 'info', 'matador-app-sync-alternate-failure', esc_html__( 'Application to 3rd party application processor failed.', 'matador-jobs' ) );

				$this->candidate_sync_status = '3';
				$this->save_data();
			}

			remove_action( 'matador_log', array( $this, 'add_to_log' ), 10 );

			return;
        }

		try {

			Logger::add( 'info', 'matador-app-sync-start', esc_html__( 'Starting application sync for local candidate', 'matador-jobs' ) . ' ' . $this->application_id );

			$bullhorn = new Bullhorn_Candidate();

			// Create Resume Object from Application Data
			$this->candidate_sync_step = 'get-resume';
			$this->candidate_resume    = self::create_resume( $bullhorn, $this->application_data );

			$this->candidate_sync_step = 'check-can-sync';
			if ( self::can_application_sync() ) {

				/**
				 * Filter : Matador Submit Candidate Check for Existing
				 *
				 * True/false filter to check if the Submit Candidate process should check for an existing candidate or
				 * not, which could result in duplicate candidates.
				 *
				 * @since 3.0.0
				 *
				 * @param string|bool
				 */
				if ( ! empty( $this->candidate_sync_step ) && apply_filters( 'matador_submit_candidate_check_for_existing', Matador::setting( 'applications_sync_check_for_existing' ) ) ) {
					// Check if this candidate already exists, get its Bullhorn Candidate ID
					if ( isset( $this->application_data['email'] ) && isset( $this->application_data['name']['lastName'] ) ) {
						$this->candidate_bhid = $bullhorn->find_candidate( $this->application_data['email'], $this->application_data['name']['lastName'] );
					}
				}

				if ( ! empty( $this->candidate_bhid ) ) {

					// Start Updating Existing Candidate
					$this->candidate_sync_step = 'existing-start';
					Logger::add( 'info', 'matador-app-existing-found', esc_html__( 'Found and updating existing remote candidate', 'matador-jobs' ) . ' ' . $this->candidate_bhid );

					// Fetch Existing Candidate Data
					$this->candidate_sync_step = 'existing-fetch';
					$this->candidate_data      = $bullhorn->get_candidate( $this->candidate_bhid );

					if ( 'Private' === $this->candidate_data->candidate->status && ! $bullhorn->has_private_candidate_entitlement() ) {

						$this->candidate_sync_step   = null;
						$this->candidate_sync_status = '6';
						$this->save_data();

						$error      = __( 'A "private" candidate applied for a role and the API user is unable modify "Private" candidates to update the record or submit them for a role.', 'matador-jobs' );
						$error_more = __( 'You must manually access their application in Matador Jobs and submit them for the position. Also, you should contact Bullhorn to grant your API User access to modify private candidates.', 'matador-jobs' );

						Logger::add( 'info', 'matador-app-sync-private_candidate', esc_html( $error . ' ( ID:' . $this->application_id . ')' ) );
						AdminNoticeGeneralMessage::message( [
							'error' => $error . ' ' . $error_more,
							'force' => true,
						] );

					} else {

						// Update Candidate from Submitted Data
						$this->candidate_sync_step = 'existing-update';
						$this->candidate_data      = self::update_candidate( $this->candidate_data, $this->application_data, $this->candidate_resume );

						// remove Last Modified from data returned data
						unset( $this->candidate_data->candidate->dateLastModified );

						// Save Updated Candidate
						$this->candidate_sync_step = 'existing-candidate-save';

						$success = $bullhorn->save_candidate( $this->candidate_data );

						if ( false === $success ) {
							if ( $this->candidate_data ) {
								Logger::add( 'info', 'update-candidate-failed-but-recovered', esc_html__( 'An existing remote candidate was not updated due to a previously logged error. Will continue without update.', 'matador-jobs' ) );
							} else {
								throw new Exception( 'error', 'update-candidate-failed', esc_html__( 'An existing remote candidate update failed and Matador is unable to continue.', 'matador-jobs' ) );
							}
						}
					}

					$this->candidate_sync_step = 'existing-candidate-complete';

				} else {

					// Start Creating a Candidate
					$this->candidate_sync_step = 'creating-start';
					Logger::add( 'info', 'matador-app-existing-not-found', esc_html__( 'An existing remote candidate was not found. Will create a new remote candidate', 'matador-jobs' ) );

					// Create Candidate Object from Resume and Application Data
					$this->candidate_sync_step = 'creating-candidate-object';
					$this->candidate_data      = self::create_candidate( $this->candidate_resume, $this->application_data );

					// Save Candidate to Bullhorn
					$this->candidate_sync_step = 'creating-candidate-save';

					$success = $bullhorn->save_candidate( $this->candidate_data );

					if ( false === $success ) {
						throw new Exception( 'error', 'create-candidate', esc_html__( 'Failed to create candidate', 'matador-jobs' ) );
					} else {
						$this->candidate_data = $success;
					}

					if ( isset( $this->candidate_data->id ) ) {

						Logger::add( 'info', 'matador-app-new-created', esc_html__( 'A new remote candidate was created as', 'matador-jobs' ) . ' ' . $this->candidate_data->id );
					}

					$this->candidate_sync_step = 'creating-candidate-complete';
				}

				// Add/update Candidate Education
				$this->candidate_sync_step = 'creating-candidate-education';
				$bullhorn->save_candidate_education( $this->candidate_data );

				// Add/update Candidate Work History
				$this->candidate_sync_step = 'creating-candidate-work-history';
				$bullhorn->save_candidate_work_history( $this->candidate_data );

				// Add/update Candidate Categories
				$this->candidate_sync_step = 'creating-candidate-categories';
				$bullhorn->save_candidate_categories( $this->candidate_data );

				// Add/update Candidate Skills
				$this->candidate_sync_step = 'creating-candidate-primary_skills';
				$bullhorn->save_candidate_primary_skills( $this->candidate_data );

				// Add/update Candidate Skills
				$this->candidate_sync_step = 'creating-candidate-secondary_skills';
				$bullhorn->save_candidate_secondary_skills( $this->candidate_data );

				// Add/update Candidate Secondary Owners
				$this->candidate_sync_step = 'creating-candidate-secondary_owners';
				$bullhorn->save_candidate_secondary_owners( $this->candidate_data );

				// Add/update Candidate Specialties
				$this->candidate_sync_step = 'creating-candidate-specialties';
				$bullhorn->save_candidate_specialties( $this->candidate_data );

				// Add/update Candidate Business Sectors
				$this->candidate_sync_step = 'creating-candidate-business_sectors';
				$bullhorn->save_candidate_business_sectors( $this->candidate_data );

				// Add/update Candidate Certifications
				$this->candidate_sync_step = 'creating-candidate-certification-list';
                $bullhorn->save_candidate_certification_list ( $this->candidate_data );

				// Save Message, if any
				$this->candidate_sync_step = 'save-message';
				if ( isset( $this->application_data['message'] ) ) {

					$bullhorn->save_candidate_note( $this->candidate_data, $this->application_data['message'], $this->application_data );
				}

				// Save Files, if any
				$this->candidate_sync_step = 'save-files';
				$this->save_candidate_files( $this->candidate_data, $this->application_data, $bullhorn );

				// Save Jobs Applied To, if any
				$this->candidate_sync_step = 'save-jobs';
				$this->save_candidate_jobs( $this->candidate_data, $this->application_data, $bullhorn );

				// Do Custom Actions
				$this->candidate_sync_step = 'do-custom-actions';
				do_action( 'matador_bullhorn_candidate', $this->application_id, $this->application_data, $this->candidate_data, $this->candidate_resume, $bullhorn );

				// If we got this far, clear the steps, update the status, and save the meta
				$this->candidate_sync_step   = null;
				$this->candidate_sync_status = '1';
				$this->save_data();
				Logger::add( 'info', 'matador-app-sync-complete', esc_html__( 'Completed application sync for local candidate', 'matador-jobs' ) . ' ' . $this->application_id );

				if ( Matador::setting( 'application_delete_local_on_sync' ) ) {
					Logger::add( 'info', 'matador-app-sync-complete', esc_html__( 'Now will delete local candidate due to Privacy and Data Storage settings. Removing local candidate ', 'matador-jobs' ) . ' ' . $this->application_id );
					wp_delete_post( $this->application_id, true );
				}
			} else {

				$this->candidate_sync_step   = null;
				$this->candidate_sync_status = '5';
				$this->save_data();
				Logger::add( 'info', 'matador-app-sync-insufficient', esc_html__( 'Application cannot sync due to too little data.', 'matador-jobs' ) . ' ' . $this->application_id );

			}
		} catch ( Exception $e ) {

			if ( false !== strpos(  $e->getMessage(), 'customObjectFieldPermission' ) ) {

				// Re-mark Status As Pending
				$this->candidate_sync_status = '-1';

				// Save Any Changes
				$this->save_data();

				// Set a 24-Hour Skip For Saving To Consent Object
				set_transient( Matador::variable( 'bullhorn_consent_object_skip', 'transients' ), true, 24 * 60 * MINUTE_IN_SECONDS );

				// Send email notifying customer of issue.
				new AdminNoticeConsentObjectPermissionMessage();

				// Log
				// Translators: Placeholder 1 is for the WPID of the Application.
				$message = __( 'Application sync failed for local candidate %1$s due to Consent Object permissions error. Will retry on next sync. Starting 24 hour candidate consent data skip.',  'matador-jobs' );
				Logger::add( 'info', 'matador_bullhorn_consent_object_failed_start', esc_html( sprintf( $message, $this->application_id ) ) );

			} elseif ( false !== strpos( $e->getMessage(), 'error persisting an entity of type: Candidate' ) ) {

				$this->candidate_sync_status = '10'; // @todo: This status is clear. Unrecoverable
				$this->save_data();

				Logger::add(
					'info',
					'matador-app-sync-fail-permanently',
					esc_html( sprintf(
					// Translators: Placeholder 1 is for the WPID of the Application.
						__(
							'Application sync failed for local candidate %1$s failed permanently. Data must be manually submitted to Bullhorn.',
							'matador-jobs'
						),
						$this->application_id
					) )
				);

			} else {

				$this->candidate_sync_status = '3'; // @todo: This status is not clear. Can we determine if the failure is recoverable?
				$this->save_data();
				Logger::add(
					'info',
					'matador-app-sync-fail',
					esc_html( sprintf(
					// Translators: Placeholder 1 is for the WPID of the Application.
						__(
							'Application sync failed for local candidate %1$s. Data must be manually submitted to Bullhorn.',
							'matador-jobs'
						),
						$this->application_id
					) )
				);
			}
		}

		remove_action( 'matador_log', array( $this, 'add_to_log' ), 10 );
		return true;
	}



	/**
	 * Save Application Data
	 *
	 * Updates the Application post type post with updated post meta from this process.
	 * @since 3.0.0
	 */
	private function save_data() {
		foreach ( array( 'application_data', 'candidate_bhid', 'candidate_data', 'candidate_resume', 'candidate_sync_status', 'candidate_sync_step' ) as $saveable ) {
			if ( isset( $this->{$saveable} ) && ! empty( $this->{$saveable} ) ) {
				update_post_meta( $this->application_id, Matador::variable( $saveable ), $this->{$saveable} );
			} else {
				delete_post_meta( $this->application_id, Matador::variable( $saveable ) );
			}
		}
	}

	/**
	 * Can Application Sync
	 *
	 * An application needs at least a last name and email. If a resume exists, check that the resume
	 * has those two fields. If not, we'll check if that information was submitted with the form.
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	private function can_application_sync() {
		// Check the Resume
		if (
			! empty( $this->candidate_resume )
			&& ! empty( $this->candidate_resume->candidate->lastName )
			&& ! empty( $this->candidate_resume->candidate->email )
		) {
			return true;
		}
		// Check the Application
		if (
			! empty( $this->application_data )
			&& ( ! empty( $this->application_data['name'] ) || ! empty( $this->application_data['lastName'] ) )
			&& ! empty( $this->application_data['email'] )
		) {
			return true;
		}
		// Got this far, the answer is no.
		return false;
	}

	/**
	 * Save Candidate Files
	 *
	 * Saves all the files in an Application array to Bullhorn.
	 * @since 3.0.0
	 *
	 * @param stdClass $candidate
	 * @param array $application
	 * @param Bullhorn_Candidate $bullhorn
	 *
	 * @throws Exception
	 *
	 * @todo handle Exception
	 */
	private function save_candidate_files( $candidate, $application, $bullhorn ) {
		if ( isset( $application['files'] ) ) {
			foreach ( $application['files'] as $id => $file ) {
				if ( ! empty( $file['path'] ) && empty( $file['synced'] ) ) {
					if ( $bullhorn->save_candidate_file( $candidate, $file['path'], $id ) ) {
						$this->application_data['files'][ $id ]['synced'] = 1;
					}
				}
			}
		}
	}

	/**
	 * Submit Candidate to Jobs
	 *
	 * Loops through all jobs in the Application
	 * @since 3.0.0
	 *
	 * @param stdClass $candidate
	 * @param array $application
	 * @param Bullhorn_Candidate $bullhorn
	 *
	 * @throws Exception
	 *
	 * @todo handle Exception
	 */
	private function save_candidate_jobs( $candidate, $application, $bullhorn ) {
		if ( isset( $application['jobs'] ) ) {
			foreach ( $application['jobs'] as $key => $job ) {
				if ( isset( $job['bhid'] ) && is_numeric( $job['bhid'] ) && ! empty( $job['synced'] ) ) {
					$success = $bullhorn->submit_candidate_to_job( $candidate, (int) $job['bhid'], $this->application_data );
					if ( false !== $success ) {
						Logger::add( 'info', 'matador-app-sync-application_linked', esc_html__( 'Linked candidate to an application with the Bullhorn ID of', 'matador-jobs' ) . ' ' . $job['bhid'] . ' ' . __( 'and Submission ID', 'matador-jobs' ) . ' ' . $success );
						$application['jobs'][ $key ]['synced'] = 1;
					}
				}
			}
		} elseif ( Matador::setting( 'applications_backup_job' ) ) {
			$success = $bullhorn->submit_candidate_to_job( $candidate, (int) Matador::setting( 'applications_backup_job' ), $this->application_data );
			if ( false !== $success ) {
				Logger::add( 'info', 'matador-app-sync-application_linked', esc_html__( 'Linked candidate to Default application with Bullhorn ID', 'matador-jobs' ) . ' ' . Matador::setting( 'applications_backup_job' ) );
			}
		}
	}

	/**
	 * Create Resume
	 *
	 * Sends a file to Bullhorn for resume parsing, ideally returning a parsed JSON object.
	 * @since 3.0.0
	 *
	 * @param Bullhorn_Candidate $bullhorn
	 * @param array $application
	 *
	 * @return stdClass
	 *
	 * @throws Exception
	 *
	 * @todo Handle Exception
	 */
	public static function create_resume( $bullhorn, $application ) {

		$resume = false;

		if ( Matador::setting( 'bullhorn_process_resumes' ) ) {

			if ( ! empty( $application['files']['resume'] ) && is_array( $application['files']['resume'] ) ) {

				$file = $application['files']['resume'];

				if ( ! empty( $file['path'] ) ) {

					$resume = $bullhorn->parse_resume( $file['path'] );

				}
			}

			if ( ! $resume && ! empty( $application['resume'] ) ) {
				$resume = $bullhorn->parse_resume( null, $application['resume'] );
				if ( ! $resume ) {
					Logger::add( 'error', 'bullhorn-application-processing-resume-error', __( 'Unprocessable resume.', 'matador-jobs' ) );
				}
			}

			if ( ! $resume ) {
				$text_resume = apply_filters( 'matador_submit_candidate_text_resume', '', $application );
				if ( ! empty( $text_resume ) ) {
					$resume = $bullhorn->parse_resume( null, $text_resume );
				}
			}

			if ( $resume && ! is_object( $resume ) ) {
				Logger::add( 'error', 'bullhorn-application-processing-error', __( 'Error on resume process from Bullhorn: ', 'matador-jobs' ) . print_r( $resume['error'], true ) );
				$resume = false;
			} elseif ( ! $resume ) {
				Logger::add( 'error', 'bullhorn-application-processing-error', __( 'No resume for applicant.', 'matador-jobs' ) );
			}
		}

		return $resume;
	}

	/**
	 * Create Candidate
	 *
	 * Creates a candidate object from the resume results (if any) and the application data
	 * @since 3.0.0
	 *
	 * @param stdClass $resume
	 * @param array $application
	 *
	 * @return stdClass|bool
	 */
	public static function create_candidate( $resume = null, $application = null ) {

		if ( ! is_array( $application ) ) {
			return false;
		}

		$candidate = ! empty( $resume ) ? $resume : new stdClass();

		$candidate->candidate = ! empty( $candidate->candidate ) ? $candidate->candidate : new stdClass(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->candidateWorkHistory = ! empty( $candidate->candidateWorkHistory ) ? $candidate->candidateWorkHistory : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->candidateEducation = ! empty( $candidate->candidateEducation ) ? $candidate->candidateEducation : array();  // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->skillList = ! empty( $candidate->skillList ) ? $candidate->skillList : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->categories = ! empty( $candidate->categories ) ? $candidate->categories : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->businessSectors = ! empty( $candidate->businessSectors ) ? $candidate->businessSectors : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->specialties = ! empty( $candidate->specialties ) ? $candidate->specialties : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->primarySkills = ! empty( $candidate->primarySkills ) ? $candidate->primarySkills : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->secondarySkills = ! empty( $candidate->secondarySkills ) ? $candidate->secondarySkills : array(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$candidate->candidate = self::candidate_name( $candidate->candidate, $application );

		$candidate->candidate = self::candidate_email( $candidate->candidate, $application );

		$candidate->candidate = self::candidate_phone( $candidate->candidate, $application );

		$candidate->candidate = self::candidate_address( $candidate->candidate, $application );

		$candidate->candidate = self::candidate_comments( $candidate->candidate, $application );

        $candidate->candidate = self::candidate_consent( $candidate->candidate, $application );

		$candidate->candidate = self::candidate_owner( $candidate->candidate, $application );

		$candidate = self::candidate_secondary_owners( $candidate, $application );

		unset( $candidate->candidate->editHistoryValue ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		unset( $candidate->candidate->smsOptIn ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		/**
		 * Matador Data Source Description
		 *
		 * Adjusts the text description for the source of the job submission. Default is "{Site Name} Website", ie:
		 * "ACME Staffing Website". Use the $entity argument to narrow the modification to certain entities.
		 *
		 * @since 3.1.1
		 * @since 3.4.0 added $data parameter
		 * @since 3.5.0 added $submission parameter
		 *
		 * @var string   $source     The value for Source. Limit of 200 characters for Candidates, 100 for
		 *                           JobSubmissions. Default is the value of the WordPress "website name" setting.
		 * @var string   $context    Limit scope of filter in filtering function
		 * @var stdClass $data.      The associated data with the $context. Should not be used without $context first.
		 * @var array    $submission The associated data with the $context's submission.
		 *
		 * @return string The modified value for Source. Warning! Limit of 200 characters for Candidates, 100 for JobSubmissions.
		 */
		$candidate->candidate->source = substr( apply_filters( 'matador_data_source_description', get_bloginfo( 'name' ), 'candidate', $candidate->candidate, $application ), 0, 200 );

		$status = 'New Lead';

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
		/**
		 * Matador Data Status Description
		 *
		 * Adjusts the value of the status for the Bullhorn data item. IE: "New Lead"
		 *
		 * @since 3.5.1
		 *
		 * @var string    $status     The value of status. Set initially by default or by settings.
		 * @var string    $entity     Limit scope of filter in to an entity
		 * @var \stdClass $data.      The associated data with the $context. Should not be used without $context first.
		 * @var array     $submission The associated data with the $context's submission.
		 *
		 * @return string             The filtered value of status.
		 */
		$candidate->candidate->status = apply_filters( 'matador_data_source_status', $status, 'candidate', $candidate->candidate, $application );

		/**
		 * Matador Submit Candidate Candidate Data Filter
		 *
		 * Modify the Candidate Object following parsing.
		 *
		 * @since 3.4.0
		 *
		 * @param stdClass $candidate
		 * @param array $application
		 * @param string $action 'create' or 'update' if you want to limit to certain changes
		 */
		$candidate->candidate = apply_filters( 'matador_submit_candidate_candidate_data', $candidate->candidate, $application, 'create' );

		$candidate = self::clear_too_many_values( $application, $candidate );

		return $candidate;
	}

	/**
	 * Update Candidate
	 *
	 * Updates the retrieved Candidate object with information from the application.
	 * @since 3.0.0
	 *
	 * @param stdClass $candidate
	 * @param array $application
	 * @param stdClass $resume
	 *
	 * @return stdClass
	 */
	private static function update_candidate( $candidate = null, $application = null, $resume = null ) {

		if ( ! $candidate || ! $application || ! is_array( $application ) ) {
			return $candidate;
		}

		$candidate->candidate = self::candidate_email( $candidate->candidate, $application );
		$candidate->candidate = self::candidate_phone( $candidate->candidate, $application );
		$candidate->candidate = self::candidate_address( $candidate->candidate, $application );
		$candidate->candidate = self::candidate_comments( $candidate->candidate, $application );
        $candidate->candidate = self::candidate_consent( $candidate->candidate, $application );

        if ( $resume && isset( $resume->candidate->description ) && ! empty( $resume->candidate->description ) ) {
			$candidate->candidate->description = $resume->candidate->description;
		} else {
			// @TODO: TEMPORARY WORK-AROUND
	        unset( $candidate->candidate->description );
        }

		/**
		 * Matador Submit Candidate Candidate Data Filter
		 *
		 * Modify the Candidate Object following parsing.
		 *
		 * @since 3.4.0
		 *
		 * @param stdClass $candidate
		 * @param array $application
		 * @param string $action 'create' or 'update' if you want to limit to certain changes
		 */
		$candidate->candidate = apply_filters( 'matador_submit_candidate_candidate_data', $candidate->candidate, $application, 'update' );

		$candidate = self::clear_too_many_values( $application, $candidate, true );

		return $candidate;
	}

	/**
	 * Candidate Name
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $person
	 * @param array $application
	 *
	 * @return stdClass
	 */
	private static function candidate_name( $person = null, $application = null ) {

		if ( $person && is_array( $application ) ) {

			if ( isset( $application['name'] ) && ! empty( $application['name'] ) ) {

				$name = $application['name'];

				if ( isset( $name['namePrefix'] ) ) {
					$person->namePrefix = $name['namePrefix']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				if ( isset( $name['firstName'] ) ) {
					$person->firstName = $name['firstName']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				if ( isset( $name['lastName'] ) ) {
					$person->lastName = $name['lastName']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				if ( isset( $name['middleName'] ) ) {
					$person->middleName = $name['middleName']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				if ( isset( $name['suffix'] ) ) {
					$person->nameSuffix = $name['suffix']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				if ( isset( $name['fullName'] ) ) {
					$person->name = $name['fullName'];
				}

				// trim the fields to max lengths
				if ( isset( $person->namePrefix ) ) {
					$person->namePrefix = substr( $person->namePrefix, 0, 5 );
				}
				if ( isset( $person->firstName ) ) {
					$person->firstName = substr( $person->firstName, 0, 50 );
				}
				if ( isset( $person->lastName ) ) {
					$person->lastName = substr( $person->lastName, 0, 50 );
				}
				if ( isset( $person->middleName ) ) {
					$person->middleName = substr( $person->middleName, 0, 50 );
				}
				if ( isset( $person->nameSuffix ) ) {
					$person->nameSuffix = substr( $person->nameSuffix, 0, 5 );
				}
				if ( isset( $person->name ) ) {
					$person->name = substr( $person->name, 0, 100 );
				}
			}
		}

		return $person;
	}

	/**
	 * Candidate Email
	 *
	 * @since 3.0.0
	 * @since 3.5.2 Check that the value returned from sanitize is valid
	 *
	 * @param stdClass $person
	 * @param array $application
	 *
	 * @return stdClass
	 */
	private static function candidate_email( $person = null, $application = null ) {

		if ( ! $person ) {

			return $person;
		}

		if ( empty( $application ) || ! is_array( $application ) ) {

			return $person;
		}

		if ( empty( $application['email'] ) || ! is_string( $application['email'] ) ) {

			return $person;
		}

		$proposed = sanitize_email( $application['email'] );

		if ( empty( $proposed ) ) {

			return $person;
		}

		$existing1 = isset( $person->email ) ? sanitize_email( $person->email ) : null;
		$existing2 = isset( $person->email2 ) ? sanitize_email( $person->email2 ) : null;

		if ( $existing1 && $existing2 ) {
			if ( $existing2 === $proposed ) {
				$person->email2 = $person->email;
				$person->email  = $proposed;
			} elseif ( $existing1 !== $proposed ) {
				$person->email3 = $person->email2;
				$person->email2 = $person->email;
				$person->email  = $proposed;
			}
		} else if ( $existing1 && $existing1 !== $proposed ) {
			$person->email2 = $person->email;
			$person->email  = $proposed;
		} else {
			$person->email = $proposed;
		}

		$person->email = substr( $person->email, 0, 100 );

		if ( ! empty ( $person->email2 ) ) {
			$person->email2 = substr( $person->email2, 0, 100 );
		}

		if ( ! empty ( $person->email3 ) ) {
			$person->email3 = substr( $person->email3, 0, 100 );
		}

		return $person;
	}

	/**
	 * Candidate Phone
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $person
	 * @param array $application
	 *
	 * @return stdClass
	 */
	private static function candidate_phone( $person = null, $application = null ) {

		if ( $person && is_array( $application ) ) {

			if ( isset( $application['phone'] ) && ! empty( $application['phone'] ) ) {

				$existing1 = isset( $person->phone ) ? preg_replace( '~\D~', '', $person->phone ) : null;
				$existing2 = isset( $person->phone2 ) ? preg_replace( '~\D~', '', $person->phone2 ) : null;
				$proposed  = preg_replace( '~\D~', '', $application['phone'] );

				if ( $existing1 && $existing2 ) {
					if ( $existing2 === $proposed ) {
						$person->phone2 = $person->phone;
						$person->phone  = esc_attr( $application['phone'] );
					} elseif ( $existing1 !== $proposed ) {
						$person->phone3 = $person->phone2;
						$person->phone2 = $person->phone;
						$person->phone  = esc_attr( $application['phone'] );
					}
				} elseif ( $existing1 && $existing1 !== $proposed ) {
					$person->phone2 = $person->phone;
					$person->phone = esc_attr( $application['phone'] );
				} else {
					$person->phone = esc_attr( $application['phone'] );
				}
			}

			if ( isset( $application['work_phone'] ) ) {

				$person->workPhone = esc_attr( $application['work_phone'] ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			}

			if ( isset( $application['mobile_phone'] ) ) {

				$person->mobile = esc_attr( $application['mobile_phone'] );
			}
		}

		if ( isset( $person->phone ) ){
			if ( is_array( $person->phone ) ) {
				$person->phone = substr( $person->phone[0], 0, 20 );
			} else {
				$person->phone = substr( $person->phone, 0, 20 );
			}
		}
		if ( isset( $person->phone2 ) ){
			if ( is_array( $person->phone2 ) ) {
				$person->phone2 = substr( $person->phone2[0], 0, 20 );
			} else {
				$person->phone2 = substr( $person->phone2, 0, 20 );
			}
		}
		if ( isset( $person->mobile ) ){
			if ( is_array( $person->mobile ) ) {
				$person->mobile = substr( $person->mobile[0], 0, 20 );
			} else {
				$person->mobile = substr( $person->mobile, 0, 20 );
			}
		}
		if ( isset( $person->workPhone ) ){
			if ( is_array( $person->workPhone ) ) {
				$person->workPhone = substr( $person->workPhone[0], 0, 20 );
			} else {
				$person->workPhone = substr( $person->workPhone, 0, 20 );
			}
		}

		return $person;
	}

	/**
	 * Candidate Address
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $person
	 * @param array $application
	 *
	 * @return stdClass
	 */
	private static function candidate_address( $person, $application ) {
		if ( $person && is_array( $application ) ) {

			// Address fields and field limits
			$address_fields = array(
				// field => character limit
				'address1'    => 40,
				'address2'    => 40,
				'city'        => 40,
				'state'       => 30,
				'zip'         => 15,
				'countryName' => 99,
			);

			/**
			 * @wordpress-filter Applicant CountryID Default
			 *
			 * Bullhorn requires a countryID for an address, but the resume processor will return no Country in many
			 * cases, especially when resumes are from US, Canadian, and Australian users (who would not have a country
			 * in their address by default, unlike EU applicants). Many new Bullhorn databases will have countryID 1 be
			 * the country of domicile of the firm, but not always.
			 *
			 * As a fallback, we set 1 just in case to prevent an error, but if it is disrupting a user's flow to have
			 * applicants set to the wrong country, they can determine the database ID of their preferred default
			 * country and add a filter for this value.
			 *
			 * @since 3.8.8
			 *
			 * @param int $countryID
			 *
			 * @return int
			 */
			$default_countryID = apply_filters( 'matador_bullhorn_applicant_countryID_default', 1 );

			// Checks if a user inputted address field exists, and sets our test to true.
			foreach ( $address_fields as $key => $unused ) {
				if ( isset( $application[ $key ] ) && ! empty( $application[ $key ] ) ) {
					$application_has_address = true;
				}
			}

			// We have user inputted address
			if ( isset( $application_has_address ) ) {

				if ( isset( $person->address ) && ! empty( $person->address ) ) {

					$person->secondaryAddress = $person->address; // phpcs:ignore WordPress.NamingConventions.ValidVariableName

				} else {

					$person->address            = new stdClass();
					$person->address->countryID = $default_countryID;

				}

				foreach ( $address_fields as $key => $length ) {
					if ( isset( $application[ $key ] ) && ! empty( $application[ $key ] ) ) {
						$person->address->{$key} = $application[ $key ];
					} else {
						unset( $person->address->{$key} );
					}
				}
			}

			// Truncate address values.
			foreach ( $address_fields as $key => $length ) {
				if ( isset( $person->address->$key ) ) {
					$person->address->$key = substr( $person->address->$key, 0, $length );
				}
				if ( isset( $person->secondaryAddress->$key ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$person->secondaryAddress->$key = substr( $person->secondaryAddress->$key, 0, $length ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
			}

			// Countries MUST have a countryID.
			// 0 will literally break Bullhorn!
			// Null won't process.
			if ( empty( $person->address->countryID ) ) {
				if ( ! isset( $person->address ) || ! is_object( $person->address ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$person->address = new stdClass();
				}
				$person->address->countryID = $default_countryID;
			}
			if ( isset( $person->secondaryAddress ) && empty( $person->secondaryAddress->countryID ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				if ( ! isset( $person->secondaryAddress ) || ! is_object( $person->secondaryAddress ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName
					$person->secondaryAddress = new stdClass(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
				}
				$person->secondaryAddress->countryID = $default_countryID;
			}
		}

		return $person;
	}

	/**
	 * Candidate Comments
	 *
	 * @since 3.0.0
	 *
	 * @param stdClass $person
	 * @param array $application
	 *
	 * @return stdClass
	 */
	private static function candidate_comments( $person = null, $application = null ) {
		if ( $person && $application ) {

			$comments = '';

			if ( isset( $application['message'] ) && ! empty( $application['message'] ) ) {

				/**
				 * Matador Submit Candidate Notes Message Prefix
				 *
				 * Modify the label for the candidate message that prepends it before being saved as a note.
				 *
				 * @since 3.4.0
				 *
				 * @param string $label the text that comes before the "Message" field on a form response.
				 */
				$label = apply_filters( 'matador_submit_candidate_notes_message_label', __( 'Message: ', 'matador-jobs' ) );

				$comments .= esc_html( PHP_EOL . $label . $application['message'] );
			}

			if ( isset( $application['job']['title'] ) && ! empty( $application['job']['title'] ) ) {

				/**
				 * Matador Submit Candidate Notes Message Prefix
				 *
				 * Modify the label for the candidate jobs that prepends it before being saved as a note.
				 *
				 * @since 3.4.0
				 *
				 * @param string $label the text that comes before the "Job" field on a form response.
				 */
				$label = apply_filters( 'matador_submit_candidate_notes_job_label', __( 'Applied via the website for this position: ', 'matador-jobs' ) );

				$value = $application['job']['title'];

				if ( isset( $application['job']['bhid'] ) && is_numeric( $application['job']['bhid'] ) ) {
					$value .= sprintf( ' (Bullhorn Job ID: %s)', $application['job']['bhid'] );
				}

				$comments .= esc_html( $label . $value . PHP_EOL );
			}

			if ( ! empty( $comments ) ) {

				if ( isset( $person->comments ) && ! empty( $person->comments ) ) {

					$person->comments .= $comments;

				} else {

					$person->comments = $comments;

				}
			}
		}

		return $person;
	}

    /**
     * Candidate Owner
     *
     * This function should set the job owner based on the settings OR form data. Assumption shall be
     * that form data will override settings.
     *
     * @since 3.8.0
     *
     * @param stdClass $person
     * @param array $application Array of structured, sanitized, validated data
     *
     * @return stdClass $person             Amended array of structured, sanitized, validated data
     */
    private static function candidate_owner( $person, $application ) {

		$owner = new stdClass();

		// First, check to see if there is a form value under 'owner'.

	    if ( ! empty( $application['owner'] ) && is_numeric( $application['owner'] ) ) {
			$owner->id = (int) $application['owner'];
	    }

		// Second, assign owner from applied job

		if ( ! isset( $owner->id ) && isset( $application['jobs'][0]['wpid'] ) ) {

			$option = matador::setting('bullhorn_candidate_owner' ) ?: 'api';

			/**
			 * Filter: Should Set Owners from Job Users
			 *
			 * When an application is associated with a job, Matador can associate the job's owner or responseUser
			 * automatically as owner for the candidate if a form field submission for owner was not otherwise provided.
			 *
			 * Note: Normally, we should filter options from the per-option magic filter, which is
			 * `matador_options_get_$key` but in this case, since the option is experimental and may not be on a user's
			 * site, we will use the option, if set, as the default state of the flag.
			 *
			 * @since   3.8.0
			 *
			 * @param string $should Default 'api'. Picks up value of setting, if set.
			 * @param array  $application The submitted application object.
			 * @param stdClass $person The person part of the candidate object (so far)
			 *
			 * @return string Return 'owner', 'responseUser' to impact output, 'api' or anything else to not.
			 */
			$option = apply_filters( 'matador_submit_candidate_should_set_owner_from_job_users', $option, $application, $person );

			if ( ! is_string( $option ) ) {
				$option = 'api';
			}

			$job_wpid = (int) $application['jobs'][0]['wpid'];

			switch ( $option ) {
                case 'owner':

					$job_owner = get_post_meta( $job_wpid, 'owner', true );

					if ( isset( $job_owner->id ) ) {
						$owner->id = absint( $job_owner->id );
                    }

                    break;
                case 'response':

					$job_response_user = get_post_meta( $job_wpid, 'responseUser', true );

					if ( isset( $job_response_user->id ) ) {
						$owner->id = absint( $job_response_user->id );
                    }

                    break;
                case 'api':
                default:
                    // Will use default;
            }
        }

		// Finally, run a filter.

        /**
         * Filter: Submit Candidate Owner
         *
         * Set (or override) values established for secondaryOwners from either the submission or settings.
         *
         * @since   3.8.0
         *
         * @param stdClass $owner       Defaults to empty stdClass, or if the value has been set, will have an `id` property.
         * @param array    $application The current Application
         * @param stdClass $person      The current person details
         *
         * @return stdClass Either empty stdClass, null, or, if value, stdClass with `id` property that is an int.
         */
        $owner = apply_filters( 'matador_submit_candidate_owner', $owner, $application, $person );

		// In order to check if the stdClass has value, we can cast it
	    // (and null) to an array and run empty() on it.
		if ( ! empty( (array) $owner ) ) {

            $person->owner = $owner;
        }

        return $person;
    }

    /**
     * Candidate Secondary Owners
     *
     * This function can set the candidate secondary owners based on form data, settings, or filters.
     *
     * @since 3.8.0
     *
     * @param stdClass $candidate
     * @param array    $application Array of structured, sanitized, validated data
     *
     * @return stdClass $person             Amended array of structured, sanitized, validated data
     */
    private static function candidate_secondary_owners( $candidate, $application ) {

		$secondary_owners = [];

        if ( ! empty( $application['secondaryOwners'] ) && is_array( $application['secondaryOwners'] ) ) {

			foreach ( $application['secondaryOwners'] as $key => $value ) {
                $application['secondaryOwners'][ $key ] = absint( $value );
            }

	        $secondary_owners = $application['secondaryOwners'];
        }

        /**
         * Filter: Should Set secondaryOwners from assignedUsers (when form data is not present)
         *
         * When an application is associated with a job, Matador can associate the job's assignedUsers automatically as
         * secondaryOwners for the candidate if a form field submission for SecondaryOwners was not otherwise provided.
         *
         * @since   3.8.0
         *
         * @param boolean $should Default false. True will trigger the routine.
         * @param array $application The submitted application object.
         * @param stdClass $candidate The candidate object (so far)
         *
		 * @return bool
         */
		$set_secondaryOwners_from_assignedUsers = apply_filters( 'matador_submit_candidate_should_set_secondaryOwners_from_assignedUsers', false, $application, $candidate );

		if ( empty( $secondary_owners ) && $set_secondaryOwners_from_assignedUsers ) {

			if ( isset( $application['jobs'][0]['wpid'] ) ) {

				$job_assigned_users = get_post_meta( $application['jobs'][0]['wpid'], 'assignedUsers', true );

				if ( $job_assigned_users && isset( $job_assigned_users->total ) && $job_assigned_users->total > 0 ) {
					foreach ( $job_assigned_users->data as $owner ) {
						$secondary_owners[] = $owner->id;
					}
				}

            }
        }

	    /**
	     * Filter: Candidate Secondary Owners
	     *
	     * Set (or override) values established for secondaryOwners from either the submission or settings.
	     *
	     * @since   3.8.0
	     *
	     * @param array    $secondary_owners Array (indexed) of IDs of Bullhorn Users to assign as secondaryOwners
	     * @param array    $application      The submitted application object.
	     * @param stdClass $candidate        The Bullhorn candidate object
	     *
	     * @return array
	     */
	    $secondary_owners = apply_filters( 'matador_submit_candidate_secondaryOwners', $secondary_owners, $application, $candidate );

		// Final check that all values are unique (and refresh the index with array_values).
	    $secondary_owners = array_values( array_unique( $secondary_owners ) );

		// If there is nothing, we should make sure we unset it.
		if ( ! empty ( $secondary_owners ) ) {
			$candidate->secondaryOwners = $secondary_owners;
		}

	    return $candidate;
    }

    /**
     * Create Candidate Consent
     *
     * Create object(s) to give to the GDPR Bullhorn custom object that manages candidate consent
     *
     * @since 3.6.0
     *
     * @param stdClass $person
     * @param array $application
     *
     * @return stdClass
     */
    private static function candidate_consent( $person = null, $application = null ) {

	    if ( ! $person || ! $application ) {

	        return $person;
	    }

	    if ( get_transient( Matador::variable( 'bullhorn_consent_object_skip', 'transients' ) ) ) {

			Logger::add( 'info', 'matador_bullhorn_consent_object_skipped', esc_html__( 'Skipping candidate consent data save.',  'matador-jobs' ) );

		    return $person;
	    }

	    // Create variables for use.
	    $consents = array();

	    // If the application requires consent, the application is rejected
	    // in processing. This check at sync only determines if the candidate
	    // should have the consent object created.
	    if ( '1' === Matador::setting( 'application_privacy_field' ) ) {

	    	$privacy = new stdClass();

		    $description = esc_html__( 'Candidate accepted Privacy Policy.', 'matador-jobs' );

		    // Add IP address to Description, if Available
		    if ( ! empty( $application['ip'] ) ) {
		    	$description .= PHP_EOL . sprintf( ' (Logged IP address: %s)', esc_attr( $application['ip'] ) );
		    }

		    // Create the consent object for Privacy Policy Acceptance
		    //
		    // Date last sent is 'date1'
		    $privacy->date1      = (int) ( microtime( true ) * 1000 );
		    // Date last received is 'date2'
		    $privacy->date2      = (int) ( microtime( true ) * 1000 );
		    // Description is 'textBlock1'
		    $privacy->textBlock1 = $description;
		    // Purpose is 'text1'
		    $privacy->text1      = esc_html__( 'Recruiting', 'matador-jobs' );
		    // Legal Basis is 'text2'
		    $privacy->text2      = esc_html__( 'Legal Obligation', 'matador-jobs' );
		    // Source is 'text3'
		    $privacy->text3      = esc_html__( 'Web Response', 'matador-jobs' );

		    /**
		     * Matador Applicant Candidate Privacy Consent
		     *
		     * Modify the custom object that we save when a candidate agree to privacy policy in the application form.
		     *
		     * @since 3.6.0
		     *
		     * @param stdClass $privacy consent objects
		     * @param array    $application the incoming application data
		     * @param stdClass $person the Person data set so far.
		     *
		     * @return stdClass modified Consent object
		     */
		    $privacy = apply_filters( 'matador_applicant_candidate_privacy_consent_object', $privacy, $application, $person );

		    // Push the object into an array of consent objects
		    $consents[] = $privacy;
		}

	    // If the application requires consent, the application is rejected
	    // in processing. This check at sync only determines if the candidate
	    // should have the consent object created.
	    if ( '1' === Matador::setting( 'application_terms_field' ) ) {

		    $terms = new stdClass();

		    $description = esc_html__( 'Candidate accepted Terms of Service.', 'matador-jobs' );

		    // Add IP address to Description, if Available
		    if ( ! empty( $application['ip'] ) ) {
			    $description .= PHP_EOL . sprintf( '(IP address: %s)', esc_attr( $application['ip'] ) );
		    }

		    // Create the consent object for Privacy Policy Acceptance
		    //
		    // Date last sent is 'date1'
		    $terms->date1      = (int) ( microtime( true ) * 1000 );
		    // Date last received is 'date2'
		    $terms->date2      = (int) ( microtime( true ) * 1000 );
		    // Description is 'textBlock1'
		    $terms->textBlock1 = $description;
		    // Purpose is 'text1'
		    $terms->text1      = esc_html__( 'Recruiting', 'matador-jobs' );
		    // Legal Basis is 'text2'
		    $terms->text2      = esc_html__( 'Contract Necessity', 'matador-jobs' );
		    // Source is 'text3'
		    $terms->text3      = esc_html__( 'Web Response', 'matador-jobs' );

		    /**
		     * Matador Applicant Candidate Terms of Service Consent
		     *
		     * Modify the custom object that we save when a candidate agree to Terms of Service in an application form.
		     *
		     * @since 3.8.0
		     *
		     * @param stdClass $terms consent objects
		     * @param array    $application the incoming application data
		     * @param stdClass $person the Person data set so far.
		     *
		     * @return stdClass modified Consent object
		     */
		    $terms = apply_filters( 'matador_applicant_candidate_terms_consent_object', $terms, $application, $person );

		    // Push the object into an array of consent objects
		    $consents[] = $terms;
	    }

	    /**
	     * Matador Applicant Candidate Consents
	     *
	     * Modify the array of consent custom objects. Use this to add additional/custom consents, ie: consent to get
	     * email updates.
	     *
	     * @since 3.6.0
	     *
	     * @param stdClass $consents array of consent objects
	     * @param array    $application the incoming application data
	     * @param stdClass $person the Person data set so far.
	     *
	     * @return stdClass modified array of Consent objects
	     */
	    $consents = apply_filters( 'matador_applicant_candidate_consents_data', $consents, $application, $person );

	    // If there is no privacy policy or custom consents, exit
	    if ( empty( $consents ) ) {

	    	return $person;
	    }

	    /**
	     * Matador Applicant Candidate Consent Object Name
	     *
	     * The API call to automatically locate the Bullhorn Candidate Consents object is taxing. To improve performance
	     * you may manually set this value, provided you confidently know its value. The value will be something like
	     * 'customObject1s'
	     *
	     * @since 3.6.0
	     *
	     * @param string $consent_object_location default empty string
	     *
	     * @return string in the format of customObject{$i}s where $i is 1-10
	     */
	    $consent_object_location = apply_filters( 'matador_applicant_candidate_consent_object_name', '' );

	    $consent_object_location = $consent_object_location ?: get_transient( Matador::variable( 'consent_object' , 'transients' ) );

	    if ( false === $consent_object_location ) {

	    	try {
			    $bullhorn = new Bullhorn_Connection();
			    $consent_object_location = $bullhorn->get_consent_object_name();
		    } catch ( \Exception $e ) {

	    		return $person;
		    }
	    }

	    if ( ! empty( $consent_object_location ) && false !== $consent_object_location ) {
		    $person->$consent_object_location = $consents;
	    }

        return $person;
    }

	/**
	 * Add to Application Sync Log
	 * @since 3.0.0
	 *
	 * @param string $level
	 * @param string $message
	 */
	public function add_to_log( $level, $message ) {

		unset( $level ); // until PHPCS 3.4+

		if ( null === $this->application_id ) {
			$this->application_id = get_the_ID() ?: intval( $_GET['sync'] );
		}

		$log = get_post_meta( $this->application_id, Matador::variable( 'candidate_sync_log' ), true );

		$now = new DateTime();

		$append = PHP_EOL . $now->format( 'Y-m-d H:i:s: ' ) . $message;

		$updated = $log . $append;

		update_post_meta( $this->application_id, Matador::variable( 'candidate_sync_log' ), $updated );
	}
}
