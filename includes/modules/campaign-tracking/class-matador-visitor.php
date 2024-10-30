<?php
/**
 * Matador Software / Matador Campaign Traffic Monitor / UTMA Cookie
 *
 * This class handles the behavior for the Matador Traffic Cookie, which that tracks ongoing user visit history.
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

use \DateTimeImmutable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Matador Visitor Cookie
 *
 * @since 1.0.0
 *
 * @package matador\ReadGACookie
 */
class Matador_Visitor extends Cookie {

	/**
	 * Property "Time of First Visit"
	 *
	 * The Matador Visitor Cookie value for the time (in microtime) the cookie was set at.
	 *
	 * @since 1.0.0
	 * @var DateTimeImmutable
	 */
	public $timestamp;

	/**
	 * Property "Sessions"
	 *
	 * The Matador Visitor Cookie value for the number of sessions the user has had on the site.
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	public $sessions;

	/**
	 * Property "Campaigns"
	 *
	 * The Matador Visitor Cookie value for the total number of campaigns the user used to enter the site.
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	public $campaigns;

	/**
	 * Property "Source"
	 *
	 * The Matador Visitor Cookie value with the source the user used to find/enter the site (typically value of
	 * utm_source).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $source;

	/**
	 * Property "Campaign"
	 *
	 * The Matador Visitor Cookie value with the campaign name the user used to find/enter the site (typically value of
	 * utm_campaign).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $campaign;

	/**
	 * Property "Medium"
	 *
	 * The Matador Visitor Cookie value with the medium the user used to enter the site (typically value of utm_medium).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $medium;

	/**
	 * Property "Term"
	 *
	 * The Matador Visitor Cookie value with the term value the user used to enter the site (typically value of
	 * utm_term).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $term;

	/**
	 * Property "Content"
	 *
	 * The Matador Visitor Cookie value with the content value the user used to enter the site (typically value of
	 * utm_content).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $content;

	/**
	 * Property "Date"
	 *
	 * A general DateTime instance we can use to create new dateTime objects.
	 *
	 * @since 1.0.0
	 * @var DateTimeImmutable
	 */
	private $date;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {
		$this->date = new \DateTimeImmutable();
	}

	/**
	 * Method "Parse"
	 *
	 * Parses the string from the cookie and assigns the properties
	 *
	 * @since 1.0.0
	 * @param  string $cookie
	 *
	 * @return self
	 */
	public function parse( $cookie ) {
		$cookie_parts    = explode( '.', $cookie, 4 );
		$this->timestamp = $this->date->createFromFormat( 'U', $cookie_parts[0] );
		$this->sessions  = (integer) $cookie_parts[1];
		$this->campaigns = (integer) $cookie_parts[2];
		$this->set_campaign_data( $cookie_parts[3] );

		return $this;
	}

	/**
	 * Set Campaign Data
	 *
	 * The fourth part of the UTMZ Cookie is string of key=value parameters separated by pipes. Read that
	 * and set the remaining properties if present.
	 *
	 * @since 1.0.0
	 * @param string $campaign
	 *
	 * @return void
	 */
	protected function set_campaign_data( $campaign ) {

		if ( empty( $campaign ) || ! is_string( $campaign ) ) {
			return;
		}

		$campaign = $this->parse_campaign_data_string( $campaign );

		if ( empty( $campaign ) || ! is_array( $campaign ) ) {
			return;
		}

		foreach ( $campaign as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Parse Campaign Data String
	 *
	 * The fourth part of the UTMZ Cookie is string of key=value parameters separated by pipes. Convert the string into
	 * an array we can more easily use to set properties.
	 *
	 * @since 1.0.0
	 * @param string $campaign_string
	 *
	 * @return array
	 */
	protected function parse_campaign_data_string( $campaign_string ) {

		if ( empty( $campaign_string ) ) {
			return [];
		}

		$campaign_array = [];

		foreach ( explode( '|', $campaign_string ) as $pair ) {
			$parts                       = explode( '=', $pair );
			$campaign_array[ $parts[0] ] = $parts[1];
		}

		return $campaign_array;
	}

}
