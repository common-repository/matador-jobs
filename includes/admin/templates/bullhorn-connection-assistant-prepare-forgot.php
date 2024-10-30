<?php
/**
 * Admin Template : Bullhorn Connection Assistant Forgot Credentials
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
	<h4><?php esc_html_e( 'Lost/Forgot Bullhorn API Credentials' ); ?></h4>
</header>


<div>

	<p>
		<?php
		esc_html_e( 'If you forgot your API Credentials, Bullhorn is pretty good about getting them for you with a short, often emailed, support request. Since you will be taking the time to get everything set up, make sure you get everything you need, including the four API credentials and your callback or redirect URI registered. To help you know what to say, we wrote this email for you to copy-and-paste, make a few updates, and send to them.', 'matador-jobs' );
		?>
	</p>

	<?php

	$endpoint = Matador::variable( 'api_redirect_uri' ) ?: trailingslashit( home_url() ) . trailingslashit( Matador::variable( 'api_endpoint_prefix' ) . 'authorize/' );

	$email  = '';
	$email .= __( 'Dear Bullhorn Sales and Support', 'matador-jobs' ) . ':' . PHP_EOL . PHP_EOL;
	// Translators: ignore the placeholder.
	$email .= sprintf( __( 'We will be using %1$s with WordPress to integrate data from our Bullhorn account to our website with your API.', 'matador-jobs' ), __( 'Matador Jobs Board', 'matador-jobs' ) ) . ' ';
	$email .= __( 'We believe we have access to the Web Developer API for our COMPANY NAME, but we forgot or lost our credentials.', 'matador-jobs' ) . ' ';
	$email .= __( 'Can you please either reset or retrieve our credentials for us?', 'matador-jobs' ) . PHP_EOL . PHP_EOL;
	$email .= __( 'At the minimum, we will need the API Client ID, API Client Secret ID, API User ID, API User Password, and the server cluster id for our account.', 'matador-jobs' ) . ' ';
	$email .= __( "Also, please also register and allow these following callback URL's:", 'matador-jobs' ) . PHP_EOL . PHP_EOL;
	$email .= $endpoint . PHP_EOL;
	$email .= ( false === strpos( $endpoint, 'http:' ) ) ? str_replace( 'https:', 'http:', $endpoint ) : str_replace( 'http:', 'https:', $endpoint ) . PHP_EOL;
	$email .= PHP_EOL . PHP_EOL;
	$email .= __( 'Thank you for your help!', 'matador-jobs' ) . PHP_EOL . PHP_EOL;
	$email .= __( 'YOUR NAME', 'matador-jobs' );

	?>

	<pre><?php echo esc_html( $email ); ?></pre>

	<div class="callout callout-info">
		<p>
			<?php
			esc_html_e( 'If you plan to use Matador on both a staging and a production site, you should ask for URLs from both sites to be registered in this email. It will save you from delays later on.', 'matador-jobs' );
			?>
		</p>
	</div>

</div>

<footer>

	<p>
		<?php
		esc_html_e( 'It can take up to 2 business days for Bullhorn to reply, but we know sometimes it is done in a few hours. You can leave the connection assistant and return right here to where you left off by clicking on the notice at the top of your screen or the "Connect to Bullhorn" button on Matador Settings once you\'re ready. Once you have your credentials, come back, and click on "Next Step".', 'matador-jobs' );
		?>
	</p>

	<?php if ( 'complete' !== Matador::setting( 'bullhorn_api_assistant' ) ) : ?>
	<?php endif; ?>

	<?php
	$button_args = array(
		'label' => __( 'Go Back', 'matador-jobs' ),
		'name'  => 'matador-settings[bullhorn_api_assistant]',
		'value' => 'prepare',
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
		'value' => 'credentials',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
	?>

</footer>