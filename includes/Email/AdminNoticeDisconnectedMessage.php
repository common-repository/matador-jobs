<?php
/**
 * Matador / Email / Admin Notice Message
 *
 * @link        http://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017-2020, Jeremy Scott
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \matador\Matador;
use \matador\Event_Log;

/**
 * Abstract Class Message
 *
 * @since 3.6.0
 *
 * @package matador\MatadorJobs\Email
 *
 * @final
 */
final class AdminNoticeDisconnectedMessage extends MessageAbstract {

	/**
	 * Key
	 *
	 * @since 3.6.0
	 *
	 * @var string
	 */
	public static $key = 'administrator-notice-disconnected';

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

		if ( '0' === Matador::setting( 'notify_admin' ) ) {

			new Event_Log( 'email-send-' . static::$key . '-disabled', __( 'Matador wants to warn the administrator of an error, but email notices are disabled. Turn on in settings for future notices.', 'matador-jobs' ) );

			return;
		}

		if ( false !== get_transient( Matador::variable( 'admin_email_timeout', 'transients' ) ) && empty( $args['force'] ) ) {

			new Event_Log( 'email-send-' . static::$key . '-throttled', __( 'Matador wants to warn the administrator of an error, but has already sent one recently.', 'matador-jobs' ) );

			return;
		}

		$args['sitename'] = get_bloginfo( 'name' );

		$email = new Email();

		$email->recipients( self::recipients( $args ) );

		$email->subject( self::subject( $args ) );

		$email->message( self::body( 'admin-notification-bullhorn-disconnected', $args ) );

		$email->send( static::$key );

		set_transient( Matador::variable( 'admin_email_timeout', 'transients' ), true, 24 * HOUR_IN_SECONDS );
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
		$site_name = get_bloginfo( 'name' );

		// Translators: Placeholder is the site name
		$subject = sprintf( __( 'Matador Jobs Plugin on %s is experiencing a connection issue requiring immediate intervention', 'matador-jobs' ), $site_name );

		/**
		 * Filter: Matador Email [Dynamic] Subject
		 *
		 * @since 3.6.0
		 *
		 * @var string $subject
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_email_' . static::key_underscored() . '_subject', $subject, $args );
	}
}
