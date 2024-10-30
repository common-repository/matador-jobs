<?php
/**
 * Matador Software / Matador Campaign Traffic Monitor / Parser
 *
 * This class allows us to access cookie objects like we would an array by implementing the ArrayAccess PHP class.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Matador Campaign Traffic Monitor
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2019-2021 Matador Software, LLC
 *
 * @see         https://github.com/jamesflight/Google-Analytics-Cookie-Parser-PHP where this was forked
 * @see         https://joaocorreia.io/google-analytics-php-cookie-parser.html where inspiration was found
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace MatadorSoftware\CampaignTrafficMonitor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Parser
 *
 * @since 1.0.0
 */
class Parser {

	/**
	 * Constructor
	 * @since 1.0.0
	 */
	public function __construct() {}

	/**
	 * Parse Cookie
	 *
	 * Checks valid cookie type and passes the cookie string to appropriate cookie parser Class.
	 * @since 1.0.0
	 *
	 * @param  string $cookie_name
	 *
	 * @return Cookie|bool
	 */
	public function parse( $cookie_name ) {

		$class = __NAMESPACE__ . '\\' . ucfirst( $cookie_name );

		if ( ! class_exists( $class ) ) {
			throw new \InvalidArgumentException( "'" . $cookie_name . "' is not a supported cookie." );
		}

		$cookie = $this->get_cookie( $cookie_name );

		if ( $cookie ) {

			$parser = new $class();

			return $parser->parse( $cookie );
		}

		return false;
	}

	/**
	 * Get Cookie
	 *
	 * Wrapper for $_COOKIE (especially for testing purposes).
	 *
	 * Note: JFlight includes this function for testing purposes but states it itself cannot be tested.
	 * @since 1.0.0
	 *
	 * @param string $name
	 *
	 * @return string|bool
	 */
	public function get_cookie( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			return $_COOKIE[ $name ];
		} else {
			return false;
		}
	}
}
