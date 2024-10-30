<?php
/**
 * Template: Jobs Listings Content - Jobs Multiselect
 *
 * Override this theme by copying it to yourtheme/matador/jobs-listings-select.php.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
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
 *
 * @var string       $id
 * @var string       $class
 * @var string|array $selected
 * @var int          $index
 * @var array        $args
 * @var WP_Query     $jobs
 */

/**
 * Action Matador Jobs Before
 *
 * Runs before everything else at the start of a "Matador Jobs" function or shortcode call.
 *
 * @since 3.0.0
 * @since 3.4.0 added $jobs, $context and $args parameters.
 *
 * @param WP_Query $jobs
 * @param string $context
 * @param array  $args
 */
do_action( 'matador_jobs_before', $jobs, 'select', $args );
?>
<select id="<?php echo esc_attr( $id ); ?>"
	name="<?php echo esc_attr( $id ); ?>[]"
	class="<?php matador_build_classes( 'matador-jobs', 'matador-jobs-select', $class ); ?>"
	title="<?php esc_html_e( 'Select a Job', 'matador-jobs' ); ?>"
	<?php echo ( isset( $multi ) && $multi ) ? 'multiple' : ''; ?>>

	<?php
	/**
	 * Action: Template Before Jobs
	 *
	 * Runs after the jobs loop in templates that feature a loop of jobs.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added the $context parameter
	 *
	 * @param WP_Query $jobs
	 * @param string   $context
	 */
	do_action( 'matador_jobs_before_jobs', $jobs, 'select' );

	while ( $jobs->have_posts() ) :

		$jobs->the_post();
		?>

		<option value="<?php echo esc_attr( get_the_id() ); ?>" <?php selected( $selected, get_the_id() ); ?> >

			<?php echo esc_html( matador_get_the_job_title() ); ?>

		</option>

		<?php
	endwhile;

	wp_reset_postdata();

	/**
	 * Action: Matador Jobs After Jobs
	 *
	 * Runs after the jobs loop but before the container (ie: <ul>, <table>) in templates that feature a loop of jobs.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added the $context parameter
	 *
	 * @param WP_Query $jobs
	 * @param string   $context
	 */
	do_action( 'matador_jobs_after_jobs', $jobs, 'listing' );
	?>

</select>

<?php
/**
 * Action Matador Jobs After
 *
 * Runs after everything else at the end of a "Matador Jobs" function or shortcode call.
 *
 * @since 3.0.0
 * @since 3.4.0 added $jobs, $context and $args parameters.
 *
 * @param WP_Query $jobs
 * @param string $context
 * @param array  $args
 */
do_action( 'matador_jobs_after', $jobs, 'select', $args );
