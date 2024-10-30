<?php
/**
 * Matador / Email / Recruiter Application Notification
 *
 * @link        http://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017, Jeremy Scott
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \stdClass;
use \Mustache_Engine;
use matador\Matador;
use matador\Helper;

/**
 * Abstract Class
 *
 * @since 3.6.0
 *
 * @package matador\MatadorJobs\Email
 *
 * @final
 */
final class ApplicationRecruiterMessage extends MessageAbstract {

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
	public static $key = 'application-recruiter';

	public function __construct() {

		// Should we send this email? Does the user have this setting on?
		if ( '1' !== Matador::setting( 'notify_recruiter' ) ) {

			return;
		}

		add_action( 'matador_new_job_application', [ __CLASS__, 'send_on_save' ], 10, 2 );
		add_action( 'matador_new_job_application_failed', [ __CLASS__, 'send_on_save_failure' ], 10, 1 );

		add_action( 'matador_bullhorn_candidate', [ __CLASS__, 'send_on_sync' ], 10, 3 );
	}

	/**
	 * Email
	 *
	 * Compile the data for and send the email.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
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

		$email->message( self::body( 'application-confirmation-for-recruiter', self::template_args( $args ) ) );

		$email->attachments( self::attachments( $args ) );

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
	 * @access public
	 * @static
	 *
	 * @param array $from
	 * @param array $args
	 *
	 * @return array RCF 2822 formatted email address parts in array
	 */
	public static function from( array $from = [], array $args = [] ) {

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_recruiter_email_header' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_recruiter_email_header', 'matador_email_application_recruiter_from' );

			$from_string = Email::parse_email_array( $from );

			/**
			 * Filter: Matador Recruiter Email Header
			 *
			 * @since      3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_recruiter_from'
			 *
			 * @var string $from
			 *
			 * @return string
			 */
			$from_string = apply_filters( 'matador_recruiter_email_header', $from_string );

