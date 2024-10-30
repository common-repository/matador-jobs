<?php
/**
 * Admin Template : Bullhorn Connection Assistant Prepare
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
	<h4><?php echo esc_html__( 'Welcome to Matador' ); ?></h4>
</header>

<div>

	<p>
		<?php
		echo esc_html__( 'Welcome to Matador. As you know, Matador makes it so your WordPress powered website can talk to your Bullhorn account, including downloading job data from and uploading candidate applications to Bullhorn. To make this magic happen, we need to first connect your site to Bullhorn.', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php esc_html_e( "To connect your site to Bullhorn, you'll need a few things:", 'matador-jobs' ); ?>
	</p>

	<ul class="checklist">
		<li><?php esc_html_e( 'A Bullhorn account with API Access turned on.', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'An API Client ID', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'An API Client Secret', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'An API User ID', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'An API User Password', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'The Matador Jobs callback URI registered to your Allowed API Redirect URI list at Bullhorn.', 'matador-jobs' ); ?></li>
	</ul>

	<p>
		<?php
		esc_html_e( 'To get started, let\'s check if you have what you need to get connected. Select from the following list a best description of your preparedness for using Matador with the Bullhorn API:', 'matador-jobs' );
		?>
	</p>

	<label for="get">
		<input id="get" type="radio" name="matador-settings[bullhorn_api_assistant]" value="prepare-get" checked="checked" />
		<?php esc_html_e( "I don't have API access or I'm not sure.", 'matador-jobs' ); ?>
	</label>
	<label for="forgot">
		<input id="forgot" type="radio" name="matador-settings[bullhorn_api_assistant]" value="prepare-forgot"/>
		<?php esc_html_e( 'I have API access, but I forgot my credentials.', 'matador-jobs' ); ?>
	</label>
	<label for="continue">
		<input id="continue" type="radio" name="matador-settings[bullhorn_api_assistant]" value="credentials"/>
		<?php esc_html_e( 'I have everything I need, lets go!', 'matador-jobs' ); ?>
	</label>
	<label for="complete">
		<input id="complete" type="radio" name="matador-settings[bullhorn_api_assistant]" value="complete"/>
		<?php esc_html_e( 'I know what I\'m doing, skip the Connection Assistant', 'matador-jobs' ); ?>
	</label>

</div>

<footer>

	<?php
	$button_args = array(
		'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
		'class' => 'button-secondary',
		'name'  => 'exit',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

	$button_args = array(
		'label' => __( 'Next Step', 'matador-jobs' ),
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
	?>

</footer>
