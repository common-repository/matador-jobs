<?php
/**
 * Template Part : Checkbox Field
 *
 * Template part to present checkbox type form fields. Override this theme
 * by copying it to wp-content/themes/{yourtheme}/matador/form-fields/field-checkbox.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
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
 * @var string $label
 * @var string $sublabel
 * @var string $description
 * @var string $name
 * @var mixed  $value
 * @var array  $attributes
 * @var array  $options
 */
?>


<div class="<?php matador_build_classes( $class ); ?>">

	<div class="matador-label">

		<?php if ( $label ) : ?>
			<h5 class="matador-field-label"><label>
					<?php echo esc_html( $label ); ?>
				</label></h5>
		<?php endif; ?>

		<?php if ( $sublabel ) : ?>
			<h6 class="matador-field-sublabel">
				<?php echo esc_html( $sublabel ); ?>
			</h6>
		<?php endif; ?>

	</div>

	<div class="matador-field">

		<ul class="checkboxes">

			<?php if ( $options && is_array( $options ) ) : ?>

				<?php // The way Matador Settings works, a field must be submitted, even if empty. ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[]" />

				<?php foreach ( $options as $option_value => $option_name ) : ?>

					<li class="checkbox">
						<?php
						$id      = $name . '-' . $option_value;
						$checked = '';
						if ( is_array( $value ) ) {
							$checked = checked( in_array( strval( $option_value ), $value, true ), true, false );
						}

						$input = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s %5$s />', $id, $name, $option_value, $checked, matador_build_attributes( $attributes ) );
						printf( '<label for="%1$s">%2$s %3$s</label>', esc_attr( $id ), $input, wp_kses_post( $option_name ) ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</li>

				<?php endforeach; ?>

			<?php else : ?>

				<li class="checkbox">

					<?php // The way Matador Settings works, a field must be submitted, even if empty. ?>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>" />

					<?php
						printf( '<label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s %3$s/>%4$s</label>',
						esc_attr( $name ), checked( $value, 1, false ), esc_attr( matador_build_attributes( $attributes ) ), esc_html( $label ) );
					?>

				</li>

			<?php endif; ?>

		</ul>

		<?php if ( $description ) : ?>
			<div class="matador-field-description"><?php echo wp_kses_post( $description ); ?></div>
		<?php endif; ?>

	</div>

</div>
