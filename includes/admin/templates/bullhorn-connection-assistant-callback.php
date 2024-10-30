<?php
/**
 * Admin Template : Bullhorn Connection Assistant Callback Step
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

	<?php
$api_redirect_uri = Admin_Tasks::is_uri_redirect_invalid();
if ( 'null_url' === $api_redirect_uri ) :
	?>

	<header>
		<h4><?php esc_html_e( 'Skip Callback Check', 'matador-jobs' ); ?></h4>
	</header>

	<div>
		<div class="callout callout-warning">
			<p>
				<?php
				esc_html_e( 'Your site is set to a null redirect URL, which is not recommended and useful only in developer mode for advanced users. We will skip this step in the connection assistant.', 'matador-jobs' );
				?>
			</p>
		</div>
	</div>

	<footer>

		<?php
		$button_args = array(
			'label' => __( 'Go Back', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'credentials',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
			'name'  => 'exit',
			'class' => 'button-secondary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Next Step', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'authorize',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</footer>

<?php elseif ( 'indeterminate' === $api_redirect_uri ) : ?>

	<header>
		<h4><?php esc_html_e( 'Unable to Check Redirect URI', 'matador-jobs' ); ?></h4>
	</header>

	<div>
		<div class="callout callout-warning">
			<p>
				<?php
				esc_html_e( 'Your site is unable to check if your Redirect URI is Registered with Bullhorn. This is usually caused by an incorrect or missing Client ID or Client Secret and/or if the Bullhorn service is currently down, eg: undergoing maintenance.', 'matador-jobs' );
				?>
			</p>
		</div>
		<p>
			<?php
			esc_html_e( 'You can choose to continue with the connection assistant, but we recommend you Go Back one step and verify your Client ID and Client Secret is correct first. If you continue to have issues, make sure Matador Event Logging is on, try again, and send a copy of your Event Log to Matador support.', 'matador-jobs' );
			?>
		</p>
		<?php
		$button_args = array(
			'label' => __( 'Re-check API URI Redirect', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'callback',
			'class' => 'button-secondary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>
	</div>

	<footer>

		<?php
		$button_args = array(
			'label' => __( 'Go Back', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'credentials',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
			'name'  => 'exit',
			'class' => 'button-secondary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Next Step', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'authorize',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</footer>

<?php elseif ( 'valid' === $api_redirect_uri ) : ?>

	<header>
		<h4><?php esc_html_e( 'Authorize Bullhorn', 'matador-jobs' ); ?></h4>
	</header>

	<div>
		<div class="callout callout-success">
			<p>
				<?php esc_html_e( 'Callback URI is Registered.', 'matador-jobs' ); ?>
			</p>
		</div>

		<p>
			<?php
			esc_html_e( 'When two software services talk to each other, like Matador and Bullhorn, they need to agree on a few things. One is where they\'ll send each other messages. Each software &quot;listens&quot; for each other to send messages to specific URLs.', 'matador-jobs' );
			?>
		</p>

		<p>
			<?php
			esc_html_e( 'For security purposes, Bullhorn requires that the callback URI be on a list of pre-approved allowed callback URIs before Matador can talk to it. Congratulations, your site\'s callback URI was valid, so we can move onto the next step!', 'matador-jobs' );
			?>
		</p>
	</div>

	<footer>
		<p>
			<?php
			esc_html_e( 'In the next and final step, we\'ll try to authorize your site with Bullhorn.', 'matador-jobs' );
			?>
		</p>

		<?php
		$button_args = array(
			'label' => __( 'Go Back', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'credentials',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
			'name'  => 'exit',
			'class' => 'button-secondary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

		$button_args = array(
			'label' => __( 'Next Step', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'authorize',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</footer>

<?php else : ?>

	<header>
		<h4><?php esc_html_e( 'Unregistered Callback URI', 'matador-jobs' ); ?></h4>
	</header>

	<div>
		<div class="callout callout-error">
			<p>
				<?php esc_html_e( 'Callback URI is not Registered with Bullhorn', 'matador-jobs' ); ?>
			</p>
		</div>

		<p>
			<?php
			esc_html_e( 'When two software services talk to each other, like Matador and Bullhorn, they need to agree on a few things. One is where they\'ll send each other messages. Each software &quot;listens&quot; for each other to send messages to specific URLs.', 'matador-jobs' );
			?>
		</p>

		<p>
			<?php
			esc_html_e( 'For security purposes, Bullhorn requires that the callback URI be on a list of pre-approved allowed callback URIs before Matador can talk to it. We\'ve checked: your site\'s callback URI is not valid. Before we can continue, you will need to get it added to the list of approved callback URIs.', 'matador-jobs' );
			?>
		</p>

		<p>
			<?php
			esc_html_e( 'You will need to send an email to Bullhorn support to request they add this URI to your allowed API Redirect URI\'s list. If you\'ve already asked them to do this and you\'re still seeing this message, its possible they didn\'t enter it exactly. This is callback or redirect URI for Matador.', 'matador-jobs' );
			?>
		</p>

		<?php $endpoint = Matador::variable( 'api_redirect_uri' ) ?: trailingslashit( home_url() ) . trailingslashit( Matador::variable( 'api_endpoint_prefix' ) . 'authorize/' ); ?>

		<code>
			<?php
			echo esc_url( $endpoint );
			?>
		</code>

		<div class="callout callout-info">
			<p>
				<?php
				esc_html_e( 'If you plan to use Matador on both a staging and a production site, you should ask for both site\'s callback URIs to be registered at the same time. It will save you from delays later on.', 'matador-jobs' );
				?>
			</p>
		</div>

		<p>
			<?php
			echo esc_html( __( 'To help you explain to them what you need, feel free to copy and paste, edit, and send this email to Bullhorn Support', 'matador-jobs' ) . ':' );
			?>
		</p>

		<?php

		$email  = '';
		$email .= __( 'Dear Bullhorn Support', 'matador-jobs' ) . ':' . PHP_EOL . PHP_EOL;
		// Translators: ignore the placeholder.
		$email .= sprintf( __( 'We will be using %1$s with WordPress to integrate data from our Bullhorn account to our website with your API.', 'matador-jobs' ), __( 'Matador Jobs Board', 'matador-jobs' ) ) . ' ';
		$email .= __( "Please add these URL's to our list of allowed API Redirect URIs too:", 'matador-jobs' ) . ' ';
		$email .= __( 'It is important that it be saved exactly as we include it below.', 'matador-jobs' ) . ' ';
		$email .= __( 'This is for the API credentials that use the follow ClientID: ', 'matador-jobs' ) . ' ';
		$email .= esc_attr( Matador::credential( 'bullhorn_api_client' ) ) . PHP_EOL . PHP_EOL; // Note, its safe to not handle for an empty return here, as an empty ClientID wouldn't reach this condition.
		$email .= $endpoint . PHP_EOL;
		$email .= ( false === strpos( $endpoint, 'http:' ) ) ? str_replace( 'https:', 'http:', $endpoint ) : str_replace( 'http:', 'https:', $endpoint ) . PHP_EOL;
		$email .= PHP_EOL . PHP_EOL;
		$email .= __( 'Thank you for your help!', 'matador-jobs' ) . PHP_EOL . PHP_EOL;
		$email .= __( 'YOUR NAME', 'matador-jobs' );

		?>

		<pre><?php echo esc_html( $email ); ?></pre>

		<?php
		$button_args = array(
			'label' => __( 'Re-check API URI Redirect', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'callback',
			'class' => 'button-primary',
		);
		Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
		?>

	</div>

	<footer>
		<p>
			<?php
			esc_html_e( 'It can take up to 2 business days for Bullhorn to reply, but we know sometimes it is done in a few hours. You can leave the connection assistant and return right here to where you left off by clicking on the notice at the top of your screen or the "Connect to Bullhorn" button on Matador Settings once you\'re ready. Once you get a reply from Bullhorn that says your API callback is updated, return to the connection assistant, and you will be able to continue to the next step, provided Bullhorn didn\'t make a mistake.', 'matador-jobs' );
			?>
		</p>

		<?php
		$button_args = array(
			'label' => __( 'Go Back', 'matador-jobs' ),
			'name'  => 'matador-settings[bullhorn_api_assistant]',
			'value' => 'credentials',
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