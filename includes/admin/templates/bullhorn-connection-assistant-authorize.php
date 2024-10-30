<?php
/**
 * Admin Template : Bullhorn Connection Assistant Authorize Step
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace matador;

?>

<header>
	<h4><?php esc_html_e( 'Authorize Site', 'matador-jobs' ); ?></h4>
</header>

<?php if ( null !== Matador::setting( 'bullhorn_api_has_authorized' ) && null !== Matador::setting( 'bullhorn_api_is_connected' ) ) : ?>

	<div>

		<div class="callout callout-success">
			<p>
				<?php esc_html_e( 'Congratulations! You\'ve connected to Bullhorn.', 'matador-jobs' ); ?>
			</p>
		</div>

		<p>
			<?php
			esc_html_e( 'Congratulations. You are now connected. Use the "Complete Connection Assistant button" to advance to the connection information screen, where you can review your Bullhorn settings, see tools and information about your Bullhorn connection, and test your site\'s ability to auto-reconnect.', 'matador-jobs' );
			?>
		</p>

	</div>

	<footer>

		<?php
		$button_args = array(
			'label' => __( 'Complete Connection Assistant', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'complete',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</footer>

<?php else : ?>

	<div>

		<?php if ( ! get_option( 'permalink_structure' ) ) : ?>

			<div class="callout callout-error">
				<p>
					<?php esc_html_e( 'Before we can authorize your site, you must have a Permalink Structure defined.', 'matador-jobs' ); ?>
				</p>
			</div>

			<p>
				<?php
				// Translators: Placeholder is for a generated URL.
				echo wp_kses_post( sprintf(
					__('The time has come to finally connect your site to Bullhorn. Unfortunately, Matador requires your site supports "Pretty Permalinks" and that a Permalink structure, other than "Plain" is enabled. <a href="%s" target="_blank">Edit your permalinks on this options page</a>, and be sure to choose any option other than "plain". Once you\'ve made that change, return here to continue.', 'matador-jobs' ), esc_url( get_admin_url( null, 'options-permalink.php' ) ) ) );
				?>
			</p>

			<p>
				<?php
				// Translators: Placeholder is for a generated URL.
				echo wp_kses_post( sprintf( __('For more information on "Pretty Permalinks", <a href="%s">visit this WordPress support document</a>.', 'matador-jobs' ), esc_url( 'https://codex.wordpress.org/Using_Permalinks' ) ) );
				?>
			</p>

		<?php else : ?>

			<p>
				<?php
				echo esc_html__( 'The time has come to finally connect your site to Bullhorn. When you click on &quot;Authorize&quot;, you will be redirected to Bullhorn\'s site where you will be asked to log in with your API Username and Password and accept the terms and conditions. After you finish that step, you will be returned here.', 'matador-jobs' );
				?>
			</p>

			<?php
			$args = array(
				'name'  => 'matador-action',
				'value' => 'authorize',
				'class' => 'button-secondary',
				'label' => __( 'Authorize Site', 'matador-jobs' ),
			);
			Template_Support::get_template_part( 'field', 'button', $args, 'form-fields', true, true );
			?>

			<div class="callout callout-warning"><p>
				<strong>
					<?php
					esc_html_e( 'Having an issue? Read this!', 'matador-jobs' );
					?>
				</strong>
				<br /><br />
					<?php
					esc_html_e( 'There is a known issue when logging into Bullhorn where users with brand new API credentials get a "HTTP 500 Error" when attempting to authorize their site. This issue only affects the first login. We are working with our Bullhorn partners to resolve the cause of the error.', 'matador-jobs' );
					?>
				<br /><br />
					<?php
					esc_html_e( 'In the meantime, we have a simple work around. The issue is caused by having a Bullhorn cookie saved to browser, so there are three ways to fix this:', 'matador-jobs' );
					?>
				<br /><br />
					* <?php esc_html_e( 'Clear your cookies and re-try authorization.', 'matador-jobs' ); ?><br />
					* <?php esc_html_e( 'Use a Private Browsing/Incognito window, and re-try authorization.', 'matador-jobs' ); ?><br />
					* <?php esc_html_e( 'Advanced users may use a cookie manager to selectively remove Bullhorn-related cookies, and retry authorization.', 'matador-jobs' ); ?>
				<br /><br />
					<?php
					esc_html_e( 'We appreciate your patience while we work with our Bullhorn partners to resolve this issue. Reminder: once you\'ve logged in for the first time, you will never need to do this again!', 'matador-jobs' );
					?>
			</p></div>

		<?php endif; ?>

	</div>

	<footer>

		<?php
		$button_args = array(
			'label' => __( 'Go Back', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'callback',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
			'name'  => 'exit',
			'class' => 'button-secondary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</footer>

<?php endif; ?>
