<?php
/**
 * Matador / Email / Message Abstract Class
 *
 * @link        http://matadorjobs.com/
 * @since       3.6.0
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

use \matador\Matador;
use \Mustache_Engine;

/**
 * Abstract Class Message
 *
 * @since 3.6.0
 *
 * @package matador\MatadorJobs\Email
 *
 * @abstract
 */
abstract class MessageAbstract implements MessageInterface {

	/**
	 * Key
	 *
	 * Give your message a name so logging can communicate which email is being sent. Spaces should be separated by
	 * dashes (-) not underscores (_) or other characters. IE: 'administrator-error' not 'administrator_error'.
	 *
	 * @since 3.6.0
	 *
	 * @var string
	 */
	public static $key = 'default';

	/**
	 * Message
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

		$email->message( self::body( $args ) );

		$email->attachments( self::attachments( $args ) );

		$email->send( static::$key );
	}

	/**
	 * From
	 *
	 * Determine the email sending address. Extend or call with filter. Should recieve the default from field, set in
	 * instatiation of the Email class, with values inherited from defaults set by Matador settings or the site.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $from
	 * @param array $args
	 *
	 * @return string|array
	 */
	protected static function from( array $from = [], array $args = [] ) {
		/**
		 * Filter: Matador Email [Dynamic] Recipients
		 *
		 * Modify the email FROM recipients. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_from'.
		 *
		 * @since 3.6.0
		 *
		 * @var array  $recipients
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_from', $from, $args );
	}

	/**
	 * Recipients
	 *
	 * Determine To recipients. Extend or call with filter.
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
	protected static function recipients( array $args = [] ) {

		if ( Matador::setting( 'admin_email' ) ) {
			$recipients = [ 'email' => Matador::setting( 'admin_email' ) ];
		} else {
			$recipients = [ 'email', get_bloginfo( 'admin_email' ) ];
		}

		/**
		 * Filter: Matador Email [Dynamic] Recipients
		 *
		 * Modify the email TO recipients. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_recipients'.
		 *
		 * @since 3.6.0
		 *
		 * @var array  $recipients
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_recipients', $recipients, $args );
	}

	/**
	 * CC
	 *
	 * Determine CC recipients. Extend or call with filter.
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
	protected static function cc( array $args = [] ) {
		/**
		 * Filter: Matador Email [Dynamic] CC
		 *
		 * Modify the email CC recipients. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_cc'.
		 *
		 * @since 3.6.0
		 *
		 * @var array  $recipients
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_cc', [], $args );
	}

	/**
	 * BCC
	 *
	 * Determine BCC recipients. Extend or call with filter.
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
	protected static function bcc( array $args = [] ) {
		/**
		 * Filter: Matador Email [Dynamic] BCC
		 *
		 * Modify the email BCC recipients. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_bcc'.
		 *
		 * @since 3.6.0
		 *
		 * @var array  $recipients
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_bcc', [], $args );
	}

	/**
	 * Subject
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
		/**
		 * Filter: Matador Email [Dynamic] Subject
		 *
		 * Modify the email subject. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_subject'.
		 *
		 * @since 3.6.0
		 *
		 * @var string $subject
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_subject', __( 'Email from Matador Jobs', 'matador-jobs' ), $args );
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
			$template = static::$key;
		}

		$renderer = new Mustache_Engine();

		$template = matador_get_template( $template . '.php', $args, 'emails' );

		/**
		 * Filter: Matador Email [Dynamic] Body
		 *
		 * Modify the email body. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_body'.
		 *
		 * @since 3.6.0
		 *
		 * @var string $template
		 * @var array  $args
		 *
		 * @return string
		 */
		$template = apply_filters( 'matador_email_' . static::key_underscored() . '_body', $template, $args );

		return $renderer->render( $template, $args );
	}

	/**
	 * Attachments
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
	protected static function attachments( array $args = [] ) {

		/**
		 * Filter: Matador Email [Dynamic] Attachments
		 *
		 * Modify the email attachments. Requires knowing the key from the extended class. IE if the key is
		 * 'administrator-error' the filter will be 'matador_email_administrator_error_attachments'.
		 *
		 * @since 3.6.0
		 *
		 * @var array  $attachments
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_attachments', [], $args );
	}

	/**
	 * Key Underscored
	 *
	 * Returns the static class key, which should be formatted with dashes as spaces, with underscores as spaces.
	 *
	 * @since 3.6.0
	 *
	 * @access protected
	 * @static
	 *
	 * @return string
	 */
	protected static function key_underscored() {

		return str_replace( '-', '_', static::$key );
	}
}
