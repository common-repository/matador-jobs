<?php
/**
 * Matador / Utilities / Email Validator Trait
 *
 * Trait to validate email to RFC 2822
 *
 * @link        https://matadorjobs.com/
 *
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Utilities
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2020-2021, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Utilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait ApplicationNotificationEmailTrait
 *
 * @package MatadorJobs\Email
 *
 * @since 3.6.0
 */
trait Rfc2822 {

	/**
	 * Validate RFC 2822 Email
	 *
	 * Accepts an array or string and determines if either the 'email' key in the array or the string is valid.
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param string|array email
	 *
	 * @return bool
	 */
	static public function is_email( $email ) {

		if ( is_string( $email ) ) {
			$test = $email;
		} elseif ( is_array( $email ) ) {
			$test = self::parse_email_array( $email );
		} else {
			$test = '';
		}

		return ! empty( self::parse_email_string( $test ) );
	}

	/**
	 * Parse Array Into RFC 2822 string
	 *
	 * Accepts an array with key 'email' required and key 'name' optional and returns a string
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param array $email
	 *
	 * @return string
	 */
	static public function parse_email_array( array $email ) {

		if ( ! array( $email ) || ! array_key_exists( 'email', $email ) ) {

			return '';
		}

		if ( ! self::is_email( $email['email'] ) ) {

			return '';
		}

		if ( ! empty( $email['name'] ) ) {

			return sprintf( '%s <%s>', $email['name'], $email['email'] );
		} else {

			return $email['email'];
		}
	}

	/**
	 * Parse RFC 2822 String Into Array
	 *
	 * Accepts a string and determines if it is a valid RFC 2822 compliant email string. And returns array of parts.
	 * Valid RFC 2822 examples are:
	 *
	 * email@example.ext
	 * User Name <email@example.ext>
	 *
	 * @since 3.6.0
	 *
	 *
	 * @param string email
	 *
	 * @return array
	 */
	static public function parse_email_string( $email ) {

		$email_matches  = [];
		$matches_normal = [];
		$matches_2822   = [];

		$from_regex   = '[a-zA-Z0-9_,\s\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
		$user_regex   = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
		$domain_regex = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
		$ipv4_regex   = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
		$ipv6_regex   = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';

		preg_match( "/^($from_regex)\s\<(($user_regex)@($domain_regex|(\[($ipv4_regex|$ipv6_regex)\])))\>$/", $email, $matches_2822 );
		preg_match( "/^($user_regex)@($domain_regex|(\[($ipv4_regex|$ipv6_regex)\]))$/", $email, $matches_normal );

		// Check for valid email as per RFC 2822 spec.
		if ( empty( $matches_normal ) && ! empty( $matches_2822 ) && ! empty( $matches_2822[4] ) ) {
			$email_matches['name']   = $matches_2822[1];
			$email_matches['email']  = $matches_2822[2];
			$email_matches['user']   = $matches_2822[3];
			$email_matches['domain'] = $matches_2822[4];
		}

		// Check for valid email as per RFC 822 spec.
		if ( empty( $matches_2822 ) && ! empty( $matches_normal ) && ! empty( $matches_normal[2] ) ) {
			$email_matches['name']   = '';
			$email_matches['email']  = $matches_normal[0];
			$email_matches['user']   = $matches_normal[1];
			$email_matches['domain'] = $matches_normal[2];
		}

		return $email_matches;
	}
}
