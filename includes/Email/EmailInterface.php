<?php
/**
 * Matador / Email Interface
 *
 * @link        http://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2020 Matador Software LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface EmailInterface
 *
 * @package MatadorJobs\Email
 *
 * @since 3.6.0
 */
interface EmailInterface {

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
	public function headers();

	/**
	 * To
	 *
	 * Creates an array of emails from the recipients array
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function to();

	/**
	 * From
	 *
	 * Sets the email header for "From:"
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param string|array Either array with keys 'name', 'email' or RFC 2822 compliant email address.
	 *
	 * @return void
	 */
	public function from( $from );

	/**
	 * Recipients
	 *
	 * Sets the recipient(s) email address(es)
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array $recipients Array of strings of emails, or array of arrays with 'email', 'name' fields.
	 *
	 * @return void
	 */
	public function recipients( array $recipients );

	/**
	 * CC (Carbon Copy)
	 *
	 * Sets the email header for "cc:"
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array $cc Array of strings of emails, or array of arrays with 'email', 'name' fields.
	 *
	 * @return void
	 */
	public function cc( array $cc );

	/**
	 * BCC (Blank Carbon Copy)
	 *
	 * Sets the email header for "bcc:"
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @param array $bcc Array of strings of emails, or array of arrays with 'email', 'name' fields.
	 *
	 * @return void
	 */
	public function bcc( array $bcc );

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
	public function subject( $subject = '' );

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
	public function message( $message = '', $html = true );

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
	public function attach( $attachment = '' );

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
	public function attachments( array $attachments = [] );

	/**
	 * Send
	 *
	 * Initializes wp_mail and sends message
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function send();

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
	public static function set_html();
}
