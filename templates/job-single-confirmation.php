<?php
/**
 * Template: Job Single Content Append Application
 *
 * Override this theme by copying it to yourtheme/matador/jobs-single-append-application.php.
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
 * @var $content
 */

$context = 'confirmation';

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

matador_the_confirmation();

/**
 * Matador Job After Content
 *
 * @since 3.0.0
 * @since 3.4.0 added param $context
 *
 * @param string $context
 */
do_action( 'matador_job_after_content', $context );
