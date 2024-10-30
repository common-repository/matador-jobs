<?php
/**
 * Deprecated Functions
 *
 * The trash bin. Stuff we once supported but plan to toss out soon.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\Template_Support;
use matador\Helper;

add_shortcode( 'bullhorn', '_deprecated_shortcode_bullhorn' );
/**
 * Deprecated Shortcode [bullhorn]
 *
 * Behaves like [matador_jobs]
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @param array $atts
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn( $atts ) {

	Helper::deprecated_notice( 'shortcode', '[bullhorn]', '[matador_jobs]' );

	if ( isset( $atts['meta_to_show'] ) ) {
		$atts['fields'] = $atts['meta_to_show'];
		unset( $atts['meta_to_show'] );
	}

	$atts = shortcode_atts( array(
		'as'            => 'listing',
		'fields'        => 'title,content',
		'location'      => null,
		'category'      => null,
		'content_limit' => null,
		'id'            => null,
		'class'         => 'matador-jobs-shortcode',
		'search'        => null,
		'selected'      => null,
		'min'           => null,
		'limit'         => null,
	), $atts );

	return Template_Support::get_jobs( $atts );
}

add_shortcode( 'bullhorn_categories', '_deprecated_shortcode_bullhorn_categories' );
/**
 * 'Bullhorn_Categories' Shortcode
 *
 * Behaves like [matador_categories]
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn_categories() {

	Helper::deprecated_notice( 'shortcode', '[bullhorn_categories]', '[matador_categories]' );

	return Template_Support::taxonomy( array() );
}

add_shortcode( 'bullhorn_states', '_deprecated_shortcode_bullhorn_states' );
/**
 * 'Bullhorn_States' Shortcode
 *
 * Behaves like [matador_locations]
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn_states() {

	Helper::deprecated_notice( 'shortcode', '[bullhorn_states]', '[matador_locations]' );

	return Template_Support::taxonomy( array( 'tax' => 'location' ) );
}

add_shortcode( 'bullhorn_search', '_deprecated_shortcode_bullhorn_search' );
/**
 * 'Bullhorn_Search' Shortcode
 *
 * Behaves like [matador_search]
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn_search() {

	Helper::deprecated_notice( 'shortcode', '[bullhorn_search]', '[matador_search]' );

	return Template_Support::search( array( 'fields' => array( 'text' ) ) );
}

add_shortcode( 'bullhorn_cv_form', '_deprecated_shortcode_bullhorn_cv_form' );
/**
 * 'Bullhorn_CV_Form' Shortcode
 *
 * Behaves like [matador_application fields='name,email,phone,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn_cv_form() {

	Helper::deprecated_notice( 'shortcode', '[bullhorn_cv_form]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'message', 'resume' ) ) );
}

add_shortcode( 'bullhorn_cv_form_with_jobs', '_deprecated_shortcode_bullhorn_cv_form_with_jobs' );
/**
 * 'Bullhorn_CV_Form_With_Jobs' Shortcode
 *
 * Behaves like [matador_application fields=name,email,phone,address,jobs,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_bullhorn_cv_form_with_jobs() {

	Helper::deprecated_notice( 'shortcode', '[bullhorn_cv_form_with_jobs]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'address', 'jobs', 'message', 'resume' ) ) );
}

add_shortcode( 'b2wp_resume_form', '_deprecated_shortcode_b2wp_resume_form' );
/**
 * 'Bullhorn_CV_Form_With_Jobs' Shortcode
 *
 * Behaves like [matador_application fields=name,email,phone,address,jobs,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_b2wp_resume_form() {

	Helper::deprecated_notice( 'shortcode', '[b2wp_resume_form]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'resume' ) ) );
}

add_shortcode( 'b2wp_application', '_deprecated_shortcode_b2wp_application' );
/**
 * 'B2WP_Application' Shortcode
 *
 * Behaves like [matador_application fields=name,email,phone,address,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_b2wp_application() {

	Helper::deprecated_notice( 'shortcode', '[b2wp_application]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'address', 'message', 'resume' ) ) );
}

add_shortcode( 'b2wp_application_with_jobs', '_deprecated_shortcode_b2wp_application_with_jobs' );
/**
 * 'B2WP_Application_with_Jobs' Shortcode
 *
 * Behaves like [matador_application fields=name,email,phone,address,jobs,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_b2wp_application_with_jobs() {

	Helper::deprecated_notice( 'shortcode', '[b2wp_application_with_jobs]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'address', 'jobs', 'message', 'resume' ) ) );
}

add_shortcode( 'b2wp_application_with_job_text', '_deprecated_shortcode_b2wp_application_with_job_text' );
/**
 * 'B2WP_Application_with_Job_Text' Shortcode
 *
 * Behaves like [matador_application fields='name,email,phone,address,request,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_b2wp_application_with_job_text() {

	Helper::deprecated_notice( 'shortcode', '[b2wp_application_with_job_text]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'address', 'request', 'message', 'resume' ) ) );
}

add_shortcode( 'b2wp_shortapp', '_deprecated_shortcode_b2wp_shortapp' );
/**
 * 'B2WP_Shortapp' Shortcode
 *
 * Behaves like [matador_application fields='name,email,phone,message,resume']
 *
 * @since unknown
 * @deprecated 3.0.0
 *
 * @return string
 */
