<?php
/**
 * Matador Software / Matador Campaign Traffic Monitor / CookieMonster
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
 * Class Cookie Monster
 *
 * @since 1.0.0
 */
class CookieMonster {

	/**
	 * Parse Cookie
	 *
	 * Checks valid cookie type and passes the cookie string to appropriate cookie parser Class.
	 * @since 1.0.0
	 *
	 * @param string $cookie_name Cookie name. Currently accepts 'utma' and 'utmz'
	 *
	 * @return Cookie|bool
	 */
	public static function eat_cookie( $cookie_name ) {

		$cookie = new Parser();

		return $cookie->parse( $cookie_name );
	}
}
