<?php
/**
 * Template: Jobs Listings Content - No Jobs
 *
 * Override this theme by copying it to yourtheme/matador/jobs-listings-empty.php.
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
 * @var array $args
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
do_action( 'matador_jobs_before', null, 'empty', $args );

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
do_action( 'matador_jobs_before_jobs', null, 'empty' );
?>

<p>
	<?php esc_html_e( 'We are sorry, but there are currently no jobs to show.', 'matador-jobs' ); ?>
</p>

<?php
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
do_action( 'matador_jobs_after_jobs', null, 'empty' );

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
do_action( 'matador_jobs_after', null, 'empty', $args );
