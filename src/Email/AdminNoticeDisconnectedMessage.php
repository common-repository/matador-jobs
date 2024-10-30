<?php
/**
 * Matador / Email / Admin Notice Message
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Helper;
use matador\Matador;
use matador\Event_Log;

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
	 * @since 3.8.0 added 'url' to args array
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function message( array $args = [] ) {

		if ( Helper::is_local_site() || Helper::is_staging_site() ) {

			return;
		}

		if ( '0' === Matador::setting( 'notify_admin' ) ) {

			new Event_Log( 'email-send-' . self::$key . '-disabled', __( 'Matador wants to warn the administrator of an error, but email notices are disabled. Turn on in settings for future notices.', 'matador-jobs' ) );

			return;
		}

		if ( empty( $args['force'] ) ) {

			$on_timeout = get_transient( Matador::variable( 'email_timeout-' . self::$key, 'transients' ) );
			$on_delay   = get_transient( Matador::variable( 'email_delay-' . self::$key, 'transients' ) );

			if ( false === $on_delay ) {

				set_transient( Matador::variable( 'email_delay-' . self::$key, 'transients' ), 1, DAY_IN_SECONDS );

				return;
			}

			if ( 5 >= (int) $on_delay ) {

				set_transient( Matador::variable( 'email_delay-' . self::$key, 'transients' ), ++$on_delay, DAY_IN_SECONDS );

				return;
			}

			if ( false !== $on_timeout ) {

				new Event_Log( 'email-send-' . self::$key . '-throttled', __( 'Matador wants to warn the administrator of an error, but has already sent one recently.', 'matador-jobs' ) );

				return;
			}
		}

		$args['sitename'] = get_bloginfo( 'name' );

		$args['url'] = get_site_url();

		$email = new Email();

		$email->recipients( self::recipients( $args ) );

		$email->subject( self::subject( $args ) );

		$email->message( self::body( 'admin-notification-bullhorn-disconnected', $args ) );

		$email->send( self::$key );

		set_transient( Matador::variable( 'email_timeout-' . self::$key, 'transients' ), true, DAY_IN_SECONDS );
	}

	/**
	 * Subject
	 *
	 * @since 3.6.0
	 * @since 3.8.0 changed value in subject placeholder from Site Name to Site URL.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected static function subject( array $args = [] ) {
		$site_url = get_site_url();

		// Translators: Placeholder is the site name
		$subject = sprintf( __( 'Matador Jobs Plugin on %s is experiencing a connection issue requiring immediate intervention', 'matador-jobs' ), $site_url );

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
		return apply_filters( 'matador_email_' . self::key_underscored() . '_subject', $subject, $args );
	}
}
