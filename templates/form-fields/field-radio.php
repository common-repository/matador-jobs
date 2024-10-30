<?php
/**
 * Template Part : Radio Field
 *
 * Template part to present radio input form fields. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-radio.php.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Form-Fields
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php if ( $options && is_array( $options ) ) : ?>

	<div class="<?php matador_build_classes( $class ); ?>">

		<div class="matador-label">

			<?php if ( $label ) : ?>
				<h5 class="matador-field-label"><label>
					<?php echo esc_html( $label ) ?>
					</label></h5>
			<?php endif; ?>

			<?php if ( $sublabel ) : ?>
				<h6 class="matador-field-sublabel">
					<?php echo esc_html( $sublabel ); ?>
				</h6>
			<?php endif; ?>

		</div>

		<div class="matador-field">

			<ul class="radio-options">

				<?php foreach ( $options as $option_value => $option_name ) : ?>

					<li class="radio-option">

						<?php

						$id = $name . '-' . $option_value;

						$input = sprintf( '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s />', $id, $name, $option_value, checked( $value, $option_value, false ), matador_build_attributes( $attributes ) );

						printf( '<label for="%1$s">%2$s %3$s</label>', esc_attr( $id ), $input, esc_html( $option_name ) );

						?>

					</li>

				<?php endforeach; ?>

			</ul>

			<?php if ( $description ) : ?>
				<div class="matador-field-description"><?php echo wp_kses_post( $description ) ?></div>
			<?php endif; ?>


		</div>

	</div>

<?php endif; ?>
