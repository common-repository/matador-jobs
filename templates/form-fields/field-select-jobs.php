<?php
/**
 * Template Part : Select Jobs Field
 *
 * Template part to present a multi-select form field with a list of Matador jobs. Override this theme
 * by copying it to yourtheme/matador/form-fields/field-select-jobs.php.
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

		<?php

		$limit = apply_filters( 'matador_application_select_jobs_limit', 200 );

		$args = array(
			'as'     => 'select',
			'selected' => isset( $selected ) ? $selected : null,
			'limit' => $limit,
		);

		matador_get_jobs( $args );

		?>

	</div>

</div>