			$from = Email::parse_email_string( $from_string );
		}

		// Backwards Compatibility to 3.4.0
		if ( has_filter( 'matador_application_confirmation_recruiter_from' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_application_confirmation_recruiter_from', 'matador_email_application_recruiter_from' );

			$from_string = Email::parse_email_array( (array) $from );

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
			$from_string = apply_filters( 'matador_application_confirmation_recruiter_from', $from_string, $args );

			$from = Email::parse_email_string( $from_string );
		}

		/**
		 * Filter: Matador Email Application Recruiter "From"
		 *
		 * Modify the "From" name and email address for the Application Confirmation for Recruiters email.
		 *
		 * @since 3.6.0, replaces 'matador_application_confirmation_recruiter_from' & 'matador_recruiter_email_header'
		 *
		 * @var array $from
		 * @var array  $args
		 *
		 * @return string RCF 2822 formatted email address
		 */
		return apply_filters( 'matador_email_application_recruiter_from', $from, $args );
	}

	/**
	 * Email Recipients
	 *
	 * Recruiter emails go to recruiters. Determine which, if any, get the email.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function recipients( array $args = [] ) {

		$recipients = [];

		if ( is_array( Matador::setting( 'notify_type' ) ) && ! empty( $args['jobs'] ) ) {

			foreach ( $args['jobs'] as $job ) {

				foreach ( Matador::setting( 'notify_type' ) as $type ) {

					$users = get_post_meta( $job['wpid'], $type, true );

					if ( isset( $users->data ) ) {
						foreach ( $users->data as $user ) {
							$recipients[] = self::recruiter_user_email_helper( $user );
						}
					} elseif ( ! empty( $users->email ) ) {
						$recipients[] = self::recruiter_user_email_helper( $users );
					}
				}
			}
		}

		if ( Matador::setting( 'recruiter_no_email' ) && empty( $recipients ) ) {
			$emails = Matador::setting( 'recruiter_no_email' );
			if ( strpos( $emails, ',' ) ) {
				foreach ( array_map( 'trim', explode( ',', $emails ) ) as $email ) {
					if ( Email::is_email( $email ) ) {
						$recipients[] = Email::parse_email_string( $email );
					}
				}
			} elseif ( Email::is_email( $emails ) ) {
				$recipients[] = Email::parse_email_string( $emails );
			}
		}

		if ( Matador::setting( 'recruiter_email' ) ) {
			$emails = Matador::setting( 'recruiter_email' );
			if ( strpos( $emails, ',' ) ) {
				foreach ( array_map( 'trim', explode( ',', $emails ) ) as $email ) {
					if ( Email::is_email( $email ) ) {
						$recipients[] = Email::parse_email_string( $email );
					}
				}
			} elseif ( Email::is_email( $emails ) ) {
				$recipients[] = Email::parse_email_string( $emails );
			}
		}

		// Backwards Compatibility 3.0.0
		if ( has_filter( 'matador_recruiter_email_recipients' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_recruiter_email_recipients', 'matador_email_application_recruiter_recipients' );

			$recipients_string = '';

			foreach ( $recipients as $recipient ) {
				$recipients_string .= ( $recipients_string ? ', ' : '' ) . Email::parse_email_array( $recipient );
			}

			/**
			 * Filter: Matador Recruiter Email Recipients
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_recruiter_recipients'
			 *
			 * @var string  $recipients
			 *
			 * @return string
			 */
			$recipients_string = apply_filters( 'matador_recruiter_email_recipients', $recipients_string );

			$recipients = (array) array_map( [ __NAMESPACE__ . '\Email', 'parse_email_string' ], array_map( 'trim', explode( ',', $recipients_string ) ) );
		}

		// Backwards Compatibility 3.4.0
		if ( has_filter( 'matador_application_confirmation_recruiter_recipients' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_application_confirmation_recruiter_recipients', 'matador_email_application_recruiter_recipients' );

			$recipients_string = '';

			foreach ( (array) $recipients as $recipient ) {
				$recipients_string .= ( $recipients_string ? ', ' : '' ) . Email::parse_email_array( $recipient );
			}

			/**
			 * Filter: Matador Application Confirmation Recruiter Recipients
			 *
			 * Modify the string of recipients for the Application Confirmation for Recruiters email.
			 *
			 * @since 3.4.0, replaces 'matador_recruiter_email_recipients'
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_recruiter_recipients'
			 *
			 * @var string $recipients
			 * @var array $application
			 */
			$recipients_string = apply_filters( 'matador_application_confirmation_recruiter_recipients', $recipients_string, $args );

			$recipients = array_map( [ __NAMESPACE__ . '\Email', 'parse_email_string' ], array_map( 'trim', explode( ',', $recipients_string ) ) );
		}

		/**
		 * Filter: Matador Email Application Recruiter Recipients
		 *
		 * Modify the array of recipients for the Application Confirmation for Recruiters email.
		 *
		 * @since 3.6.0, replaces 'matador_application_confirmation_recruiter_recipients'
		 *
		 * @var string $recipients
		 * @var array $application
		 */
		return apply_filters( 'matador_email_application_recruiter_recipients', $recipients, $args );
	}

	/**
	 * Email Subject
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected static function subject( array $args = [] ) {

		// Translators: Placeholder 1 is the localized word, based on user settings, for Resume or CV
		$subject = sprintf( __( 'New %1$s Submission', 'matador-jobs' ), Helper::resume_or_cv() );

		if ( ! empty( $args['jobs'] ) && ! empty( $args['jobs'][0]['title'] ) ) {

			if ( 1 === count( $args['jobs'] ) ) {
				// Translators: Placeholder 1 is the applied job title.
				$subject = sprintf( __( 'Submission Notification for %1$s', 'matador-jobs' ), $args['jobs'][0]['title'] );
			} elseif ( 2 === count( $args['jobs'] ) ) {
				// Translators: Placeholder 1 is the applied job title.
				$subject = sprintf( __( 'Submission Notification for %1$s and 1 other position', 'matador-jobs' ), $args['jobs'][0]['title'] );
			} else {
				$additional_roles = count( $args['jobs'] ) - 1;
				// Translators: Placeholder 1 is the applied job title, Placeholder 2 is the number of additional roles applied to
				$subject = sprintf( __( 'Submission Notification for %1$s and %2$s other positions', 'matador-jobs' ), $args['jobs'][0]['title'], $additional_roles );
			}
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_recruiter_email_subject' ) && ! empty( $args['jobs'][0]['title'] ) ) {

			Helper::deprecated_notice( 'filter', 'matador_recruiter_email_subject', 'matador_email_application_recruiter_subject' );

			/**
			 * Filter: Matador Recruiter Email Subject (with Job Title)
			 *
			 * @since      3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_recruiter_subject'
			 *
			 * @var string  $from
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_recruiter_email_subject', $subject );
		}

		// Backwards Compatibility to 3.4.0
		if ( has_filter( 'matador_recruiter_email_subject_no_title' ) && empty( $args['jobs'][0]['title'] ) ) {

			Helper::deprecated_notice( 'filter', 'matador_recruiter_email_subject_no_title', 'matador_email_application_recruiter_subject' );

			/**
			 * Filter: Matador Recruiter Email Subject (no Job Title)
			 *
			 * @since      3.0.0
			 *
			 * @deprecated 3.4.0, use 'matador_email_application_recruiter_subject'
			 *
			 * @var string $subject
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_recruiter_email_subject_no_title', $subject );
		}

		// Backwards Compatibility to 3.4.0
		if ( has_filter( 'matador_application_confirmation_recruiter_subject' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_application_confirmation_recruiter_subject', 'matador_email_application_recruiter_subject' );

			/**
			 * Filter: Matador Application Confirmation Recruiter Subject
			 *
			 * Modify the email subject for the Application Confirmation for Recruiters email.
			 *
			 * @since 3.4.0, replaces 'matador_recruiter_email_subject_no_title' & 'matador_recruiter_email_subject'
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_recruiter_subject'
			 *
			 * @var string $subject
			 * @var array  $args
			 * @var string $job_title
			 *
			 * @return string
			 */
			$subject = apply_filters( 'matador_application_confirmation_recruiter_subject', $subject, $args, $args['jobs'][0]['title'] );
		}

		/**
		 * Filter: Matador Email Application Recruiter Subject
		 *
		 * Modify the email subject for the Application Recruiters email.
		 *
		 * @since 3.6.0, replaces 'matador_application_confirmation_recruiter_subject'
		 *
		 * @var string $subject
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_application_recruiter_subject', $subject, $args );
	}

	/**
	 * Body
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
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
			$template = 'application-confirmation-for-recruiter';
		}

		$renderer = new Mustache_Engine();

		$template = matador_get_template( $template . '.php', $args, 'emails' );

		// Backwards Compatibility to 3.2.0
		if ( has_filter( 'matador_email_recruiter_content' ) ) {

			Helper::deprecated_notice( 'filter', 'matador_email_recruiter_content', 'matador_email_application_recruiter_body' );

			/**
			 * Filter: Matador Email Recruiter Content
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
			$template = apply_filters( 'matador_email_recruiter_content', $template, $args );
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_email_recruiter_content_before' ) ) {

			Helper::deprecated_notice( 'action', 'matador_email_recruiter_content_before', 'matador_email_application_recruiter_body' );

			ob_start();

			/**
			 * Matador Email Recruiter Content Before
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_recruiter_body'
			 *
			 * @param string $deprecated_1 This never was passed a non-empty value. It was a never used placeholder.
			 * @param string $deprecated_2 This never was passed a non-empty value. It was a never used placeholder.
			 */
			do_action( 'matador_email_recruiter_content_before', '', '' );

			$template = ob_get_clean() . $template;
		}

		// Backwards Compatibility to 3.0.0
		if ( has_filter( 'matador_email_recruiter_content_after' ) ) {

			Helper::deprecated_notice( 'action', 'matador_email_recruiter_content_before', 'matador_email_application_recruiter_body' );

			ob_start();

			/**
			 * Matador Email Recruiter Content After
			 *
			 * @since 3.0.0
			 *
			 * @deprecated 3.6.0, use 'matador_email_application_recruiter_body'
			 *
			 * @param string $deprecated_1 This never was passed a non-empty value. It was a never used placeholder.
			 * @param string $deprecated_2 This never was passed a non-empty value. It was a never used placeholder.
			 */
			do_action( 'matador_email_recruiter_content_after', '', '' );

			$after = ob_get_clean();

			if ( ! strpos( $after, $template ) ) {
				$template .= $after;
			}
		}

		/**
		 * Filter: Matador Email Recruiter  Body
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
		$template = apply_filters( 'matador_email_application_recruiter_body', $template, $args );

		return $renderer->render( $template, $args );
	}

	/**
	 * Recruiter User Helper
	 *
	 * Looping through the possible recipients in the Bullhorn user array can be a pain. This helps do it while
	 * simplifying the Recipients function.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param stdClass $user
	 *
	 * @return array
	 */
	public static function recruiter_user_email_helper( $user ) {

		if ( ! isset( $user->email ) ) {

			return [];
		}

		if ( ! Email::is_email( $user->email ) ) {

			return [];
		}

		$first = ! empty( $user->firstName ) ? $user->firstName : ''; //PHPCS:ignore
		$last  = ! empty( $user->lastName ) ? $user->lastName : ''; //PHPCS:ignore
		$email = $user->email;

		if ( $first || $last ) {
			return [
				'name'  => ( $first && $last ) ? $first . ' ' . $last : $first . $last,
				'email' => $email,
			];
		}

		return [ 'email' => $email ];
	}


	/**
	 * Send Notification
	 *
	 * Wrapper function to handle pass-through from action to self::message
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param int $wpid
	 * @param array $application
	 *
	 * @return void
	 */
	public static function send_on_save( $wpid, $application ) {

		// Should we send this email? Does the user have this setting on?
		if ( '1' === Matador::setting( 'notify_recruiter_when' ) ) {

			return;
		}

		$application['wpid'] = $wpid;
		self::message( $application );
	}

	public static function send_on_save_failure( $post_args ) {

		$args = $post_args[ Matador::variable( 'application_data' ) ];
		$args['post_content'] = $post_args['post_content'];

		self::message( $args );
	}

	public static function send_on_sync( $wpid, $application, $candidate ) {
		$candidate_id = $candidate->candidate->id;

		// Don't we don't have a remote ID and the emails should be sent after sync
		if ( '1' !== Matador::setting( 'notify_recruiter_when' ) || empty( $candidate_id ) ) {

			return;
		}

		$application['bhid'] = $candidate_id;
		$application['wpid'] = $wpid;

		self::message( $application );
	}
}
