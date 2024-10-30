<?php
/**
 * Matador / Utilities / Parse Email
 *
 * Trait to parse emails to/from strings/arrays in both `email@email.com` and `Name <email@email.com>` formats.
 *
 * @link        https://matadorjobs.com/
 *
 * @since       3.6.0 as RFC2822 Trait
 * @since       3.8.0 as ParseEmail Trait
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
 * Trait ParseEmail
 *
 * @package MatadorJobs\Email
 *
 * @since 3.6.0 as Rfc2822
 * @since 3.8.0 as ParseEmail
 */
trait ParseEmail {

	/**
	 * Is Email?
	 *
	 * Accepts an array or string and determines if either the 'email' key in the array or the string is valid.
	 *
	 * @since 3.6.0
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
	 * Parse Array Into Email String
	 *
	 * Accepts an array with key 'email' required and key 'name' optional and returns a string
	 *
	 * @since 3.6.0
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
	 * Parse Email String Into Array
	 *
	 * Accepts a string and determines if it is name/email string or just email and returns parts after validating email
	 * has at least one `@` symbol. Examples:
	 *
	 * `email@example.ext` returns `[ 'name' => '', 'email' => 'email@example.net' ]`
	 * `User Name <email@example.ext>` returns `[ 'name' => 'User Name', 'email' => 'email@example.net' ]`
	 *
	 * Formerly validated against RFC2822 standard, but the standard is outdated with RFC6531 replacing it, and that is
	 * nearly impossible to validate against.
	 *
	 * @since 3.6.0
	 * @since 3.8.0 removed RFC2822 email validation.
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	static public function parse_email_string( $email ) {

		$matches_email        = [];
		$matches_name_email   = [];

		$result  = [];

		preg_match( "/^(.+)\s\<(.+)\>$/", $email, $matches_name_email );

		// Check for valid email as per RFC 2822 spec.
		if ( ! empty( $matches_name_email ) && ! empty( $matches_name_email[2] ) ) {
			$result['name']   = $matches_name_email[1];
			$result['email']  = $matches_name_email[2];
		}

		preg_match( "/^(.+)$/", $email, $matches_email );

		// Check for valid email as per RFC 822 spec.
		if ( empty( $matches_name_email ) && ! empty( $matches_email ) && ! empty( $matches_email[1] ) ) {
			$result['name']   = '';
			$result['email']  = $matches_email[1];
		}

		// Check if email has `@` sign, which with RFC6531 is basically the only thing we can use to verify it is an email.
		// if ( isset( $result['email'] ) && str_contains( $result['email'], '@' ) ) { // PHP 8.0 implements str_contains
		if ( isset( $result['email'] ) && false !== strpos( $result['email'], '@' ) ) { // PHP 5.6+

			return $result;
		}

		return [];
	}
}
