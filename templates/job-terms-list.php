<?php
/**
 * Template: Job Terms List
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/job-terms-list.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.2.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defined before include:
 * @var string $before
 * @var string $before_each
 * @var string $after_each
 * @var string $after
 * @var string $separator
 * @var mixed  $class
 * @var bool   $link
 * @var string $link_method
 * @var bool   $link_multi
 * @var array  $terms
 * @var string $taxonomy
 */
?>

<?php
/**
 * Action matador_job_terms_list_before
 *
 * Add content before a list of job terms and before its opening
 * markup is added. Action is passed the string name of the taxonomy
 * and the array of terms.
 *
 * @since 3.2.0
 *
 * @arg string $taxonomy
 * @arg array $terms
 */
do_action( 'matador_job_terms_list_before', $taxonomy, $terms );
?>

<?php if ( $before ) : ?>
	<?php echo wp_kses_post( $before ); ?>
<?php else : ?>
	<span class="<?php matador_build_classes( $class, 'matador-job-terms', 'matador-job-' . $taxonomy . '-terms' ); ?>">
<?php endif; ?>

<?php
/**
 * Action matador_job_terms_list_before_terms
 *
 * Add content before a list of job terms but after opening markup
 * was added. Action is passed the string name of the taxonomy and
 * the array of terms.
 *
 * @since 3.2.0
 *
 * @arg string $taxonomy
 * @arg array $terms
 */
do_action( 'matador_job_terms_list_before_terms', $taxonomy, $terms );
?>

<?php foreach ( $terms as $term ) : ?>

	<?php
	$i = isset( $i ) ? ++$i : 1;
	/**
	 * Filter matador_job_terms_list_separator
	 *
	 * Modify the separator character placed after a job term.
	 *
	 * @since 3.2.0
	 *
	 * @arg string $separator
	 */
	$separate = count( $terms ) > $i ? apply_filters( 'matador_job_terms_list_separator', $separator ) : '';
	?>

	<?php
	/**
	 * Action matador_job_terms_list_before_each
	 *
	 * Add content before each term in a list of job terms.
	 * Action is passed the string name of the taxonomy and
	 * the term array.
	 *
	 * @since 3.2.0
	 *
	 * @arg string $taxonomy
	 * @arg array $term
	 */
	do_action( 'matador_job_terms_list_before_each', $taxonomy, $term );
	?>

	<?php echo $before_each ? wp_kses_post( $before_each ) : null; ?>

	<?php if ( $link ) : ?>

		<?php
		$url     = matador_get_term_link( $term, $term->taxonomy, $link_method, $link_multi );
		$current = matador_is_current_term( $term, $term->taxonomy ) ? 'matador-job-term-current' : null;
		$classes = array( 'matador-job-term', 'matador-job-term-' . $term->slug, $current );
		?>

		<a href="<?php echo esc_url( $url ); ?>" class="<?php matador_build_classes( $classes ); ?>"><?php echo esc_html( $term->name ); ?></a><?php echo wp_kses_post( $separate ); ?>

		<?php

	else :

		echo esc_html( $term->name );
		echo wp_kses_post( $separate );

	endif;

	echo $after_each ? wp_kses_post( $after_each ) : null;

	/**
	 * Action matador_job_terms_list_after_each
	 *
	 * Add content after each term in a list of job terms.
	 * Action is passed the string name of the taxonomy and
	 * the term array.
	 *
	 * @since 3.2.0
	 *
	 * @arg string $taxonomy
	 * @arg array $term
	 */
	do_action( 'matador_job_terms_list_after_each', $taxonomy, $term );
	?>

<?php endforeach; ?>

<?php
/**
 * Action matador_job_terms_list_after_terms
 *
 * Add content after a list of job terms but before its closure
 * markup is added. Action is passed the string name of the
 * taxonomy and the array of terms.
 *
 * @since 3.2.0
 *
 * @arg string $taxonomy
 * @arg array $terms
 */
do_action( 'matador_job_terms_list_before_terms', $taxonomy, $terms );
?>
<?php if ( $after ) : ?>
	<?php echo wp_kses_post( $after ); ?>
<?php else : ?>
	</span>
<?php endif; ?>

<?php
/**
 * Action matador_job_terms_list_after
 *
 * Add content after a list of job terms and after its closure
 * was added. Action is passed the string name of the taxonomy
 * and the array of terms.
 *
 * @since 3.2.0
 *
 * @arg string $taxonomy
 * @arg array $terms
 */
do_action( 'matador_job_terms_list_after', $taxonomy );
?>
