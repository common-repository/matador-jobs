<?php
/**
 * Template Part : Hidden Field
 *
 * Template part to present a hidden field (and other types that behave and display
 * like text fields) form fields. Override this theme by copying it to
 * yourtheme/matador/form-fields/field-hidden.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.6.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Form-Fields
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017 - 2020 Jeremy Scott, Paul Bearne
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before include:
 *
 * @var $name
 * @var $type
 * @var $value
 * @var $attributes
 * @var $class
 * @var $label
 * @var $sublabel
 * @var $description
 */

printf( '<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" %4$s />', esc_attr( $type ), esc_attr( $name ), esc_attr( $value ), matador_build_attributes( $attributes ) );
