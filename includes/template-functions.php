<?php
/**
 * Matador / Template Functions
 *
 * This creates callable functions for use in the front-end and on themes.
 * Functions are not namespaced and calls to internal functions must be namespaced.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul BearneMatador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Template_Support;
use matador\Job_Taxonomies;
use matador\Helper;

/**
 * Matador Get Template
 *
 * Loads a template and passes an array of variables and returns content.
 *
 * @since  3.0.0
 * @since  3.4.0 added $echo parameter
 *
 * @param string $name         Name of template to use.
 * @param array  $args         Array of args passed to template in key => value format. Default empty array. Optional.
 * @param string $subdirectory Name of subdirectory where template is located. Default null. Optional.
 * @param bool   $echo         Whether to print or return the template. Accepts true or false. Default false. Optional.
 *
 * @return bool|string
 */
function matador_get_template( $name, $args = array(), $subdirectory = null, $echo = false ) {

	return Template_Support::get_template( $name, $args, $subdirectory, false, $echo );
}

/**
 * Matador Get Template Part
 *
 * Gets template part (for templates in loops).
 *
 * @since 3.0.0
 *
 * @param string $slug         Prefix for name of template part.
 * @param string $name         Name of template to use.
 * @param array  $args         Array of args passed to template in key => value format. Default empty array. Optional.
 * @param string $subdirectory Name of subdirectory where template is located. Default null. Optional.
 * @param bool   $echo         Whether to print or return the template. Accepts true or false. Default false. Optional.
 *
 * @return bool|string
 */
function matador_get_template_part( $slug, $name = '', $args = array(), $subdirectory = 'parts', $echo = true ) {

	return Template_Support::get_template_part( $slug, $name, $args, $subdirectory, false, $echo );
}

/**
 * Matador Get The Job Title
 *
 * Returns the job title.
 *
 * @since  3.0.0
 * @since  3.4.0 Added $context parameter.
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string      Filtered Title
 */
function matador_get_the_job_title( $id = null, $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	/**
	 * Filter: Matador Template The Job Title
	 *
	 * Filters the Job Title as returned from the job post. Does not modify the data, just the output.
	 *
	 * @since 3.4.0
	 *
	 * @param string $title   The Job Title from database.
	 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
	 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
	 *
	 */
	return wp_kses_post( apply_filters( 'matador_template_the_job_title', Template_Support::the_job_title( $id ), $id, $context ) );
}

/**
 * Matador The Job Title
 *
 * Prints job title
 *
 * @since  3.0.0
 * @since  3.4.0 Added $context parameter.
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_title( $id = null, $context = '' ) {
	echo matador_get_the_job_title( $id, $context );
}

/**
 * Matador Get The Job Title Link
 *
 * Returns job title wrapped in an anchor tag linking to the job's single page.
 *
 * @since  3.4.0
 *
 * @see template templates/parts/the-job-title-link.php for output formatting.
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string Formatted HMTL.
 */
function matador_get_the_job_title_link( $id = null, $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	$template_args = array(
		'id'      => $id ?: get_the_ID(),
		'context' => $context,
	);

	return matador_get_template( 'the-job-title-link.php', $template_args, 'parts' );
}

/**
 * Matador The Job Title Link
 *
 * Prints job title wrapped in an anchor tag linking to the job's single page.
 *
 * @since  3.4.0
 *
 * @see template templates/parts/the-job-title-link.php for output formatting.
 *
 * @param int    $id      ID of job (optional)
 * @param string $context Context for the use of this function (optional)
 */
function matador_the_job_title_link( $id = null, $context = '' ) {
	echo wp_kses_post( matador_get_the_job_title_link( $id, $context ) );
}

/**
 * Matador Get The Job Link
 *
 * Returns the permalink URL of the job.
 *
 * @since  3.0.0
 * @since  3.4.0 Added $context parameter.
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string  String with job permalink
 */
function matador_get_the_job_link( $id = null, $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	$url = Template_Support::the_job_permalink( $id );

	/**
	 * Filter: Matador Template The Job Link
	 *
	 * @since 3.4.0
	 *
	 * @param string   URL of the Job
	 * @param int|null ID of the Job
	 * @param string   Template context.
	 *
	 * @return string
	 */
	return esc_url( apply_filters( 'matador_template_the_job_link', $url, $id, $context ) );
}

