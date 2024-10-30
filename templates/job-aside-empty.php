<?php
/**
 * Template: Job Aside Empty
 *
 * Controls the output of the matador_job_aside() and [matador_job] shortcode when a job is not found. Shows one of two
 * messages to site admins based on whether. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/job-aside.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
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
?>

<aside class="matador-job-aside matador-job-aside-empty <?php echo esc_attr( implode( $args['class'] ) ); ?>">

	<h4><?php esc_html_e( 'No Job Found', 'matador-jobs' ); ?></h4>

	<?php
	esc_html_e(
		"We're sorry. We were tying to show you an awesome job, but the position is no longer accepting applicants.",
		'matador-jobs'
	);
	?>

</aside>
