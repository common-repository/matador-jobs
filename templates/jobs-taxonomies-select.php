<?php
/**
 * Template: Taxonomy Terms as Select
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/jobs-taxonomies-select.php.
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
 * @var array $taxonomy
 * @var string $label
 * @var string $as
 * @var string $method
 * @var bool $multi
 * @var bool $show_all_option
 * @var mixed $class
 * @var array $terms
 * @var array $args (contains all the above in one array)
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

<div class="<?php matador_build_classes( 'matador-terms', 'matador-terms-select', "matador-taxonomy-{$taxonomy['slug']}-terms-select", $class ); ?>">

	<?php
	/**
	 * Action: Matador Taxonomy Terms Before Terms
	 *
	 * Fires before terms but after opening markup for the [matador_taxonomy] shortcode and matador_taxonomy() function.
	 *
	 * Note: Action fires in both list and select type templates. Pass your function the $args attribute and use the
	 * $args['as'] to narrow the focus of your action.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_before_terms', $args );
	?>

	<label for="<?php echo esc_attr( $taxonomy['key'] ); ?>">
		<span class="matador-screen-reader-text">
		<?php
		if ( 'value' === $method && ! $multi ) {
			$screen_reader_text = __( 'Limit jobs to this', 'matador-jobs' ) . ' ' . $taxonomy['single'];
		} elseif ( 'value' === $method && $multi ) {
			$screen_reader_text = __( 'Limit jobs to these', 'matador-jobs' ) . ' ' . $taxonomy['plural'];
		} else {
			$screen_reader_text = __( 'View jobs in this', 'matador-jobs' ) . ' ' . $taxonomy['single'];
		}
		/**
		 * Filter: Matador Taxonomy Terms Select Label Screen Reader Text
		 *
		 * To ensure ease of use, Matador includes some a11y phrases. This can be filtered to for clearer
		 * statements.
		 *
		 * @since 3.3.0
		 *
		 * @var string
		 * @var array $args
		 */
		echo esc_html(
			apply_filters( 'matador_taxonomy_terms_select_label_screen_reader_text', $screen_reader_text, $args )
		);
		?>
		</span>
		<?php if ( $label ) : ?>
			<?php echo esc_html( $label ); ?>
		<?php endif; ?>
		<?php
		/**
		 * Action: Matador Taxonomy Terms Select Label
		 *
		 * Fires inside the <label> for the [matador_taxonomy] shortcode and matador_taxonomy() function in 'select'
		 * mode.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomy_terms_select_label', $args );
		?>
	</label>

	<select id="<?php echo esc_attr( $taxonomy['key'] ); ?>"
	        name="<?php echo esc_attr( $taxonomy['key'] ) . ( $multi ? '[]' : '' ); ?>"
	        data-method="<?php echo esc_attr( $method ); ?>" <?php echo ( $multi ) ? 'multiple' : ''; ?> >

		<?php if ( true === $show_all_option ) : ?>

			<?php matador_get_template_part( 'jobs-taxonomies-select', 'all', $args ); ?>

		<?php endif; ?>

		<?php foreach ( $terms as $term ) : ?>

			<?php $args['term'] = $term; ?>

			<?php matador_get_template_part( 'jobs-taxonomies-select', 'term', $args ); ?>

		<?php endforeach; ?>

	</select>


	<?php
	/**
	 * Action: Matador Taxonomy Terms After Terms
	 *
	 * Fires after closing markup for the [matador_taxonomy] shortcode and matador_taxonomy() function. Note: Action
	 * fires in both list and select type templates. Pass your function the $args attribute and use the $args['as'] to
	 * narrow the focus of your action.
	 *
	 * @since 3.3.0
	 *
	 * @var array $args
	 */
	do_action( 'matador_taxonomy_terms_after_terms', $args );
	?>

</div>

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
