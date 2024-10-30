<?php
/**
 * Admin Template Part : Field License Key
 *
 * Template for the special settings field called 'license_key'. This template can not overridden.
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
 *
 * @var string $name
 * @var string $class
 * @var string $label
 * @var string $sublabel
 * @var string $value
 * @var array  $attributes
 */

use \matador\Updater;

?>

<div class="<?php matador_build_classes( $class ); ?>">

	<div class="matador-label">

		<?php if ( $label ) : ?>
			<h5 class="matador-field-label">
				<label>
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

		printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" %4$s />', esc_attr( $name ), esc_attr( $name ), esc_attr( $value ), esc_attr( matador_build_attributes( $attributes ) ) );

		$status = matador\Matador::setting( 'license_core_status' );

		?>

		<div class="matador-field-description">

			<?php
			switch ( $status ) :

				case ( false ) :
					?>

					<div class="callout callout-warning">
						<p>
							<?php esc_html_e( 'Enter your license key and then "Activate Site" to access automatic updates and add-ons.', 'matador-jobs' ); ?>
						</p>
					</div>
					<input type="submit" name="license_activate" class="button button-primary" value="<?php echo esc_attr( __( 'Activate License', 'matador-jobs' ) ); ?>" />

					<?php
					break;
				case( 'valid' ) :
					?>

					<div class="callout callout-success">
						<p>
							<?php echo Updater::get_license_status_description( $status ) ; ?>
						</p>
					</div>
					<input type="submit" name="license_deactivate" class="button button-secondary" value="<?php echo esc_attr( __( 'Deactivate Site', 'matador-jobs' ) ); ?>" />
					<?php
					break;
				default:
					?>

					<div class="callout callout-error">
						<p>
							<?php echo Updater::get_license_status_description( $status ) ?>
						</p>
					</div>
					<input type="submit" name="license_activate"  class="button button-primary"  value="<?php echo esc_attr( __( 'Activate License', 'matador-jobs' ) ); ?>" />

				<?php endswitch; ?>

		</div>

	</div>

</div>
