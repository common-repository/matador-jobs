<?php
/**
 * Template: Jobs Listings Content - Jobs Listing
 *
 * Override this theme by copying it to yourtheme/matador/jobs-listing.php.
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
 * @var string   $class
 * @var int      $index
 * @var array    $args
 * @var WP_Query $jobs
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
do_action( 'matador_jobs_before', $jobs, 'listing', $args );
?>

<div class="<?php matador_build_classes( 'matador-jobs', 'matador-jobs-listing', $class ); ?>">

	<?php
	/**
	 * Action: Matador Jobs Before Jobs
	 *
	 * Runs before the jobs loop during a "Matador Jobs" function or shortcode call.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added the $context parameter
	 *
	 * @param WP_Query $jobs
	 * @param string   $context
	 */
	do_action( 'matador_jobs_before_jobs', $jobs, 'listing' );

	while ( $jobs->have_posts() ) :

		$jobs->the_post();

		matador_get_template_part( 'jobs-listing', 'job', array_merge( $args, array( 'index' => $index++ ) ), 'parts' );

	endwhile;

	wp_reset_postdata();

	/**
	 * Action: Matador Jobs After Jobs
	 *
	 * Runs after the jobs loop during a "Matador Jobs" function or shortcode call.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added the $context parameter
	 *
	 * @param WP_Query $jobs
	 * @param string   $context
	 */
	do_action( 'matador_jobs_after_jobs', $jobs, 'listing' );
	?>

</div>

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
do_action( 'matador_jobs_after', $jobs, 'listing', $args );
