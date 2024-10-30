<?php
/**
 * Template Part : Select Field
 *
 * Template part to present select input form fields. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-multi.php.
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

<?php if ( $options || is_array( $options ) ) : ?>

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
				<h6 class="matador-field-sublabel">
					<?php echo esc_html( $sublabel ); ?>
				</h6>
			<?php endif; ?>

		</div>

		<div class="matador-field">

			<select multiple id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>[]" <?php echo matador_build_attributes( $attributes ); ?>>

				<?php foreach ( $options as $option_value => $option_name ) : ?>

					<?php
                    if ( is_array( $value ) ) {
                        $selected = selected( in_array( strval( $option_value ), $value, true ), true, false );
                    } else {
                        $selected = selected( $value, $option_value, false );
                    }
                    ?>

					<?php printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option_value ), $selected , esc_html( $option_name ) ); ?>

				<?php endforeach; ?>

			</select>

			<?php if ( $description ) : ?>
				<div class="matador-field-description">
					<?php echo wp_kses_post( $description ); ?>
				</div>
			<?php endif; ?>


		</div>

	</div>

<?php endif; ?>
