<?php
/**
 * Template: Jobs Listings Content - List Jobs
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/jobs-listings-list.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
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
do_action( 'matador_jobs_before', $jobs, 'list', $args );
?>

<ul class="<?php matador_build_classes( 'matador-jobs', 'matador-jobs-list', $class ); ?>">

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
	 * @param string $context
	 */
	do_action( 'matador_jobs_before_jobs', $jobs, 'list' );

	while ( $jobs->have_posts() ) :

		$jobs->the_post();

		matador_get_template_part( 'jobs-list', 'job', array_merge( $args, array( 'index' => $index++ ) ), 'parts' );

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
	 * @param string $context
	 */
	do_action( 'matador_jobs_after_jobs', $jobs, 'listing' );
	?>

</ul>

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
do_action( 'matador_jobs_after', $jobs, 'list', $args );