/**
 * Matador Get The Job Permalink
 *
 * Alias of matador_get_the_job_link
 *
 * @since  3.4.0
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string  String with job permalink
 */
function matador_get_the_job_permalink( $id = null, $context = '' ) {
	return esc_url( matador_get_the_job_link( $id, $context ) );
}

/**
 * Matador The Job Link
 *
 * Prints matador_get_the_job_link
 *
 * @since  3.4.0
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_link( $id = null, $context = '' ) {
	echo esc_url( matador_get_the_job_link( $id, $context ) );
}

/**
 * Matador Get Job Apply Link
 *
 * Gets the URL for the Application of a Job. Determined by settings.
 *
 * @since  3.0.0
 * @since  3.4.0 added the $context parameter
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string The job application permalink
 */
function matador_get_the_job_apply_link( $id = null, $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	$url = Template_Support::the_job_apply_link( $id );

	/**
	 * Filter: Matador Template The Job Apply Link
	 *
	 * @since 3.4.0
	 *
	 * @param string   URL of the Job
	 * @param int|null ID of the Job
	 * @param string   Template context.
	 *
	 * @return string
	 */
	return esc_url( apply_filters( 'matador_template_the_job_apply_link', $url, $id, $context ) );
}

/**
 * Matador The Job Apply Link
 *
 * Prints matador_get_the_job_apply_link
 *
 * @since  3.4.0
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_apply_link( $id = null, $context = '' ) {
	echo esc_url( matador_get_the_job_apply_link( $id, $context ) );
}

/**
 * Matador Get Job Application Confirmation Link
 *
 * Gets the URL for the Application Confirmation of a Job, aka the "Thank You Page". Determined by settings.
 *
 * @since  3.0.0
 * @since  3.4.0 added the $context parameter
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string The job application confirmation link
 */
function matador_get_the_job_confirmation_link( $id = null, $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	$url = Template_Support::the_job_confirmation_link( $id );

	/**
	 * Filter: Matador Template The Job Application Confirmation Link
	 *
	 * @since 3.4.0
	 *
	 * @param string   URL of the Job
	 * @param int|null ID of the Job
	 * @param string   Template context.
	 *
	 * @return string
	 */
	return esc_url( apply_filters( 'matador_template_the_job_confirmation_link', $url, $id, $context ) );
}

/**
 * Matador The Job Application Confirmation Link
 *
 * Prints matador_get_the_job_confirmation_link
 *
 * @since  3.4.0
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_confirmation_link( $id = null, $context = '' ) {
	echo esc_url( matador_get_the_job_confirmation_link( $id, $context ) );
}

/**
 * Matador Get The Jobs Link
 *
 * Gets a link to either the Jobs page or Jobs post type archive.
 *
 * @since  3.3.0
 *
 * @return string
 */
function matador_get_the_jobs_link() {
	return esc_url( Template_Support::the_jobs_link() );
}

/**
 * Matador The Jobs Permalink
 *
 * Gets a link to either the Jobs page or Jobs post type archive.
 *
 * @since  3.3.0
 */
function matador_the_jobs_link() {
	echo esc_url( matador_get_the_jobs_link() );
}

/**
 * Matador The General Application URL
 *
 * Gets a permalink URL to the General Application page, if active.
 *
 * @since  3.8.0
 *
 * @return string
 */
function matador_get_the_general_application_url() {
	return esc_url( Template_Support::the_general_application_url() );
}

/**
 * Matador The General Application URL
 *
 * Prints a permalink URL to the General Application page, if active.
 *
 * @since  3.8.0
 */
function matador_the_general_application_url() {
	echo esc_url( matador_get_the_general_application_url() );
}

/**
 * Matador The General Application Link
 *
 * Gets an HTML-formatted link to the General Application page, if active.
 *
 * @since  3.8.0
 *
 * @param string $label The label for the button. Defaults to 'Submit a General Application.'
 *
 * @return string
 */
function matador_get_the_general_application_link( $label = '' ) {
	return esc_url( Template_Support::the_general_application_link( $label ) );
}

/**
 * Matador The General Application Link
 *
 * Prints an HTML-formatted link to the General Application page, if active.
 *
 * @since  3.8.0
 *
 * @param string $label The label for the button. Defaults to 'Submit a General Application.'
 */
