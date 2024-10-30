<?php
/**
 * Matador Software / Matador Campaign Traffic Monitor / Cookie (Abstract)
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

use \ArrayAccess;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Class Cookie
 *
 * @since 1.0.0
 *
 * @abstract
 */
abstract class Cookie implements ArrayAccess {

	/**
	 * Offset Exists
	 *
	 * When the object as an array has checks if the given key (offset) exists.
	 *
	 * @since 1.0.0
	 * @param string $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		return property_exists( $this, $offset );
	}

	/**
	 * Offset Get
	 *
	 * When the object as an array has a value for a given key (offset) called.
	 *
	 * @since 1.0.0
	 * @param string $offset
	 *
	 * @return mixed The value of the given property.
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->$offset;
	}

	/**
	 * Offset Set
	 *
	 * When the object as an array has a value for a given key (offset) set.
	 *
	 * @since 1.0.0
	 * @param string $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->$offset = $value;
	}

	/**
	 * Offset Unset
	 *
	 * When the object as an array has a value for a given key (offset) unset.
	 *
	 * @since 1.0.0
	 * @param string $offset
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		$this->$offset = null;
	}

}