function _deprecated_shortcode_b2wp_shortapp() {

	Helper::deprecated_notice( 'shortcode', '[b2wp_shortapp]', '[matador_application]' );

	return Template_Support::application( array( 'fields' => array( 'name', 'email', 'phone', 'message', 'resume' ) ) );
}

//
// Deprecated Global Namespace Functions
//

/**
 * Matador Get The Job Content
 *
 * Returns the job content as an excerpt (default or custom) or the full job content. Must be used in a loop.
 *
 * @since 3.0.0
 * @deprecated 3.4.0 in favor of get_the_job_description as its naming convention is more in line with our users.
 *
 * @param string $limit   The limit for the content. Accepts 'full' for full content, 'excerpt' for the default excerpt,
 *                        or an integer for the limit of words to generate an excerpt. Default 'excerpt'. Optional.
 *
 * @return string         Job content/description
 */
function matador_get_the_job_content( $limit = 'excerpt' ) {

	return wp_kses_post( matador_get_the_job_description( null, $limit, '' ) );
}

//
// Deprecated Filters/Actions
//

add_filter( 'matador_rewrites_taxonomy', '_deprecated_matador_taxonomy_rewrites_key', 10, 2 );
/**
 * Deprecated Filter Handler for 'matador_taxonomies_rewrites_$key'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 * @var string $key
 *
 * @return array
 */
function _deprecated_matador_taxonomy_rewrites_key( $args, $key ) {
	if ( has_filter( "matador_taxonomy_rewrites_{$key}" ) ) {

		matador\Helper::deprecated_notice( 'filter', "matador_taxonomy_rewrites_{$key}", 'matador_rewrites_taxonomy' );

		/**
		 * Filter: Matador Taxonomies Rewrites (deprecated)
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 *
		 * @return array
		 */
		return apply_filters( "matador_taxonomy_rewrites_{$key}", $args );
	}
	return $args;
}

add_action( 'matador_taxonomy_terms_before', '_deprecated_matador_taxonomies_before', 10 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_before'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_before( $args ) {

	// Though it was intended to one day apply to both select and list templates,
	// this action never added to the select template prior to its retirement, but
	// its successor was. So if we are calling the select template, ignore.
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_before' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_before', 'matador_taxonomy_terms_before' );

		/**
		 * Action: Matador Taxonomies Before
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 */
		do_action( 'matador_taxonomies_before' );
	}
}

add_action( 'matador_taxonomy_terms_before', '_deprecated_matador_taxonomies_list_before', 10 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_before'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 */
function _deprecated_matador_taxonomies_list_before() {

	// This call was only for the 'list' template
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_list_before' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_before', 'matador_taxonomy_terms_before' );

		/**
		 * Action: Matador Taxonomies List Before
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 */
		do_action( 'matador_taxonomies_list_before' );
	}
}

