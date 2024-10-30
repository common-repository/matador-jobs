<?php
/**
 * Template Actions & Filters
 *
 * The trash bin. Stuff we once supported but plan to toss out soon.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use matador\Matador;

add_action( 'matador_application_before_fields', 'matador_do_application_confirmation', 5 );
add_action( 'matador_job_before_content', 'matador_do_confirmation', 5 );
add_action( 'matador_job_before_content', 'matador_do_job_info', 10 );
add_action( 'matador_job_after_content', 'matador_do_application', 10 );
add_action( 'matador_job_after_content', 'matador_do_job_navigation', 15 );
add_action( 'matador_jobs_after', 'matador_do_pagination', 10, 3 );
add_filter( 'the_title', 'matador_do_job_title', 10, 2 );
add_filter( 'matador_template_the_job_meta_bullhorn_job_id', 'matador_prepend_number_sign_to_job_id', 10, 3 );

/**
 * Matador Do Job Info
 *
 * Action function to add the job info.
 *
 * @since 3.4.0
 *
 * @param string $context Template context.
 */
function matador_do_job_info( $context ) {
	if ( ! in_array( $context, array( 'single', 'archive', 'application' ), true ) ) {
		return;
	}
	if ( Matador::setting( 'show_job_meta' ) ) {
		matador_the_job_info();
	}
}

/**
 * Matador Do Job Navigation
 *
 * Action function to add the job navigation.
 *
 * @since 3.4.0
 *
 * @param string $context Template context.
 */
function matador_do_job_navigation( $context ) {
	if ( ! in_array( $context, array( 'single', 'archive', 'application', 'confirmation' ), true ) ) {
		return;
	}
	matador_the_job_navigation();
}

/**
 * Matador Do Application
 *
 * Action function to add the job application.
 *
 * @since 3.4.0
 *
 * @param string $context Template context.
 */
function matador_do_application( $context ) {
	if ( ! Matador::setting( 'applications_accept' ) ) {

		return;
	}

	if ( 'single' !== $context ) {
		return;
	}

	if ( 'append' === Matador::setting( 'applications_apply_method' )
		&& 'complete' === get_query_var( 'matador-apply' ) ) {
		return;
	}

	if ( 'append' === Matador::setting( 'applications_apply_method' ) ) {
		matador_the_application();
	}
}

/**
 * Matador Do Application Confirmation
 *
 * Action function to add the job confirmation
 *
 * @since 3.4.0
 *
 * @param string $context Template context.
 */
function matador_do_confirmation( $context ) {
	if ( 'single' !== $context ) {
		return;
	}

	if ( 'append' === Matador::setting( 'applications_apply_method' )
		&& 'complete' === get_query_var( 'matador-apply' ) ) {
		matador_the_confirmation();
	}
}

/**
 * Matador Do Application Confirmation
 *
 * Action function to add the job confirmation inside the application form wrap
 *
 * @since 3.7.0
 *
 * @param string $context Template context.
 */
function matador_do_application_confirmation( $context ) {
	if ( 'single' === $context ) {
		return;
	}

	if ( 'append' === Matador::setting( 'applications_apply_method' )
	     && 'complete' === get_query_var( 'matador-apply' ) ) {
		matador_the_confirmation();
	}
}

/**
 * Matador Do Pagination
 *
 * Action function to add the pagination before a list of jobs
 *
 * @since 3.3.0
 * @since 3.5.0 Now expects the args array and will conditionally show pagination based on 'paginate' value.
 *
 * @param WP_Query $jobs
 * @param string   $context Template context.
 * @param array    $args
 */
function matador_do_pagination( $jobs, $context, $args ) {

	if ( 'select' === $context ) {
		return;
	}

	if ( ! isset( $args['paginate'] ) || false === $args['paginate'] ) {
		return;
	}

	if ( empty( $jobs ) ) {
		return;
	}

	matador_pagination( $jobs, $context );
}

/**
 * Matador Do Job Title
 *
 * Filter function to prepend "Apply to:" and "Thank you for applying to:" to a job title.
 *
 * @since 3.4.0
 *
 * @param string $title The Job Title
 * @param int    $id    The Job ID
 *
 * @return string
 */
function matador_do_job_title( $title = '', $id = 0 ) {

	if ( ! ( get_the_ID() === $id && in_the_loop() && is_main_query() && ! is_admin() ) ) {
		return $title;
	}
	if ( 'create' === Matador::setting( 'applications_apply_method' )
		&& 'apply' === get_query_var( 'matador-apply' ) ) {

		/**
		 * Filter: Matador Job Confirmation Job Title Prepend
		 *
		 * The message that is prepended to the job title on a job confirmation.
		 *
		 * @string 3.4.0
		 *
		 * @param string $thank_you The message. Default is 'Thank you for your application to:'
		 *
		 * @return string
		 */
		$apply_to = apply_filters( 'matador_job_title_application_prepend', __( 'Apply to:', 'matador-jobs' ) );

		return esc_html( $apply_to ) . ' <em>' . $title . '</em>';
	}
	if ( 'create' === Matador::setting( 'applications_confirmation_method' )
		&& 'complete' === get_query_var( 'matador-apply' ) ) {
		add_filter( 'matador_job_confirmation_headline', '__return_false' );

		/**
		 * Filter: Matador Job Confirmation Job Title Prepend
		 *
		 * The message that is prepended to the job title on a job confirmation.
		 *
		 * @string 3.4.0
		 *
		 * @param string $thank_you The message. Default is 'Thank you for your application to:'
		 *
		 * @return string
		 */
		$thank_you = apply_filters( 'matador_job_title_confirmation_prepend', __( 'Thank you for your application to: ', 'matador-jobs' ) );

		return esc_html( $thank_you ) . ' <em>' . $title . '</em>';
	}
	return $title;
}

/**
 * Matador Prepend Number Sign to Job ID
 *
 * Filter function to add the "#" to Job ID
 *
 * @since 3.4.0
 *
 * @param string $meta
 * @param int    $id
 * @param string $context Template context.
 *
 * @return string
 */
function matador_prepend_number_sign_to_job_id( $meta, $id, $context ) {
	// Unset unused parameters included for completeness.
	unset( $id, $context );

	return '#' . $meta;
}

// Add $ and number separators to Salary and Salary Range
