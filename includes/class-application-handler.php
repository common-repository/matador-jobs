<?php
/**
 * Matador / Application Handler
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 *
 * @docs action / filter
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\MatadorJobs\Email\AdminNoticeGeneralMessage;
use matador\MatadorJobs\Email\ApplicationApplicantMessage;
use matador\MatadorJobs\Email\ApplicationRecruiterMessage;

class Application_Handler {

	/**
	 * Variable: Request
	 *
	 * Holds the $_REQUEST global after nonce was checked.
	 *
	 * @var array $request
	 *
	 * @since 1.0.0
	 */
	private $request;

	/**
	 * Variable: Files
	 *
	 * Holds the $_FILES global after nonce was checked.
	 *
	 * @var array $files
	 *
	 * @since 1.0.0
	 */
	private $files;

	/**
	 * Variable: Used
	 *
	 * Keeps track of form fields used by various processes so the
	 * final catch-all can sweep up and include all miscellanous custom
	 * fields.
	 *
	 * @var array $used
	 *
	 * @since 1.0.0
	 */
	private $used;

	/**
	 * Class Constructor
	 *
	 * Sets up an instance of the class for use in processing a form.
	 *
	 * @since  1.0.0
	 * @param array $application
	 *
	 * @return void
	 */
	public function __construct( $application = null ) {
        new ApplicationRecruiterMessage();
        new ApplicationApplicantMessage();

		if ( ! $application ) {

			$nonce_key = Matador::variable( 'application', 'nonce' );

			if ( isset( $_REQUEST[ $nonce_key ] ) && wp_verify_nonce( $_REQUEST[ $nonce_key ], $nonce_key ) ) {
				/**
				 * Action: Matador Application Handler Start
				 *
				 * Actions to be performed prior to handling an Application
				 *
				 * @since 3.3.0
				 *
				 * @param array $_REQUEST
				 */
				do_action( 'matador_application_handler_start', $_REQUEST );

				$ignored = array( 'matador_application', '_wp_http_referer', 'submit' );

				/**
				 * Filter: Matador Application Handler Start Ignored Fields
				 *
				 * Allows developers to designate fields to ignore in Matador's Application Handler
				 *
				 * @since 3.3.0
				 *
				 * @var array $ignored
				 */
				$ignored = apply_filters( 'matador_application_handler_start_ignored_fields', $ignored );

				$this->add_used_fields( $ignored );

				/**
				 * Filter: Application Form Raw data
				 *
				 * @since   3.0.0
				 *
				 * @var array $application_data
				 */
				$this->request = apply_filters( 'matador_application_data_raw', $_REQUEST );

				$this->files = $_FILES;

			} else {
				Logger::add( 2, 'nonce-failed', __( 'Application save: nonce failed', 'matador-jobs' ) );
				wp_die( 'Sorry, your nonce did not verify.' );
			}
		} else {

			/**
			 * Filter: Application Form Raw data
			 *
			 * @since   3.0.0
			 *
			 * @var array $application_data
			 */
			$this->request = apply_filters( 'matador_application_data_raw', $application );
		}
	}

	/**
	 * Apply
	 *
	 * This function is called on the submit of a POST request to the /application
	 * endpoint. This function handles the validation and formatting of the POST data
	 * into usable pieces that are then saved to the Application post type in the local
	 * database.
	 *
	 * @since  1.0.0
	 *
	 * @return integer $application_id
	 */
	public function apply() {

		if ( false === $this->request ) {

			return false;
		}

		// @todo fix checkbox problem and remove this hacky solution
		if (
			'1' === Matador::setting( 'application_privacy_field' )
			&& ! (
				isset( $this->request['privacy_policy_opt_in'] )
				&& in_array( 1, array_map( 'intval', array_values( $this->request['privacy_policy_opt_in'] ) ) )
			)
		) {

			return false;
		}

		// @todo fix checkbox problem and remove this hacky solution
		if (
			'1' === Matador::setting( 'application_terms_field' )
			&& ! (
				isset( $this->request['terms_of_service_opt_in'] )
				&& in_array( 1, array_map( 'intval', array_values( $this->request['terms_of_service_opt_in'] ) ) )
			)
		) {

			return false;
		}

		$this->add_used_fields( 'privacy_policy_opt_in', 'terms_of_service_opt_in' );

		$processed_data = $this->process_application();

		/**
		 * Filter: Application data after processing before saving
		 *
		 * @since 3.0.0
		 *
		 * @deprecated 3.7.0 please use 'matador_application_data_processing' instead, as it activates earlier
		 *
		 * @param array $processed_data
		 * @param array $raw_data
		 */
		$application = apply_filters( 'matador_application_data_processed', $processed_data, $this->request );// Process Application

		// Save Application
		return $this->save_application( $application );
	}

	/**
	 * Process Submitted Data
	 *
	 * Processes raw form data into a structured array of
	 * sanitized and validated data.
	 * @since 3.0.0
	 *
	 * @return bool|array $application sanitized, validated, structured data
	 */
	public function process_application() {

		$submitted = $this->request;
		$files     = $this->files;

		// Instantiate the Output Array
		$application = array(
			'application'  => array(),
			'post_content' => '',
		);

		$application = $this->get_user_ip( $application );
		$application = $this->get_type_from_form( $submitted, $application );
		$application = $this->get_name_from_form( $submitted, $application );
		$application = $this->get_bullhorn_fields_from_form( $submitted, $application );
		$application = $this->get_jobs_from_form( $submitted, $application );

		/**
		 * Filter : Matador Application Data Processing
		 *
		 * @since 3.7.0
		 *
		 * @param array $application
		 * @param array $request
		 * @param Application_Handler $this
		 *
		 * @return array
		 */
		$application = apply_filters( 'matador_application_data_processing', $application, $submitted, $this );

		// Cleanup, catches everything else not caught and sanitized already
		$application = $this->get_message_and_other_fields_from_form( $submitted, $application );

		if ( ! empty( $files ) ) {
			try {
				$application = $this->get_submitted_files( $files, $application );
			} catch ( Exception $e ) {
				return false;
			}
		}

		return $application;
	}

	/**
	 * Add User IP to Application Data
	 * @since 3.4.0
	 *
	 * @param array $application
	 * @return array $application Application Data
	 */
	private function get_user_ip( $application ) {

		if ( empty( $application ) ) {
			wp_die( 'get_name_from_form Function called improperly' );
		}
		$application['application']['ip'] = Helper::get_user_ip();
		return $application;
	}

	/**
	 * Save Application
	 *
	 * Creates an application post type object from structured, sanitized,
	 * validated data.
	 *
	 * @param  array $application Processed application data
	 *
	 * @return integer $application_id WP ID of saved application
	 *
	 * @since 1.0.0
	 */
	private function save_application( $application ) {

		/**
		 * Filter: Application post args
		 *
		 * @since   3.0.0
		 *
		 * @param array $processed_data
		 * @param array $raw_data
		 */
		$insert_post_args = apply_filters( 'matador_application_post_args', array(
			'post_title'   => self::get_application_post_title( $application ),
			'post_content' => $application['post_content'],
			'post_type'    => Matador::variable( 'post_type_key_application' ),
			'post_author'  => 1,
			'post_status'  => 'publish',
			'meta_input'   => array(
				Matador::variable( 'application_data' )      => $application['application'],
				Matador::variable( 'candidate_sync_status' ) => -1,
				Matador::variable( 'submission_type' )       => ! empty( $application['type'] ) ? $application['type'] : 'application',
			),
		), $application, $this->request );// Create post object

		$wp_id = wp_insert_post( $insert_post_args );

		if ( ! is_wp_error( $wp_id ) || 0 !== $wp_id ) {

			$recent_applications = get_transient( Matador::variable( 'analytics_recent_applications', 'transients' ) );

			set_transient( Matador::variable( 'analytics_recent_applications', 'transients' ), ( $recent_applications ? ++$recent_applications : 1 ), DAY_IN_SECONDS );

			do_action( 'matador_new_job_application', $wp_id, $application['application'] );

		} else {
			Logger::add( 'error', 'matador-app-candidate-save-failed', esc_html__( 'The save of a new application failed. The data is: ', 'matador-jobs' ) . print_r( $application, true ) );

			do_action( 'matador_new_job_application_failed', $insert_post_args );

			AdminNoticeGeneralMessage::message( [
				'error' => esc_html__( 'A candidate application could not be saved to WordPress for an unknown reason. The data is: ', 'matador-jobs' ) . print_r( $application, true ),
				'force' => true,
			] );
		}

		return $wp_id;
	}

	/**
	 * Add Used Fields
	 *
	 * Appends the class instance variable $used with new
	 * fields that are consumed by application processing
	 * functions. This allows the $used array to be checked
	 * against when Application::get_message_and_other_fields_from_form
	 * is called;
	 *
	 * @param  array|string ..| any number of string or array arguments.
	 *
	 * @since 1.0.0
	 */
	public function add_used_fields() {
		$added = func_get_args();
		foreach ( $added as $field ) {
			if ( is_string( $field ) ) {
				$this->used[] = $field;

			} elseif ( is_array( $field ) ) {
				foreach ( $field as $included ) {
					$this->used[] = $included;
				}
			}
		}
	}

	/**
	 * Get Submission Type
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Will now set "application" as the default type whenever no form value is present.
	 *
	 * @param array $submitted
	 * @param array $application
	 *
	 * @return array
	 */
	private function get_type_from_form( $submitted = [], $application = [] ) {

		if ( empty( $submitted ) || empty( $application ) ) {
			wp_die( 'get_type_from_form Function called improperly' );
		}

		if ( ! empty( $submitted['type'] ) ) {
			$application['type'] = $submitted['type'];
			$this->add_used_fields( 'type' );
		} else {
			$application['type'] = 'application';
		}

		return $application;
	}

	/**
	 * Get Name From Submitted
	 *
	 * Site operators have two ways to create their application form when it comes to the
	 * applicant's name. One is the "simple" method, which uses one field for a person's
	 * name. Should they want greater control/greater option for their users, they may
	 * opt for the "complex" name, which presents their user with the following fields:
	 * firstName, middleName, lastName, nameSuffix, and namePrefix. This function
	 * determines which of the two were chosen, and regardless of the method, returns
	 * an array with a name split up into pieces as Bullhorn requires.
	 *
	 * @param array $submitted Nonce-checked raw form data
	 * @param array $application Array of structured, sanitized, validated data
	 *
	 * @return array             Amended array of structured, sanitized, validated data
	 *
	 * @since 1.0.0
	 */
	public function get_name_from_form( $submitted = null, $application = null ) {

		if ( empty( $submitted ) || empty( $application ) ) {
			wp_die( 'get_name_from_form Function called improperly' );
		}

		$name = array();

		if ( ! empty( $submitted['name'] ) ) {

			$name = $this->parse_single_name_field( $submitted['name'] );

		} else {

			if ( ! empty( $submitted['namePrefix'] ) ) {

				$name['namePrefix'] = sanitize_text_field( $submitted['namePrefix'] );
			}
			if ( ! empty( $submitted['firstName'] ) ) {

				$name['firstName'] = sanitize_text_field( $submitted['firstName'] );
			}
			if ( ! empty( $submitted['middleName'] ) ) {

				$name['middleName'] = sanitize_text_field( $submitted['middleName'] );
			}
			if ( ! empty( $submitted['lastName'] ) ) {

				$name['lastName'] = sanitize_text_field( $submitted['lastName'] );
			}
			if ( ! empty( $submitted['nameSuffix'] ) ) {

				$name['nameSuffix'] = sanitize_text_field( $submitted['nameSuffix'] );
			}

			if ( ! empty( $name ) ) {
				$name['fullName'] = trim( implode( ' ', array_filter( $name ) ) );
			}
		}

		if ( ! empty( $name ) ) {
			$application['application']['name'] = $name;
			//translators: Label for a person\'s name. ie: "Name: John Doe"
			$label = esc_html__( 'Name', 'matador-jobs' );
			$application['post_content']        = $label . ': ' . $name['fullName'] . PHP_EOL . PHP_EOL;
		}

		$this->add_used_fields( 'name', 'firstName', 'middleName', 'lastName', 'namePrefix', 'nameSuffix' );

		return $application;

	}

	/**
	 * Parse Single Name Field
	 *
	 * When a site operator decided to provide a single name field
	 * this function will parse the name into a first, last, and
	 * also potentially middle and suffix name fields.
	 *
	 * @param  string $raw_name Raw name to process.
	 *
	 * @return array            Array of name parts.
	 *
	 * @since 1.0.0
	 */
	private function parse_single_name_field( $raw_name = null ) {

		if ( empty( $raw_name ) || ! is_string( $raw_name ) ) {
			return array();
		}

		$name = array();

		// Remove security escaping, ie O'Reilly as O\'Reilly, remove extra whitespace, sanitize
		$raw_name = preg_replace( '/\s+/', ' ', sanitize_text_field( stripslashes( $raw_name ) ) );

		$all_parts  = explode( ', ', trim( esc_attr( $raw_name ) ) );
		$name_parts = explode( ' ', trim( $all_parts[0] ) );
		$suffixes   = array_slice( $all_parts, 1 );

		$name['firstName'] = $name_parts[0];

		$allowed_suffixes = Matador::variable( 'application_name_suffixes' );

		if ( 2 < count( $name_parts ) ) {
			if ( in_array( strtolower( end( $name_parts ) ), $allowed_suffixes, true ) ) {
				$suffixes[] = end( $name_parts );
				if ( 3 < count( $name_parts ) ) {
					$name['middleName'] = $name_parts[1];
				}
				$name['lastName'] = $name_parts[ count( $name_parts ) - 2 ];
			} else {
				$name['middleName'] = $name_parts[1];
				$name['lastName']   = end( $name_parts );
			}
		} else {
			$name['lastName'] = end( $name_parts );
		}

		if ( ! empty( $suffixes ) ) {
			foreach ( $suffixes as $suffix ) {
				$name['suffix'] = isset( $name['suffix'] ) ? $name['suffix'] . ', ' . strtoupper( $suffix ) : strtoupper( $suffix );
			}
		}

		$name['fullName'] = trim( implode( ' ', array_filter( $name ) ) );

		return $name;
	}

	/**
	 * Get Bullhorn Standard Fields from Form
	 *
	 * There are a number fields site operators may include
	 * in their forms that Bullhorn considers a standard field.
	 * This will map them to Bullhorn's structure.
	 *
	 * @param array $submitted Nonce-checked raw form data
	 * @param array $application Array of structured, sanitized, validated data
	 *
	 * @return array             Amended array of structured, sanitized, validated data
	 *
	 * @since 1.0.0
	 */
	public function get_bullhorn_fields_from_form( $submitted = null, $application = null ) {

		if ( ! $submitted || ! $application ) {
			wp_die( 'get_bullhorn_fields_from_form Function called improperly' );
		}

		// @todo filter name should be underscored.
		$bullhorn_fields = array_unique( apply_filters( 'matador-fields-to-add-to-application', array(
			'email',
			'mobile',
			'phone',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'occupation',
			'companyName',
		) ) );

		// Grab The Fields Structure So We Can Use the Label
		$structure = self::application_fields_structure();

		foreach ( $bullhorn_fields as $key ) {

			if ( array_key_exists( $key, $submitted ) ) {

				if ( is_array( $submitted[ $key ] ) ) {

					$submitted[ $key ] = array_filter( $submitted[ $key ], function( $value ) {
						// removes null and empty string,
						// allows explicit false and 0 through
						return ! is_null( $value ) && $value !== '';
					} );

					if ( empty( $submitted[ $key ] ) ) {

						continue;
					}

					$labels = array();

					foreach ( $submitted[ $key ] as $value ) {
						/**
						 * Filter: Application content message values
						 *
						 * Filter and change the label for the value. Useful for select, multiselect, checkbox, and
						 * radio form field types where the value passed to the form is not human-useful, ie, most
						 * checkboxes pass "1" for true, but to a human you might want "Accepted". Affects saved summary
						 * in Application Post Type content and affects email content.
						 *
						 * @since   3.5.0
						 *
						 * @param string $label Current label, which often defaults to the current value.
						 * @param string $key Key for field.
						 * @param string $value The current value.
						 */
						$labels[] = apply_filters( 'matador_application_content_line_item', $value, $key, $value );
					}

					$application['application'][ $key ] = array_map( 'sanitize_text_field', $submitted[ $key ] );

					$submit_text = implode( ', ', $labels );

				} elseif ( 'email' === $submitted[ $key ] ) {

					$submit_text = apply_filters( 'matador_application_content_line_item', $submitted[ $key ], $key, $submitted[ $key ] );

					$application['application'][ $key ] = sanitize_email( $submitted[ $key ] );

				} elseif ( true === $submitted[ $key ] || false === $submitted[ $key ] ) {

					$submit_text = apply_filters( 'matador_application_content_line_item', $submitted[ $key ], $key, $submitted[ $key ] );

					$application['application'][ $key ] = $submitted[ $key ] ? 1 : 0;

				} elseif ( null === $submitted[ $key ] || ( is_string( $submitted[ $key ] ) && empty( $submitted[ $key ] ) ) ) {

					continue;

				} else {

					$submit_text = apply_filters( 'matador_application_content_line_item', $submitted[ $key ], $key, $submitted[ $key ] );

					$application['application'][ $key ] = is_numeric( $submitted[ $key ] ) ? trim( $submitted[ $key ] ) : sanitize_text_field( $submitted[ $key ] );
				}

				/**
				 * Filter: Application Transcript Include Field
				 *
				 * Determines whether the application line should be included in the transcript (which is used for email
				 * and other uses)
				 *
				 * @since 3.8.0
				 *
				 * @param bool   $should    Default true
				 * @param string $key       The form field key name.
				 * @param string $submitted The data provided in the submission.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'matador_application_transcript_include_field', true, $key, $submit_text ) ) {

					$label = ( isset( $structure[ $key ] ) ? $structure[ $key ] : $structure[ $key ]['label'] ) ? $structure[ $key ]['label'] : $key;
					// $label = ( $structure[ $key ] ?? $structure[ $key ]['label'] ) ? $structure[ $key ]['label'] : $key;

					/**
					 * Filter: Application Content Message Line Label
					 *
					 * Filter and change the label used in the 'content' of the application post type post.
					 *
					 * @since   3.7.0
					 * @since   3.8.0 The filtered value was given pre-processing prior to the filter, which might make some
					 *                old filter calls that did an exact check will now fail.
					 *
					 * @param string $label Current label
					 * @param string $key Key for field.
					 * @param string $submit_text Text linked to the label.
					 */
					$label = apply_filters( 'matador_application_content_line_label', $label, $key, $submit_text );

					$application['post_content'] .= $label . ': ' . $submit_text . PHP_EOL . PHP_EOL;
				}
			}
		}

		$this->add_used_fields( $bullhorn_fields );

		return $application;
	}

	/**
	 * Get the Job Details from Form
	 *
	 * Site operators have several ways to pass the job variable to the form. Check for the ways
	 * and return job data as appropriate.
	 *
	 * @param array $submitted Nonce-checked raw form data
	 * @param array $application Array of structured, sanitized, validated data
	 *
	 * @return array             Amended array of structured, sanitized, validated data
	 *
	 * @since 1.0.0
	 */
	private function get_jobs_from_form( $submitted, $application ) {

		if ( ! $submitted || ! $application ) {
			wp_die( 'get_jobs_from_form Function called improperly' );
		}

		// Save the Jobs to the Application
		if ( ! empty( $submitted['wpid'] ) && is_numeric( $submitted['wpid'] ) ) {
			$job_listing = get_post( $submitted['wpid'] );
			if ( is_object( $job_listing ) ) {
				$job = array(
					'title' => $job_listing->post_title,
					'wpid'  => $job_listing->ID,
					'bhid'  => ( isset( $submitted['bhid'] ) ) ? $submitted['bhid'] : Helper::the_job_bullhorn_id( (int) $submitted['wpid'] ),
				);
				if ( ! empty( $job['bhid'] ) ) {
					$job['synced'] = 'Pending';
				} else {
					$job['synced'] = 'Not Applicable';
				}
				$application['application']['jobs'][] = $job;
				$application['post_content']         .= self::get_job_info_as_string( $job );
			}
		} elseif ( ! empty( $submitted['bhid'] ) && is_numeric( $submitted['bhid'] ) ) {
			$job_listing = Helper::get_post_by_bullhorn_id( $submitted['bhid'] );
			if ( is_object( $job_listing ) ) {
				$job                                  = array(
					'title' => $job_listing->post_title,
					'wpid'  => $job_listing->ID,
				);
				$job['bhid']                          = (int) $submitted['bhid'];
				$job['synced']                        = 'Pending';
				$application['application']['jobs'][] = $job;
				$application['post_content']         .= self::get_job_info_as_string( $job );
			}
		} elseif ( ! empty( $submitted['jobs'] ) && is_array( $submitted['jobs'] ) ) {
			foreach ( $submitted['jobs'] as $wpid ) {
				$job_listing = get_post( $wpid );
				if ( is_object( $job_listing ) ) {
					$job = array(
						'title' => $job_listing->post_title,
						'wpid'  => $job_listing->ID,
						'bhid'  => (int) get_post_meta( $job_listing->ID, '_matador_source_id', true ),
					);
					if ( ! empty( $job['bhid'] ) ) {
						$job['synced'] = 'Pending';
					} else {
						$job['synced'] = 'Not Applicable';
					}
					$application['application']['jobs'][] = $job;
					$application['post_content']         .= self::get_job_info_as_string( $job );
				}
				unset( $job );
			}
		} elseif ( ! empty( $submitted['request'] ) ) {
			$application['post_content'] .= esc_html__( 'Custom Job Request', 'matador-jobs' ) . sanitize_title( $submitted['request'] );
		}

		$this->add_used_fields( 'bhid', 'wpid', 'jobs', 'request' );

		return $application;
	}

	/**
	 * Get the Job Details from Form
	 *
	 * Takes a job array as created by the function Class::Get_Jobs_From_Form and returns as string that
	 * can be appended to the Application post content.
	 *
	 * @param  array $job Job array created by class::get_jobs_from_form
	 *
	 * @return string       String to append to $application['post_content'] with applied job information.
	 * @see                 Application_Handler::get_jobs_from_form
	 *
	 * @since 1.0.0
	 */
	private static function get_job_info_as_string( $job = null ) {
		if ( empty( $job ) ) {
			return '';
		}
		$job_title = esc_html__( 'Job Applied For', 'matador-jobs' ) . ': ' . $job['title'];

		if ( ! empty( $job['wpid'] ) || ! empty( $job['bhid'] ) ) {
			$job_title_suffix  = isset( $job['bhid'] ) ? 'BHID: ' . $job['bhid'] : null;
			$job_title_suffix .= ( isset( $job_title_suffix ) && isset( $job['wpid'] ) ) ? ' | ' : '';
			$job_title_suffix .= isset( $job['wpid'] ) ? 'WPID: ' . $job['wpid'] : '';
			$job_title         = sprintf( '%s ( %s )', $job_title, $job_title_suffix ) . PHP_EOL . PHP_EOL;
		}

		return $job_title;
	}

	/**
	 * Get Message and Misc Fields from Form
	 *
	 * Site operators may add a number of non-Bullhorn fields at their discretion through
	 * filters, etc. This final function sweeps up remaining fields and appends them to a
	 * messages field.
	 *
	 * @since 3.0.0
	 * @since 3.8.10 Prior behavior collected every non-registered form field, which became problematic as anti-spam
	 *               scripts (and malware) began appending hidden fields to forms. Now only registered fields will be
	 *               included.
	 *
	 * @param array $submitted Nonce-checked raw form data
	 * @param array $application Array of structured, sanitized, validated data
	 *
	 * @return array             Amended array of structured, sanitized, validated data
	 */
	private function get_message_and_other_fields_from_form( $submitted, $application ) {

		if ( ! $submitted || ! $application ) {
			wp_die( 'get_message_and_other_fields_from_form Function called improperly' );
		}

		$notes  = [];
		$fields = self::application_fields_structure();

		if ( ! empty( $submitted['message'] ) ) {
			/**
			 * Matador Submit Candidate Notes Message Label
			 *
			 * Modify the label for the candidate message that prepends it before being saved as a note.
			 *
			 * @since 3.4.0
			 *
			 * @param  string $label the text that comes before the "Message" field on a form response.
			 * @return string $label
			 */
			$label   = apply_filters( 'matador_submit_candidate_notes_message_label', __( 'Message: ', 'matador-jobs' ) );
			$notes[] = $label . esc_html( wp_strip_all_tags( $submitted['message'] ) );
			$this->add_used_fields( 'message' );
		}
		foreach ( $submitted as $key => $field ) {

			if ( ! array_key_exists( $key, self::application_fields_structure() ) ) {

				continue;
            }

			if ( ! in_array( $key, $this->used, true ) && ! empty( $field ) ) {
				$line_item = '';
				if ( is_array( $field ) ) {
					foreach ( $field as $item ) {
						if ( ! empty( $item ) ) {
							$line_item = isset( $line_item ) ? $line_item . ', ' . $item : $item;
						}
					}
				} else {
					$line_item = $field;
				}
				$line_label = isset( $fields[ $key ]['label'] ) ? $fields[ $key ]['label'] : ucwords( $key );
				/**
				 * Filter: Application note message content entry label
				 *
				 * @since   3.4.1
				 *
				 * @param string $line_label
				 * @param string $key
				 * @param mixed  $field
				 */
				$line_label = apply_filters( 'matador_application_note_line_label', $line_label, $key, $field );
				/**
				 * Filter: Application note message content
				 *
				 * @since   3.4.1
				 *
				 * @param string $line_item
				 * @param string $key
				 * @param mixed  $field
				 */
				$line_item = apply_filters( 'matador_application_note_line_item', $line_item, $key, $field );

				$notes[] = $line_label . ': ' . $line_item;
				unset( $line_item );
				unset( $line_label );
			}
		}

		if ( ! empty( $notes ) ) {
			/**
			 * Filter: Application note message content
			 *
			 * @since   3.0.0
			 *
			 * @param array $notes
			 */
			$application['application']['message'] = '</p> <p>' . implode( '.</p> <p>', apply_filters( 'matador_application_note_content', $notes ) );
			$application['post_content']          .= implode( " \n\n" . PHP_EOL, $notes ) . PHP_EOL;
		}

		return $application;
	}

	/**
	 * Get Submitted FilesFiles
	 *
	 * Site operators may allow users to upload a resume and cover letter file
	 * by default, and via settings and filters, any number of additional files.
	 * This function saves them and indexes them on the application array.
	 *
	 * @param array $files Nonce-checked raw form data
	 * @param array $application Array of structured, sanitized, validated data
	 *
	 * @return array             Amended array of structured, sanitized, validated data
	 * @throws Exception
	 * @since 1.0.0
	 */
	private function get_submitted_files( $files, $application ) {

		if ( ! $application || ! $files ) {
			wp_die( 'get_submitted_files Function called improperly' );
		}

		// Get an array of file field keys that we'll accept input for.
		$allowed_files_keys = Matador::variable( 'application_allowed_files_keys' );

		// Create an Array of Files to be Saved
		$files_to_save = [];

		// Loop through each allowed file input key
		foreach ( $allowed_files_keys as $key ) {

			// Check if a file was uploaded with that key.
			if ( ! self::is_file_uploaded( $files, $key ) ) {

				continue;
			}

			if ( 'files' === $key ) {
				foreach ( $files[ $key ]['name'] as $k => $name ) {
					$files_to_save[ $key . '_' . $k ] = [
						'name'     => $name,
						'type'     => $files[ $key ]['type'][ $k ],
						'tmp_name' => $files[ $key ]['tmp_name'][ $k ],
						'error'    => $files[ $key ]['error'][ $k ],
						'size'     => $files[ $key ]['size'][ $k ],
					];
				}

			} else {

				$files_to_save[ $key ] = $files[ $key ];
			}
		}

		if ( empty( $files_to_save ) ) {

			return $application;
		}

		foreach ( $files_to_save as $index => $file ) {

			$name = self::save_uploaded_file( $file, $index . '-' );

			if ( false !== $name ) {
				$url = trailingslashit( Matador::variable( 'uploads_cv_url' ) ) . $name;

				$file_data = array(
					'url'    => $url,
					'path'   => trailingslashit( Matador::variable( 'uploads_cv_dir' ) ) . $name,
					'file'   => $name,
					'synced' => 0,
				);

				$application['application']['files'][ $index ] = $file_data;

			} else {

				$application['post_content'] .= PHP_EOL . 'A file was rejected because of a bad file type.' . PHP_EOL . ' The rejected file details were: ' . PHP_EOL . wp_json_encode( $file ) . PHP_EOL;
			}
		}

		return $application;
	}

	/**
	 * Save Uploaded File
	 *
	 * This function will take a file array from the $_FILES, review it is
	 * from an accepted file format, rename it using the optional $prefix
	 * variable and timestamp it to prevent duplicates, and move it from
	 * the RAM temporary directory to a specified local directory.
	 *
	 * @param array $file An array of from the $_FILES global
	 * @param string $prefix Optional prefix to renamed file
	 *
	 * @return string|boolean  File name or false
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function save_uploaded_file( $file = null, $prefix = '' ) {

		// check to make sure file was posted
		if ( ! is_array( $file ) || ! is_string( $prefix ) ) {
			throw new Exception( 'error', 'matador-internal-error', esc_html__( 'Function called incorrectly.', 'matador-jobs' ) );
		}

		// check file type is allowed
		// (this is a safety check, form validation should prevent)
		list( $ext, $format ) = Helper::get_file_type( $file['name'] );
		if ( ! $ext || ! $format ) {
			Logger::add( '2', 'invalid_file_type', __( 'An invalid file type was uploaded', 'matador-jobs' ) );
		}

		// Check if Uploads Folder Exists, Try to Create It
		if ( ! file_exists( Matador::variable( 'uploads_cv_dir' ) ) ) {
			if ( ! mkdir( Matador::variable( 'uploads_cv_dir' ) ) && ! is_dir( Matador::variable( 'uploads_cv_dir' ) ) ) {
				throw new Exception( 'error', 'matador-application-uploads-folder', esc_html__( 'Application cannot save resume because folder is missing or WordPress is unable to create it.', 'matador-jobs' ) );
			}
			// Security bugfix. Ensure index.php file was never deleted.
			if ( ! file_exists(Matador::variable( 'uploads_cv_dir' ) . '/index.php' ) ) {
				touch( Matador::variable( 'uploads_cv_dir' ) . '/index.php' );
			}
		}

		// Get the original file name as uploaded
		$name = sanitize_file_name( strtolower( substr( $prefix . date( 'YmdHis' ), -29 ). '-' . substr( md5($file['name']), -5 ) . '-' . substr( $file['name'], -70 ) ) );

		// Determine the server location for the file to be stored.
		$location = trailingslashit( Matador::variable( 'uploads_cv_dir' ) ) . $name;

		// Store the file.
		if ( is_uploaded_file( $file['tmp_name'] ) ) {
			if ( move_uploaded_file( $file['tmp_name'], $location ) ) {
				chmod( $location, 0666 );

				return $name;
			}
		} elseif (
			file_exists( $file['tmp_name'] ) &&
		    trailingslashit( Matador::variable( 'uploads_cv_dir' ) ) === trailingslashit( dirname( $file['tmp_name'] ) )
		) {
			if ( rename( $file['tmp_name'], $location )) {
				chmod( $location, 0666 );

				return $name;
			}
		}

		return false;
	}

	/**
	 * Is File Uploaded
	 *
	 * Checks the $_FILES array for a job with the specified key.
	 *
	 * @param array $files Nonce-checked raw files data
	 * @param string $key Key name for file to check
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	private static function is_file_uploaded( $files, $key ) {

		if ( ! isset( $files[ $key ] ) ) {

			return false;
		}

		return ( isset( $files[ $key ]['size'] ) ) && ( 0 !== $files[ $key ]['size'] );
	}

	/**
	 * Get the Job Application Post Title
	 *
	 * Site operators have several ways to pass the job variable to the form. Check for the ways
	 * and return job data as appropriate.
	 *
	 * @param  array $application Application array
	 *
	 * @return string             String with title for Application Post.
	 *
	 * @since 1.0.0
	 */
	private static function get_application_post_title( $application ) {
		if ( ! empty( $application ) ) {

			$title = array();

			if ( ! empty( $application['application']['name']['fullName'] ) ) {
				$title[] = $application['application']['name']['fullName'];
			} else {
				$title[] = esc_html__( 'An applicant', 'matador-jobs' );
			}

			if ( isset( $application['type'] ) && 'referral' === $application['type'] ) {

				$title[] = esc_html__( 'was referred to', 'matador-jobs' );
			} else {
				$title[] = esc_html__( 'applied to', 'matador-jobs' );
			}
			if ( isset( $application['application']['jobs'] ) && ! empty( $application['application']['jobs'][0]['title'] ) ) {
				$title[] = $application['application']['jobs'][0]['title'];
			} else {
				$title[] = esc_html__( 'a position', 'matador-jobs' );
			}

			if ( isset( $application['application']['jobs'] ) && ! empty( $application['application']['jobs'] ) && is_array( $application['application']['jobs'] ) && count( $application['application']['jobs'] ) > 1 ) {
				// Translators: number of additional positions.
				$title[] = sprintf( esc_html__( 'and %1$s other position(s).', 'matador-jobs' ), count( $application['application']['jobs'] ) - 1 );
			}

			return implode( ' ', $title );
		}

		return esc_html__( 'Application received.', 'matador-jobs' );
	}

	/**
	 * Application Form Fields
	 *
	 * Function used by Template_Support to process array of field names into
	 * form fields array.
	 *
	 * @see Template_Support::application
	 *
	 * @param  array $fields Array of field names
	 * @param  array $require Array of required field names
	 *
	 * @return array Array of structured application fields
	 *
	 * @since 1.0.0
	 */
	public static function application_fields( $fields = null, $require = null ) {

		if ( ! isset( $fields ) || empty( $fields ) ) {
			$fields = self::application_fields_defaults();
		}

		$fields  = self::check_format_of_field_names( $fields );
		$fields  = self::magic_application_fields( $fields );

		$require = self::check_format_of_field_names( $require );
		$require = self::magic_application_fields( $require );

		$fields_details = self::application_fields_structure();

		$detailed_fields = array();

		foreach ( $fields as $field ) {
			if ( array_key_exists( $field, $fields_details ) ) {
				$detailed_fields[ $field ] = $fields_details[ $field ];

				if ( is_array( $require ) ) {

					if ( in_array( $field, $require, true ) ) {
						$detailed_fields[ $field ]['attributes']['required'] = true;
						$detailed_fields[ $field ]['class'][]                = 'matador-required';
					} else {
						unset( $detailed_fields[ $field ]['attributes']['required'] );
					}
				}
			}
		}

		if ( Matador::setting( 'application_privacy_field' ) ) {
			$detailed_fields['privacy_policy_opt_in'] = $fields_details['privacy_policy_opt_in'];
		}

		if ( Matador::setting( 'application_terms_field' ) ) {
			$detailed_fields['terms_of_service_opt_in'] = $fields_details['terms_of_service_opt_in'];
		}

		/**
		 * Filter: Application form fields
		 *
		 * @since   3.0.0
		 *
		 * @param array $detailed_fields
		 */
		return apply_filters( 'matador_application_fields', $detailed_fields );
	}

	/**
	 * Check Format of Field Names
	 *
	 * Looks for commonly improperly formatted field names and re-formats them
	 * as required. Protects our users from frustrating errors, but unfortunately
	 * ties up options for future field names.
	 *
	 *
	 * @param  array $fields Array of field names
	 *
	 * @return array Array of application fields after magic fields were found
	 *
	 * @since 3.0.0
	 */
	public static function check_format_of_field_names( $fields ) {
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $fields;
		}

		foreach ( $fields as &$key ) {
			switch ( $key ) {
				// Fixes 'first', 'firstname' and 'first_name' to 'firstName
				case 'firstname':
				case 'first_name':
				case 'first-name':
				case 'first':
					$key = 'firstName';
					break;
				// Fixes 'last', 'lastname' and 'last_name' to 'lastName
				case 'lastname':
				case 'last_name':
				case 'last-name':
				case 'last':
					$key = 'lastName';
					break;
				case 'middlename':
				case 'middle_name':
				case 'middle-name':
				case 'middle':
					$key = 'middleName';
					break;
				case 'nameprefix':
				case 'name_prefix':
				case 'name-prefix':
				case 'prefix':
					$key = 'namePrefix';
					break;
				case 'namesuffix':
				case 'name_suffix':
				case 'name-suffix':
				case 'suffix':
					$key = 'nameSuffix';
					break;
				case 'first-and-last-name':
				case 'firstandlastname':
				case 'firstAndLastName':
					$key = 'first_and_last_name';
					break;
				case 'first-middle-last-name':
				case 'firstmiddlelastname':
				case 'firstMiddleLastName':
					$key = 'first_middle_last_name';
					break;
				case 'complexName':
				case 'complexname':
				case 'complex-name':
					$key = 'complex_name';
					break;
				default:
					break;
			}
		}

		return $fields;
	}

	/**
	 * Magic Application Form Fields
	 *
	 * Takes an array of fields and magic fields and coverts them to standard field names.
	 *
	 * @param  array $fields Array of field names
	 *
	 * @return array Array of application fields after magic fields were found
	 *
	 * @since 3.0.0
	 */
	public static function magic_application_fields( $fields = null ) {

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $fields;
		}

		// Convert 'complex_name' into 'namePrefix', 'firstName', 'middleName',
		// 'lastName', and 'nameSuffix'.
		foreach ( array_keys( $fields, 'complex_name', true ) as $key ) {
			$fields = Helper::array_replace( $fields, $key, array( 'namePrefix', 'firstName', 'middleName', 'lastName', 'nameSuffix' ) );
		}

		// Convert 'first_middle_last_name' into 'firstName', 'middleName',
		// and 'lastName'.
		foreach ( array_keys( $fields, 'first_middle_last_name', true ) as $key ) {
			$fields = Helper::array_replace( $fields, $key, array( 'firstName', 'middleName', 'lastName' ) );
		}

		// Convert 'first_and_last_name' into 'firstName' and 'lastName'.
		foreach ( array_keys( $fields, 'first_and_last_name', true ) as $key ) {
			$fields = Helper::array_replace( $fields, $key, array( 'firstName', 'lastName' ) );
		}

		// Convert 'address' into 'address1', 'city', 'state', and 'zip'.
		// Further, if 'address2' exists, place it after 'address1'.
		foreach ( array_keys( $fields, 'address', true ) as $key ) {

			$fields = Helper::array_replace( $fields, $key, array( 'address1', 'city', 'state', 'zip' ) );

			if ( in_array( 'address2', $fields, true ) ) {
				$fields = Helper::array_remove( $fields, 'address2' );
				$fields = Helper::array_insert( $fields, 'address1', 'address2' );
			}
		}

		// We can only have 1 of the Resume/CV fields. Since 'cv' is magically
		// converted to 'resume', remove 'cv'.
		if ( in_array( 'cv', $fields, true ) && in_array( 'resume', $fields, true ) ) {
			$fields = Helper::array_remove( $fields, 'cv' );
		}

		// Convert 'cv' to 'resume'. We allow 'cv' as a field to gives international
		// users a more familiar field name, but we don't look for it in form processing
		// so we much use 'resume' when building the form.
		foreach ( array_keys( $fields, 'cv', true ) as $key ) {
			$fields = Helper::array_replace( $fields, $key, array( 'resume' ) );
		}

		return $fields;
	}

	/**
	 * Application Fields Defaults
	 *
	 * Function used by Application:application_fields to fetch default
	 * form fields array when none are passed to it.
	 *
	 * @see Application_Handler::application_fields
	 *
	 * @return array Array of default or initial application fields
	 *
	 * @since 1.0.0
	 */
	public static function application_fields_defaults() {

		$application_fields = Matador::setting( 'application_fields' );

		if ( ! empty( $application_fields ) && is_array( $application_fields ) ) {
			$default = $application_fields;
		} else {
			$default = array( 'name', 'email', 'phone', 'resume' );
		}

		/**
		 * Filter: Application form defaults fields
		 *
		 * @since   3.0.0
		 *
		 * @param array $detailed_fields
		 */
		return apply_filters( 'matador_application_fields_defaults', $default );
	}

	/**
	 * Application Fields Structure
	 *
	 * Function used by Application:application_fields to fetch default
	 * form fields array when none are passed to it.
	 *
	 * @see Application_Handler::application_fields
	 *
	 * @return array Array of application fields structure
	 *
	 * @since 1.0.0
	 */
	private static function application_fields_structure() {
		/**
		 * Filter: Application form label separator
		 *
		 * @since   3.0.0
		 *
		 * @param string ':'
		 */
		$colon = apply_filters( 'matador_application_fields_descriptions_colon', ':' );

		/**
		 * Filter: Application form defaults fields
		 *
		 * @since   3.0.0
		 *
		 * @param array $possible_form_inputs
		 */
		return apply_filters( 'matador_application_fields_structure', array(
			'name'                  => array(
				'type'       => 'text',
				'label'      => esc_html__( 'Your Name', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required'  => true,
					'minlength' => '2',
				),
			),
			'namePrefix'            => array(
				'type'  => 'text',
				'label' => esc_html__( 'Prefix', 'matador-jobs' ) . $colon,
			),
			'firstName'             => array(
				'type'       => 'text',
				'label'      => esc_html__( 'First Name', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required'  => true,
					'minlength' => '2',
				),
			),
			'middleName'            => array(
				'type'  => 'text',
				'label' => esc_html__( 'Middle Name', 'matador-jobs' ) . $colon,
			),
			'lastName'              => array(
				'type'       => 'text',
				'label'      => esc_html__( 'Last Name', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required'  => true,
					'minlength' => '2',
				),
			),
			'nameSuffix'            => array(
				'type'  => 'text',
				'label' => esc_html__( 'Suffix', 'matador-jobs' ) . $colon,
			),
			'address1'              => array(
				'type'  => 'text',
				'label' => esc_html__( 'Address', 'matador-jobs' ) . $colon,
			),
			'address2'              => array(
				'type'  => 'text',
				'label' => esc_html__( 'Address (Continued)', 'matador-jobs' ) . $colon,
			),
			'city'                  => array(
				'type'  => 'text',
				'label' => esc_html__( 'City', 'matador-jobs' ) . $colon,
			),
			'state'                 => array(
				'type'  => 'text',
				'label' => esc_html__( 'State/Province', 'matador-jobs' ) . $colon,

			),
			'zip'                   => array(
				'type'  => 'text',
				'label' => esc_html__( 'ZIP/Postal Code', 'matador-jobs' ) . $colon,
			),
			'email'                 => array(
				'type'       => 'email',
				'template'   => 'text',
				'label'      => esc_html__( 'Email Address', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required' => true,
				),
			),
			'phone'                 => array(
				'type'       => 'tel',
				'template'   => 'text',
				'label'      => esc_html__( 'Phone Number', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required' => true,
				),
			),
			'mobile'                => array(
				'type'       => 'tel',
				'template'   => 'text',
				'label'      => esc_html__( 'Mobile Phone Number', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'required' => true,
				),
			),
			'resume'                => array(
				'type'        => 'file',
				// translators: User setting result for how to refer to the CV/Resume
				'label'       => sprintf( esc_html__( 'Upload %1$s File', 'matador-jobs' ), esc_html( Helper::resume_or_cv() ) ) . $colon,
				// translators: Placeholders are list of accepted file types
				'description' => sprintf( esc_html__( 'Attach a resume file. Accepted file types are %s.', 'matador-jobs' ), Matador::variable( 'accepted_files_types' ) ),
				'attributes'  => array(
					'required' => true,
					'accept'   => implode( ',', Matador::variable( 'accepted_file_extensions' ) ),
				),
			),
			'letter'                => array(
				'type'        => 'file',
				'label'       => esc_html__( 'Upload Cover Letter', 'matador-jobs' ) . $colon,
				// translators: Placeholders are list of accepted file types
				'description' => sprintf( esc_html__( 'Attach a cover letter file. Accepted file types are %s.', 'matador-jobs' ), Matador::variable( 'accepted_files_types' ) ),
				'attributes'  => array(
					'accept' => implode( ',', Matador::variable( 'accepted_file_extensions' ) ),
				),
			),
			'files'                 => array(
				'type'        => 'files',
				'label'       => esc_html__( 'Upload Additional  Files', 'matador-jobs' ) . $colon,
				// translators: Placeholders are list of accepted file types
				'description' => sprintf( esc_html__( 'Attach additional files. Accepted file types are %s.', 'matador-jobs' ), Matador::variable( 'accepted_files_types' ) ),
				'attributes'  => array(
					'accept' => implode( ',', Matador::variable( 'accepted_file_extensions' ) ),
				),
			),
			'message'               => array(
				'type'        => 'textarea',
				'label'       => esc_html__( 'Message to Recruiters.', 'matador-jobs' ),
				'description' => esc_html__( 'Include a message to the recruiters.', 'matador-jobs' ),
			),
			'jobs'                  => array(
				'type'        => 'select-jobs',
				'label'       => esc_html__( 'Select Jobs to Apply For', 'matador-jobs' ) . $colon,
				'description' => esc_html__( 'Select one or many jobs you\'d like to apply for.', 'matador-jobs' ),
			),
			'request'               => array(
				'type'       => 'text',
				'label'      => esc_html__( 'Types of Positions You\'re Interested In', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'placeholder' => esc_html__( 'eg: Software Engineer', 'matador-jobs' ),
				),
			),
			'profile'               => array(
				'type'       => 'url',
				'template'   => 'text',
				'label'      => esc_html__( 'LinkedIn Url Or Link To Your Portfolio', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'placeholder' => esc_html( 'https://...' ),
				),
			),
			'contact_method_text'   => array(
				'type'       => 'text',
				'label'      => esc_html__( 'Best way to get in touch', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'placeholder' => esc_html__( 'eg: Email', 'matador-jobs' ),
				),
			),
			'privacy_policy_opt_in' => array(
				'type'        => 'checkbox',
				'label'       => null,
				'options'     => array(
					'1' => esc_html__( 'By submitting this application, you give us permission to store your personal information, and use it in the consideration of your fitness for the position, including sharing it with the hiring firm.', 'matador-jobs' ),
				),
				'description' => ( ( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ) || Matador::setting( 'privacy_policy_page' ) ) ? sprintf( '<a href="%s" target="_blank">' . esc_html__( 'Review our Privacy Policy for more information.', 'matador-jobs' ) . '</a>', function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : get_page_link( Matador::setting( 'privacy_policy_page' ) ) ) : null,
				'attributes'  => array(
					'required'          => 'required',
					'data-msg-required' => __( 'You must agree to our Privacy Policy.', 'matador-jobs' ),
					'minlength'         => 1,
				),
			),
			'terms_of_service_opt_in' => array(
				'type'        => 'checkbox',
				'label'       => null,
				'options'     => array(
					'1' => esc_html__( 'By submitting this application, you agree to our Terms of Service.', 'matador-jobs' ),
				),
				'description' => ( Matador::setting( 'terms_policy_page' ) && -1 !== Matador::setting( 'terms_policy_page' ) ) ? sprintf( '<a href="%2$s" target="_blank">%1$s</a>', esc_html__( 'Review our Terms of Service for more information.', 'matador-jobs' ), get_page_link( Matador::setting( 'terms_policy_page' ) ) ) : '',
				'attributes'  => array(
					'required'          => 'required',
					'data-msg-required' => __( 'You must agree to our Terms of Service.', 'matador-jobs' ),
					'minlength'         => 1,
				),
			),
			'occupation'            => array(
				'type'       => 'text',
				'label'      => __( 'Current Job Title', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'maxlength' => 50,
				),
			),
			'companyName'            => array(
				'type'       => 'text',
				'label'      => __( 'Current Company', 'matador-jobs' ) . $colon,
				'attributes' => array(
					'maxlength' => 100,
				),
			),
		) );
	}
}