add_action( 'matador_taxonomy_terms_before_terms', '_deprecated_matador_taxonomies_before_terms', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_before'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_before_terms( $args ) {

	// Though it was intended to one day apply to both select and list templates,
	// this action never added to the select template prior to its retirement, but
	// its successor was. So if we are calling the select template, ignore.
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_before_terms' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_before_terms', 'matador_taxonomy_terms_before_terms' );

		/**
		 * Action: Matador Taxonomies Before Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_before_terms', $args );
	}
}

add_action( 'matador_taxonomy_terms_before_terms', '_deprecated_matador_taxonomies_list_before_terms', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_before'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_list_before_terms( $args ) {

	// This call was only for the 'list' template
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_list_before_terms' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_before_terms', 'matador_taxonomy_terms_before_terms' );

		/**
		 * Action: Matador Taxonomies Before Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_list_before_terms', $args );
	}
}

add_action( 'matador_taxonomy_terms_before_term', '_deprecated_matador_taxonomies_list_before_href', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_before_href'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_list_before_href( $args ) {
	if ( has_filter( 'matador_taxonomies_list_before_href' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_before_href', 'matador_taxonomy_terms_before_term' );

		/**
		 * Action: Matador Taxonomies List Before HREF
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_list_before_href', $args );
	}
}

add_filter( 'matador_taxonomy_terms_term_label', '_deprecated_matador_taxonomies_list_text', 10, 3 );
/**
 * Deprecated Filter Handler for 'matador_taxonomies_list_text'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var string   $name
 * @var \WP_Term $term
 * @var array    $args
 *
 * @return string
 */