function matador_the_general_application_link( $label = '' ) {
	echo esc_url( matador_get_the_general_application_link( $label ) );
}

function matador_the_job_navigation( $id = null, $context = '' ) {
	echo wp_kses_post( Template_Support::the_job_navigation( $id, $context ) );
}

/**
 * Matador Get The Job Description
 *
 * Returns the job description as an excerpt (240 words default or custom) or the full job description.
 *
 * @since 3.4.0
 * @since 3.5.6 moved 'matador_template_the_job_description' filter to class-template-support to alleviate issue with
 *              the application of the_content filter use.
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $limit   The limit for the content. Accepts 'full' for full content, 'excerpt' for the default excerpt,
 *                        or an integer for the limit of words to generate an excerpt. Default 'excerpt'. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string         Job content/description
 */
function matador_get_the_job_description( $id = null, $limit = 'excerpt', $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	return Template_Support::the_job_description( $id, $limit, $context );
}

/**
 * Matador The Job Description
 *
 * Prints the job description.
 *
 * @since 3.4.0
 *
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $limit   The limit for the content. Accepts 'full' for full content, 'excerpt' for the default excerpt,
 *                        or an integer for the limit of words to generate an excerpt. Default 'excerpt'. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_description( $id = null, $limit = 'excerpt', $context = '' ) {
	echo matador_get_the_job_description( $id, $limit, $context );
}

/**
 * Matador Get the Job Posted Date
 *
 * Returns the date the job was published (externally, which is set to the WordPress published date).
 *
 * @since      3.4.0
 * @deprecated 3.7.2 use matador_get_the_job_date()
 *
 * @param  string $format The PHP date format. Default empty string. Optional.
 * @param  int    $id     The WordPress post (job) ID. Default null. Optional in loop.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string
 */
function matador_get_the_job_posted_date( $format = '', $id = null, $context = '' ) {
	return matador_get_the_job_date( $format, $id, $context );
}

/**
 * Matador the Job Posted Date
 *
 * Echoes the date the job was published (externally, which is set to the WordPress published date).
 *
 * @since      3.4.0
 * @deprecated 3.7.2 use matador_the_job_date()
 *
 * @param  string $format The PHP date format. Default empty string. Optional.
 * @param  int    $id     The WordPress post (job) ID. Default null. Optional in loop.
 */
function matador_the_job_posted_date( $format = '', $id = null ) {
	echo matador_the_job_date( $format, $id );
}

/**
 * Matador Get the Job Date
 *
 * Returns the date the job was published (externally, which is set to the WordPress published date).
 *
 * @since  3.7.2
 *
 * @param  string $format The PHP date format. Default empty string. Optional.
 * @param  int    $id     The WordPress post (job) ID. Default null. Optional in loop.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string
 */
function matador_get_the_job_date( $format = '', $id = null, $context = '' ) {
	return esc_html( Template_Support::the_job_posted_date( $format, $id, $context ) );
}

/**
 * Matador the Job Date
 *
 * Echoes the date the job was published (externally, which is set to the WordPress published date).
 *
 * @since  3.7.2
 *
 * @param  string $format The PHP date format. Default empty string. Optional.
 * @param  int    $id     The WordPress post (job) ID. Default null. Optional in loop.
 */
function matador_the_job_date( $format = '', $id = null ) {
	echo esc_html( Template_Support::the_job_posted_date( $format, $id ) );
}

/**
 * Matador Get The Job Relative Age
 *
 * Get the relative age of the job posted day versus today, ie: '5 days ago' or 'Just Now'.
 *
 * @see Template_Support::the_job_relative_age() for filter documentation for `matador_the_job_relative_age`.
 *
 * @since 3.7.0
 *
 * @param int|null $id Job WPID. Optional
 *
 * @return string
 */
function matador_get_the_job_relative_age( $id = null ) {
	return Template_Support::the_job_relative_age( $id );
}

/**
 * Matador (Print) The Job Relative Age
 *
 * @see matador_get_the_job_relative_age()
 *
 * @since 3.7.0
 *
 * @param int|null $id Job WPID. Optional
 */
function matador_the_job_relative_age( $id = null ) {
	echo matador_get_the_job_relative_age( $id );
}

