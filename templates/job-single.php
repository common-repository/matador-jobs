<?php
/**
 * Template: Job Single Content
 *
 * Controls the output of the matador_job_aside and [matador_job] shortcode. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/job-aside.php.
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
 * @var $content
 */

$context = 'single';

/**
 * Matador Job Before Content
 *
 * After the jobs' wrapper, before the template content.
 *
 * @since 3.0.0
 *
 * @param string $context
 */
do_action( 'matador_job_before_content', $context );

echo $content;

/**
 * Matador Job After Content
 *
 * @since 3.0.0
 * @since 3.4.0 added param $context
 *
 * @param string $context
 */
do_action( 'matador_job_after_content', $context );