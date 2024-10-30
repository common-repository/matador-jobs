<?php
/**
 * Admin Template Part : Select Page
 *
 * Template for the special settings field called 'page'. Creates a
 * menu of WP pages. This template can not overridden.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates/Parts
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Defined before include:
 * @var string $label
 * @var string $sublabel
 * @var string $name
 * @var mixed  $value
 * @var array  $attributes
 */
?>

<div class="<?php matador_build_classes( $class ); ?>">

	<div class="matador-label">

		<?php if ( $label ) : ?>
			<h5 class="matador-field-label">
				<label for="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			</h5>
		<?php endif; ?>
		<?php if ( $sublabel ) : ?>
			<h6 class="matador-field-sublabel"><label>
					<?php echo esc_html( $sublabel ); ?>
				</label></h6>
		<?php endif; ?>

	</div>

	<div class="matador-field">

		<?php

		wp_dropdown_pages( array(
			'name'                  => esc_attr( $name ),
			'id'                    => esc_attr( $name ),
			'depth'                 => 2,
			'selected'              => esc_attr( $value ),
			'show_option_no_change' => esc_attr( ! empty( $show_option_no_change ) ? $show_option_no_change : null ),
			'echo'                  => true,
		) );

		?>

		<?php if ( $description ) : ?>
			<div class="matador-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $error ) : ?>
			<div class="callout callout-error">
				<p><?php echo esc_html( $error ); ?></p>
			</div>
		<?php endif; ?>

	</div>

</div>
