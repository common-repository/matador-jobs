<?php
/**
 * Admin Template : Upgrade
 *
 * @link        https://matadorjobs.com/
 * @since       3.5.6
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2019-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before include:
 *
 * @var $upgrades
 */

?>

<div class="wrap">

	<h1 class="matador-settings-page-title">
		<?php echo esc_html( apply_filters( 'matador_settings_page_title', esc_html__( 'Upgrade Matador', 'matador-jobs' ) ) ); ?>
	</h1>

	<form method="post" id="general_options_form" class="matador-settings-form">

		<?php wp_nonce_field( Matador::variable( 'options', 'nonce' ) ); ?>

		<?php if ( $upgrades ) : ?>

			<?php foreach ( $upgrades as $version => $description ) : ?>

				<?php
				$flag = str_replace( '.', '-', $version );
				?>

				<div class="matador-settings-section">

					<h4 class="matador-settings-section-title">
						<?php
						// Translators: The version being upgraded to
						echo esc_html( sprintf( __( 'Upgrade to %1$s', 'matador-jobs' ), $version ) );
						?>
					</h4>

					<?php echo wp_kses_post( $description ); ?>

					<input type="submit" class="button button-primary" value="Complete Upgrade" name="<?php echo esc_attr( $flag ); ?>" />

				</div>

			<?php endforeach; ?>

		<?php else : ?>

			<div class="matador-settings-section">

				<h4 class="matador-settings-section-title"><?php esc_html_e( 'All Upgrades Complete', 'matador-jobs' ); ?></h4>

				<p><?php esc_html_e( 'Thank you for upgrading Matador.', 'matador-jobs' ); ?></p>

				<p><a href="<?php echo esc_url( Matador::variable( 'options_url' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Return to Matador Settings', 'matador-jobs' ); ?></a></p>

			</div>

		<?php endif; ?>

		<input type="hidden" value="1" name="admin_notices">

	</form>

</div>
