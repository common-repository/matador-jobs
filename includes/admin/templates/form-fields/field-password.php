<?php
/**
 * Admin Template Part : Field Password
 *
 * Admin override password type settings field. This template can not overridden.
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
			<h6 class="matador-field-sublabel"><label>
					<?php echo esc_html( $sublabel ); ?>
				</label></h6>
		<?php endif; ?>

	</div>
	<div class="matador-field">

		<?php printf( '<input type="password" id="%1$s" name="%1$s" value="%2$s" %3$s />', esc_attr( $name ), esc_attr( $value ), esc_attr( matador_build_attributes( $attributes ) ) ); ?>

		<a href="#" class="show-password">
			<span class="show">
				<?php echo esc_html__( 'Show', 'matador-jobs' ); ?>
			</span>
			<span class="hide">
				<?php echo esc_html__( 'Hide', 'matador-jobs' ); ?>
			</span>
		</a>

		<?php if ( $description ) : ?>
			<div class="matador-field-description">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>

	</div>

</div>
