<?php
/**
 * Template Part : Textarea Field
 *
 * Template part to present textarea form fields. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-textarea.php.
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
}
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
			<h6 class="matador-field-sublabel">
				<?php echo esc_html( $sublabel ); ?>
			</h6>
		<?php endif; ?>

	</div>
	<div class="matador-field">

		<?php printf( '<textarea id="%1$s" name="%1$s" %3$s>%2$s</textarea>', esc_attr( $name ), esc_html( $value ), matador_build_attributes( $attributes ) ); ?>

		<?php if ( $description ) : ?>
			<div class="matador-field-description">
				<?php echo wp_kses_post( $description ) ?>
			</div>
		<?php endif; ?>

	</div>

</div>
