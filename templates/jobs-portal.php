<?php
/**
 * Template: Jobs Portal
 *
 * Override this theme by copying it to yourtheme/matador/jobs-portal.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2022, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use matador\Matador;

/**
 * Defined before include:
 *
 * @var array      $args
 * @var array|bool $header
 * @var array|bool $jobs
 * @var array|bool $sidebar
 * @var array|bool $footer
 * @var string     $headline
 * @var array      $class
 */

/**
 * Action Matador Jobs Before
 *
 * Runs before everything else at the start of a "Matador Portal" function or shortcode call.
 *
 * @since 3.8.0
 *
 * @param WP_Query $jobs
 * @param string $context
 * @param array  $args
 */
do_action( 'matador_template_portal_before', $args );
?>

<div class="<?php matador_build_classes( 'matador-portal', $class ); ?>">

	<?php if ( isset( $header ) && ! empty( $header ) ) : ?>

		<div class="matador-portal-header">

			<?php foreach( $header as $header_part ) : ?>

				<?php if ( 'headline' === $header_part ) : ?>

					<h2 class="matador-portal-headline"><?php echo esc_html( $headline ); ?></h2>

				<?php elseif ( 'search' === $header_part ) : ?>

					<?php matador_search_form(); ?>

				<?php elseif ( is_array( $header_part ) && isset( $header_part['search'] ) ) : ?>

					<?php matador_search_form( [ 'fields' => $header_part['search'] ] ); ?>

				<?php elseif ( has_action( "matador_template_portal_$header_part" ) ) : ?>

					<?php do_action( "matador_template_portal_$header_part" ); ?>

				<?php endif; ?>

			<?php endforeach; ?>

		</div>

	<?php endif; ?>

	<div class="matador-portal-body-container">

		<?php if ( isset( $jobs ) && ! empty( $jobs ) ) : ?>

			<div class="matador-portal-body">

				<?php foreach( $jobs as $part ) : ?>

					<?php if ( 'headline' === $part ) : ?>

						<h2 class="matador-portal-headline"><?php echo esc_html( $headline ); ?></h2>

					<?php elseif ( 'search' === $part ) : ?>

						<?php matador_search_form(); ?>

					<?php elseif ( is_array( $part ) && isset( $part['search'] ) ) : ?>

						<?php matador_search_form( [ 'fields' => $part['search'] ] ); ?>

					<?php elseif ( 'jobs' === $part ) : ?>

						<?php matador_get_jobs(); ?>

					<?php elseif ( is_array( $part ) && isset( $part['jobs'] ) ) : ?>

						<?php matador_get_jobs( [ 'fields' => $part['jobs'] ] ); ?>

					<?php elseif ( ! is_array( $part ) && has_action( "matador_template_portal_$part" ) ) : ?>

						<?php do_action( "matador_template_portal_$part" ); ?>

					<?php endif; ?>

				<?php endforeach; ?>

			</div>

		<?php else : ?>

			<div class="matador-portal-body">

				<?php matador_get_jobs(); ?>

			</div>

		<?php endif; ?>

		<?php if ( isset( $sidebar ) && ! empty( $sidebar ) ) : ?>

			<div class="matador-portal-aside">

				<?php foreach( $sidebar as $part ) : ?>

					<?php if ( 'headline' === $part ) : ?>

						<p class="matador-portal-headline"><?php echo esc_html( $headline ); ?></p>

					<?php elseif ( 'search' === $part ) : ?>

						<?php matador_search_form(); ?>

					<?php elseif ( is_array( $part ) && isset( $part['search'] ) ) : ?>

						<?php matador_search_form( [ 'fields' => $part['search'] ] ); ?>

					<?php elseif ( in_array( $part, array_keys( Matador::variable( 'job_taxonomies' ) ), true ) ) : ?>

						<?php matador_taxonomy( [ 'taxonomy' => $part, 'method' => 'filter' ] ); ?>

					<?php elseif ( has_action( "matador_template_portal_$part" ) ) : ?>

						<?php do_action( "matador_template_portal_$part" ); ?>

					<?php endif; ?>

				<?php endforeach; ?>

			</div>

		<?php endif; ?>

	</div>

	<?php if ( isset( $footer ) && ! empty( $footer ) ) : ?>

		<div class="matador-portal-footer">

			<?php foreach( $footer as $part ) : ?>

				<?php if ( 'headline' === $part ) : ?>

					<p class="matador-portal-headline"><?php echo esc_html( $headline ); ?></p>

				<?php elseif ( 'search' === $part ) : ?>

					<?php matador_search_form(); ?>

				<?php elseif ( is_array( $part ) && isset( $part['search'] ) ) : ?>

					<?php matador_search_form( [ 'fields' => $part['search'] ] ); ?>

				<?php elseif ( has_action( "matador_template_portal_$part" ) ) : ?>

					<?php do_action( "matador_template_portal_$part" ); ?>

				<?php endif; ?>

			<?php endforeach; ?>

		</div>

	<?php endif; ?>

</div>

<?php
/**
 * Action Matador Jobs After
 *
 * Runs after everything else at the end of a "Matador Portal" function or shortcode call.
 *
 * @since 3.8.0
 *
 * @param array  $args
 */
do_action( 'matador_template_portal_after', $args );
