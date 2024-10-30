<?php
/**
 * Matador / ThirdParty / Akismet
 *
 * Note: Support for 3rd Party Plugins is provided as a courtesy and specific performance is not guaranteed.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.7
 *
 * @package     Matador Jobs Board
 * @subpackage  ThirdParty
 * @author      Matador US, LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2023, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\ThirdParty\Akismet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Akismet
 *
 * @since 3.8.7
 */
class Akismet {

	/**
	 * Constructor
	 *
	 * @since 3.8.7
	 */
	public function __construct() {
		add_action( 'init', [ __CLASS__, 'init' ] );
	}

	/**
	 * Run on WordPress Init
	 *
	 * @since 3.8.7
	 *
	 * @return void
	 */
	public static function init() {
		if ( defined( 'AKISMET_VERSION' ) ) {
			add_filter( 'matador_application_data_raw', [ __CLASS__, 'remove_akismet_fields' ] );
		}
	}

	/**
	 * Remove Akismet Fields From Matador Application Processor
	 *
	 * Akismet adds several hidden fields to all forms on a WordPress site. This class will clean up those fields so it
	 * does not impact application data saved to Bullhorn.
	 *
	 * @since 3.8.7
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	public static function remove_akismet_fields( $request ) {

		foreach ( $request as $key => $value ) {
			if ( str_contains( $key, 'ak_' ) ) {
				unset( $request[ $key ] );
			}
		}

		return $request;
	}
}