/**
 * Matador the Job Meta
 *
 * Returns the meta field value of a Matador Job post.
 *
 * @since  3.0.0
 * @since  3.4.0 added $context parameter
 *
 * @param string $key Meta key name. Required.
 * @param int    ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string
 */
function matador_get_the_job_meta( $key, $id = null, $context = '' ) {

	if ( in_array( $key, Job_Taxonomies::registered_taxonomies(), true ) ) {

		/**
		 * Matador The Job Meta Taxonomy Args
		 *
		 * Use this to pass various args to the taxonomies array. Most helpful for defining whether the the taxonomy
		 * terms are links, and what kind of links (filter links, links to archive, etc).
		 *
		 * @see Template_Support::the_job_terms_list() for arguments
		 *
		 * @since 3.7.5
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		$args = apply_filters( 'matador_get_the_job_meta_taxonomy_args', [] );


		return matador_get_the_job_terms_list( $key, $id, $args );
	}

	$meta = Template_Support::the_job_meta( $key, $id );

	/**
	 * Dynamic Filter: Matador The Job Meta
	 *
	 * @since 3.0.0
	 *
	 * @param string $meta    Meta value.
	 * @param string $key     Meta key name
	 * @param int    $id      ID of job.
	 *
	 * @return string
	 */
	$meta = apply_filters( 'matador_the_job_meta_' . $key, $meta, $key, $id );

	/**
	 * Dynamic Filter: Matador Template The Job Meta
	 *
	 * @since 3.4.0
	 *
	 * @param string $meta    Meta value.
	 * @param int    $id      ID of job.
	 * @param string $context Template context, for filtering purposes.
	 *
	 * @return string
	 */
	$meta = apply_filters( 'matador_template_the_job_meta_' . $key, $meta, $id, $context );

	/**
	 * Filter: Matador Template The Job Meta
	 *
	 * @since 3.4.0
	 *
	 * @param string $meta    Meta value.
	 * @param string $key     Meta key name
	 * @param int    $id      ID of job.
	 * @param string $context Template context, for filtering purposes.
	 *
	 * @return string
	 */
	$meta = apply_filters( 'matador_template_the_job_meta', $meta, $key, $id, $context );

	return wp_kses_post( $meta );
}

/**
 * Matador Job Meta
 *
 * Prints the meta field value of a Matador Job post.
 *
 * @since  3.0.0
 * @since  3.4.0 added $context parameter
 *
 * @param string $key     Meta key name. Required.
 * @param int    $id      ID of Job. Accepts integer or null. Default null. Optional.
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_meta( $key, $id = null, $context = '' ) {
	echo wp_kses_post( matador_get_the_job_meta( $key, $id, $context ) );
}

/**
 * Matador Get The Job Info
 *
 * Returns a formatted list of job meta.
 *
 * @since  3.4.0
 *
 * @see template templates/parts/the-job-info.php for formatting.
 *
 * @param  array  $fields  An associative array of meta fields where the key is the field name or taxonomy name and
 *                         the value is the desired label to go before the field. Default empty array. Optional.
 * @param  array  $atts    Attributes array. Default empty array. Optional. @see TemplateSupport::the_job_info()
 * @param  string $context The template context for filtering purposes. Default is empty string. Optional.
 *
 * @return string
 */
function matador_get_the_job_info( $fields = array(), $atts = array(), $context = '' ) {
	return wp_kses_post( Template_Support::the_job_info( $fields, $atts, $context ) );
}

/**
 * Matador The Job Info
 *
 * Returns a formatted list of job meta.
 *
 * @since  3.4.0
 *
 * @see template templates/parts/the-job-info.php for formatting.
 *
 * @param  array  $fields  An associative array of meta fields where the key is the field name or taxonomy name and
 *                         the value is the desired label to go before the field. Default empty array. Optional.
 * @param  array  $atts    Attributes array. Default empty array. Optional. @see TemplateSupport::the_job_info()
 * @param  string $context The template context for filtering purposes. Default is empty string. Optional.
 */
function matador_the_job_info( $fields = array(), $atts = array(), $context = '' ) {
	echo wp_kses_post( matador_get_the_job_info( $fields, $atts, $context ) );
}

/**
 * Matador Get Job Bullhorn ID
 *
 * Gets the Bullhorn ID for the job.
 *
 * @since  3.0.0
 *
 * @param  array  $id  ID of job. Default null. Optional.
 *
 * @return string|bool Bullhorn Job ID
 */
function matador_get_the_job_bullhorn_id( $id = null ) {
	$id = Template_Support::the_job_external_id( $id, 'bullhorn' );
	return $id ? (int) $id : '';
}

/**
 * Matador The Job Bullhorn ID
 *
 * Gets the Bullhorn ID for the job.
 *
 * @since  3.4.0
 *
 * @param  array  $id  ID of job. Default null. Optional.
 */
function matador_the_job_bullhorn_id( $id = null ) {
	echo (int) matador_get_the_job_bullhorn_id( $id );
}

/**
 * Matador Get Job Field
 *
 * Gets just a meta field for a Matador Job and provides some formatting
 *
 * @since  3.4.0
 *
 * @see template templates/parts/the-job-field.php for output formatting.
 *
 * @param string $key     Meta key name. Required.
 * @param int    $id      ID of job. Default null. Optional.
 * @param array  $atts    Array of attribute arguments @see Template_Support::the_job_field
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 *
 * @return string Formatted HTML
 */
function matador_get_the_job_field( $key, $id = null, $atts = array(), $context = '' ) {

	if ( ! is_string( $context ) ) {
		$context = '';
	}

	$field = Template_Support::the_job_field( $key, $id, $atts, $context );

	/**
	 * Dynamic Filter Matador Template The Job Meta
	 *
	 * @since 3.4.0
	 *
	 * @param string $field   Meta field.
	 * @param int    $id      ID of job.
	 * @param array  $atts    Attributes array passed to function.
	 * @param string $context Template context, for filtering purposes.
	 *
	 * @return string
	 */
	$field = apply_filters( 'matador_template_the_job_field_' . $key, $field, $id, $atts, $context );

	/**
	 * Filter Matador Template The Job Meta
	 *
	 * @since 3.4.0
	 *
	 * @param string $field   Meta field.
	 * @param string $key     Meta key name.
	 * @param int    $id      ID of job.
	 * @param array  $atts    Attributes array passed to function.
	 * @param string $context Template context, for filtering purposes.
	 *
	 * @return string
	 */
	return wp_kses_post( apply_filters( 'matador_template_the_job_field', $field, $key, $id, $atts, $context ) );
}

/**
 * Matador Job Field
 *
 * Gets just a meta field for a Matador Job and provides some formatting
 *
 * @since  3.0.0
 * @since  3.4.0 added $context parameter
 *
 * @see template templates/parts/the-job-field.php for output formatting.
 *
 * @param string $key     Meta key name. Required.
 * @param int    $id      ID of job. Default null. Optional.
 * @param array  $atts    Array of arguments @see Template_Support::the_job_field
 * @param string $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_the_job_field( $key, $id = null, $atts = array(), $context = '' ) {
	echo wp_kses_post( matador_get_the_job_field( $key, $id, $atts, $context ) );
}

/**
 * Matador Get Job
 *
 * Retrieves an single job, formatted in an aside element.
 *
 * @since  3.0.0
 *
 * @see template templates/job-aside.php for formatting
 * @see template templates/job-aside-none.php for formatting when args return no job
 *
 * @param array $args Array of arguments with some required. @see Template_Support::get_job() for description.
 *
 * @return string
 */
function matador_get_job( $args = array() ) {
	return wp_kses_post( Template_Support::get_job( $args ) );
}

/**
 * Matador Job
 *
 * Retrieves an single job, formatted in an aside element.
 *
 * @since  3.0.0
 *
 * @see template templates/job-aside.php for formatting
 * @see template templates/job-aside-none.php for formatting when args return no job
 *
 * @param array $args Array of arguments with some required. @see Template_Support::get_job() for description.
 */
function matador_job( $args = array() ) {
	echo wp_kses_post( matador_get_job( $args ) );
}

/**
 * Matador Jobs
 *
 * Gets a list, listing, or table of jobs based on passed arguments.
 *
 * @since  3.0.0
 *
 * @see template templates/jobs-listings-listing.php for formatting when 'as' is 'listing' or not set.
 * @see template templates/jobs-listings-list.php for formatting when 'as' is 'list'
 * @see template templates/jobs-listings-table.php for formatting when 'as' is 'table'
 * @see template templates/jobs-listings-select.php for formatting when 'as' is 'select'
 * @see template templates/jobs-listings-empty.php for formatting when args return no jobs
 *
 * @param array $args Optional array of arguments. @see Template_Support::get_jobs() for more info.
 */
function matador_get_jobs( $args = array() ) {
	echo wp_kses_post( Template_Support::get_jobs( $args ) );
}

/**
 * Get The Job Terms
 *
 * Returns an array of all job terms. Pass the $taxonomy argument as
 * either a string with a single taxonomy name or an array of taxonomy
 * names to limit your results.
 *
 * @since  3.2.0
 *
 * @param string|array $taxonomy (optional)
 * @param int $id (optional)
 *
 * @return array|bool
 */
function matador_get_the_job_terms( $taxonomy = '', $id = null ) {
	return Template_Support::the_job_terms( $taxonomy, $id );
}

/**
 * Get The Job Terms List
 *
 * Returns a list of all job terms for a given taxonomy and job, wrapped in a span. Important to use $args when showing
 * categories on a filtered page. By default lists each term with a link to the archive, separated by a comma.
 *
 * @since  3.2.0
 *
 * @see template templates/job-terms-list.php for output formatting.
 *
 * @param string|array $taxonomy Taxonomy name (slug). Default empty string. Required.
 * @param int          $id       ID of job post. Default null. Required outside of loop, optional in loop.
 * @param array        $args     (optional) @see Template_Support::the_job_terms_list for full list of arguments.
 *
 * @return string
 */
function matador_get_the_job_terms_list( $taxonomy = '', $id = null, $args = array() ) {
	return wp_kses_post( Template_Support::the_job_terms_list( $taxonomy, $id, $args ) );
}

/**
 * The Job Terms List
 *
 * Returns a list of all job terms for a given taxonomy and job, wrapped in a span. Important to use $args when showing
 * categories on a filtered page. By default lists each term with a link to the archive, separated by a comma.
 *
 * @since  3.2.0
 *
 * @see template templates/job-terms-list.php for output formatting.
 *
 * @param string|array $taxonomy Taxonomy name (slug). Default empty string. Required.
 * @param int          $id       ID of job post. Default null. Required outside of loop, optional in loop.
 * @param array        $args     (optional) @see Template_Support::the_job_terms_list for full list of arguments.
 */
function matador_the_job_terms_list( $taxonomy = '', $id = null, $args = array() ) {
	echo wp_kses_post( matador_get_the_job_terms_list( $taxonomy, $id, $args ) );
}

/**
 * Matador Taxonomy Terms
 *
 * Prints the terms of a taxonomy.
 *
 * @since  3.0.0
 *
 * @param array $args @see Template_Support::taxonomy() for full list of arguments.
 */
function matador_taxonomy( $args ) {
	echo Template_Support::taxonomy( $args );
}

/**
 * Matador Get Term Link
 *
 * Retrieves the URL for the term, either as a link to the archive or the current page with URL query vars. Use the
 * $method and $multi arguments to match the taxonomy list options if using a filter style list.
 *
 * @since  3.0.0
 *
 * @param WP_Term $term   WP_Term object for current term. Required.
 * @param string  $tax    Taxonomy name (slug). Required.
 * @param string  $method String for which method the link is being used. Default 'list'. Optional.
 * @param bool    $multi  Whether the link is a filter multi. Default false. Optional.
 *
 * @return string
 */
function matador_get_term_link( $term, $tax, $method = 'list', $multi = false ) {
	return Template_Support::get_term_link( $term, $tax, $method, $multi );
}

/**
 * Matador Is Current Term
 *
 * Returns a true/false for whether the term is currently selected, either by being on the taxonomy term page or the
 * term being an active filter.
 *
 * @since  3.0.0
 *
 * @param WP_Term $term The WP_Term being checked. Default null. Optional.
 * @param string  $tax  The taxonomy name (slug). Default empty string. Optional.
 *
 * @return bool
 */
function matador_is_current_term( $term = null, $tax = '' ) {
	return Template_Support::is_filter_term_selected( $term, $tax );
}

/**
 * Matador Jobs Search Form
 *
 * Creates a search form for jobs.
 *
 * @since  3.0.0
 *
 * @param  array  $args  Array of arguments
 */
function matador_search_form( $args = array() ) {
	echo Template_Support::search( $args );
}

/**
 * Matador Get The Job Application
 *
 * Returns a Application for a Job
 *
 * @since  3.4.0
 *
 * @param  array  $args  Array of arguments
 *
 * @return string the application in formatted html
 */
function matador_get_the_application( $args = array() ) {
	return Template_Support::application( $args );
}

/**
 * Matador The Application
 *
 * Prints a job application form
 *
 * @since  3.4.0
 *
 * @param  array  $args  Array of arguments
 */
function matador_the_application( $args = array() ) {
	echo matador_get_the_application( $args );
}

/**
 * Matador Application
 *
 * Prints a job application form
 *
 * @since  3.0.0
 *
 * @param  array  $args  Array of arguments
 */
function matador_application( $args = array() ) {
	echo matador_get_the_application( $args );
}

/**
 * Matador Get The Confirmation
 *
 * Returns Confirmation for a Job
 *
 * @since  3.4.0
 *
 * @return string the confirmation in formatted html
 */
function matador_get_the_confirmation() {
	return matador_get_template( 'the-job-confirmation.php', array(), 'parts' );
}

/**
 * Matador The Confirmation
 *
 * Prints an Confirmation for a Job
 *
 * @since  3.4.0
 */
function matador_the_confirmation() {
	echo matador_get_the_confirmation();
}

/**
 * Matador Form Field Args
 *
 * Parses args for consumption by a form field template part.
 *
 * @since  3.0.0
 *
 * @param  array  $args   Array of arguments
 * @param  string $field  Field name
 *
 * @return array          Array with two entries, 1st an array of parsed arguements, 2nd a string with template name
 */
function matador_form_field_args( $args, $field ) {
	return Helper::form_field_args( $args, $field );
}

/**
 * Matador Pagination
 *
 * Creates pagination for the current page.
 *
 * @since  3.0.0
 *
 * @param WP_Query $query   WP_Query to base pagination. Optional, will use current global if omitted.
 * @param string   $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
 */
function matador_pagination( $query = null, $context = '' ) {
	echo wp_kses_post( Template_Support::pagination( $query, $context ) );
}

/**
 * Matador Build Attributes
 *
 * Creates pagination for the current page.
 *
 * @since  3.0.0
 *
 * @param  array  $attributes_array  Array of arguments
 * @param  bool   $echo              Whether to echo or return string
 * @return string                    string of attributes
 */
function matador_build_attributes( $attributes_array = array(), $echo = false ) {
	$output = Helper::build_attributes( $attributes_array );
	if ( $echo ) {
		echo esc_attr( $output );
	}
	return $output;
}

/**
 * Matador Button Classes
 *
 * Most themes use pure html, in most cases, and thus Matador is great at adopting theme conventions like for forms,
 * text, and more. But when it comes to button styles, themes use many class names, often depending on the underlying
 * CSS framework. In Matador, button or button-like objects are dynamically assigned class names, making it easy for
 * developers to use theme button class names via our matador_template_button_classes filter.
 *
 * @since  3.6.0
 *
 * @param string|array $classes A string or array of strings or array of strings of classes.
 * @param string $context The context of the button, either primary, secondary, or tertiary.
 *
 * @return void, echos string of escaped classes, space-separated.
 */
function matador_button_classes( $classes = 'matator-button', $context = 'primary' ) {

	$classes = matador\Template_Support::button_classes( $classes, $context );

	/**
	 * Filter Matador Template Button Classes
	 *
	 * @since 3.6.0
	 *
	 * @param string $classes Space-separated string of classes for a button
	 * @param string $context Button context, for filtering purposes, either 'primary', 'secondary', or 'tertiary'
	 *
	 * @return string
	 */
	echo esc_attr( apply_filters( 'matador_template_button_classes', $classes, $context ) );
}

/**
 * Matador Build Classes
 *
 * Takes an unlimited number of arguments of strings or arrays
 *
 * @since  3.0.0
 *
 * @param  array|string  Strings or Arrays of classes
 */
function matador_build_classes() {
	echo esc_attr( call_user_func_array( array( 'matador\Template_Support', 'build_classes' ), func_get_args() ) );
}
