<?php
/**
 * Template : Application
 *
 * Template to display the application. Override this theme by copying it to yourtheme/matador/application.php.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Form-Fields
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php if ( 'complete' === get_query_var( 'matador-apply' ) && ! empty( $args['shortcode'] ) ) : ?>

	<?php

	matador_the_confirmation();

	matador_the_job_navigation( '', 'shortcode-confirmation' );

	?>

<?php elseif ( ! empty( $fields ) ) : ?>

	<?php

	do_action( 'matador_application_before_form', $args );

	$classes = implode( ' ', apply_filters( 'matador_application_form_classes', $args['class'] ) );

	?>

	<form id="matador-application-form" class="<?php echo esc_attr( $classes ); ?>"
		action="<?php echo esc_url( get_home_url( null, matador\Matador::variable( 'api_endpoint_prefix' ) . 'application' ) ); ?>"
		enctype="multipart/form-data" method="post">

		<?php do_action( 'matador_application_before_fields' ); ?>

		<?php wp_nonce_field( matador\Matador::variable( 'application', 'nonce' ), matador\Matador::variable( 'application', 'nonce' ), true ); ?>

		<?php

		if ( is_user_logged_in() ) :

			printf( '<input type="hidden" id="_wpuser" name="_wpuser" value="%s" />', get_current_user_id() ?: 0 );

		endif;

		if ( ! array_key_exists( 'jobs', $fields ) ) {

			if ( isset( $bhid ) ) {
				printf( '<input id="bhid" name="bhid" type="hidden" value="%s" />', esc_attr( $bhid ) );
			} elseif ( is_singular( matador\Matador::variable( 'post_type_key_job_listing' ) ) ) {
				printf( '<input id="bhid" name="bhid" type="hidden" value="%s" />', esc_attr( get_post_meta( get_the_ID(), '_matador_source_id', true ) ) );
			}

			if ( isset( $wpid ) ) {
				printf( '<input id="wpid" name="wpid" type="hidden" value="%s" />', esc_attr( $wpid ) );
			} elseif ( is_singular( matador\Matador::variable( 'post_type_key_job_listing' ) ) ) {
				printf( '<input id="wpid" name="wpid" type="hidden" value="%s" />', esc_attr( get_the_id() ) );
			}
		} else {

			if ( isset( $wpid ) ) {
				$fields['jobs']['selected'] = esc_attr( $wpid );
			} elseif ( matador\Matador::variable( 'post_type_key_job_listing' ) === get_post_type() ) {
				$fields['jobs']['selected'] = esc_attr( get_the_id() );
			}
		}
		?>

		<?php if ( isset( $type ) ) : ?>
			<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>" />
		<?php endif; ?>

		<?php
		foreach ( $fields as $field => $args ) :

			// Prepare Field Args
			list( $args, $template ) = matador_form_field_args( $args, $field );

			// Easy access for embedded $variable strings.
			$type = $args['type'];

			// @since 3.5.0 added $args to following.
			// @todo need to comment all filters/actions in template

			do_action( 'matador_application_before_field', $args );

			do_action( "matador_application_before_field_type_$type", $args );

			do_action( "matador_application_before_field_template_$template", $args );

			do_action( "matador_application_before_field_name_$field", $args );

			matador_get_template_part( 'field', $template, $args, 'form-fields' );

			do_action( "matador_application_after_field_name_$field", $args );

			do_action( "matador_application_after_field_template_$template", $args );

			do_action( "matador_application_after_field_type_$type", $args );

			do_action( 'matador_application_after_field', $args );

		endforeach;
		?>

		<?php do_action( 'matador_application_after_fields' ); ?>

        <input id="matador-submit" class="<?php matador_button_classes( 'matador-button', 'primary' ); ?>" name="submit" type="submit"
		       value="<?php echo esc_attr( apply_filters( 'matador_application_submit_button_text', esc_attr__( 'Submit Application', 'matador-jobs' ) ) ); ?>"/>

		<?php do_action( 'matador_application_after_submit' ); ?>

		<div id="matador-upload-overlay">

			<div class="matador-upload-overlay-background"></div>

			<div class="matador-upload-overlay-message">

				<span class="spinner"></span>

				<p><?php echo esc_html( apply_filters( 'matador_application_submitted_message', __( 'We are uploading your application. It may take a few moments to read your resume. Please wait!', 'matador-jobs' ) ) ); ?></p>

			</div>

		</div>

	</form>

	<?php do_action( 'matador_application_after_form' ); ?>

<?php else : ?>

	<?php esc_html_e( 'No fields passed to form. Set default fields in the Matador settings.', 'matador-jobs' ); ?>

<?php endif; ?>