<?php
/**
 * Template: The Job Info
 *
 * Controls the output of the matador_the_job_info() function and [matador_job_info] shortcode, which are
 * used by various templates and functions. Override this in your theme by copying it to
 * wp-content/themes/your-theme-folder/matador/parts/the-job-info.php
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
 * @var int|null $id ID of the Job
 * @var array $fields Array with the fields to include in the info header.
 * @var string $before String (with HTML) to present before the field.
 * @var string $after String (with HTML) to present after the field.
 * @var string $class String of class(es), space separated.
 * @var string $context Template context.
 */
?>

<ul class="<?php matador_build_classes( 'matador-job-meta', "matador-job-meta-job-{$id}", $class ); ?>">

	<?php if ( $before ) : ?>

		<?php
		echo wp_kses_post( $before );
		do_action( 'matador_job_info_before', $context, $fields, $args );
		?>

	<?php endif; ?>

	<?php foreach ( $fields as $key => $label ) : ?>

		<?php
		switch ( $key ) :

			// Fields that might be passed into $fields array but aren't in the meta header.
			case 'title':
			case 'info':
			case 'link':
			case 'content':
				// Silence is intended.
				break;

			case 'date':
				$value     = esc_html( matador_get_the_job_date() );
				$titleized = '';
				break;

			default:
				$value     = matador_get_the_job_meta( $key, $id, $context );
				$label     = esc_html( $label );
				$titleized = sanitize_title( $value );

		endswitch;

		if ( isset( $value ) && $value ) :

			$classes = array(
				'matador-job-meta-field',
				"matador-job-field-$key",
			);

			if ( isset( $titleized ) ) {
				$classes[] = "matador-job-field-value-{$titleized}";
			}

			do_action( 'matador_job_info_before_item', $key, $context );

			?>

			<li class="<?php matador_build_classes( $classes ); ?>">

				<?php

				do_action( 'matador_job_info_before_label', $key, $context );

				printf( '<span class="matador-job-meta-label">%1$s</span> ', wp_kses_post( $label ) );

				do_action( 'matador_job_info_after_label', $key, $context );

				do_action( 'matador_job_info_before_value', $key, $context );

				printf( '<span class="matador-job-meta-value">%1$s</span>', wp_kses_post( $value ) );

				do_action( 'matador_job_info_after_value', $key, $context );

				?>

			</li>

			<?php

			do_action( 'matador_job_info_after_item', $key, $context );

		endif;
		?>

		<?php unset( $value, $titleized ); ?>

	<?php endforeach; ?>

	<?php if ( $after ) : ?>

		<?php
		do_action( 'matador_job_info_after', $context, $fields, $args );
		echo wp_kses_post( $after );
		?>

	<?php endif; ?>

</ul>
