<?php
/**
 * Template: Taxonomies Terms List Term
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-taxonomies-list-term.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.3.0 extracted from original parent template
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
 * @var \WP_Term $term
 * @var array $taxonomy
 * @var string $method
 * @var bool $multi
 * @var array $args (contains all the above in one array)
 */
?>

<?php
$url     = matador_get_term_link( $term, $taxonomy['key'], $method, $multi );
$current = matador_is_current_term( $term, $taxonomy['key'] ) ? 'matador-term-current' : null;
?>

<li class="<?php matador_build_classes( 'matador-term', "matador-term-{$term->slug}", "matador-taxonomy-{$taxonomy['name']}-term-{$term->slug}", $current ); ?>">

	<?php
	/**
	 * Action: Matador Taxonomy Terms Before Term
	 *
	 * Fires before each term in [matador_taxonomy] shortcode an matador_taxonomy() function. Use with the
	 * arguments to target specific terms, specific taxonomy terms, or all terms.
	 *
	 * @since 3.3.0
	 *
	 * @var \WP_Term $term
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_before_term', $term, $args );
	?>

	<a href="<?php echo esc_url( $url ); ?>">
		<span class="matador-screen-reader-text">
			<?php

			if ( 'filter' === $method && $current ) {
				$screen_reader_text = __( 'Hide jobs filed under', 'matador-jobs' );
			} elseif ( 'filter' === $method ) {
				$screen_reader_text = __( 'Show jobs filed under', 'matador-jobs' );
			} else {
				$screen_reader_text = __( 'View jobs filed under', 'matador-jobs' );
			}

			/**
			 * Filter: Matador Taxonomy Term Screen Reader Text
			 *
			 * To ensure ease of use, Matador includes some a11y phrases. This can be filtered to for clearer
			 * statements.
			 *
			 * @since 3.3.0
			 *
			 * @var string
			 * @var \WP_Term $term
			 * @var array $args
			 */
			echo esc_html( apply_filters( 'matador_taxonomy_terms_term_screen_reader_text', $screen_reader_text, $term, $args ) );
			?>
		</span>
		<?php
		/**
		 * Filter: Matador Taxonomy Term Term Label
		 *
		 * @since 3.3.0
		 *
		 * @var string
		 * @var \WP_Term $term
		 * @var array $args
		 */
		echo esc_html( apply_filters( 'matador_taxonomy_terms_term_label', $term->name, $term, $args ) );
		?>
	</a>

	<?php
	/**
	 * Action: Matador Taxonomy Terms After Term
	 *
	 * Fires after each term in [matador_taxonomy] shortcode an matador_taxonomy() function. Use with the
	 * arguments to target specific terms, specific taxonomy terms, or all terms.
	 *
	 * @since 3.3.0
	 *
	 * @var \WP_Term $term
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_after_term', $term, $args );
	?>

</li>
