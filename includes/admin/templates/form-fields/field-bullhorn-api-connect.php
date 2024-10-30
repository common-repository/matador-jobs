<?php
/**
 * Admin Template Part : Field Bullhorn API Connect
 *
 * Template for the special settings field called 'bullhorn_api_connect'. This template can not overridden.
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

		<div class="matador-field-description">

			<?php $is_connected = matador\Matador::setting( 'bullhorn_api_is_connected' ) ?: false; ?>

			<?php if ( $is_connected ) : ?>

				<div class="callout callout-success">
					<p>
						<?php
						esc_html_e( 'Your site is connected to Bullhorn!', 'matador-jobs' );
						?>
					</p>
				</div>

			<?php else : ?>

				<div class="callout callout-error">
					<p>
						<?php
						esc_html_e( 'Your site is not connected to Bullhorn. Use the Connection Assistant to connect.', 'matador-jobs' );
						?>
					</p>
				</div>

			<?php endif; ?>

			<input id="matador_action" type="hidden" name="matador_action" value="" />

			<?php

			$format = '<button type="button" id="%1$s" class="%2$s"> %3$s</button> ';

			printf( $format, 'connect_to_bullhorn', 'button button-primary', esc_html__( 'Connection Assistant', 'matador-jobs' ) );

			/**
			 * Filter: Matador Bullhorn Job Listing Show Sync Buttons
			 * @see matador\Job_Listing::add_sync_now_button_to_job_listings_table for documentation
			 */
			if ( apply_filters( 'matador_bullhorn_job_listing_show_sync_buttons', true ) && $is_connected ) {
			    $format = '<button type="button" id="%1$s" class="%2$s"><img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" /> %3$s</button> ';
				printf( $format, 'sync', 'matador-admin-button-bullhorn button', esc_html__( 'Sync', 'matador-jobs' ) );
				printf( $format, 'sync-full', 'matador-admin-button-bullhorn button button-secondary', esc_html__( 'Hard Sync (Break Cache)', 'matador-jobs' ) );
				if ( isset( $_GET['adv-sync'] ) ) {
					printf( $format, 'sync-tax', 'sync', esc_html__( 'Sync Just Tax', 'matador-jobs' ) );
					printf( $format, 'sync-jobs', 'sync', esc_html__( 'Sync Just Jobs', 'matador-jobs' ) );
				}
			}

			?>

		</div>

	</div>

</div>

