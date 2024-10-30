<?php
/**
 * Template Part : File Field
 *
 * Template part to present file input form fields. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-file.php.
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
}?>

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

		<label for="<?php echo esc_attr( $name ); ?>" class="for-file">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
				<path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"></path>
				<span><?php echo esc_html( $label ); ?> &hellip;</span>
			</svg>
		</label>
		<input type="file" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>[]" multiple="multiple" class="inputfile" data-multiple-caption="{count} <?php esc_html_e( 'files selected', 'matador-jobs' ); ?>" <?php echo matador_build_attributes( $attributes ); ?> />


		<?php if ( $description ) : ?>
			<div class="matador-field-description"><?php echo wp_kses_post( $description ); ?></div>
		<?php endif; ?>

	</div>

</div>
