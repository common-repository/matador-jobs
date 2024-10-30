<?php
/**
 * Template: Job Partial for Jobs List
 *
 * Override this theme by copying it to /wp-content/themes/{yourtheme}/matador/parts/jobs-listings-list-each.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Parts
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
 * @var $index
 * @var $args
 */

$context = 'list';

/**
 * Matador Job Before (Action)
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param string $context
 * @param int    $index
 */
do_action( 'matador_job_before', $context, $index );

$classes = array(
	'matador-job',
	'matador-job-' . $context,
	'matador-job-' . get_the_ID(),
	'matador-job-nth-' . $index,
	0 === $index % 2 ? 'matador-job-even' : 'matador-job-odd',
);

if ( get_post_meta( get_the_ID(), 'isRemote', true ) ) {
	$classes[] = 'matador-job-remote';
}
?>

<li class="<?php matador_build_classes( $classes ); ?> ">

	<?php
	/**
	 * Matador Job Before Content
	 *
	 * After the jobs' wrapper, before the template content.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added param $context
	 *
	 * @param string $context
	 */
	do_action( 'matador_job_before_content', $context );

	matador_the_job_title_link( get_the_ID(), $context );

	/**
	 * Matador Job After Content
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added param $context
	 *
	 * @param string $context
	 */
	do_action( 'matador_job_after_content', $context );
	?>

</li>

<?php
/**
 * Matador Job After (Action)
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param int    $index
 * @param string $context
 */
do_action( 'matador_job_after', $context, $index );
