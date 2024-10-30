<?php
/**
 * Matador / Email / Applicant Application Notification
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Mustache_Engine;
use \matador\Matador;
use \matador\Helper;

/**
 * Abstract Class
 *
 * @since 3.6.0
 *
 * @package matador\MatadorJobs\Email
 *
 * @final
 */
final class ApplicationApplicantMessage extends MessageAbstract {

	use ApplicationMessagesTrait;

	/**
	 * Key
	 *
	 * Give your message a name so logging can communicate which email is being sent.
	 *
	 * @since 3.6.0
	 *
	 * @var string
	 */
	public static $key = 'application-applicant';

	public function __construct() {

		// Should we send this email? Does the user have this setting on?
		if ( '1' !== Matador::setting( 'notify_applicant' ) ) {

			return;
		}

		add_action( 'matador_new_job_application', [ __CLASS__, 'send_on_save' ], 10, 2 );
		add_action( 'matador_new_job_application_failed', [ __CLASS__, 'send_on_save_failure' ], 10, 1 );
	}

	/**
	 * Email
	 *
	 * Compile the data for and send the email.
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function message( array $args = [] ) {

		$email = new Email();

		$email->from( self::from( $email->mail['from'], $args ) );

		$email->recipients( self::recipients( $args ) );

		$email->subject( self::subject( $args ) );

		$email->message( self::body( 'application-confirmation-for-candidate', self::template_args( $args ) ) );

		$email->send( static::$key );
	}

	/**
	 * Email From
	 *
	 * Allows filtering of the default from field, set in instatiation of class, with values inherited from abstract,
	 * which itself tries to set defaults from Matador settings.
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $from
	 * @param array $args
	 *
	 * @return array RCF 2822 formatted email address parts in array
	 */
	public static function from( array $from = [], array $args = [] ) {

		// Backwards Compatibility to 3.0.0 and 3.4.0
		if ( has_filter( 'matador_applicant_email_header' ) || has_filter( 'matador_application_confirmation_candidate_from' ) ) {

			$from_string = Email::parse_email_array( (array) $from[0] );

			$headers = [ 'From: ' . $from_string ];

			if ( has_filter( 'matador_applicant_email_header' ) ) {

				Helper::deprecated_notice( 'filter', 'matador_applicant_email_header', 'matador_email_application_recruiter_from' );

				/**
				 * Filter: Matador Recruiter Email Header
				 *
				 * @since      3.0.0
				 *
				 * @return string
				 * @var string $from
				 *
				 * @deprecated 3.4.0, use 'matador_email_application_recruiter_from'
				 *
				 */
				$headers = apply_filters( 'matador_applicant_email_header', $from_string );
			}

			if ( has_filter( 'matador_application_confirmation_candidate_from' ) ) {

				Helper::deprecated_notice( 'filter', 'matador_application_confirmation_candidate_from', 'matador_email_application_recruiter_from' );

				/**
				 * Filter: Matador Application Confirmation Recruiter From
				 *
				 * Modify the "From" name and email address for the Application Confirmation for Recruiters email.
				 *
				 * @since 3.4.0, replaces 'matador_recruiter_email_header'
				 *
				 * @deprecated 3.6.0, use 'matador_email_application_recruiter_from'
				 *
				 * @var string $from
				 * @var array  $args
				 *
				 * @return string
				 */
				$headers = apply_filters( 'matador_application_confirmation_candidate_from', $headers, $args );
			}

			foreach ( $headers as $header ) {
				if ( substr( trim( $header ), 0, strlen( 'From:' ) ) === 'From:' ) {
					$from_string = trim( substr( trim( $header ), 0, strlen( 'From:' ) ) );
					break;
				}
			}

			$from = [ Email::parse_email_string( $from_string ) ];
		}

		/**
		 * Filter: Matador Email Application Applicant "From"
		 *
		 * Modify the "From" name and email address for the Application Confirmation for Applicants email.
		 *
		 * @since 3.6.0, replaces 'matador_application_confirmation_applicant_from' & 'matador_applicant_email_header'
		 *
		 * @var array $from
		 * @var array $args
		 *
		 * @return string RCF 2822 formatted email address
		 */
		return apply_filters( 'matador_email_application_applicant_from', $from, $args );
	}

