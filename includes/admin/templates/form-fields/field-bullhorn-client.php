<?php
/**
 * Admin Template Part : Field Bullhorn API Connect
 *
 * Template for the special settings field called 'bullhorn-client'. This template can not overridden.
 *
 * @link        https://matadorjobs.com/
 * @since       3.1.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Form-Fields
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
			<h6 class="matador-field-sublabel">
				<?php echo esc_html( $sublabel ); ?>
			</h6>
		<?php endif; ?>

	</div>
	<div class="matador-field">

		<?php printf( '<input type="text" id="%1$s" name="%1$s" value="%2$s" %3$s />', esc_attr( $name ), esc_attr( $value ), matador_build_attributes( $attributes ) ); ?>

		<?php if ( isset( $error ) || ( $value && ! matador\Matador::setting( 'bullhorn_api_client_is_valid' ) ) ) : ?>
			<div class="callout callout-error">
				<p><?php echo esc_html__( 'Your Bullhorn Client ID is invalid. Double check you entered it correctly, and if so, you may need to submit a support ticket to Bullhorn.', 'matador-jobs' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<div class="matador-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>

	</div>

</div>
