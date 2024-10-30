<?php
/**
 * Template: The Job Title Link
 *
 * Controls the output of the matador_the_job_title_link() and matador_get_the_job_title_link() functions, which are
 * used by various templates and functions. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/parts/the-job-title-link.php
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

/**
 * Defined before include:
 *
 * @var int|null $id      ID of the Job
 * @var string   $context Template context.
 */
?>

<a href="<?php matador_the_job_link( $id, $context ); ?>" title="<?php matador_the_job_title( $id, 'anchor-title' ); ?>"><?php matador_the_job_title( $id, $context ); ?></a>
