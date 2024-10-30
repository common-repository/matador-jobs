<?php
/**
 * Template: Taxonomy Terms as List
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/jobs-taxonomies-list.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 * @since       3.3.0 largely rewritten to make template cleaner and easier to customize with actions.
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before include:
 * @var array  $taxonomy
 * @var string $label
 * @var string $as
 * @var string $method
 * @var bool   $multi
 * @var bool   $show_all_option
 * @var mixed  $class
 * @var array  $terms
 * @var array  $args (contains all the above in one array)
 */
?>

<?php
/**
 * Action: Matador Taxonomy Terms Before
 *
 * Fires before opening markup for the [matador_taxonomy] shortcode and matador_taxonomy() function.
 *
 * @since 3.3.0
 *
 * @var array $args
 */
do_action( 'matador_taxonomy_terms_before', $args );
?>

<?php if ( $label ) : ?>

<h4 class="<?php matador_build_classes( 'matador-terms-header', "matador-taxonomy-{$taxonomy['slug']}-header" ); ?>"><?php echo esc_html( $label ); ?></h4>

<?php endif; ?>

<ul class="<?php matador_build_classes( 'matador-terms', 'matador-terms-list', "matador-taxonomy-{$taxonomy['slug']}-terms-list", $class ); ?>">

	<?php
	/**
	 * Action: Matador Taxonomy Terms Before Terms
	 *
	 * Fires before terms but after opening markup for the [matador_taxonomy] shortcode and matador_taxonomy() function.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_before_terms', $args );
	?>

	<?php
	if ( in_array( $show_all_option, array( 'both', 'both_if', 'before', 'before_if' ), true ) ) {
		matador_get_template_part( 'jobs-taxonomies-list', 'all', $args );
	}
	?>

	<?php foreach ( $terms as $term ) : ?>

		<?php $args['term'] = $term; ?>

		<?php matador_get_template_part( 'jobs-taxonomies-list', 'term', $args ); ?>

	<?php endforeach; ?>

	<?php
	if ( in_array( $show_all_option, array( 'both', 'both_if', 'after', 'after_if' ), true ) ) {
		matador_get_template_part( 'jobs-taxonomies-list', 'all', $args );
	}
	?>

	<?php
	/**
	 * Action: Matador Taxonomy Terms After Terms
	 *
	 * Fires after terms but before closing markup for the [matador_taxonomy] shortcode and matador_taxonomy() function.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_after_terms', $args );
	?>

</ul>

<?php
/**
 * Action: Matador Taxonomy Terms After
 *
 * Fires after closing markup for the [matador_taxonomy] shortcode and matador_taxonomy() function.
 *
 * @since 3.3.0
 *
 * @var array $args
 */
do_action( 'matador_taxonomy_terms_after', $args );
?>
