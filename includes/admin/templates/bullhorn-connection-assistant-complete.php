<?php
/**
 * Admin Template : Bullhorn Connection Assistant Doctor
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
	<h4><?php esc_html_e( 'Bullhorn Connection Settings and Tools', 'matador-jobs' ); ?></h4>
</header>

<div>

	<?php // IS CONNECTED CHECK ?>

	<?php if ( Matador::setting( 'bullhorn_api_is_connected' ) ) : ?>

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
				esc_html_e( 'Your site is not connected to Bullhorn. Re-connect by clicking on the "Authorize" button below.', 'matador-jobs' );
				?>
			</p>
		</div>

	<?php endif; ?>

	<?php // IS APPROVED URL CHECK ?>

	<?php

	$api_redirect_uri = Admin_Tasks::is_uri_redirect_invalid();

	if ( 'null_url' === $api_redirect_uri ) :

		?>

		<div class="callout callout-warning">
			<p>
				<?php
				esc_html_e( 'Your site is set to a null redirect URL, which is not recommended and useful only in developer mode for advanced users. Matador cannot check if your URI Redirect is set.', 'matador-jobs' );
				?>
			</p>
		</div>

	<?php elseif ( 'invalid' === $api_redirect_uri ) : ?>

		<div class="callout callout-error">
			<p>
				<?php esc_html_e( "Callback URI is not registered with Bullhorn.", 'matador-jobs' ); ?>
				<a href="<?php esc_url( Bullhorn_Connection_Assistant::get_url() ); ?>"><?php esc_html_e( 'Troubleshoot.', 'matador-jobs' ); ?></a>
			</p>
		</div>

	<?php elseif ( 'indeterminate' === $api_redirect_uri ) : ?>

		<div class="callout callout-warning">
			<p>
				<?php esc_html_e( 'Matador is unable to check for a valid Callback URI. This is sometimes caused by a missing or invalid Client ID or Client Secret. Other causes are logged in the Matador Event Log. This may or may not signify an issue with your site.', 'matador-jobs' ); ?>
				<a href="<?php esc_url( Bullhorn_Connection_Assistant::get_url() ); ?>"><?php esc_html_e( 'Troubleshoot.', 'matador-jobs' ); ?></a>
			</p>
		</div>

	<?php endif; ?>

	<?php

	// IS LOGGED IN AS CORRECT USER

	$logged_in_user = get_transient( Matador::variable( 'bullhorn_logged_in_user', 'transients' ) );
	$settings_user  = matador::credential( 'bullhorn_api_user' );

	?>

	<?php if ( ! empty( $logged_in_user ) && $logged_in_user !== $settings_user ) : ?>
		<div class="callout callout-error">
			<p>
				<?php esc_html_e( 'Your site is connected to Bullhorn with a User ID that does not match the Bullhorn API User ID in your settings. This will cause Matador to behave in unexpected ways; we at Matador call this "the cookie bug."', 'matador-jobs' ); ?>
				<?php
				// Translators: Placeholder 1 and 2 are user ID strings for the Bullhorn system.
				printf( esc_html__( 'You are authenticated as %1$s when your declared user should be %2$s.', 'matador-jobs' ), sprintf( '<code>%s</code>', $logged_in_user ), sprintf( '<code>%s</code>', $settings_user ) );
				?>
				<?php printf( ' <a href="%1$s" target="_blank">%2$s</a>', 'https://docs.matadorjobs.com/articles/the-bullhorn-cookie-bug/', esc_html__( 'Read our help document on how to resolve this issue.', 'matador-jobs' ) ); ?>
			</p>
		</div>
	<?php endif; ?>

	<?php // IS PASSWORD URL-SAFE? ?>

	<?php
	$password_is_url_safe = Helper::is_url_safe_string( Matador::credential( 'bullhorn_api_pass' ) );
	if ( Matador::credential( 'bullhorn_api_pass' ) && ! $password_is_url_safe ) :
	?>

		<div class="callout callout-warning">
			<p>
				<?php
				$unsafe = '<kbd>%</kbd> <kbd>[</kbd> <kbd>]</kbd> <kbd>{</kbd> <kbd>}</kbd> <kbd>|</kbd> <kbd>\</kbd> <kbd>^</kbd> <kbd>?</kbd> <kbd>/</kbd>';
				printf( esc_html__( 'Your API Password contains URL unsafe characters. While valid, this will cause automatic reconnects to fail. Please update your password to exclude the following characters: %s', 'matador-jobs' ), $unsafe );
				?>
			</p>
		</div>

	<?php endif; ?>

	<?php // IS ABLE TO AUTO RECONNECT CHECK ?>

	<?php if ( $password_is_url_safe && 'valid' === $api_redirect_uri && Matador::credential( 'bullhorn_api_user' ) && Matador::credential( 'bullhorn_api_pass' ) ) : ?>

		<div class="callout callout-success">
			<p>
				<?php
				esc_html_e( 'Your site is set up for automatic reconnect attempts.', 'matador-jobs' );
				?>
			</p>
		</div>

	<?php else : ?>

		<div class="callout callout-warning">
			<p>
				<?php
				esc_html_e( 'Your site is not able to attempt automatic reconnects. Automatic reconnects require an API User and Password and valid callback URI.', 'matador-jobs' );
				?>
			</p>
		</div>

	<?php endif; ?>

	<?php // IS CAREER PORTAL DOMAIN ROOT MATCH ?>

	<?php if ( 1 !== (int) Matador::setting( 'bullhorn_ignore_career_portal_root' ) ) : ?>

		<?php if ( -1 === (int) get_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ) ) ) : ?>

			<?php
			$message[] = __( 'Your Matador Jobs job board URL on this site is not set in Bullhorn as your "Career Portal" URL. If this site operates as your primary job board, this will impact the performance of other Bullhorn integrations.', 'matador-jobs' );
			// Translators: placeholder is a URL.
			$message[] = sprintf( __( 'If this site is your primary job board, your "careerPortalDomainRoot" should be %s.', 'matador-jobs' ), '<code>' . trailingslashit( get_home_url() . '/' . Matador::setting( 'post_type_slug_job_listing' ) ) . '{?}</code>' );
			$message[] = sprintf( '<a href="%2$s" target="_blank">%1$s</a>', __( 'See our help documentation site for more information.', 'matador-jobs' ), esc_url( 'https://docs.matadorjobs.com/articles/bullhorn-careerportaldomainroot-setting/' ) );
			$message[] = '<br /><br />';

			$button    = '<a class="button button-primary" href="%s">%s</a><br />';
			$url       = add_query_arg( 'recheck_domain_root', true );
			$message[] = sprintf( $button, $url, esc_html__( 'Retest', 'matador-jobs' ) );
			?>

			<div class="callout callout-warning">
				<p>
					<?php
					echo implode( ' ', $message );
					?>
				</p>
			</div>

		<?php elseif ( 1 === (int) get_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ) ) ) : ?>

			<div class="callout callout-success">
				<p>
					<?php esc_html_e( 'Your Matador Jobs job board URL on this site is set in Bullhorn as your "Career Portal" URL.', 'matador-jobs' ); ?>
				</p>
			</div>

		<?php endif; ?>

	<?php endif; ?>

	<h4><?php esc_html_e( 'Bullhorn Connection Actions', 'matador-jobs' ); ?></h4>

	<?php
	$args = array(
		'name'       => 'matador-action',
		'value'      => 'authorize',
		'class'      => 'button-secondary',
		'label'      => __( 'Authorize Site', 'matador-jobs' ),
	);
	Template_Support::get_template_part( 'field', 'button', $args, 'form-fields', true, true );

	$args = array(
		'name'       => 'matador-action',
		'value'      => 'deauthorize',
		'class'      => 'button-secondary',
		'label'      => __( 'Deauthorize Site', 'matador-jobs' ),
	);
	Template_Support::get_template_part( 'field', 'button', $args, 'form-fields', true, true );

	$args = array(
		'name'       => 'matador-action',
		'value'      => 'test-reconnect',
		'class'      => 'button-secondary',
		'label'      => __( 'Test Auto Reconnect', 'matador-jobs' ),
	);
	Template_Support::get_template_part( 'field', 'button', $args, 'form-fields', true, true );

	$args = array(
		'name'       => 'matador-action',
		'value'      => 'reset-assistant',
		'class'      => 'button-secondary',
		'label'      => __( 'Reset Assistant', 'matador-jobs' ),
		'novalidate' => true,
	);
	Template_Support::get_template_part( 'field', 'button', $args, 'form-fields', true, true );

	?>

	<h4><?php esc_html_e( 'Edit Bullhorn Connection Settings', 'matador-jobs' ); ?></h4>

	<?php

	$fields = array( 'bullhorn_api_client', 'bullhorn_api_secret', 'bullhorn_api_user', 'bullhorn_api_pass', 'bullhorn_ignore_career_portal_root' );

	foreach ( $fields as $field ) {

		$field_args = Settings_Fields::instance()->get_field( $field );

		if ( is_array( $field_args ) ) {

			/**
			 * @wordpress-action Matador Admin Settings Before {Each} Field
			 *
			 * @since 3.8.2
			 */
			do_action( 'matador_settings_before_field_' . $field );

			list( $args, $template ) = Options::form_field_args( $field_args, $field );

			Template_Support::get_template_part( 'field', $template, $args, 'form-fields', true, true );

			/**
			 * @wordpress-action Matador Admin Settings After {Each} Field
			 *
			 * @since 3.8.2
			 */
			do_action( 'matador_settings_after_field_' . $field );
		}
	}

	?>
	<p>
		<?php
		esc_html_e( 'You must have the following URL registered with Bullhorn in order for Matador to be able to connect.', 'matador-jobs' );
		echo ' ';
		echo Matador::variable( 'api_redirect_uri' ) ?: trailingslashit( home_url() ) . trailingslashit( Matador::variable( 'api_endpoint_prefix' ) . 'authorize/' );
		?>
	</p>

</div>

<footer>
	<button type="submit" class="button-primary">
		<?php esc_html_e( 'Save Changes', 'matador-jobs' ); ?>
	</button>
	<button type="submit" name="exit" class="button-primary exit-connection-assistant">
		<?php esc_html_e( 'Save & Exit', 'matador-jobs' ); ?>
	</button>
</footer>