	/**
	 * Email Recipients
	 *
	 * Applicant emails go to applicants. Determine which, if any, get the email.
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function recipients( array $args = [] ) {

		if ( ! isset( $args['email'] ) || ! Email::is_email( $args['email'] ) ) {

			return [];
		}

		if ( ! empty( $args['name']['fullname'] ) ) {
			$recipients = [
				'name'  => $args['name']['fullname'],
				'email' => $args['email'],
			];
		} else {
			$recipients = [
				'email' => $args['email'],
			];
		}

		// Backwards Compatibility 3.0.0
		if ( has_filter( 'matador_applicant_email_recipients' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_applicant_email_recipients', 'matador_email_application_applicant_recipients' );

			$recipients_string = '';

			foreach ( $recipients as $recipient ) {
				$recipients_string .= ( $recipients_string ? ', ' : '' ) . Email::parse_email_array( $recipient );
			}

			/**
			 * Filter: Matador Application Confirmation Candidate Recipients
			 *
			 * Modify the string of recipients for the Application Confirmation for candidate email.
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.6.0
			 *
			 * @var array $recipients
			 * @var array $application
			 */
			$recipients_string = apply_filters( 'matador_applicant_email_recipients', $recipients, $application );

			$recipients = (array) array_map( [ __NAMESPACE__ . '\Email', 'parse_email_string' ], array_map( 'trim', explode( ',', $recipients_string ) ) );
		}

		/**
		 * Filter: Matador Email Application Applicant Recipients
		 *
		 * Modify the array of recipients for the Application Confirmation for Applicants email.
		 *
		 * @since 3.6.0, replaces 'matador_applicant_email_recipients'
		 *
		 * @var string $recipients
		 * @var array $application
		 */
		return apply_filters( 'matador_email_application_applicant_recipients', $recipients, $args );
	}

	/**
	 * Email Subject
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected static function subject( array $args = [] ) {

		$name = $args['name']['fullName'];

		// Translators: Placeholder 1 is the localized word, based on user settings, for Resume or CV and placeholder 2 is the applicant name.
		$subject = sprintf( __( '%2$s, thank you for your %1$s submission', 'matador-jobs' ), sanitize_text_field( Helper::resume_or_cv() ), sanitize_text_field( $name ) );

		if ( ! empty( $args['jobs'] ) && ! empty( $args['jobs'][0]['title'] ) ) {

			if ( 1 === count( $args['jobs'] ) ) {
				// Translators: Placeholder 1 is the applied job title and placeholder 2 is the applicant name.
				$subject = sprintf( __( '%2$s, Thank you for your application to %1$s', 'matador-jobs' ), $args['jobs'][0]['title'], $name );
			} elseif ( 2 === count( $args['jobs'] ) ) {
				// Translators: Placeholder 1 is the applied job title and placeholder 2 is the applicant name.
				$subject = sprintf( __( '%2$s, Thank you for your application to %1$s and 1 other position', 'matador-jobs' ), $args['jobs'][0]['title'], $name );
			} else {
				$additional_roles = count( $args['jobs'] ) - 1;
				// Translators: Placeholder 1 is the applied job title, placeholder 2 is the applicant name, and placeholder 3 is the number of additional roles the candidate applied to
				$subject = sprintf( __( '%2$s, Thank you for your application to %1$s and %3$s other positions', 'matador-jobs' ), $args['jobs'][0]['title'], $name, $additional_roles );
			}
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_applicant_email_subject' ) && ! empty( $args['jobs'][0]['title'] ) ) {

			Helper::deprecated_notice( 'filter', 'matador_applicant_email_subject', 'matador_email_application_applicant_subject' );

			/**
			 * Filter: Matador Applicant Email Subject (with Job Title)
			 *
			 * @since      3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_applicant_subject'
			 *
			 * @var string  $from
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_applicant_email_subject', $subject );
		}

		// Backwards Compatibility to 3.4.0
		if ( has_filter( 'matador_applicant_email_subject_no_title' ) && empty( $args['jobs'][0]['title'] ) ) {

			Helper::deprecated_notice( 'filter', 'matador_applicant_email_subject_no_title', 'matador_email_application_applicant_subject' );

			/**
			 * Filter: Matador Applicant Email Subject (no Job Title)
			 *
			 * @since      3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_applicant_subject'
			 *
			 * @var string $subject
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_applicant_email_subject_no_title', $subject );
		}

		// Backwards Compatibility to 3.4.0
		if ( has_filter( 'matador_application_confirmation_candidate_subject' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_application_confirmation_applicant_subject', 'matador_email_application_applicant_subject' );

			/**
			 * Filter: Matador Application Confirmation Applicant Subject
			 *
			 * Modify the email subject for the Application Confirmation for applicants email.
			 *
			 * @since 3.4.0, replaces 'matador_application_email_subject_no_title' & 'matador_applicant_email_subject'
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_applicant_subject'
			 *
			 * @var string $subject
			 * @var array  $args
			 * @var string $job_title
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_application_confirmation_candidate_subject', $subject, $args, $args['jobs'][0]['title'] );
		}

		/**
		 * Filter: Matador Email Application Applicant Subject
		 *
		 * Modify the email subject for the Application Applicants email.
		 *
		 * @since 3.6.0, replaces 'matador_application_confirmation_Applicant_subject'
		 *
		 * @var string $subject
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_application_applicant_subject', $subject, $args );
	}

	/**
	 * Body
	 *
	 * @since 3.6.0
	 *
	 *
	 * @uses Mustache_Engine
	 * @see https://github.com/bobthecow/mustache.php/wiki
	 *
	 * @param string $template
	 * @param array $args
	 *
	 * @return string
	 */
	protected static function body( $template = '', array $args = [] ) {

		if ( empty( $template ) ) {
			$template = 'application-confirmation-for-applicant';
		}

		$renderer = new Mustache_Engine();

		$template = matador_get_template( $template . '.php', $args, 'emails' );

		// Backwards Compatibility to 3.2.0
		if ( has_filter( 'matador_email_applicant_content' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_email_applicant_content', 'matador_email_application_applicant_body' );

			/**
			 * Filter: Matador Email Applicant Content
			 *
			 * Update the email content.
			 *
			 * @since 3.2.0
			 * @since 3.6.0, this applied before Mustache tags
			 *
			 * @deprecated 3.6.0
			 *
			 * @var string $template
			 * @var array $args
			 *
			 * @return string
			 */
			$template = apply_filters( 'matador_email_applicant_content', $template, $args );
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_email_applicant_content_before' ) ) {

			Helper::deprecated_notice( 'action', 'matador_email_applicant_content_before', 'matador_email_application_applicant_body' );

			ob_start();

			/**
			 * Matador Email Recruiter Content Before
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.6.0, use filter 'matador_email_application_applicant_body'
			 *
			 * @param string $deprecated_1 This never was passed a non-empty value. It was a never used placeholder.
			 * @param string $deprecated_2 This never was passed a non-empty value. It was a never used placeholder.
			 */
			do_action( 'matador_email_applicant_content_before', '', '' );

			$before = ob_get_clean();

			if ( ! strpos( $before, $template ) ) {
				$template = $before . $template;
			}
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_email_applicant_content_after' ) ) {

			Helper::deprecated_notice( 'action', 'matador_email_applicant_content_after', 'matador_email_application_applicant_body' );

			ob_start();

			/**
			 * Matador Email Recruiter Content After
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_applicant_body'
			 *
			 * @param string $deprecated_1 This never was passed a non-empty value. It was a never used placeholder.
			 * @param string $deprecated_2 This never was passed a non-empty value. It was a never used placeholder.
			 */
			do_action( 'matador_email_applicant_content_after', '', '' );

			$after = ob_get_clean();

			if ( ! strpos( $after, $template ) ) {
				$template .= $after;
			}
		}

		/**
		 * Filter: Matador Email Applicant  Body
		 *
		 * Modify the email template before Mustache is applied.
		 *
		 * @since 3.6.0
		 *
		 * @var string $template
		 * @var array  $args
		 *
		 * @return string
		 */
		$template = apply_filters( 'matador_email_application_applicant_body', $template, $args );

		return $renderer->render( $template, $args );
	}

	/**
	 * Send Notification
	 *
	 * Wrapper function to handle pass-through from action to self::message
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param int $wpid
	 * @param array $application
	 *
	 * @return void
	 */
	public static function send_on_save( $wpid, $application ) {

		if ( 'application' !== get_post_meta( $wpid, Matador::variable( 'submission_type' ), true ) ) {

			return;
		}

		$application['wpid'] = $wpid;
		self::message( $application );
	}

	/**
	 * Send Notification
	 *
	 * Wrapper function to handle pass-through from action to self::message
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $post_args
	 *
	 * @return void
	 */
	public static function send_on_save_failure( $post_args ) {

		if ( 'application' !== $post_args['meta_input'][ Matador::variable( 'submission_type' ) ] ) {

			return;
		}

		$args = $post_args['meta_input'][ Matador::variable( 'application_data' ) ];
		$args['post_content'] = $post_args['post_content'];
		self::message( $args );
	}
}