function _deprecated_matador_taxonomies_list_text( $name, $term, $args ) {

	if ( has_filter( 'matador_taxonomies_list_text' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_taxonomies_list_text', 'matador_taxonomy_terms_term_label' );

		$current = matador_is_current_term( $term, $args['taxonomy']['key'] );

		/**
		 * Filter: Matador Taxonomies List Text
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var string $name
		 * @var bool   $current
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_taxonomies_list_text', $name, $current, $args );
	}
	return $name;
}

add_action( 'matador_taxonomy_terms_after_term', '_deprecated_matador_taxonomies_list_after_href', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_after_href'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_list_after_href( $args ) {
	if ( has_filter( 'matador_taxonomies_list_after_href' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_after_href', 'matador_taxonomy_terms_after_term' );

		/**
		 * Action: Matador Taxonomies List After HREF
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_list_after_href', $args );
	}
}

add_action( 'matador_taxonomy_terms_after_terms', '_deprecated_matador_taxonomies_list_after_terms', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_after_terms'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_list_after_terms( $args ) {

	// This call was only for the 'list' template
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_list_after_terms' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_after_terms', 'matador_taxonomy_terms_after_terms' );

		/**
		 * Action: Matador Taxonomies List After Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_list_after_terms', $args );
	}
}

add_action( 'matador_taxonomy_terms_after_terms', '_deprecated_matador_taxonomies_after_terms', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_after_terms'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_after_terms( $args ) {

	// Though it was intended to one day apply to both select and list templates,
	// this action never added to the select template prior to its retirement, but
	// its successor was. So if we are calling the select template, ignore.
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_after_terms' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_after_terms', 'matador_taxonomy_terms_after_terms' );

		/**
		 * Action: Matador Taxonomies After Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_after_terms', $args );
	}
}

add_action( 'matador_taxonomy_terms_after', '_deprecated_matador_taxonomies_list_after', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_list_after'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_list_after( $args ) {

	// This call was only for the 'list' template
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_list_after' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_list_after', 'matador_taxonomy_terms_after' );

		/**
		 * Action: Matador Taxonomies List After Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_list_after', $args );
	}
}

add_action( 'matador_taxonomy_terms_after', '_deprecated_matador_taxonomies_after', 10, 1 );
/**
 * Deprecated Action Handler for 'matador_taxonomies_after'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $args
 */
function _deprecated_matador_taxonomies_after( $args ) {

	// Though it was intended to one day apply to both select and list templates,
	// this action never added to the select template prior to its retirement, but
	// its successor was. So if we are calling the select template, ignore.
	if ( ! empty( $args['as'] ) && 'list' !== $args['as'] ) {
		return;
	}

	if ( has_filter( 'matador_taxonomies_after' ) ) {

		matador\Helper::deprecated_notice( 'action', 'matador_taxonomies_after', 'matador_taxonomy_terms_after' );

		/**
		 * Action: Matador Taxonomies After Terms
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array $args
		 */
		do_action( 'matador_taxonomies_after', $args );
	}
}

add_filter( 'matador_taxonomy_terms_all_term_label', '_deprecated_matador_reset_list_filter_text', 10, 2 );
/**
 * Deprecated Filter Handler for 'matador_reset_list_filter_text'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var string   $label
 * @var array    $args
 *
 * @return string
 */
function _deprecated_matador_reset_list_filter_text( $label, $args ) {

	if ( has_filter( 'matador_reset_list_filter_text' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_reset_list_filter_text', 'matador_taxonomy_terms_all_term_label' );

		/**
		 * Filter: Reset List Filter Text
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var string $name
		 * @var bool   $current
		 * @var array  $args
		 *
		 * @return string
		 */
		return apply_filters( 'matador_reset_list_filter_text', $label, $args['taxonomy']['key'] );
	}
	return $label;
}

add_filter( 'matador_taxonomy_terms_arg_method', '_deprecated_matador_taxonomy_output_args_methods', 10 );
/**
 * Deprecated Filter Handler for 'matador_taxonomy_output_args_methods'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $allowed
 *
 * @return array
 */
function _deprecated_matador_taxonomy_output_args_methods( $allowed ) {

	if ( has_filter( 'matador_taxonomy_output_args_methods' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_taxonomy_output_args_methods', 'matador_taxonomy_terms_arg_method' );

		/**
		 * Filter: Reset List Filter Text
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 *
		 * @var array  $allowed
		 *
		 * @return string
		 */
		return apply_filters( 'matador_taxonomy_output_args_methods', $allowed );
	}
	return $allowed;
}

add_filter( 'matador_taxonomy_terms_arg_show_all_option', '_deprecated_matador_taxonomy_output_args_show_all_options', 10 );
/**
 * Deprecated Filter Handler for 'matador_taxonomy_output_args_show_all_options'
 *
 * @since      3.3.0
 * @deprecated 3.3.0
 *
 * @var array $allowed
 *
 * @return array
 */
function _deprecated_matador_taxonomy_output_args_show_all_options( $allowed ) {

	if ( has_filter( 'matador_taxonomy_output_args_show_all_options' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_taxonomy_output_args_show_all_options', 'matador_taxonomy_terms_arg_show_all_option' );

		/**
		 * Filter: Reset List Filter Text
		 *
		 * @since      3.0.0
		 * @deprecated 3.3.0
		 ** @var array $allowed
		 *
		 * @return string
		 */
		return apply_filters( 'matador_taxonomy_output_args_show_all_options', $allowed );
	}

	return $allowed;
}


add_filter( 'matador_submit_candidate_candidate_data', '_deprecated_matador_add_data_to_candidate_data', 10, 3 );
/**
 * Deprecated Filter Handler for 'matador_add_data_to_candidate_data'
 *
 * @since      3.4.0
 * @deprecated 3.4.0
 *
 * @param stdClass $candidate
 * @param array $application
 * @param string $action
 *
 * @return string
 */
function _deprecated_matador_add_data_to_candidate_data( $candidate, $application, $action ) {

	if ( has_filter( 'matador_add_data_to_candidate_data' ) && 'create' === $action ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_add_data_to_candidate_data', 'matador_submit_candidate_candidate_data' );

		/**
		 * Matador Add Data to Candidate Data
		 *
		 * Modify the created candidate before returning for save.
		 *
		 * @since 3.0.0
		 * @deprecated 3.4.0
		 *
		 * @param stdClass $candidate
		 * @param array $application
		 */
		return apply_filters( 'matador_add_data_to_candidate_data', $candidate, $application );
	}

	return $candidate;
}

add_filter( 'matador_submit_candidate_candidate_data', '_deprecated_matador_bullhorn_update_existing_candidate', 10, 3 );
/**
 * Deprecated Filter Handler for 'matador_bullhorn_update_existing_candidate'
 *
 * @since      3.4.0
 * @deprecated 3.4.0
 *
 * @param stdClass $candidate
 * @param array $application
 * @param string $action
 *
 * @return string
 */
function _deprecated_matador_bullhorn_update_existing_candidate( $candidate, $application, $action ) {

	if ( has_filter( 'matador_bullhorn_update_existing_candidate' ) && 'update' === $action ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_bullhorn_update_existing_candidate', 'matador_submit_candidate_candidate_data' );

		/**
		 * Matador Add Data to Candidate Data
		 *
		 * Modify the created candidate before returning for save.
		 *
		 * @since 3.0.0
		 * @deprecated 3.4.0
		 *
		 * @param stdClass $candidate
		 * @param array $application
		 */
		return apply_filters( 'matador_bullhorn_update_existing_candidate', $candidate, $application );
	}

	return $candidate;
}

add_filter( 'matador_submit_candidate_notes_message_label', '_deprecated_bullhorn_message_prefix', 10, 1 );
/**
 * Deprecated Filter Handler for 'bullhorn_message_prefix'
 *
 * @since      3.4.0
 * @deprecated 3.4.0
 *
 * @param string $label
 *
 * @return string
 */
function _deprecated_bullhorn_message_prefix( $label ) {

	if ( has_filter( 'bullhorn_message_prefix' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'bullhorn_message_prefix', 'matador_submit_candidate_notes_message_label' );

		/**
		 * Bullhorn Message Prefix
		 *
		 * Modify the label for the candidate message that prepends it before being saved as a note.
		 *
		 * @since 3.0.0
		 * @deprecated 3.4.0
		 *
		 * @param string $label the text that comes before the "Message" field on a form response.
		 */
		return apply_filters( 'bullhorn_message_prefix', $label );

	}

	return $label;
}

add_filter( 'matador_submit_candidate_notes_jobs_label', '_deprecated_bullhorn_position_prefix', 10, 1 );
/**
 * Deprecated Filter Handler for 'bullhorn_message_prefix'
 *
 * @since      3.4.0
 * @deprecated 3.4.0
 *
 * @param string $label
 *
 * @return string
 */
function _deprecated_bullhorn_position_prefix( $label ) {

	if ( has_filter( 'bullhorn_position_prefix' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'bullhorn_position_prefix', 'matador_submit_candidate_notes_jobs_label' );

		/**
		 * Bullhorn Position Prefix
		 *
		 * Modify the label for the candidate's jobs that prepends it before being saved as a note.
		 *
		 * @since 3.0.0
		 * @deprecated 3.4.0
		 *
		 * @param string $label the text that comes before the "Jobs" field on a form response.
		 */
		return apply_filters( 'bullhorn_position_prefix', $label );
	}

	return $label;
}

add_filter( 'matador_template_the_job_description_excerpt_more', '_deprecated_matador_get_the_job_excerpt_more', 10, 1 );
/**
 * Deprecated Filter Handler for 'bullhorn_message_prefix'
 *
 * @since      3.4.0
 * @deprecated 3.4.0
 *
 * @param string $more
 *
 * @return string
 */
function _deprecated_matador_get_the_job_excerpt_more( $more ) {

	if ( has_filter( 'matador_get_the_job_excerpt_more' ) ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_get_the_job_excerpt_more', 'matador_template_the_job_description_excerpt_more' );

		/**
		 * Matador Get the Job Excerpt More
		 *
		 * @since 3.0.0
		 * @deprecated 3.4.0
		 *
		 * @param string $more
		 *
		 * @return string
		 */
		return apply_filters( 'matador_get_the_job_excerpt_more', $more );
	}

	return $more;
}

if ( has_filter( 'matador-the-job-where' ) ) {

	add_filter( 'matador_bullhorn_import_the_job_where', '_deprecated_matador_the_job_where' );
	/**
	 * Deprecated Filter Handler for 'matador-the-job-where'
	 *
	 * @since      3.8.0
	 * @deprecated 3.8.0
	 *
	 * @param string $where
	 *
	 * @return string
	 */
	function _deprecated_matador_the_job_where( $where ) {

		matador\Helper::deprecated_notice( 'filter', 'matador-the-job-where', 'matador_bullhorn_import_the_job_where' );

		/**
		 * Deprecated Filter : Matador the Job Where
		 *
		 * @since      3.0.0
		 * @deprecated 3.5.0
		 *
		 * @param string $where
		 *
		 * @return string
		 */
		return apply_filters( 'matador-the-job-where', $where );
	}
}

if ( has_filter( 'matador_bullhorn_import_overwrite_job_on_sync' ) ) {

	add_filter( 'matador_bullhorn_import_skip_job_on_update', '_deprecated_matador_bullhorn_import_overwrite_job_on_sync', 10, 3 );
	/**
	 * Deprecated Filter Handler for 'bullhorn_message_prefix'
	 *
	 * @since      3.5.4
	 * @deprecated 3.5.4
	 *
	 * @param bool   $overwrite default true
	 * @param object $job       the current job being imported
	 * @param int    $wpid      the ID corresponding to the current job if it exists in DB, else null
	 *
	 * @return string
	 */
	function _deprecated_matador_bullhorn_import_overwrite_job_on_sync( $overwrite, $job, $wpid ) {

		matador\Helper::deprecated_notice( 'filter', 'matador_bullhorn_import_overwrite_job_on_sync', 'matador_bullhorn_import_skip_job_on_update' );

		/**
		 * Filter : Matador Import overwrite on sync
		 *
		 * Filter to control in a job should overwritten. Renamed to matador_bullhorn_import_skip_job_on_update with
		 * boolean flipped (true once delivered desired behavior, now false does).
		 *
		 * @since      3.5.0
		 * @deprecated 3.5.4
		 *
		 * @param bool   $overwrite default true
		 * @param object $job       the current job being imported
		 * @param int    $wpid      the ID corresponding to the current job if it exists in DB, else null
		 *
		 * @return bool
		 */
		return ! apply_filters( 'matador_bullhorn_import_overwrite_job_on_sync', true, $job, $wpid );
	}
}

//
// Deprecated Templates
//

add_filter( 'matador_locate_template', '_deprecated_moved_template_files', 10, 3 );
/**
 * Handle Moved Template Files
 *
 * @since      3.2.0
 * @since      3.3.0 added handling for jobs-taxonomies-list-all.php
 * @deprecated 3.2.0
 *
 * @var string $template
 * @var string $name
 * @var string $subdirectory
 *
 * @return string
 */
function _deprecated_moved_template_files( $template, $name, $subdirectory ) {

	unset( $subdirectory ); // Not used yet, maybe later.

	/**
	 * Handle Moved/Renamed Template email_applicant_content.php
	 *
	 * New location emails/application-confirmation-for-candidate.php
	 *
	 * @since 3.2.0
	 */
	if ( 'application-confirmation-for-candidate.php' === $name ) {
		$deprecated_location = locate_template( array( trailingslashit( 'matador' ) . 'email_applicant_content.php' ) );

		if ( $deprecated_location ) {
			return $deprecated_location;
		}
	}

	/**
	 * Handle Moved/Renamed Template email_recruiter_content.php
	 *
	 * New location emails/application-confirmation-for-recruiter.php
	 *
	 * @since 3.2.0
	 */
	if ( 'application-confirmation-for-recruiter.php' === $name ) {
		$deprecated_location = locate_template( array( trailingslashit( 'matador' ) . 'email_recruiter_content.php' ) );

		if ( $deprecated_location ) {
			return $deprecated_location;
		}
	}

	/**
	 * Handle Moved Template jobs-taxonomies-list-all.php
	 *
	 * New location parts/jobs-taxonomies-list-all.php
	 *
	 * @since 3.3.0
	 */
	if ( 'jobs-taxonomies-list-all.php' === $name ) {
		$deprecated_location = locate_template( array( trailingslashit( 'matador' ) . $name ) );

		if ( $deprecated_location ) {
			return $deprecated_location;
		}
	}

	return $template;
}
