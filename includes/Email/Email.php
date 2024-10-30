<?php
/**
 * Matador / Email / Email Abstract Class
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

use matador\Matador;
use matador\Event_Log;

/**
 * Abstract Class EmailAbstract
 *
 * @since 3.6.0
 *
 * @package MatadorJobs\Email
 */
class Email implements EmailInterface {

	use Rfc2822Trait;

	/**
	 * Stores the email object.
	 *
	 * @since 3.6.0
	 *
	 * @var array
	 *
	 * @return void
	 */
	public $mail = array();

	/**
	 * Construct
	 *
	 * @since 3.6.0
	 *
	 * @var array
	 *
	 * @return void
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Set Up
	 *
	 * Sets up the default WP Mail object
	 *
	 * @since 3.6.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function setup() {

		$default_from_name  = Matador::setting( 'email_from_name' ) ?: get_bloginfo( 'name' );
		$default_from_email = Matador::setting( 'email_from_address' ) ?: get_option( 'admin_email' );

		/**
		 * Filter: Matador Email Default From Name (Default Context)
		 *
		 * Modify the "From" name for the default email, which defaults to either the Matador setting or the Web Site name
		 *
		 * @since 3.2.0
		 * @since 3.6.0 adds the $context argument, to allow for narrowing the filters' affects when applied elsewhere
		 *
		 * @var string $from_name Name for the "From" ie "ACME Job Board"
		 * @var string $context The context for this application of the filter, in this case, 'default'
		 */
		$from_name = apply_filters( 'matador_email_from_name', $default_from_name, 'default' );

		/**
		 * Filter: Matador Email Default From Name (Default Context)
		 *
		 * Modify the "From" name for the default email, which defaults to either the Matador setting or the Web Site name
		 *
		 * @since 3.2.0
		 * @since 3.6.0 adds the $context argument, to allow for narrowing the filters' affects when applied elsewhere
		 *
		 * @var string $from_name Name for the "From" ie "ACME Job Board"
		 * @var string $context The context for this application of the filter, in this case, 'default'
		 */
		$from_email = apply_filters( 'matador_email_from_email', $default_from_email, 'default' );

