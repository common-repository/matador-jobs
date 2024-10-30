<?php
/**
 * Template: Jobs Search Form Taxonomy Field
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-search-field-taxonomy.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.3.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before include:
 * @var array $field
 */

$args = array(
	'taxonomy' => $field,
	'as'       => 'select',
	'method'   => 'value',
	'class'    => 'matador-search-form-field-group matador-search-form-field-' . $field,
);

/**
 * Filter: Matador Search From Taxonomy Field Args
 *
 * Modifies the args array passed to matador_taxonomy() from the Jobs Search Taxonomy Field template.
 *
 * @since 3.3.0
 */
$args = apply_filters( 'matador_search_from_taxonomy_field_args', $args );

matador_taxonomy( $args );
