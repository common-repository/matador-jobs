<?php
/**
 * Template Part : Select Field
 *
 * Template part to present select input form fields. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-select.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 * @since       3.6.0 added support for option groups via nested arrays in options.
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
}

/**
 * Defined before include:
 *
 * @var string $type
 * @var string $label
 * @var string $sublabel
 * @var string $description
 * @var string $name
 * @var array $options
 * @var array $attributes
 * @var array|string $class
 * @var string $default
 * @var string $value
 * @var array $args
 */

// In select form field types, you may want to use the default setting instead of placeholder attribute
if ( ! empty( $default ) && ( ! is_string( $value ) && ! is_int( $value ) && empty( $value ) ) ) {
	$value = $default;
}

?>

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

			<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo matador_build_attributes( $attributes ); ?>>

				<?php foreach ( $options as $option_value => $option_name ) : ?>

					<?php
					/*
					 * If the option name has an array, the option name is the key for an option group.
					 */
					if ( is_array( $option_name ) ) :
						?>

						<optgroup label="<?php echo esc_html( $option_value ); ?>">

							<?php foreach ( $option_name as $sub_opt_value => $sub_opt_name ) : ?>

								<?php printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $sub_opt_value ), selected( $value, $sub_opt_value, false ), esc_html( $sub_opt_name ) ); ?>

							<?php endforeach; ?>

						</optgroup>

						<?php

					else :

						?>

						<?php printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option_value ), selected( $value, $option_value, false ), esc_html( $option_name ) ); ?>

						<?php

					endif;

					?>

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
