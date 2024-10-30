<?php
/**
 * Template Part : Switch Field
 *
 * Template part to present 'switch' form fields. This template can not overridden.
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates/Form-Fields
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before includes
 * @var string $label
 * @var string $sublabel
 * @var string $name
 * @var string $value
 */
?>

	<div class="<?php matador_build_classes( $class ); ?>">

		<div class="matador-label">

			<?php if ( $label ) : ?>
				<h5 class="matador-field-label">
					<?php echo esc_html( $label ); ?>
				</h5>
			<?php endif; ?>

			<?php if ( $sublabel ) : ?>
				<h6 class="matador-field-sublabel">
					<?php echo esc_html( $sublabel ); ?>
				</h6>
			<?php endif; ?>

		</div>

		<div class="matador-field">

			<div class="matador-switch">

				<input type="radio" id="<?php echo esc_attr( $name ), '-1'; ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php echo checked( $value, '1', false ), ' ', matador_build_attributes( $attributes ); ?> />
				<label for="<?php echo esc_attr( $name ), '-1'; ?>"><?php esc_html_e( 'On', 'matador-jobs' ); ?></label>

				<input type="radio" id="<?php echo esc_attr( $name ), '-0'; ?>" name="<?php echo esc_attr( $name ); ?>" value="0" <?php echo checked( $value, '0', false ), ' ', matador_build_attributes( $attributes ); ?> />
				<label for="<?php echo esc_attr( $name ), '-0'; ?>"><?php esc_html_e( 'Off', 'matador-jobs' ); ?></label>

				<span class="switch-outside"><span class="switch-inside"></span></span>
			</div>

			<?php if ( $description ) : ?>
				<div class="matador-field-description"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>

		</div>

	</div>

