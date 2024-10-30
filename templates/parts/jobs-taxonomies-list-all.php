<?php
/**
 * Template: Taxonomy Terms as List All Item
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-taxonomies-list-all.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 * @since       3.3.0 largely rewritten, moved into parts/ folder
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
if ( $method && 'filter' === $method ) {
	$url                = remove_query_arg( $taxonomy['key'], matador\Helper::get_nopaging_url() );
	$terms_are_selected = ! empty( $_GET[ $taxonomy['key'] ] ); // WPCS: CSRF ok.
} else {
	$url                = matador_get_the_jobs_link();
	$terms_are_selected = is_tax();
}
?>

<li class="<?php matador_build_classes( 'matador-term', 'matador-term-all', "matador-taxonomy-{$taxonomy['slug']}-term-all", $terms_are_selected ?: 'matador-term-current' ); ?>" >

	<?php
	/**
	 * Action: Matador Taxonomy Terms before All Term
	 *
	 * Fires before the optional all term in the [matador_taxonomy] shortcode an matador_taxonomy() function. Use with
	 * the arguments to target specific taxonomies.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_before_all_term', $args );
	?>

	<a href="<?php echo esc_url( $url ); ?>">
		<span class="matador-screen-reader-text">
			<?php
			$pluralize = count( $terms ) === 1 ? esc_html( $taxonomy['single'] ) : esc_html( $taxonomy['plural'] );

			if ( 'filter' === $method && $terms_are_selected ) {
				// translators: the placeholder is for the plural or singular form of the taxonomy name
				$screen_reader_text = sprintf( __( 'Show jobs from all %s', 'matador-jobs' ), $pluralize );
			} elseif ( 'filter' === $method ) {
				// translators: the placeholder is for the plural or singular form of the taxonomy name
				$screen_reader_text = sprintf( __( 'Showing jobs from all %s', 'matador-jobs' ), $pluralize );
			} else {
				$screen_reader_text = __( 'View all jobs', 'matador-jobs' );
			}

			/**
			 * Filter: Matador Taxonomy All Term Screen Reader Phrase
			 *
			 * To ensure ease of use, Matador includes some a11y phrases.
			 *
			 * @since 3.3.0
			 *
			 * @var string $screen_reader_text
			 * @var bool   $terms_are_selected
			 * @var array  $args
			 */
			echo esc_html( apply_filters(
				'matador_taxonomy_terms_all_term_screen_reader_text',
				$screen_reader_text, $terms_are_selected, $args
			) );
			?>
		</span>
		<span aria-hidden="true">
			<?php
			if ( 'filter' === $method && $terms_are_selected ) {
				$label = __( 'Deselect All', 'matador-jobs' );
			} elseif ( 'filter' === $method ) {
				$label = __( 'All', 'matador-jobs' );
			} else {
				$label = __( 'All Jobs', 'matador-jobs' );
			}
			/**
			 * Filter: Matador Taxonomy All Term Name
			 *
			 * @since 3.3.0
			 *
			 * @var string $label
			 * @var array  $args
			 */
			echo esc_html( apply_filters( 'matador_taxonomy_terms_all_term_label', $label, $args ) );
			?>
		</span>
	</a>

	<?php
	/**
	 * Action: Matador Taxonomy Terms after All Term
	 *
	 * Fires after the optional all term in [matador_taxonomy] shortcode an matador_taxonomy() function. Use with the
	 * arguments to target specific taxonomies.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_after_all_term', $args );
	?>

</li>