		$this->mail = [
			'headers'     => [],
			'from'        => [
				[
					'name'  => $from_name,
					'email' => $from_email,
				],
			],
			'to'          => [],
			'recipients'  => [],
			'cc'          => [],
			'bcc'         => [],
			'subject'     => __( 'Email from', 'matador-jobs' ) . ' ' . $from_name,
			'message'     => '',
			'attachments' => [],
			'html'        => true,
		];
	}

	/**
	 * Headers
	 *
	 * Sets the email headers, including the from, cc, and bcc, and returns false if a from is not present.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function headers() {

		$headers = [];

		if ( empty( $this->mail['from'] ) || empty( $this->mail['from'][0] ) || ! is_array( $this->mail['from'][0] ) ) {
			return false;
		} else {
			$headers[] = 'From: ' . self::parse_email_array( $this->mail['from'][0] );
		}

		if ( ! empty( $this->mail['cc'] ) ) {
			$cc = '';
			foreach ( $this->mail['cc'] as $email ) {
				$cc .= ( empty( $cc ) ? ' ' : ', ' ) . self::parse_email_array( $email );
			}
			$headers[] = 'cc:' . $cc;
		}

		if ( ! empty( $this->mail['bcc'] ) ) {
			$bcc = '';
			foreach ( $this->mail['bcc'] as $email ) {
				$bcc .= ( empty( $bcc ) ? ' ' : ', ' ) . self::parse_email_array( $email );
			}
			$headers[] = 'bcc: ' . $bcc;
		}

		$this->mail['headers'] = $headers;

		return true;
	}

	/**
	 * To
	 *
	 * Creates an array of emails from the recipients array
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function to() {

		if ( empty( $this->mail['recipients'] ) ) {

			return false;
		}

		$to = [];

		foreach ( $this->mail['recipients'] as $recipient ) {
			$to[] = self::parse_email_array( $recipient );
		}

		if ( empty( $to ) ) {

			return false;
		}

		$this->mail['to'] = $to;

		return true;
	}

	/**
	 * From
	 *
	 * Validates and Sets/Replaces From
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array $from
	 *
	 * @return void
	 */
	public function from( $from = [] ) {
		$this->addressee( $from, 'from', true );
	}

	/**
	 * Recipients
	 *
	 * Sets the recipient(s) email address(es)
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array|string $recipients
	 * @param bool $replace whether to append or replace existing values
	 *
	 * @return void
	 */
	public function recipients( array $recipients = [], $replace = false ) {
		$this->addressee( $recipients, 'recipients', $replace );
	}


	/**
	 * CC (Carbon Copy)
	 *
	 * Validates and Sets/Replaces CC Emails
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array|string $recipients
	 * @param bool $replace whether to append or replace existing values
	 *
	 * @return void
	 */
	public function cc( array $recipients = [], $replace = false ) {
		$this->addressee( $recipients, 'cc', $replace );
	}

	/**
	 * BCC (Blank Carbon Copy)
	 *
	 * Sets or updates the email header for "bcc:"
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array|string $recipients
	 * @param bool $replace whether to append or replace existing values
	 *
	 * @return void
	 */
	public function bcc( array $recipients = [], $replace = false ) {
		$this->addressee( $recipients, 'bcc', $replace );
	}

	/**
	 * Subject
	 *
	 * Sets the email subject line
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string $subject
	 *
	 * @return void
	 */
	public function subject( $subject = '' ) {

		if ( empty( $subject ) || ! is_string( $subject ) ) {

			return;
		}

		$this->mail['subject'] = esc_html( $subject );
	}

	/**
	 * Message
	 *
	 * Sets the message content
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string $message
	 * @param bool $html
	 *
	 * @return void
	 */
	public function message( $message = '', $html = true ) {

		if ( empty( $message ) || ! is_string( $message ) ) {

			return;
		}

		if ( ! $html ) {
			$this->mail['html'] = false;
		}

		$this->mail['message'] = wp_kses_post( $message );
	}

	/**
	 * Attach
	 *
	 * Adds an attachment to the message while keeping the existing ones, if any
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function attach( $attachment = '' ) {
		if ( empty( $attachment ) || ! is_string( $attachment ) ) {

			return;
		}

		if ( file_exists( $attachment ) ) {
			$this->mail['attachments'][] = $attachment;
		}
	}

	/**
	 * Attachments
	 *
	 * Replaces the message attachment(s)
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array
	 *
	 * @return void
	 */
	public function attachments( array $attachments = [] ) {
		if ( empty( $attachments ) && ! is_array( $attachments ) ) {
			return;
		}
		foreach ( $attachments as $index => $attachment ) {
			if ( ! file_exists( $attachment ) ) {
				unset( $attachments[ $index ] );
			}
		}
		if ( ! empty( $attachments ) ) {
			$this->mail['attachments'] = $attachments;
		}
	}

	/**
	 * Send
	 *
	 * Initializes wp_mail and sends message
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string $name A name for the email for logging purposes
	 *
	 * @return void
	 */
	public function send( $name = 'matador' ) {

		if ( ! $this->prepare( $name ) ) {
			return;
		}

		if ( $this->mail['html'] ) {
			add_filter( 'wp_mail_content_type', [ __CLASS__, 'set_html' ], 22 );
		}

		$status = wp_mail( $this->mail['to'], $this->mail['subject'], $this->mail['message'], $this->mail['headers'], $this->mail['attachments'] );

		if ( $this->mail['html'] ) {
			remove_filter( 'wp_mail_content_type', [ __CLASS__, 'set_content_type_html' ], 22 );
		}

		switch ( $status ) {
			case null:
			case false:
				new Event_Log( "email-send-failed-$name", __( 'Email failed for unknown issue.', 'matador-jobs' ) );
				break;
			case true:
				new Event_Log( "email-send-success-$name", __( 'Email sent to ', 'matador-jobs' ) . implode( ', ', $this->mail['to'] ) );
				break;
		}
	}

	/**
	 * Prepare
	 *
	 * Prepares the email objects for WPMail, also validates the email has all the parts needed.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string $name A name for the email for logging purposes
	 *
	 * @return bool
	 */
	public function prepare( $name = 'matador' ) {

		if ( ! $this->headers() ) {
			$error = __( 'No valid "From" email address', 'matador-jobs' );
		}

		if ( ! $this->to() ) {
			$error = __( 'No valid "To" email address', 'matador-jobs' );
		}

		if ( ! $this->mail['subject'] ) {
			$error = __( 'No email subject', 'matador-jobs' );
		}

		if ( ! $this->mail['message'] ) {
			$error = __( 'No email content', 'matador-jobs' );
		}

		if ( isset( $error ) ) {
			$error_prefix = __( 'Email failed:', 'matador-jobs' ) . ' ';
			new Event_Log( "email-send-failed-$name", $error_prefix . $error );
			return false;
		}

		return true;
	}

	/**
	 * Set HTML
	 *
	 * Returns a string for the WP Mail filter to set the content type as HTML
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @return string
	 */
	public static function set_html() {

		return 'text/html';
	}

	/**
	 * Addressee
	 *
	 * A helper function to validate an email address and assign it to the mail object for any email address recieving
	 * header, aka: to, from, cc, and bcc.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string|array $email
	 * @param string $header must be 'to', 'from', 'cc', or 'bcc'
	 * @param bool $replace whether to append or replace the existing value(s), if any
	 *
	 * @return void
	 */
	private function addressee( $email, $header, $replace ) {

		if ( ! in_array( $header, [ 'recipients', 'from', 'cc', 'bcc' ], true ) ) {
			// $error = cannot set email to this header

			return;
		}

		if ( empty( $email ) ) {
			// $error = no email address passed.

			return;
		}

		if ( ! is_string( $email ) && ! is_array( $email ) ) {
			// $error = email is not a string or array

			return;
		}

		if ( is_array( $email ) ) {

			if ( ! array_key_exists( 'email', $email ) ) {

				if ( array_key_exists( 'name', $email ) ) {
					// $error = email has 'name' key but not 'email' key
					return;
				}

				if ( ! array_key_exists( 0, $email ) ) {
					// $error = invalid array of emails
					return;
				}

				if ( ! is_string( $email[0] ) && empty( $email[0]['email'] ) ) {
					// $error = invalid array items in array of emails
					return;
				}

				if ( true === $replace ) {
					$this->mail[ $header ] = [];
				}

				foreach ( $email as $each ) {
					$this->addressee( $each, $header, false );
				}

				return;
			} else {
				if ( self::is_email( $email ) ) {
					$email = self::parse_email_string( self::parse_email_array( $email ) );
				} else {

					return;
				}
			}
		} else {
			$email = self::parse_email_string( $email );
		}

		$existing = $this->mail[ $header ];

		if ( ! empty( $existing ) && ! $replace ) {
			$new = true;
			foreach ( $existing as $index => $each ) {
				if ( $email['email'] === $each['email'] ) {

					$this->mail[ $header ][ $index ] = $email;

					$new = false;

					break;
				}
			}
			if ( $new ) {
				$this->mail[ $header ][] = $email;
			}
		} else {
			$this->mail[ $header ] = [ $email ];
		}
	}
}
