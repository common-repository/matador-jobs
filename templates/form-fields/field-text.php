<?php
/**
 * Template Part : Text Field
 *
 * Template part to present a text field (and other types that behave and display
 * like text fields) form fields. Override this theme by copying it to
 * yourtheme/matador/form-fields/field-text.php.
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
/**
 * Defined before include:
 *
 * @var $name
 * @var $type
 * @var $value
 * @var $attributes
 * @var $class
 * @var $label
 * @var $sublabel
 * @var $description
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
			<h6 class="matador-field-sublabel">
				<?php echo esc_html( $sublabel ); ?>
			</h6>
		<?php endif; ?>

	</div>
	<div class="matador-field">

		<?php printf( '<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" %4$s />', esc_attr( $type ), esc_attr( $name ), esc_attr( $value ), matador_build_attributes( $attributes ) ); ?>

		<?php if ( $description ) : ?>
			<div class="matador-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>

		<?php if ( isset( $error ) ) : ?>
			<div class="callout callout-error">
				<p><?php echo esc_html( $error ); ?></p>
			</div>
		<?php endif; ?>

	</div>

</div>
