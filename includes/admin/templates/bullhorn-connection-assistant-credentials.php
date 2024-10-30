<?php
/**
 * Admin Template : Bullhorn Connection Assistant Credentials
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
	<h4><?php echo esc_html__( 'Bullhorn API Credentials' ); ?></h4>
</header>

<div>

	<p>
		<?php
		echo esc_html__( 'Input the credentials you got from Bullhorn. The API User ID and API User Password are not required, but we recommend they be provided as it allows us to streamline your authorization process and attempt to automatically reconnect when your site becomes disconnected from Bullhorn.', 'matador-jobs' );
		?>
	</p>

	<?php

	$fields = array( 'bullhorn_api_client', 'bullhorn_api_secret', 'bullhorn_api_user', 'bullhorn_api_pass' );

	foreach ( $fields as $field ) {

		$field_args = Settings_Fields::instance()->get_field( $field );

		if ( is_array( $field_args ) ) {

			list( $args, $template ) = Options::form_field_args( $field_args, $field );

			Template_Support::get_template_part( 'field', $template, $args, 'form-fields', true, true );

		}

		if ( get_transient( "error_$field" ) ) {
			?>
			<div class="callout callout-error">
				<p><?php echo esc_html( get_transient( "error_$field" ) ); ?></p>
			</div>
			<?php
			delete_transient( "error_$field" );
		}
	}

	?>

</div>

<footer>
	<p>
		<?php esc_html_e( 'After you advance to the next step, we\'ll first check that you provided a valid Client ID. If not, we\'ll bring you back here and let you know. If your Client ID is valid, we\'ll advance and use these credentials to test if your callback URI is properly registered. Your browser may take up to 30 seconds to complete this check.', 'matador-jobs' ); ?>
	</p>

	<?php
	$button_args = array(
		'label'      => __( 'Go Back', 'matador-jobs' ),
		'name'       => 'matador-settings[bullhorn_api_assistant]',
		'value'      => 'prepare',
		'novalidate' => true,
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

	$button_args = array(
		'label'      => __( 'Exit Connection Assistant', 'matador-jobs' ),
		'name'       => 'exit',
		'class'      => 'button-secondary',
		'novalidate' => true,
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

	$button_args = array(
		'label' => __( 'Next Step', 'matador-jobs' ),
		'name'  => 'matador-settings[bullhorn_api_assistant]',
		'value' => 'callback',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
	?>

</footer>
