<?php
/**
 * Admin Template : Bullhorn Connection Assistant Datacenter
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
	<h4><?php echo esc_html__( 'Select a Regional Datacenter' ); ?></h4>
</header>

<div>
	<p>
		<?php
		esc_html_e( 'To improve speed for its users, Bullhorn has servers located across the world. Your Bullhorn account lives on a server &quot;cluster&quot; which is located at one of many server locations across the world. Each server location shares one API datacenter.', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( 'When you log in to Bullhorn, your web browser is automatically told where in the world your account is located, and you usually don\'t notice it. When Matador accesses your account with the API, we can speed up your site by telling Bullhorn that we already know where your account is located.', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( 'Unfortunately, determining your Datacenter may require you to contact Bullhorn. No fear, though as this setting is easy to change later on. If you don\'t know your datacenter, choose one physically near to you, contact Bullhorn to get the cluster after you complete setup, and change it if your guess was wrong.', 'matador-jobs' );
		?>
	</p>

	<?php

	$field_args = Settings_Fields::instance()->get_field( 'bullhorn_api_center' );

	if ( is_array( $field_args ) ) {

		list( $args, $template ) = Options::form_field_args( $field_args, 'bullhorn_api_center' );

		Template_Support::get_template_part( 'field', $template, $args, 'form-fields', true, true );

	}
	?>

	<table class="clusters-table">
		<thead>
		<tr>
			<th><?php esc_html_e( 'If your cluster is...', 'matador-jobs' ); ?></th>
			<th><?php esc_html_e( 'Choose this option.', 'matador-jobs' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>CLS40, CLS41, CLS42, CL43</td>
			<td><?php esc_html_e( 'North America Eastern', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS30, CLS31, CLS32, CLS33, CLS34, CL35</td>
			<td><?php esc_html_e( 'North America Western', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS50</td>
			<td><?php esc_html_e( 'North America Western (Cluster 50)', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS60</td>
			<td><?php esc_html_e( 'Asia/Pacific Singapore', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS66</td>
			<td><?php esc_html_e( 'Asia/Pacific Australia', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS21, CLS22, CLS23</td>
			<td><?php esc_html_e( 'Europe United Kingdom', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS70</td>
			<td><?php esc_html_e( 'Europe Germany', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS71</td>
			<td><?php esc_html_e( 'Europe French', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS29</td>
			<td><?php esc_html_e( 'UK Sandbox Environments', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS91, CLS99</td>
			<td><?php esc_html_e( 'US Sandbox Environments', 'matador-jobs' ); ?></td>
		</tr>
		</tbody>
	</table>

</div>

<footer>

	<p>
		<?php
		esc_html_e( 'After you click on "Next Step" below, we\'ll take you to a page where you can input your Bullhorn API Credentials.', 'matador-jobs' );
		?>
	</p>

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
