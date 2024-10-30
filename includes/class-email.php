<?php
/**
 * Matador / Emailer
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) || class_exists( 'Matador_Email' ) ) {
	exit;
}

class Email {

	/**
	 * Stores the email object.
	 *
	 * @var array
	 */
	public $mail = array();

	public function __construct() {
		$this->headers();
	}

	public function headers( $headers = array() ) {
		if ( empty( $headers ) ) {
			$headers = array( sprintf( 'From: %s < %s >', apply_filters( 'matador_email_from_name', get_bloginfo( 'name' ) ), apply_filters( 'matador_email_from_email', get_option( 'admin_email' ) ) ) );
		}
		$this->mail['headers'] = $headers;
	}

	public function subject( $subject ) {
		$this->mail['subject'] = $subject;
	}

	public function recipients( $recipients ) {
		$this->mail['to'] = $recipients;
	}

	public function message( $message ) {
		$this->mail['message'] = $message;
	}

	public function attachment( $attachment ) {
		$this->mail['attachments'][] = $attachment;
	}

	public function send() {
		$success = null;
		if ( ! isset( $this->mail['attachments'] ) ) {
			$this->mail['attachments'] = array();
		}

		if ( ! empty( $this->mail['subject'] ) && ! empty( $this->mail['message'] ) ) {
			add_filter( 'wp_mail_content_type', array( __CLASS__, 'set_content_type_html' ), 22 );

			$success = wp_mail( $this->mail['to'], $this->mail['subject'], $this->mail['message'], $this->mail['headers'], $this->mail['attachments'] );

			remove_filter( 'wp_mail_content_type', array( __CLASS__, 'set_content_type_html' ) );
		}

		return $success;
	}
}
