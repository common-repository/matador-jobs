<?php
/**
 * Template: The Job Field
 *
 * Controls the output of the matador_the_job_field() function and [matador_job_field] shortcode, which are
 * used by various templates and functions. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/parts/the-job-field.php
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
 * @var int|null $id        ID of the Job
 * @var string   $key       String with the key name of the meta.
 * @var string   $before    String (with HTML) to present before the field.
 * @var string   $after     String (with HTML) to present after the field.
 * @var string   $class     String of class(es), space separated.
 * @var string   $context   Template context.
 */
?>

<span class="<?php matador_build_classes( 'matador-job-field', "matador-job-field-$key", $class ); ?>">

	<?php if ( $before ) : ?>

		<?php echo wp_kses_post( $before ); ?>

	<?php endif; ?>

	<?php matador_the_job_meta( $key, $id, $context ); ?>

	<?php if ( $after ) : ?>

		<?php echo wp_kses_post( $after ); ?>

	<?php endif; ?>

</span>
