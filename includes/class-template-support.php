<?php
/**
 * Class Template Support
 *
 * A class to contain functions and helpers that generate front-end output.
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

use DateTime;
use WP_Query;
use WP_Term;
use WP_Error;

final class Template_Support {

	/**
	 * Get Template
	 *
	 * Loads a template and passes an array of variables.
	 *
	 * @since 3.0.0
	 * @since 3.3.0 added support for outputting template path in html comments
	 *
	 *
	 * @param string $name Name of template to use.
	 * @param array $args Array of args passed to template.
	 * @param string $subdirectory Name of subdirectory where template is located.
	 * @param bool $admin Whether the template is an admin template, which prohibits theme override
	 * @param bool $echo Whether to echo the template or return it as a string.
	 *
	 * @return string|bool
	 */
	public static function get_template( $name, $args = array(), $subdirectory = '', $admin = false, $echo = false ) {

		$template = null;

		if ( $name ) {
			$template = self::locate_template( $name, $subdirectory, $admin );
		}

		if ( $template ) {
			if ( file_exists( $template ) ) {
				if ( $args && is_array( $args ) ) {
					extract( $args ); // @codingStandardsIgnoreLine (I'm gonna do it anyway)
				}

				if ( ! $echo ) {
					ob_start();
				}

				/**
				 * Filter: Get Template Print Template Name Comment
				 *
				 * This filter defaults to false, but when provided a true, will add an HTML comment above each Matador
				 * template inclusion. WARNING: Use this with caution and only in development, as it reveals sensitive
				 * information about your server.
				 *
				 * @since 3.3.0
				 *
				 * @param bool $print Whether to include an HTML comment with the name of the template for debugging.
				 */
				if ( apply_filters( 'matador_get_template_print_name_comment', false ) ) {
					echo '<!-- ' . esc_html__( 'start output', 'matador-jobs' ) . ': ' . esc_url( $template ) . ' -->';
				}

				include $template;

				if ( ! $echo ) {

					return preg_replace( '/\s+/', ' ', ob_get_clean() );
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Get Template Part
	 *
	 * Gets template part (for templates in loops).
	 *
	 * @since 3.0.0
	 *
	 *
	 * @param string $slug Name prefix for template part.
	 * @param string $name Name of template part to use.
	 * @param array $args Array of args passed to template.
	 * @param string $subdirectory Name of subdirectory where template is located.
	 * @param bool $admin Whether the template is an admin template, which prohibits theme override
	 * @param bool $echo Whether to echo the template or return it as a string.
	 *
	 * @return string|bool
	 */
	public static function get_template_part( $slug, $name = '', $args = array(), $subdirectory = 'parts', $admin = false, $echo = false ) {

		$template_name = $slug . '-' . $name . '.php';

		return self::get_template( $template_name, $args, $subdirectory, $admin, $echo );
	}

	/**
	 * Locate Template
	 *
	 * Locates a template and return the path for inclusion.
	 *
	 * @since 3.0.0
	 * @since 3.3.0 added support for extension templating
	 *
	 *
	 * @param string $name Name of template part to use.
	 * @param string $subdirectory Name of subdirectory where template is located.
	 * @param bool   $admin Whether the template is an admin template, which prohibits theme override
	 *
	 * @return string|bool
	 */
	public static function locate_template( $name, $subdirectory = '', $admin = false ) {

		$template = false;

		if ( $admin ) {

			// Check Admin Subdirectory for Admin Templates
			$plugin_admin_template_directory = Matador::$directory . 'includes/admin/templates/' . $subdirectory;

			if ( file_exists( trailingslashit( $plugin_admin_template_directory ) . $name ) ) {
				$template = trailingslashit( $plugin_admin_template_directory ) . $name;
			}
		} else {

			// Locate Template within Theme
			$template = locate_template( array( trailingslashit( 'matador' ) . trailingslashit( $subdirectory ) . $name ) );
		}

		// Look Within Main Templates Directory
		if ( ! $template ) {

			$plugin_template_directory = Matador::$directory . 'templates/' . $subdirectory;

			if ( file_exists( trailingslashit( $plugin_template_directory ) . $name ) ) {
				$template = trailingslashit( $plugin_template_directory ) . $name;
			}

			/**
			 * Filter: Locate Template Replace Default Template
			 *
			 * Gives extension developers the ability to override the selected core template without overriding a theme
			 * replacement. This is useful if an extension needs to replace a core template but still wants to allow a
			 * user the ability to replace their template, which is not possible with the "matador_locate_template"
			 * filter that fires later.
			 *
			 * @since 3.3.0
			 *
			 * @param string $template     Name of found template.
			 * @param string $name         Template being searched for.
			 * @param string $subdirectory Subdirectory template should be found in.
			 */
			$template = apply_filters( 'matador_locate_template_replace_default', $template, $name, $subdirectory );
		}

		// Look in Registered Extension Templates Directories
		if ( ! $template ) {
			/**
			 * Filter: Location Template Directories
			 *
			 * Gives extension developers the ability to add directories to be looked in should a template not be found.
			 * This does not override a default template, which should not be done by an extension.
			 *
			 * @since 3.3.0
			 *
			 * @param array $directories Array of additional directories where a template could exist.
			 */
			foreach ( apply_filters( 'matador_locate_template_additional_directories', array() ) as $directory ) {
				$extension_directory = $directory . 'templates/' . $subdirectory;

				if ( file_exists( trailingslashit( $extension_directory ) . $name ) ) {
					$template = trailingslashit( $extension_directory ) . $name;
				}
			}
		}

		/**
		 * Filter: Locate Template
		 *
		 * Gives extension developers and theme developers the ability to override the selected template. This should be
		 * used with caution, as it may interrupt expected behavior, which is the plugin template hierarchy.
		 *
		 * @since 3.0.0
		 *
		 * @param string $template     Name of found template.
		 * @param string $name         Template being searched for.
		 * @param string $subdirectory Subdirectory template should be found in.
		 */
		return apply_filters( 'matador_locate_template', $template, $name, $subdirectory );
	}

	/**
	 * Matador The Job Title
	 *
	 * Fetches the title of the job
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  int $id ID of job (optional when used in WP loop)
	 *
	 * @return string
	 */
	public static function the_job_title( $id = null ) {
		$id = is_int( $id ) ? $id : get_the_id();

		return get_the_title( $id );
	}

	/**
	 * Matador The Job Description
	 *
	 * Fetches the filtered description from the job.
	 *
	 * @since 3.4.0
	 * @since 3.5.6 added 'context' parameter to support move of 'matador_template_the_job_description' filter
	 *
	 *
	 * @param int        $id      The job ID. (optional)
	 * @param int|string $limit   The limit for the excerpt. Accepts integer for number of words, 'excerpt' which is 240
	 *                            words, or 'full' which is the full Description with formatting.
	 * @param string     $context The template context.
	 *
	 * @return string
	 */
	public static function the_job_description( $id = null, $limit = 'excerpt', $context = '' ) {

		// Either we need an ID, or we need to be in the loop.
		if ( ! in_the_loop() && ! is_numeric( $id ) ) {
			$id = get_the_ID();
		}

		// If we have an ID, we need to check its a job.
		if ( is_numeric( $id ) && Matador::variable( 'post_type_key_job_listing' ) !== get_post_type( $id ) ) {
			return '';
		}

		if ( null !== $id ) {
			$description = get_post_field( 'post_content', $id );
		} else {
			$description = get_the_content();
		}

		if ( ! empty( $description ) && 'full' !== $limit ) {
			$description = self::the_job_excerpt( $description, $limit );
		}

		/**
		 * Filter: Matador Template The Job Description
		 *
		 * @since 3.4.0
		 * @since 3.5.6 moved from template-functions.php
		 *
		 * @param string $description  The job description
		 * @param int    $id           The job ID
		 * @param string $limit        The word limit or 'excerpt' or 'full'
		 * @param string $context      Template context, for filtering purposes
		 *
		 * @return string The job description
		 */
		$description = apply_filters( 'matador_template_the_job_description', $description, $id, $limit, $context );

		add_filter( 'matador_doing_custom_loop', '__return_true', 11 );

		$description = apply_filters( 'the_content', $description );

		remove_filter( 'matador_doing_custom_loop', '__return_true', 11 );

		return $description;
	}

	/**
	 * The Job Excerpt
	 *
	 * Gets an excerpt of the job content, stripped of tags and shortcodes.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 revised to now accept description instead of generating it.
	 *
	 *
	 * @param string     $description  The description to be excerpted.
	 * @param int|string $limit        The limit for the excerpt. Accepts integer for number of words, 'excerpt' which
	 *                                 is 240 words, or 'full' which is the full Description with formatting.
	 *
	 * @return null|string
	 */
	public static function the_job_excerpt( $description, $limit ) {

		if ( empty( $description ) ) {
			return '';
		}

		if ( ! is_numeric( $limit ) ) {
			/**
			 * Filter: Matador Template the Job Description Default Excerpt Length
			 *
			 * @since 3.4.0
			 *
			 * @param int The excerpt length in number of words.
			 *
			 * @return int The excerpt length in number of words.
			 */
			$limit = apply_filters( 'matador_template_the_job_description_default_excerpt_length', 240 );
		}

		$description = wp_strip_all_tags( $description );
		$trimmed     = null;

		if ( mb_strlen( $description ) > $limit ) {

			$subex = mb_substr( $description, 0, $limit - 5 );

			$exwords = explode( ' ', $subex );

			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

			if ( $excut < 0 ) {
				$trimmed = mb_substr( $subex, 0, $excut );
			} else {
				$trimmed = $subex;
			}

			/**
			 * Filter: Matador Template the Job Description Default Excerpt Length
			 *
			 * @since 3.4.0
			 *
			 * @param  string The 'more' link for when an excerpt should lead to more content.
			 *
			 * @return string
			 */
			$more = apply_filters( 'matador_template_the_job_description_excerpt_more', '[...]' );

			$trimmed .= $more;

		} else {

			$trimmed = $description;

		}

		return $trimmed;
	}

	/**
	 * Matador The Job Posted Date
	 *
	 * Fetches job posted date. Output is filtered.
	 *
	 * @since  3.4.0
	 *
	 *
	 * @param  string $format  The PHP date format. Default empty string. Optional.
	 * @param  int    $id      The WordPress post (job) ID. Default null. Optional in loop.
	 * @param  string $context The template context for more specific filtering.
	 *
	 * @return string
	 */
	public static function the_job_posted_date( $format = '', $id = null, $context = '' ) {

		$setting = Matador::setting( 'matador_date_format' );

		if ( ! $format && $setting ) {
			$format = $setting;
		}

		/**
		 * Filter Matador The Job Posted Date Format
		 *
		 * Overrides the passed, setting, or default date format.
		 *
		 * @since 3.4.0
		 *
		 * @param string $context the template context.
		 */
		$format = apply_filters( 'matador_the_job_posted_date_date_format', $format, $context );

		if ( is_numeric( $id ) ) {
			$id = intval( $id );
		} else {
			$id = get_the_id();
		}

		return esc_html( get_the_date( $format, $id ) );
	}

	/**
	 * Matador The Job Date Difference
	 *
	 * Fetches a DateTime object holding the difference in time since the job was posted and now
	 *
	 * @since  3.7.0
	 *
	 * @param  int $id The WordPress post (job) ID. Default null. Optional in loop.
	 *
	 * @return DateTime The job's age.
	 */
	public static function the_job_date_difference( $id = null ) {

		if ( is_numeric( $id ) ) {
			$id = intval( $id );
		} else {
			$id = get_the_id();
		}

		$date = new DateTime( self::the_job_posted_date( 'c', $id ) );
		$now  = new DateTime();

		return $date->diff( $now );
	}

	/**
	 * Matador The Job Relative Age
	 *
	 * Returns a relative statement on the job's age in reference to how long ago it was posted.
	 *
	 * @since  3.7.0
	 *
	 * @param  int $id The WordPress post (job) ID. Default null. Optional in loop.
	 *
	 * @return DateTime The job's age.
	 */
	public static function the_job_relative_age( $id = null ) {

		$diff = self::the_job_date_difference( $id );

		if ( $diff->m > 2 ) {
			$age = sprintf( _n( '%s month ago', '%s months ago', $diff->m ), number_format_i18n( $diff->m ) );
		} elseif ( $diff->d > 14 ) {
			$age = sprintf( _n( '%s week ago', '%s weeks ago', round( $diff->d / 7 ) ), number_format_i18n( round( $diff->d / 7 ) ) );
		} elseif ( $diff->d > 1 ) {
			$age = sprintf( _n( '%s day ago', '%s days ago', $diff->d ), number_format_i18n( $diff->d ) );
		} elseif ( 1 === $diff->d ) {
			$age = _x( 'Yesterday', 'Age in days, 1 day, relative term', 'matador-jobs' );
		} elseif ( $diff->h >= 1 ) {
			$age = sprintf( _n( '%s hour ago', '%s hours ago', $diff->h ), number_format_i18n( $diff->h ) );
		} else {
			$age = _x( 'Just now', 'A job posted very recently, in the last hour or less', 'matador-jobs' );
		}

		/**
		 * Filter: The Job Age
		 *
		 * Filter the output of the_job_age function based on the difference between the current date and job posted
		 * date.
		 *
		 * @since 3.7.0
		 *
		 * @param string $age A string representing the relative age of the job, ie: `5 Days Ago` or `Just Now`
		 * @param DateTime $diff A DateTime object with the age of the job.
		 *
		 * @return string Modified string representing the relative age of the job.
		 */
		return apply_filters( 'matador_the_job_relative_age', $age, $diff );
	}

	/**
	 * Matador The Job Meta
	 *
	 * Fetches job meta with a key. Output is filtered.
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  string $key
	 * @param  int $id (optional when in WP loop)
	 *
	 * @return string
	 */
	public static function the_job_meta( $key = null, $id = null ) {
		if ( ! $key ) {
			return '';
		}

		if ( 'date' === $key ) {
			return self::the_job_posted_date( '', $id );
		}

		if ( 'age' === $key ) {
			return self::the_job_relative_age( $id );
		}

		if ( in_array( $key, [ 'bullhorn_job_id', 'bullhorn_id' ], true ) ) {
			return self::the_job_external_id( $id, 'bullhorn' );
		}

		if ( is_numeric( $id ) ) {
			$id = intval( $id );
		} else {
			$id = get_the_id();
		}

		return esc_html( get_post_meta( $id, $key, true ) );
	}

	/**
	 * Matador The Job ID
	 *
	 * Fetches job ID with a key. Output is filtered.
	 *
	 * @since  3.7.0
	 *
	 * @param  int $id (optional when in WP loop)
	 * @param  string $source (optional, defaults to bullhorn)
	 *
	 * @return string
	 */
	public static function the_job_external_id( $id = null, $source = 'bullhorn' ) {

		if ( is_numeric( $id ) ) {
			$id = intval( $id );
		} else {
			$id = get_the_id();
		}

		if ( empty( $source ) ) {
			$source = 'bullhorn';
		}

		if ( $source === get_post_meta( $id, '_matador_source', true ) ) {
			return (int) get_post_meta( $id, '_matador_source_id', true );
		}

		return '';
	}

	/**
	 * Matador Job Field
	 *
	 * Returns a formatted string with the job meta field.
	 *
	 * @since  3.0.0
	 * @since  3.4.0 added the $context parameter, and 'container' to attribute array.
	 *
	 *
	 * @param  string $key
	 * @param  string $id
	 * @param  string $context
	 * @param  array  $atts {
	 *     Optional array of $attributes.
	 *
	 *     @type int    $job       Alias for $id parameter.
	 *     @type string $before    String of html or plain text to include before the field.
	 *     @type string $after     String of html or plain text to include after the field.
	 *     @type string $class     String of class(es) to add to field wrapper.
	 * }
	 *
	 * @return string
	 */
	public static function the_job_field( $key = null, $id = null, $atts = array(), $context = '' ) {
		if ( ! $key ) {
			return '';
		}

		$atts['key'] = $key;

		if ( is_numeric( $id ) ) {
			$id = intval( $id );
		} elseif ( ! $id && isset( $atts['job'] ) && is_numeric( $atts['job'] ) ) {
			$id = intval( $atts['job'] );
		} else {
			$id = get_the_id();
		}

		if ( Matador::variable( 'post_type_key_job_listing' ) !== get_post_type( $id ) ) {
			return '';
		}

		$atts['id'] = $id;

		if ( empty( $atts['before'] ) || ! is_string( $atts['before'] ) ) {
			$atts['before'] = '';
		}

		if ( empty( $atts['after'] ) || ! is_string( $atts['after'] ) ) {
			$atts['after'] = '';
		}

		if ( empty( $atts['class'] ) || ! ( is_string( $atts['class'] ) || is_array( $atts['class'] ) ) ) {
			$atts['class'] = '';
		}

		if ( empty( $context ) || ! is_string( $context ) ) {
			$atts['context'] = '';
		} else {
			$atts['context'] = $context;
		}

		return self::get_template( 'the-job-field.php', $atts, 'parts' );
	}

	/**
	 * The Job Info
	 *
	 * Returns a formatted list of job meta.
	 *
	 * @since  3.4.0
	 *
	 *
	 * @uses template templates/parts/the-job-info.php for formatting.
	 *
	 * @param  array  $fields  An associative array of meta fields where the key is the field name or taxonomy name and
	 *                         the value is the desired label to go before the field. Default empty array. Optional, but
	 *                         function will return empty string if $fields array is empty.
	 * @param  string $context The template context for filtering purposes. Default is empty string. Optional.
	 * @param  array  $atts {
	 *     Optional array of $attributes.
	 *
	 *     @type int    $id        ID of job post type post to build meta list for.
	 *     @type int    $job       Alias for $id parameter.
	 *     @type string $before    String of html or plain text to include before the field.
	 *     @type string $after     String of html or plain text to include after the field.
	 *     @type string $class     String of class(es) to add to field wrapper.
	 * }
	 *
	 * @return string
	 */
	public static function the_job_info( $fields = array(), $atts = array(), $context = '' ) {

		$default_fields = array(
			'job_general_location' => _x( 'Location:', 'Label for Location in Job Info block', 'matador-jobs' ),
			'isRemote'             => _x( 'Remote:', 'Label for Remote Job in Job Info block', 'matador-jobs' ),
			'employmentType'       => _x( 'Type:', 'Label for employmentType in Job Info block', 'matador-jobs' ),
			'bullhorn_job_id'      => _x( 'Job', 'Label for Job ID in Job Info block. Often followed immediately by the number prepended by an octothorpe/number sign. e.g.: Job #12345', 'matador-jobs' ),
		);

		/**
		 * Filter: Show the Pay in the Info Bar
		 *
		 * Override the Settings Option on whether to show/hide the Salary in the Info Bar
		 *
		 * @since   3.8.4
		 *
		 * @return boolean (or string "0"/"1")
		 */
		if ( apply_filters( 'matador_template_job_info_show_pay', Matador::setting( 'jsonld_salary' ) ) ) {
			$default_fields['salary_string'] = _x( 'Salary', 'Label for salary string in job info block', 'matador-jobs' ) . ':';
		}

		/**
		 * Filter: Job Info Default Fields
		 *
		 * Modify the 'default' fields shown in the info bar. The shortcode, functions, and blocks allow the user to set
		 * custom selections for the fields in the info bar, so this is only active when the user has opted to use
		 * default options and/or did not specify options.
		 *
		 * @since   3.4.0
		 *
		 * @return array
		 */
		$default_fields = apply_filters( 'matador_template_job_info_default_fields', $default_fields );

		// Set the $atts to $default fields if none were passed
		if ( empty( $fields ) ) {
			$atts['fields'] = $default_fields;
		} else {
			$atts['fields'] = $fields;
		}

		if ( isset( $atts['id'] ) && is_numeric( $atts['id'] ) ) {
			$atts['id'] = intval( $atts['id'] );
		} elseif ( isset( $atts['job'] ) && is_numeric( $atts['job'] ) ) {
			$atts['id'] = intval( $atts['job'] );
		} else {
			$atts['id'] = get_the_id();
		}

		if ( empty( $atts['before'] ) || ! is_string( $atts['before'] ) ) {
			$atts['before'] = '';
		}

		if ( empty( $atts['after'] ) || ! is_string( $atts['after'] ) ) {
			$atts['after'] = '';
		}

		if ( empty( $atts['class'] ) || ! ( is_string( $atts['class'] ) || is_array( $atts['class'] ) ) ) {
			$atts['class'] = '';
		}

		if ( $atts['fields'] === $default_fields ) {
			$atts['class'] .= ' matador-job-meta-default';
		}

		if ( empty( $context ) || ! is_string( $context ) ) {
			$atts['context'] = '';
		} else {
			$atts['context'] = $context;
			if ( is_array( $atts['class'] ) ) {
				$atts['class'][] = "matador-job-meta-$context";
			} else {
				$atts['class'] .= " matador-job-meta-$context";
			}
		}

		return self::get_template( 'the-job-info.php', $atts, 'parts' );
	}

	/**
	 * Matador The Job Permalink
	 *
	 * Fetches the permalink of the job
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  int $id ID of job (optional when used in WP loop)
	 *
	 * @return string
	 */
	public static function the_job_permalink( $id = null ) {
		$id = is_numeric( $id ) ? $id : get_the_id();

		return get_the_permalink( $id );
	}

	/**
	 * Matador The Job Apply Link
	 *
	 * Fetches the permalink to the job's apply page
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  array $id ID of job (optional when used in WP loop)
	 *
	 * @return string
	 */
	public static function the_job_apply_link( $id = null ) {
		$id            = is_int( $id ) ? $id : get_the_id();
		$apply_setting = Matador::is_pro() ? Matador::setting( 'applications_apply_method' ) : 'custom';

		switch ( $apply_setting ) {
			case 'append':
				return self::the_job_permalink( $id ) . '#matador-application-form';
			case 'create':
				return trailingslashit( self::the_job_permalink( $id ) ) . 'apply';
			case 'custom':
			default:
				$page = Matador::setting( 'applications_apply_page' );

				if ( - 1 !== $page ) {
					$custom = get_page_link( $page );
				} else {
					$custom = home_url( '/' );
				}

				$query_args = array(
					'wpid' => $id,
					'bhid' => self::the_job_meta( '_matador_source_id' ),
				);

				return add_query_arg( $query_args, $custom );
		}
	}

	/**
	 * Matador Jobs Link
	 *
	 * Returns the URL of the Jobs Listing Page or Archive, depending on whether user has created
	 * a page with the slug or not.
	 *
	 * @since  3.3.0
	 *
	 * @return string
	 */
	public static function the_jobs_link() {

		$job_board_location_setting = Matador::setting( 'post_type_job_board_location' );

		if ( null === $job_board_location_setting || -1 === $job_board_location_setting ) {

			$url = get_post_type_archive_link( Matador::variable( 'post_type_key_job_listing' ) );

		} else {

			$url = get_permalink( $job_board_location_setting );
		}

		/**
		 * Matador The Jobs Link
		 *
		 * Modify the Jobs URL. For example, to append an anchor.
		 *
		 * @since 3.7.2
		 *
		 * @param string $url
		 * @return string
		 */
		return apply_filters( 'matador_the_jobs_link', $url );

	}

	/**
	 * Matador The Job Confirmation Link
	 *
	 * Fetches the permalink job's application confirmation (thank you) page.
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  array $id ID of job (optional when used in WP loop)
	 *
	 * @return string
	 */
	public static function the_job_confirmation_link( $id = null ) {
		$id                   = is_int( $id ) ? $id : get_the_id();
		$confirmation_setting = Matador::setting( 'applications_confirmation_method' );

		switch ( $confirmation_setting ) {
			case 'append':
				return add_query_arg( 'matador-apply', 'complete', self::the_job_permalink( $id ) );
			case 'create':
				return trailingslashit( self::the_job_permalink( $id ) ) . 'confirmation';
			case 'custom':
			default:
				$page = Matador::setting( 'applications_confirmation_page' );
				if ( - 1 !== $page ) {
					return get_page_link( apply_filters( 'wpml_object_id', $page, 'page', true ) );
				} else {
					return home_url( '/' );
				}
		}
	}

	/**
	 * The General Application URL
	 *
	 * Gets the URL for the General Application page set in settings. If none, will return an empty string.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public static function the_general_application_url() {

		$page = Matador::setting( 'applications_general_apply_page' );

		if ( ! $page || -1 === $page ) {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'The matador_{get}_the_general_application_url() functions will produce no output until you\'ve set the General Application page in settings.', 'matador-jobs' ), '3.8.0' );

			return '';
		}

		return get_permalink( $page ) ?: '';
		// return get_permalink( $page ) ?? '';
	}

	/**
	 * The General Application Link
	 *
	 * Gets an HTML-formatted link for the General Application page set in settings. If none, will return an empty string.
	 *
	 * @since 3.8.0
	 *
	 * @param array $args Array of arguments. {
	 *   @type string The label for the button. Defaults to empty string and results in 'Submit a General Application' (in template).
	 * }
	 *
	 * @return string
	 */
	public static function the_general_application_link( $args = [] ) {

		$url = self::the_general_application_url();

		if ( ! $url ) {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'Functions referencing the General Application will produce no output until you\'ve set the General Application page in settings.', 'matador-jobs' ), '3.8.0' );

			return '';
		}

		$args = wp_parse_args( $args, [
			'url'   => $url,
			'label' => '',
		] );

		return self::get_template( 'general-application-link.php', $args );
	}

	/**
	 * Matador The Job Navigation
	 *
	 * Uses conditionals to determine the appropriate contextual buttons for the job and displays them.
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param array  $id      ID of job (optional when used in WP loop)
	 * @param string $context The template context.
	 *
	 * @return string
	 */
	public static function the_job_navigation( $id = null, $context = '' ) {

		$id      = is_numeric( $id ) ? (int) $id : get_the_id();
		$context = ! empty( $context ) && is_string( $context ) ? $context : '';
		$buttons = array();
		$rule    = '';

		if ( is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {

			// We are on a 'Thank You' page generated by Matador or the Thank You is showing.
			// Show only a back to jobs button.
			if ( 'complete' === get_query_var( 'matador-apply', false ) ) {
				$rule    = 'thank-you';
				$buttons = array(
					'jobs' => __( 'More Jobs', 'matador-jobs' ),
				);
			}

			// We are on a 'Application' page generated by Matador.
			// Show a back to job and back to jobs button.
			if ( 'apply' === get_query_var( 'matador-apply', false ) ) {
				$rule    = 'application';
				$buttons = array(
					'job'  => __( 'More About this Job', 'matador-jobs' ),
					'jobs' => __( 'See All Jobs', 'matador-jobs' ),
				);
			}

			// We are on a single page where the application is not show.
			// Show an apply button and back to jobs button.
			if ( empty( $buttons ) && 'append' !== Matador::setting( 'applications_apply_method' ) ) {
				$rule    = 'single-no-application';
				$buttons = array(
					'apply' => __( 'Apply Now', 'matador-jobs' ),
					'jobs'  => __( 'See All Jobs', 'matador-jobs' ),
				);
			}

			// We are on a single page where the application is appended.
			// Show a back to jobs button.
			if ( empty( $buttons ) ) {
				$rule    = 'single-with-application';
				$buttons = array(
					'jobs' => __( 'See All Jobs', 'matador-jobs' ),
				);
			}
		} else {

			// We are on a page where the shortcode is showing, and the thank you message should be up.
			// Show only a back to jobs button.
			if ( 'complete' === get_query_var( 'matador-apply', false ) && 'shortcode-confirmation' === $context ) {
				$rule    = 'thank-you';
				$buttons = array(
					'jobs' => __( 'View Jobs', 'matador-jobs' ),
				);
			}

			// We are on a Jobs Page, Shortcode, Etc. We create a separate application page.
			// Show a link to the job and the application page.
			if ( 'append' !== Matador::setting( 'applications_apply_method' ) ) {
				$rule    = 'external-to-more-and-application';
				$buttons = array(
					'apply' => __( 'Apply Now', 'matador-jobs' ),
					'job'   => __( 'More Info', 'matador-jobs' ),
				);
			}

			// We are on a Jobs Page, Shortcode, Etc. We append a separate application page.
			// Show a link to the job and the application page.
			if ( empty( $buttons ) ) {
				$rule    = 'external-to-more-only';
				$buttons = array(
					'job' => __( 'Apply Now', 'matador-jobs' ),
				);
			}
		}

		/**
		 * Filter: The Job Navigation Button Labels
		 *
		 * @since 3.4.0
		 *
		 * @var array  $buttons Associative array with the button name as key and label as value.
		 * @var string $rule    The corresponding conditional rule that established this set of buttons.
		 * @var string $context The template context.
		 *
		 * @return array $buttons
		 */
		$buttons = apply_filters( 'matador_template_the_job_navigation_buttons', $buttons, $rule, $context );

		$args = array(
			'id'      => $id,
			'buttons' => $buttons,
			'rule'    => $rule,
			'context' => $context,
		);

		return self::get_template( 'the-job-navigation.php', $args, 'parts' );
	}

	/**
	 * Matador Get Job
	 *
	 * Retrieves an single job from the list of jobs.
	 *
	 * @since  3.0.0
	 *
	 *
	 * @param  array $args {
	 *     Optional array of arguments
	 *
	 *     @type string|int    show           What job to show. Default 'newest'. Accepts 'newest', 'random' or integer
	 *                                        of WordPress post ID of job entry to show.
	 *     @type int           excerpt_length Length of excerpt to show in number of characters. Default 160.
	 *     @type bool          job_info       Whether to show job info. Default false. Accepts boolean. Optional.
	 *     @type bool          apply_buttons  Whether to show apply button(s). Default false. Accepts boolean. Optional.
	 *     @type string|array  class          CSS class(es) to append to `<aside>` html block. Default empty string.
	 *                                        Accepts a string of class separated by a space or an array of classes.
	 *     @type boolean       loop           Whether the function looped to try and find a random result, often in the
	 *                                        case of when 'show' is set to a post and the post is not found. Internal
	 *                                        use only. Default false. Accepts true, false.
	 * }
	 *
	 * @return string        Formatted html
	 */
	public static function get_job( $args = null ) {

		$output = null;
		$args   = self::get_job_parse_args( $args );
		$job    = new WP_Query( self::get_job_parse_query( $args ) );

		if ( $job->have_posts() && ! is_wp_error( $job ) ) {

			$job->the_post();

			$output = self::get_template( 'job-aside.php', $args );

			wp_reset_postdata();

		} else {
			if ( 'random' !== $args['show'] ) {

				return self::get_job( array(
					'show' => 'random',
					'loop' => true,
				) );
			}

			$output = self::get_template( 'job-aside-empty.php', $args );
		}

		return $output;
	}

	/**
	 * Matador Get Job Parse Args
	 *
	 * Parses the passed args array for the Get Job function.
	 *
	 * @since  3.4.0
	 *
	 *
	 * @param  array $args Optional array of arguments @see Template_Support::get_job
	 *
	 * @return array       Array of parsed args
	 */
	private static function get_job_parse_args( $args ) {

		if ( ! empty( $args['show'] ) ) {
			$args['show'] = esc_attr( $args['show'] );
		}

		//
		// 'fields' parameter. Which fields' data to include
		//
		if ( ! empty( $args['fields'] ) && is_string( $args['fields'] ) ) {
			$args['fields'] = Helper::jobs_fields_string_to_array( $args['fields'] );
		} elseif ( ! empty( $args['fields'] ) && is_array( $args['fields'] ) ) {
			$sanitized = array();
			foreach ( $args['fields'] as $field => $label ) {
				$sanitized[ esc_attr( $field ) ] = esc_html( $label );
			}
			$args['fields'] = $sanitized;
		} else {
			unset( $args['fields'] );
		}

		if ( ! empty( $args['class'] ) ) {

			if ( is_array( $args['class'] ) ) {
				$args['class'] = esc_attr( $args['class'] );
			} else {
				$args['class'] = explode( ',', esc_attr( $args['class'] ) );
			}
		}

		if ( ! empty( $args['content_limit'] ) ) {
			$args['content_limit'] = is_numeric( $args['content_limit'] ) ? intval( $args['content_limit'] ) : 'excerpt';
		}

		// Merge Sanitized Options With Default Values
		return wp_parse_args( $args, array(
			'show'          => 'newest',
			'fields'        => 'title,info,content,link',
			'class'         => '',
			'content_limit' => 'excerpt',
		) );
	}

	/**
	 * Matador Get Job Query Args
	 *
	 * Parses the passed args array into a WP_Query args for the Get Job function.
	 *
	 * @since  3.4.0
	 *
	 *
	 * @param  array $args Optional array of arguments @see Template_Support::get_job
	 *
	 * @return array
	 */
	private static function get_job_parse_query( $args ) {
		return array(
			'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
			'orderby'        => ( 'random' === $args['show'] ) ? 'rand' : 'date',
			'p'              => ( is_numeric( $args['show'] ) ) ? $args['show'] : null,
			'posts_per_page' => 1,
		);
	}

	/**
	 * Matador Get Jobs
	 *
	 * Display multiple jobs in various ways based on arguments.
	 *
	 * @since  3.0.0
	 * @since  3.5.0 deprecated 'limit', 'minimum' & added 'jobs_per_page', 'backfill', and 'paginate'
	 *
	 *
	 * @param  array $args {
	 *      Optional. Array of parameters. Default is empty.
	 *
	 *      @type string         $as            The type of output desired. Accepts 'list' for an unordered list of
	 *                                          jobs, 'listing' for a collection of jobs in <article> tags, 'table'
	 *                                          for jobs presented in an html <table>, and 'select' for jobs as options
	 *                                          in a <select> form field. Filterable, so developers may add additional
	 *                                          allowed values. Defaults to 'listing'.
	 *      @type string|array   $fields        Fields to display. Accepts comma separated list of fields names with an
	 *                                          optional pipe '|' character followed by a label after the field name or
	 *                                          a $key => $label array of fields. Allowed fields are any taxonomy key,
	 *                                          and custom field, 'title', 'content', 'excerpt', and 'link' for
	 *                                          contextual navigation to the job and/or application. Defaults
	 *                                          to array( 'title' => 'Title', 'content' => 'Description', 'link' =>
	 *                                          'Apply' ).
	 *      @type string|int     $content_limit Limit the length of the 'content' field output. Accepts 'full' for the
	 *                                          full content, 'excerpt' for the default excerpt only, and any positive
	 *                                          integer to limit the content by number of words. Ignored when $as is
	 *                                          'list' or 'select'. Default is 240.
	 *      @type integer        $jobs_per_page How many jobs per page, aka 'posts_per_page'. Accepts any positive
	 *                                          integer for number of posts, and -1 for all posts (use with caution).
	 *                                          Defaults to 100 when $as is 'list', 25 when $as is 'table', or 12.
	 *      @type integer        $backfill      Minimum number of jobs to show. Useful when making special pages for a
	 *                                          a taxonomy or when used in conjunction with [matador_taxonomy] filters.
	 *                                          Ensures some jobs are shown even when no jobs match search results.
	 *                                          Accepts any positive integer. Default is null/not set. Backfill cannot
	 *                                          be more than $jobs_per_page, and if a user passes a $backfill more than
	 *                                          $jobs_per_page, $backfill will be set to $jobs_per_page.
	 *      @type bool|mixed     $paginate      Whether to show page navigation when query can produce more results.
	 *                                          When function is used to generate an archive or function is called via
	 *                                          shortcode or editor block to create a psuedo-archive, pagination should
	 *                                          be in use. When function is called for sidebar content or as post
	 *                                          content, it should not! For backward compatibility, default is true.
	 *                                          Define and pass false, null, 0, "0", "false", "no", or "off" to disable
	 *                                          pagination.
	 *      @type string         $search        Limit jobs to the passed search terms. Accepts any string. Default is
	 *                                          null/not set.
	 *      @type string|array   $$taxonomy     Limit jobs to the taxonomy term(s). This is a dynamic value, so each
	 *                                          registered taxonomy gets an argument with its name. Accepts a string of
	 *                                          ids or slugs separated by a comma or an array of ids or slugs. Default
	 *                                          is null/not set.
	 *      @type integer|array  $selected      When $as is 'select', determines which options are selected. Checks
	 *                                          $_GET array when passed no value and $as is 'select'. Accepts array,
	 *                                          integer, or numeric string. Ignored when $as is not 'select'. Default
	 *                                          null.
	 *      @type boolean        $multi         When $as is 'select', determines whether the <select> field is a multi
	 *                                          select field. Ignored otherwise. Default false.
	 *      @type string         $id            ID for output container. Accepts any string. Ignored when $as is
	 *                                          'select'. Default is null/not set.
	 *      @type string|array   $class         Class for output container. Accepts any string or array of strings.
	 *                                          Default is null/not set.
	 *      @type integer        $min           Deprecated 3.1.0. Use $backfill.
	 *      @type integer        $minimum       Deprecated 3.5.0. Use $backfill.
	 *      @type integer        $limit         Deprecated 3.5.0. Use $jobs_per_page.
	 * }
	 *
	 * @return string        Formatted html
	 */
	public static function get_jobs( $args ) {

		$args = self::get_jobs_parse_args( $args );

		$args['jobs'] = self::query_jobs( $args );

		if ( $args['jobs'] ) {
			$output = self::get_template( "jobs-{$args['as']}.php", $args );
		} else {
			$output = self::get_template( 'jobs-empty.php' );
		}

		return $output;
	}

	/**
	 * Matador Job Portal
	 *
	 * Generates a simple "portal" with some basic options for users of page builders or otherwise no-code WordPress.
	 *
	 * @since 3.8.0
	 *
	 * @param  array $args {
	 *      Optional. Array of parameters. Default is empty.
	 *
	 *      @type string $header        The header, which spans over and across the sidebar and jobs main. Accepts
	 *                                  'search' or 'search:[fields+...]', headline, supported extensions keywords, and
	 *                                  false. Default is 'search'.
	 *      @type string $jobs          The jobs section, which is inside a div side-by-side the sidebar (if present).
	 *                                  Accepts 'jobs' or 'jobs:[fields+...]', 'search' or 'search:fields[+...]',
	 *                                  headline, and supported extensions keywords. Default is
	 *                                  'jobs:title,content,link' or 'jobs:title,info,content,link' if the Setting
	 *                                  'Show Job Info Header" is true.
	 *      @type string $footer        A footer, which spans under and across the sidebar and jobs main. Accepts
	 *                                  'search' or 'search:[fields+...]', headline, supported extensions keywords, and
	 *                                  false. Default is 'false'.
	 *      @type string $sidebar       The sidebar, which is inside a div side-by-side the jobs. Accepts 'headline',
	 *                                  '$taxonomies' which default to 'method=filter', 'search' or
	 *                                  'search:[fields+...]', supported extensions keywords, and false. Default is
	 *                                  'categories,locations,types', or the default Matador Jobs taxonomies. Note:
	 *                                   sidebars created with this arg will display on the right.
	 *      @type string $sidebar_right Alias for above.
	 *      @type string $sidebar_left  Alias for 'sidebar', except sidebar will display on the left.
	 *      @type string $headline      The text to use if the argument 'headline' is provided in any of the spaces.
	 *                                  Default is 'Search Jobs' (translated). Surround text in quotes if including more
	 *                                  than one word spaces. If HTML formatting is required, you must add developer
	 *                                  filters.
	 *      @type string $class         Additional class names, separated by spaces. Default empty string.
	 * }
	 *
	 * @return string
	 */
	public static function jobs_portal( $args ) {

		/**
		 * Filter: Matador Portal Shortcode/Function Args Before Filter
		 *
		 * Modify the $args array of the Matador Portal function/shortcode before processing.
		 *
		 * @since 3.8.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_portal_args_before', $args );

		if ( ! isset( $args['class'] ) ) {
			$args['class'] = '';
		}

		foreach ( $args as $key => $arg ) {

			if ( false === $arg ) {

				continue;
			}

			if ( is_array( $arg ) ) {

				continue;
			}

			$arg = trim( $arg );

			if ( 'false' === strtolower( $arg ) ) {

				$args[ $key ] = false;
				continue;
			}

			if ( 'jobs' === $key && 'jobs' === $arg ) {
				 if ( Matador::setting( 'show_job_meta' ) ) {
					 $arg = 'jobs:title+info+excerpt+link';
				 } else {
					 $arg = 'jobs:title+excerpt+link';
				}
			}

			if ( 'headline' !== $key ) {

				if ( str_contains( $arg, ',' ) ) {
					$parts = array_map( 'trim', explode( ',', $arg ) );
				} else {
					$parts = [ $arg ];
				}

				foreach ( $parts as $index => $part ) {

					$offset = strpos( $part, ':' );

					if ( false !== $offset ) {

						$pieces = explode( ':', $part );

						$parts[ $index ] = [ $pieces[0] => implode( ',', explode( '+', substr( $part, $offset + 1 ) ) )];

					}
				}

				$args[ $key ] = $parts;
			}
		}

		if ( ! empty( $args['sidebar_left'] ) && ! empty( $args['sidebar_right'] ) ) {
			$args['sidebar_left'] = false;
		}

		if ( ! empty( $args['sidebar_left'] ) ) {
			$args['sidebar'] = $args['sidebar_left'];
			unset( $args['sidebar_left'] );
			$args['class'][] = 'sidebar-left';
		}

		if ( ! empty( $args['sidebar_right'] ) ) {
			$args['sidebar'] = $args['sidebar_right'];
			unset( $args['sidebar_right'] );
		}

		unset( $args['sidebar_left'] );
		unset( $args['sidebar_right'] );

		if ( empty( $args['headline'] ) ) {
			$args['headline'] = __( 'Search Jobs', 'matador-jobs' );
		}

		/**
		 * Filter: Matador Portal Shortcode/Function Args After Filter
		 *
		 * Modify the $args array of the Matador Portal function/shortcode after processing.
		 *
		 * @since 3.8.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_portal_args_after', $args );

		return self::get_template( 'jobs-portal.php', $args );
	}

	/**
	 * Get Jobs Parse Args
	 *
	 * @since 3.0.0
	 * @since 3.5.0 deprecated 'limit', 'minimum' & added 'jobs_per_page', 'backfill', and 'paginate'
	 *
	 *
	 * @param  array $args Optional array of arguments. @see Template_Support::get_jobs
	 *
	 * @return array
	 */
	private static function get_jobs_parse_args( $args = array() ) {

		/**
		 * Filter: Matador Jobs Shortcode/Function Args
		 *
		 * Modify the $args array of the Matador Jobs function/shortcode before processing.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_jobs_listing_args', $args );

		//
		// 'as' parameter. Allows user to pass a limit for number of jobs to return
		//

		/**
		 * Filter: Matador Jobs Shortcode/Function Args "as" Allowed Values
		 *
		 * Allows us to amend the list of allowed values for "as" arg.
		 *
		 * @since 3.3.0
		 *
		 * @var array $allowed
		 */
		$allowed_as = apply_filters( 'matador_jobs_listing_arg_as', array( 'list', 'listing', 'table', 'select' ) );

		if ( ! empty( $args['as'] ) && in_array( esc_attr( strtolower( $args['as'] ) ), $allowed_as, true ) ) {
			$args['as'] = esc_attr( strtolower( $args['as'] ) );
		} else {
			$args['as'] = false;
		}

		//
		// 'fields' parameter. Which fields' data to include
		//
		if ( ! empty( $args['fields'] ) && is_string( $args['fields'] ) ) {
			$args['fields'] = Helper::jobs_fields_string_to_array( $args['fields'] );
		} elseif ( ! empty( $args['fields'] ) && is_array( $args['fields'] ) ) {
			$sanitized = array();
			foreach ( $args['fields'] as $field => $label ) {
				$sanitized[ esc_attr( $field ) ] = esc_html( $label );
			}
			$args['fields'] = $sanitized;
		} else {
			unset( $args['fields'] );
		}
		if ( ! empty( $args['fields'] ) && is_array( $args['fields'] ) && 'table' === $args['as'] ) {
			unset( $args['fields']['info'] );
		}

		//
		// Back-compat: 'fields' has 'more' or 'apply'.
		//
		if ( isset( $args['fields'] ) && in_array( 'more', array_keys( $args['fields'] ), true ) ) {
			$args['fields']['link'] = $args['fields']['more'];
			unset( $args['fields']['more'] );
		}
		if ( isset( $args['fields'] ) && in_array( 'apply', array_keys( $args['fields'] ), true ) ) {
			$args['fields']['link'] = $args['fields']['apply'];
			unset( $args['fields']['apply'] );
		}

		//
		// 'fields' has 'excerpt' while 'content_limit' is empty.
		//
		if (
			isset( $args['fields'] )
			&& in_array( 'excerpt', array_keys( $args['fields'] ), true )
			&& empty( $args['content_limit'] )
		) {
			$args['fields']['content'] = $args['fields']['excerpt'];
			$args['content_limit']     = 'excerpt';
			unset( $args['fields']['excerpt'] );
		}

		//
		// 'content_limit' parameter, or length of content allowed.
		//
		if ( ! empty( $args['content_limit'] ) ) {
			if ( 'select' === $args['as'] || 'list' === $args['as'] ) {
				unset( $args['content_limit'] );
			} elseif ( is_numeric( $args['content_limit'] ) ) {
				$args['content_limit'] = intval( $args['content_limit'] );
			} elseif ( in_array( esc_attr( strtolower( $args['content_limit'] ) ), array( 'excerpt', 'full' ), true ) ) {
				$args['content_limit'] = esc_attr( strtolower( $args['content_limit'] ) );
			} else {
				unset( $args['content_limit'] );
			}
		}

		//
		// backwards compatibility: 'limit' parameter
		//
		if ( ! empty( $args['limit'] ) && empty( $args['jobs_per_page'] ) ) {
			$args['jobs_per_page'] = intval( $args['limit'] );
		}

		//
		// Normalize shortcode param 'jobs_to_show'
		//
		if ( ! empty( $args['jobs_to_show'] ) && empty( $args['jobs_per_page'] ) ) {
			$args['jobs_per_page'] = intval( $args['jobs_to_show'] );
		}
		unset( $args['jobs_to_show'] );

		//
		// 'jobs_per_page' parameter. Allows user to pass a limit for number of jobs to return
		//
		if ( ! empty( $args['jobs_per_page'] ) ) {
			$args['jobs_per_page'] = intval( $args['jobs_per_page'] );
		} elseif ( ! empty( $args['as'] ) ) {
			switch ( $args['as'] ) {
				case 'select':
					break;
				case 'list':
					$args['jobs_per_page'] = (int) 100;
					break;
				case 'table':
					$args['jobs_per_page'] = (int) 25;
					break;
				default:
					$args['jobs_per_page'] = (int) 12;
			}
		} else {
			$args['jobs_per_page'] = (int) 12;
		}

		//
		// backwards compatibility: 'min' parameter (deprecated in favor of backfill)
		//
		if ( empty( $args['backfill'] ) && ! empty( $args['min'] ) && is_numeric( $args['min'] ) ) {
			$args['backfill'] = absint( $args['min'] );
		} elseif ( empty( $args['backfill'] ) && ! empty( $args['min'] ) ) {
			$args['backfill'] = $args['jobs_per_page'];
		}
		unset( $args['min'] );

		//
		// backwards compatibility: 'minimum' parameter (deprecated in favor of backfill)
		//
		if ( empty( $args['backfill'] ) && ! empty( $args['minimum'] ) && is_numeric( $args['minimum'] ) ) {
			$args['backfill'] = absint( $args['minimum'] );
		} elseif ( empty( $args['backfill'] ) && ! empty( $args['minimum'] ) ) {
			$args['backfill'] = $args['jobs_per_page'];
		}
		unset( $args['minimum'] );

		//
		// 'backfill' parameter, or minimum number of jobs to show (in searched/filtered lists)
		//
		if ( ! empty( $args['backfill'] ) && is_numeric( $args['backfill'] ) ) {
			$args['backfill'] = absint( $args['backfill'] );
		} elseif ( ! empty( $args['backfill'] ) ) {
			$args['backfill'] = $args['jobs_per_page'];
		} else {
			$args['backfill'] = null;
		}

		//
		// 'backfill' cannot be greater than 'jobs_per_page'
		//
		if ( ! empty( $args['jobs_per_page'] ) && ! empty( $args['backfill'] && $args['backfill'] > $args['jobs_per_page'] ) ) {
			$args['backfill'] = $args['jobs_per_page'];
		}

		//
		// 'paginate' parameter. Allows user to turn on/off pagination
		//
		if ( isset( $args['paginate'] ) ) {

			if ( empty( $args['paginate'] ) ) {
				$args['paginate'] = false;
			} elseif (
				is_string( $args['paginate'] ) &&
				in_array( strtolower( $args['paginate'] ), array( 'false', 'no', 'off' ), true )
			) {
				$args['paginate'] = false;
			} else {
				$args['paginate'] = true;
			}
		} else {
			$args['paginate'] = true;
		}

		//
		// '$taxonomy' parameter. Allows user to pass slugs and IDs.
		//
		foreach ( Matador::variable( 'job_taxonomies' ) as $taxonomy => $tax_args ) {
			if ( ! empty( $args[ $taxonomy ] ) ) {
				$args[ $tax_args['key'] ] = esc_attr( strtolower( $args[ $taxonomy ] ) );
			}
			unset( $args[ $taxonomy ] );
		}

		//
		// 'search' parameter. Allows user to pass search terms
		//
		if ( ! empty( $args['search'] ) ) {
			$args['search'] = esc_attr( $args['search'] );
		}

		//
		// 'selected' parameter
		//
		if ( 'select' === $args['as'] ) {
			if ( ! empty( $args['selected'] ) ) {
				if ( is_array( $args['selected'] ) ) {
					// @todo, should verify that input is a valid, active job
					$sanitized = array();
					foreach ( $args['selected'] as $selected ) {
						$sanitized[] = intval( $selected );
					}
				} elseif ( is_numeric( $args['selected'] ) ) {
					// @todo, should verify that input is a valid, active job
					$args['selected'] = intval( $args['selected'] );
				} else {
					unset( $args['selected'] );
				}
			} elseif ( ! empty( $_GET['jobs'] ) ) { // WPCS: CSRF ok.
				$submitted = $_GET['jobs']; // WPCS: CSRF ok.
				if ( is_array( $submitted ) ) {
					// @todo, should verify that input is a valid, active job
					$sanitized = array();
					foreach ( $submitted as $selected ) {
						$sanitized[] = intval( $selected );
					}
				} elseif ( is_numeric( $submitted ) ) {
					// @todo, should verify that input is a valid, active job
					$args['selected'] = intval( $submitted );
				}
			} else {
				$args['selected'] = null;
			}
		} else {
			unset( $args['selected'] );
		}

		//
		// 'multi' parameter
		//
		if ( 'select' === $args['as'] ) {
			if ( ! empty( $args['multi'] ) ) {
				$args['multi'] = 'false' !== $args['multi'] ? (bool) $args['multi'] : false;
			} else {
				$args['multi'] = false;
			}
		} else {
			unset( $args['selected'] );
		}

		//
		// 'id' parameter, or id value of jobs wrapper
		//
		if ( ! empty( $args['id'] ) ) {
			$args['id'] = esc_attr( $args['id'] );
		}
		if ( 'select' === $args['as'] ) {
			$args['id'] = 'jobs';
		}

		//
		// 'class' parameter, or class value of jobs wrapper
		//
		if ( ! empty( $args['class'] ) ) {
			$args['class'] = esc_attr( $args['class'] );
		}

		//
		// 'index' parameter, should not be set by user input. Defined here for use by template only
		//
		$args['index'] = 1;

		//
		// Define Defaults to Compare Submitted Against
		//
		$defaults = array(
			'as'            => 'listing', //list, table, listing
			'jobs_per_page' => (int) 12, //number of posts
			'search'        => null,
			'fields'        => array(
				'title' => __( 'Title', 'matador-jobs' ),
				'link'  => __( 'Apply', 'matador-jobs' ),
			),
			'content_limit' => (int) 240, // content limit (in words), or 'full', 'excerpt';
			'selected'      => null,
			'backfill'      => null,
			'id'            => null,
			'class'         => null,
			'index'         => 1, // don't set this. Used in the template.
		);
		if ( 'listing' === $args['as'] ) {
			$defaults['fields']['content'] = __( 'Description', 'matador-jobs' );
			$defaults['fields']['info']    = __( 'Job Info', 'matador-jobs' );
		}
		foreach ( Matador::variable( 'job_taxonomies' ) as $taxonomy => $tax_args ) {
			$defaults[ $tax_args['key'] ] = null;
		}

		// All the way at the top, we set 'as' to false if we got a value that was not in a list of approved
		// values. So that it can be updated to the default, we now unset it. We didn't initially unset it
		// because it is used to sanitize/clean the query in several places.
		if ( ! $args['as'] ) {
			unset( $args['as'] );
		}

		/**
		 * Filter: Matador Jobs Shortcode/Function Default Args
		 *
		 * Modify the array of default/expected args the Matador Jobs function/shortcode.
		 *
		 * @since 3.0.0
		 *
		 * @var array $args
		 */
		$defaults = apply_filters( 'matador_jobs_listing_default_args', $defaults );

		// Merge Sanitized Options With Default Values
		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter: Matador Jobs Shortcode/Function Args
		 *
		 * Modify the $args array of the Matador Jobs function/shortcode before processing.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_jobs_listing_args_after', $args );

		return $args;
	}

	/**
	 * Query Jobs
	 *
	 * @since 3.0.0
	 *
	 *
	 * @param  array $args Optional array of arguments. @see Template_Support::get_jobs
	 *
	 * @return null|WP_Query
	 */
	private static function query_jobs( $args ) {

		$found = array();

		$jobs = new WP_Query( self::query_jobs_args( $args ) );

		// If an Error or if no jobs were found, return no jobs if we don't require a minimum
		if ( is_wp_error( $jobs ) || ( ! $jobs->have_posts() && empty( $args['backfill'] ) ) ) {
			return null;
		}

		// If no/too few jobs were found and we need to find more to meet our minimum
		if ( ! empty( $args['backfill'] ) && $jobs->post_count < $args['backfill'] ) {

			foreach ( $jobs->get_posts() as $post ) {
				$found[] = $post->ID;
			}

			$more = new WP_Query( self::query_jobs_args( array(
				'jobs_per_page' => $args['backfill'] - $jobs->post_count,
				'post__not_in'  => $found,
			) ) );

			if ( ! $more->have_posts() || is_wp_error( $more ) ) {
				unset( $more );
				unset( $found );
			} else {
				foreach ( $more->get_posts() as $post ) {
					$found[] = $post->ID;
				}
			}
		}

		if ( ! empty( $found ) ) {
			$jobs = new WP_Query( self::query_jobs_args( array(
				'post__in' => $found,
				'order'    => 'post__in',
			) ) );
		}

		return $jobs;
	}

	/**
	 * Matador Get Jobs Query Args
	 *
	 * Takes an array of arguments, and combined with instance-specific data,
	 * builds arguments for a WP Query for Jobs.
	 *
	 * @since  3.0.0
	 * @since  3.7.0 added support for matador_xid searches
	 *
	 *
	 * @param  array $args Optional array of arguments. @see Template_Support::get_jobs
	 *
	 * @return array         WP_Query formatted arguments array.
	 */
	private static function query_jobs_args( $args = null ) {

		// Query Builder Requires Input
		if ( ! $args ) {
			return array();
		}

		$query = array();

		// Query Builder is Always for the Job Listing Post Type:
		$query['post_type'] = sanitize_key( Matador::variable( 'post_type_key_job_listing' ) );

		// Get/Sanitize the Limit Variable:
		if ( isset( $args['jobs_per_page'] ) ) {
			$query['posts_per_page'] = intval( $args['jobs_per_page'] );
		}

		// Exclude post(s):
		if ( isset( $args['post__not_in'] ) || is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {
			if ( isset( $args['post__not_in'] ) && is_array( $args['post__not_in'] ) ) {
				$query['post__not_in'] = $args['post__not_in'];
			} elseif ( isset( $args['post__not_in'] ) ) {
				$query['post__not_in'] = array( $args['post__not_in'] );
			}
			if ( is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {
				if ( isset( $query['post__not_in'] ) ) {
					$query['post__not_in'] = array_merge( $query['post__not_in'], array( get_the_ID() ) );
				} else {
					$query['post__not_in'] = array( get_the_ID() );
				}
			}
		}

		// Include post(s):
		if ( ! empty( $args['post__in'] ) && is_array( $args['post__in'] ) && ! isset( $args['post__not_in'] ) ) {
			$query['post__in'] = $args['post__in'];
		}

		// Include taxonomies:
		foreach ( Matador::variable( 'job_taxonomies' ) as $key => $taxonomy ) {

			if (
				isset( $args[ $taxonomy['key'] ] )
				|| ( isset( $_GET[ $taxonomy['key'] ] ) && ! empty( $_GET[ $taxonomy['key'] ] ) ) // WPCS: CSRF ok.
			) {

				$terms = array();

				if ( isset( $args[ $taxonomy['key'] ] ) ) {
					foreach ( explode( ',', $args[ $taxonomy['key'] ] ) as $term ) {  // WPCS: CSRF ok.
						if ( ! array_key_exists( $term, $terms ) ) {
							$terms[] = sanitize_title( $term );
						}
					}
				}

				if (
					isset( $_GET[ $taxonomy['key'] ] ) && // WPCS: CSRF ok.
					! empty( $_GET[ $taxonomy['key'] ] ) // WPCS: CSRF ok.
				) {
					foreach ( explode( ',', $_GET[ $taxonomy['key'] ] ) as $term ) {  // WPCS: CSRF ok.
						if ( ! array_key_exists( $term, $terms ) ) {
							$terms[] = sanitize_title( $term );
						}
					}
				}

				$tax_query = array(
					'taxonomy' => sanitize_key( $taxonomy['key'] ),
					'field'    => 'slug',
					'terms'    => $terms,
				);
			}
			if ( ! empty( $tax_query ) ) {
				$query['tax_query'][] = $tax_query;
				unset( $tax_query );
			}
		}
		if ( ! empty( $query['tax_query'] ) && count( $query['tax_query'] ) > 1 ) {
			$query['tax_query']['relation'] = apply_filters( 'matador_jobs_listing_query_taxonomy_relation', 'AND' );
		}

		// Include pagination
		$paged = 1;

		if ( is_front_page() ) {
			$path = explode( '?', $_SERVER['REQUEST_URI'] );

			if ( false !== strpos( $path[0], '/page/' ) ) {
				$p     = explode( '/', trim( $path[0], '/' ) );
				$paged = is_numeric( end( $p ) ) ? end( $p ) : $paged;
			}
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : $paged;
		}

		if ( 1 < $paged ) {
			$query['paged'] = $paged;
		}

		// Include search terms
		if ( isset( $_GET['matador_s'] ) && ! empty( $_GET['matador_s'] ) ) {

			if ( is_numeric( $_REQUEST['matador_s'] ) && (int) $_REQUEST['matador_s'] > 0 ) {
				$meta_query = [
					[
						'key'     => '_matador_source_id',
						'value'   => (int) $_REQUEST['matador_s'],
						'type'    => 'NUMERIC',
						'compare' => '=',
					],
				];

				$query['meta_query'] = $meta_query;

			} else {

				$query['s'] = sanitize_text_field( strtolower( $_GET['matador_s'] ) ); // WPCS: CSRF ok.

			}

		} elseif ( ! empty( $args['search'] ) ) {

			$query['s'] = sanitize_text_field( strtolower( $args['search'] ) );

		}

		// Include XID search
		if ( ! empty( $_GET['matador_xid'] ) && is_numeric( $_GET['matador_xid'] ) ) {
			$query['meta_query'] = array( array(
				'key' => '_matador_source_id',
				'compare' => '=',
				'type' => 'NUMERIC',
				'value' => (int) $_GET['matador_xid'],
			) );
		}

		/**
		 * Filter Query Job Args
		 *
		 * @wordpress-filter `matador_jobs_query_jobs_args`
		 *
		 * Modify the $query array of the Matador Jobs function/query before processing.
		 *
		 * @since 3.8.18
		 *
		 * @var array $query A formatted WordPress Query array.
		 * @var array $args  The input array of args.
		 *
		 * @return array Formatted to WordPress Query array
		 */
		return apply_filters( 'matador_jobs_query_jobs_args', $query, $args );
	}

	/**
	 * The Job Terms
	 *
	 * Returns an array of all job terms. Pass the $taxonomy argument as either a string with a single taxonomy name or
	 * an array of taxonomy names to limit your results.
	 *
	 * @since 3.2.0
	 *
	 *
	 * @param mixed $taxonomy (optional)
	 * @param int $id (optional)
	 *
	 * @return array|bool
	 */
	public static function the_job_terms( $taxonomy = null, $id = null ) {

		// Validate an ID is a Number or Get the ID
		if ( ! empty( $id ) ) {
			if ( is_numeric( $id ) ) {
				$id = intval( $id );
			} else {
				$id = get_the_id();
			}
		} else {
			$id = get_the_id();
		}

		// Validate ID is a Matador Job Listing
		if ( Matador::variable( 'post_type_key_job_listing' ) !== get_post( $id )->post_type ) {
			return false;
		}

		// Get the Registered Taxonomies
		$taxonomies = (array) Matador::variable( 'job_taxonomies' );

		// If no taxonomy was passed, use all, otherwise check and convert
		// if needed shorthand taxonomy names to longhand taxonomy names (with matador- prefix).
		if ( empty( $taxonomy ) ) {
			$taxonomy = array_keys( $taxonomies );
		} elseif ( is_array( $taxonomy ) ) {
			foreach ( $taxonomy as $index => $tax ) {
				if ( ! array_key_exists( $taxonomy[ $index ], $taxonomies ) ) {
					unset( $taxonomy[ $index ] );
				}
			}
		} elseif ( is_string( $taxonomy ) ) {
			if ( ! array_key_exists( $taxonomy, $taxonomies ) ) {
				unset( $taxonomy );
			}
		}

		if ( empty( $taxonomy ) ) {
			return false;
		}

		$terms = array();

		if ( is_array( $taxonomy ) ) {
			foreach ( $taxonomy as $tax ) {
				$terms[ $tax ] = wp_get_post_terms( $id, $taxonomies[ $tax ]['key'] );
			}
		} else {
			$terms = wp_get_post_terms( $id, $taxonomies[ $taxonomy ]['key'] );
		}

		if ( empty( $terms ) ) {
			return false;
		}

		return $terms;
	}

	/**
	 * The Job Terms List
	 *
	 * Returns an formatted string of job terms for the given taxonomy.
	 *
	 * @since 3.2.0
	 *
	 *
	 * @param string $taxonomy
	 * @param int $id
	 * @param array $args
	 *
	 * @return string
	 */
	public static function the_job_terms_list( $taxonomy = '', $id = null, $args = array() ) {

		if ( ! is_string( $taxonomy ) || empty( $taxonomy ) ) {
			return '';
		}

		$terms = self::the_job_terms( $taxonomy, $id );

		$args = wp_parse_args( $args, array(
			'separator'   => ',',
			'before'      => '',
			'before_each' => '',
			'after_each'  => '',
			'after'       => '',
			'class'       => '',
			'link'        => true,
			'link_method' => 'link',
			'link_multi'  => false,
		) );

		if ( $terms ) {
			$args['terms']    = $terms;
			$args['taxonomy'] = $taxonomy;
			return self::get_template( 'job-terms-list.php', $args );
		}

		return '';
	}

	/**
	 * Matador Taxonomy Terms
	 *
	 * Retrieves and display taxonomy terms in various formats
	 *
	 * @since  3.0.0
	 * @since  3.3.0 new parameters 'orderby' and 'hide_empty' added.
	 * @since  3.7.0 new parameter 'label'
	 *
	 * @param  array $args {
	 *      Optional. Array of parameters. Default is empty.
	 *
	 *      @type string      $taxonomy         Taxonomy name to which results should be limited. Default is 'category'.
	 *      @type string      $tax              Taxonomy name to which results should be limited. Alias of $taxonomy.
	 *                                          When $taxonomy is not present, will use $tax. When $taxonomy is present,
	 *                                          $tax is ignored. Default is null.
	 *      @type string      $as               How the list should be presented. Accepts 'list' for an unordered list
	 *                                          of terms, 'select', 'multiselect', and 'dropdown' for a <select> field.
	 *                                          Default is 'list'.
	 *      @type bool        $multi            Whether the <select> field is a multi select. If $as is set to
	 *                                          'multiselect', $multi is set to true. $multi can also be set directly.
	 *                                          Accepts true or false. Default is 'false'. Ignored when $as is not
	 *                                          'select'.
	 *      @type string      $method           Method on how terms should function in the UI. Accepts 'link' which are
	 *                                          traditional links to WordPress taxonomy archive pages, 'filter' which
	 *                                          are links to the current page with additional query args that are read
	 *                                          by a Matador pre_get_posts() action to limit results on the active page,
	 *                                          'search' which are links to the jobs page with the taxonomy as a search
	 *                                          term (if used on search page, would reset filters), 'value' which are
	 *                                          not links. $method = 'value' is ignored when $as is 'list', and in that
	 *                                          case 'link' is used. Default is 'link'.
	 *      @type string|bool $show_all_option  Should an "All" term be included in the list or select. Accepts boolean
	 *                                          true and false, 'before' which adds the "All" term before the other
	 *                                          terms, 'before_if' which adds the "All" term before the other terms only
	 *                                          when a term(s) is(are) selected or the visitor is on the term archive,
	 *                                          'after' which adds the "All" term after the other terms, and 'after_if'
	 *                                          which adds the "All" term after the other terms only when a term(s)
	 *                                          is(are) selected or the visitor is on the term archive, 'both' and
	 *                                          'both_if' perform a combination of 'before' and 'after' or 'before_if'
	 *                                          and 'after_if', respectively. When $as is not 'list', only true or false
	 *                                          is recognized, and a string value will be converted to true. Default is
	 *                                          true when $as is 'select', 'multiselect', or 'dropdown' and false when
	 *                                          $as is 'list'.
	 *      @type string      $orderby          How to order the terms. Accepts 'name' to sort alphabetically by term
	 *                                          name, 'slug' to sort alphabetically by term slug, 'description' to sort
	 *                                          description, and 'count' to sort by number of jobs in term. Default is
	 *                                          'name'.
	 *      @type bool        $hide_empty       Whether to exclude terms with no jobs. Boolean, so accepts true, which
	 *                                          excludes empty terms, and false, which includes empty terms. Default is
	 *                                          true.
	 *      @type string      $label            A label to include prior to the taxonomy terms dropdown or headline
	 *                                          before a list.
	 *      @type string      $class            Additional class for output. When $as is 'list', the class is added to
	 *                                          the <ul> tag, when $as is 'select', 'multiselect', or 'dropdown', the
	 *                                          class is added to the <select> tag. Default null.
	 * }
	 *
	 * @return string|bool
	 */
	public static function taxonomy( $args = array() ) {

		/**
		 * Filter: Matador Taxonomy Terms Args
		 *
		 * Modify the $args array of the Matador Taxonomy Terms function/shortcode before processing.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_taxonomy_terms_args', $args );

		// Function can accept 'taxonomy' or 'tax' as the key,
		// but we end up using 'taxonomy'.
		if ( empty( $args['taxonomy'] ) ) {
			if ( ! empty( $args['tax'] ) ) {
				$args['taxonomy'] = esc_attr( $args['tax'] );
			} else {
				$args['taxonomy'] = 'category';
			}
		}

		// Get array of registered Taxonomies
		$taxonomies = (array) Matador::variable( 'job_taxonomies' );

		// Validate the taxonomy exists ( even the default could technically be unset by developers )
		if (
			! empty( $args['taxonomy'] ) &&
			in_array( strtolower( $args['taxonomy'] ), array_keys( $taxonomies ), true )
		) {
			$name                     = $args['taxonomy'];
			$args['taxonomy']         = $taxonomies[ $name ];
			$args['taxonomy']['name'] = $name;
			$args['taxonomy']['slug'] = Matador::setting( "taxonomy_slug_{$name}" );
			unset( $args['taxonomy']['args'] );
			unset( $taxonomies );
		} else {
			return false;
		}

		// Set if we are in multiselect mode
		if ( ! empty( $args['multi'] ) ) {
			if ( 'false' === $args['multi'] ) {
				$args['multi'] = false;
			} else {
				$args['multi'] = true;
			}
		} elseif ( ! empty( $args['as'] ) && 'multiselect' === strtolower( $args['as'] ) ) {
			$args['multi'] = true;
		} else {
			$args['multi'] = false;
		}

		/**
		 * Filter: Taxonomy Terms Arg "As" Allowed Values
		 *
		 * Allows us to amend the list of allowed values for "as" arg.
		 *
		 * @since 3.3.0
		 *
		 * @var array $allowed
		 */
		$allowed_as = apply_filters( 'matador_taxonomy_terms_arg_as', array( 'dropdown', 'select', 'multiselect' ) );
		if ( ! empty( $args['as'] ) ) {
			if ( in_array( strtolower( $args['as'] ), $allowed_as, true ) ) {
				$args['as'] = 'select';
			} else {
				$args['as'] = 'list';
			}
		} else {
			$args['as'] = 'list';
		}

		/**
		 * Filter: Taxonomy Terms Arg "Method" Allowed Values
		 *
		 * Allows us to amend the list of allowed values for "Method" arg.
		 *
		 * @since 3.3.0
		 *
		 * @var array $allowed
		 */
		$allowed_methods = apply_filters( 'matador_taxonomy_terms_arg_method', array( 'link', 'filter', 'value', 'search' ) );
		if ( ! empty( $args['method'] ) && in_array( strtolower( $args['method'] ), $allowed_methods, true ) ) {
			$args['method'] = esc_attr( strtolower( $args['method'] ) );
			// Method cannot be 'value' when As is 'list', override to 'link'
			if ( 'list' === $args['as'] && 'value' === $args['method'] ) {
				$args['method'] = 'link';
			}
		} else {
			// Default is 'link'
			$args['method'] = 'link';
		}

		if ( 'select' === $args['as'] && in_array( $args['method'], array( 'link', 'filter' ), true ) ) {
			wp_enqueue_script( 'matador_javascript' );
		}

		// "Show All" Option Handling
		// Convert all forms of true/false to (bool) true/false
		if ( isset( $args['show_all_option'] ) ) {
			if ( in_array( strtolower( $args['show_all_option'] ), array( 'true', true, '1', 1 ), true ) ) {
				$args['show_all_option'] = true;
			} elseif ( in_array( strtolower( $args['show_all_option'] ), array( 'false', false, '0', 0 ), true ) ) {
				$args['show_all_option'] = false;
			}
		}

		// "Show All" Option Handling
		// If is not set, default is true when 'select', false when 'list'.
		if ( ! isset( $args['show_all_option'] ) ) {
			if ( 'select' === $args['as'] ) {
				$args['show_all_option'] = true;
			} else {
				$args['show_all_option'] = false;
			}
		}

		// "Show All" Option Handling
		// When a "list" type, accepts strings as argument
		if ( $args['show_all_option'] && 'list' === $args['as'] ) {

			/**
			 * Filter: Taxonomy Terms Arg "Show All Option" Allowed Values
			 *
			 * Allows us to amend the list of allowed values for "Method" arg.
			 *
			 * @since 3.3.0
			 *
			 * @var array $allowed
			 */
			$allowed_show_all_option = apply_filters(
				'matador_taxonomy_terms_arg_show_all_option',
				array( 'after', 'after_if', 'before', 'before_if', 'both', 'both_if', true, false )
			);

			// If argument is (bool) true, assign it the default string value
			if ( is_bool( $args['show_all_option'] ) ) {
				$args['show_all_option'] = 'before';
			}

			// If not a string type now, set to false. Else make sure its lowercase
			if ( ! is_string( $args['show_all_option'] ) ) {
				$args['show_all_option'] = false;
			} else {
				$args['show_all_option'] = strtolower( $args['show_all_option'] );
			}

			// Validate string types arguments are allowed
			if ( ! in_array( $args['show_all_option'], $allowed_show_all_option, true ) ) {
				$args['show_all_option'] = false;
			}

			// Set include to false if an "if" argument is passed and the condition is not valid
			if ( false !== strpos( $args['show_all_option'], '_if' ) ) {
				if (
					'filter' === $args['method'] &&
					Helper::get_nopaging_url() === remove_query_arg( $args['taxonomy']['key'], Helper::get_nopaging_url() )
				) {
					$args['show_all_option'] = false;
				} elseif (
					'link' === $args['method'] &&
					is_post_type_archive( Matador::variable( 'post_type_key_job_listing' ) )
				) {
					$args['show_all_option'] = false;
				}
			}
		}

		// Hide Empty
		if (
			isset( $args['hide_empty'] )
			&& in_array( strtolower( $args['hide_empty'] ), array( 'false', false, '0', 0 ), true )
		) {
			$args['hide_empty'] = false;
		} else {
			$args['hide_empty'] = true;
		}

		// Orderby
		if (
			! empty( $args['orderby'] )
			&& in_array( strtolower( $args['orderby'] ), array( 'name', 'slug', 'description', 'count' ), true )
		) {
			$args['orderby'] = strtolower( $args['orderby'] );
		} else {
			$args['orderby'] = 'name';
		}

		// Label
		if ( ! empty( $args['label'] ) ) {
			$args['label'] = esc_html( $args['label'] );
		} else {
			$args['label'] = '';
		}

		// Class
		if ( ! empty( $args['class'] ) ) {
			$args['class'] = esc_attr( $args['class'] );
		}

		// Clean up, delete 'tax', if present
		unset( $args['tax'] );

		// Merge Sanitized Options With Default Values
		$args = wp_parse_args( $args, array(
			'as'     => 'list',
			'multi'  => false,
			'method' => 'link',
			'class'  => null,
		) );

		/**
		 * Filter: Matador Taxonomy Terms Args After
		 *
		 * Modify the $args array of the Matador Taxonomy Terms function/shortcode after processing.
		 * Warning: use with caution.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_taxonomy_terms_args_after', $args );

		$terms = get_terms( array(
			'taxonomy'   => $args['taxonomy']['key'],
			'hide_empty' => $args['hide_empty'],
			'orderby'    => $args['orderby'] ?: 'name',
			'order'      => 'count' === $args['orderby'] ? 'DESC' : 'ASC',
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$args['terms'] = $terms;
			return self::get_template( 'jobs-taxonomies-' . $args['as'] . '.php', $args );
		}

		return false;
	}

	/**
	 * Matador Get Term Link
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Term $term
	 * @param string  $tax
	 * @param string  $method
	 * @param bool    $multi
	 *
	 * @return string|WP_Error
	 */
	public static function get_term_link( $term, $tax, $method = 'link', $multi = false ) {
		if ( ! $term || ! $tax ) {
			return '';
		}

		switch ( $method ) {
			case 'filter':
				if ( self::is_filter_term_selected( $term, $tax ) ) {
					if ( $multi ) {
						$slugs = array();
						if ( isset( $_REQUEST[ $tax ] ) ) {
							$slugs = explode( ',', $_REQUEST[ $tax ] ); // WPCS: CSRF ok.
							$slugs = array_diff( $slugs, array( $term->slug ) );
						}

						if ( empty( $slugs ) ) {
							return remove_query_arg( $tax, Helper::get_nopaging_url() );
						} else {
							return add_query_arg( array( $tax => implode( ',', $slugs ) ), Helper::get_nopaging_url() );
						}
					} else {
						return remove_query_arg( $tax, Helper::get_nopaging_url() );
					}
				} else {
					if ( $multi ) {
						$current = isset( $_REQUEST[ $tax ] ) ? sanitize_text_field( $_REQUEST[ $tax ] ) : ''; // WPCS: CSRF ok.
						$slugs   = empty( $current ) ? array() : explode( ',', $current );
						$slugs[] = $term->slug;

						return add_query_arg( array( $tax => implode( ',', $slugs ) ), Helper::get_nopaging_url() );
					} else {
						return add_query_arg( array( $tax => $term->slug ), Helper::get_nopaging_url() );
					}
				}

			case 'search':
				return add_query_arg( array( $tax => $term->slug ), self::the_jobs_link() );

			case 'link':
				return get_term_link( $term );

			case 'value':
			default:
				return $term->slug;
		}
	}

	/**
	 * Matador is Filter Term Selected
	 *
	 * Checks if the filter term is currently active.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_Term $term
	 * @param string $tax
	 *
	 * @return bool
	 */
	public static function is_filter_term_selected( $term, $tax ) {
		return (
			(
				! empty( $_REQUEST[ $tax ] ) // WPCS: CSRF ok.
				&& in_array( $term->slug, explode( ',', $_REQUEST[ $tax ] ), true ) // WPCS: CSRF ok.
			)
		) || is_tax( $tax, $term->term_id );
	}

	/**
	 * Matador Search Form
	 *
	 * Builds a Job Search form.
	 *
	 * @since  3.0.0
	 * @since  3.7.0 added support for ID search fields
	 *
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function search( $args ) {

		/**
		 * Filter: Search Form Args
		 *
		 * Modify the $args array of the Matador Search function/shortcode before processing.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_search_form_args', $args );

		/**
		 * Filter: Search Form Arg "Fields" Allowed Values
		 *
		 * Allows us to amend the list of allowed values for "Method" arg.
		 *
		 * @since 3.3.0
		 *
		 * @var array $allowed
		 */
		$allowed_fields = apply_filters(
			'matador_search_form_arg_fields',
			array_merge( Job_Taxonomies::registered_taxonomies(), array( 'keyword', 'id', 'reset' ) )
		);

		if ( ! empty( $args['fields'] ) ) {
			if ( is_string( $args['fields'] ) ) {
				$args['fields'] = Helper::comma_separated_string_to_escaped_array( $args['fields'] );
			} else {
				$args['fields'] = Helper::array_values_escaped( $args['fields'] );
			}
			foreach ( $args['fields'] as $key => $field ) {
				if ( 'text' === $field ) {
					$args['fields'][ $key ] = 'keyword';
					continue;
				}
				if ( ! in_array( $field, $allowed_fields, true ) ) {
					unset( $args['fields'][ $key ] );
					continue;
				}
			}
		} else {
			$args['fields'] = array( 'keyword' );
		}

		if ( ! empty( $args['class'] ) ) {
			$args['class'] = esc_attr( $args['class'] );
		} else {
			$args['class'] = '';
		}

		/**
		 * Filter: Search Form Args
		 *
		 * Modify the $args array of the Matador Search function/shortcode after processing. Warning:
		 * use with caution.
		 *
		 * @since 3.3.0
		 *
		 * @var array $args
		 */
		$args = apply_filters( 'matador_search_form_args_after', $args );

		wp_enqueue_script( 'matador_javascript' );

		return self::get_template( 'jobs-search.php', $args );
	}

	/**
	 * Application
	 *
	 * Builds a job application
	 *
	 * @since  3.0.0
	 *
	 * @param  array $args
	 *
	 * @return string
	 */
	public static function application( $args ) {

		if ( ! Matador::setting( 'applications_accept' ) ) {

			return;
		}

		if ( ! empty( $args['fields'] ) ) {
			if ( is_string( $args['fields'] ) ) {
				$args['fields'] = Helper::comma_separated_string_to_escaped_array( $args['fields'] );
			} else {
				$args['fields'] = Helper::array_values_escaped( $args['fields'] );
			}
		} else {
			$args['fields'] = array();
		}

		if ( ! empty( $args['require'] ) ) {
			if ( is_string( $args['require'] ) ) {
				$args['require'] = Helper::comma_separated_string_to_escaped_array( $args['require'] );
			} else {
				$args['require'] = Helper::array_values_escaped( $args['require'] );
			}
			foreach ( $args['require'] as $key => $field ) {
				if ( ! in_array( $field, $args['fields'], true ) ) {
					unset( $args['require'][ $key ] );
				}
			}
		} else {
			$args['require'] = apply_filters( 'matador_default_required_fields', array( 'name', 'email', 'resume' ) );
		}

		$args['fields'] = Application_Handler::application_fields( $args['fields'], $args['require'] );

		unset( $args['require'] );

		if ( empty( $args['wpid'] ) && ! empty( $_REQUEST['wpid'] ) ) {
			$args['wpid'] = intval( $_REQUEST['wpid'] );
		}

		if ( ! empty( $args['wpid'] ) ) {
			$exists = get_post( intval( $args['wpid'] ) );
			if ( ! $exists || Matador::variable( 'post_type_key_job_listing' ) !== $exists->post_type ) {
				unset( $args['wpid'] );
			} else {
				$args['wpid']  = intval( $args['wpid'] );
				$args['title'] = $exists->post_title;
				$bhid          = ( isset( $args['bhid'] ) ) ? $args['bhid'] : Helper::the_job_bullhorn_id( $args['wpid'] );
				if ( $bhid ) {
					$args['bhid'] = $bhid;
				}
			}
		} else {
			unset( $args['wpid'] );
		}

		if ( empty( $args['bhid'] ) && ! empty( $_REQUEST['bhid'] ) ) {
			$args['bhid'] = intval( $_REQUEST['bhid'] );
		}

		if ( ! empty( $args['bhid'] ) && empty( $args['wpid'] ) ) {

			$exists = Helper::get_post_by_bullhorn_id( intval( $args['bhid'] ) );

			if ( $exists ) {
				$args['title'] = $exists->post_title;
				$args['wpid']  = $exists->ID;
				$args['bhid']  = intval( $args['bhid'] );
			} else {
				unset( $args['bhid'] );
			}
		} else {
			unset( $args['bhid'] );
		}

		if ( isset( $args['class'] ) && ! empty( $args['class'] ) && ( is_array( $args['class'] ) || is_string( $args['class'] ) ) ) {
			if ( is_array( $args['class'] ) ) {
				$args['class'] = Helper::array_values_escaped( $args['class'] );
			} else {
				$args['class'] = Helper::comma_separated_string_to_escaped_array( $args['class'] );
			}
		} else {
			$args['class'] = [];
		}

		$args['class'][] = 'matador-application';

		wp_enqueue_script( 'matador_javascript' );

		/*
		 * Filter to adjust the args passed into the template for applications
		 *
		 * $args array()
		 *
		 * @since   3.4.0
		 */
		return self::get_template( 'application.php', apply_filters( 'matador_application_form_args', $args ) );
	}

	/**
	 * Matador Pagination
	 *
	 * Adds pagination for Job Lists
	 *
	 * @since  3.0.0
	 *
	 * @param WP_Query $query   WP Query object to determine pagination from. Optional.
	 * @param string   $context Template context, for filtering purposes. Accepts any string. Default empty string. Optional.
	 *
	 * @return string|bool
	 */
	public static function pagination( $query = null, $context = '' ) {

		if ( ! $query ) {
			global $wp_query;
			$query = $wp_query;
		}

		if ( $query->max_num_pages > 1 ) {

			$pagination_args = array(
				'base'    => str_replace( 99999, '%#%', esc_url( get_pagenum_link( 99999 ) ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => $query->max_num_pages,
				'context' => $context,
			);

			if ( is_front_page() ) {

				$paged = 1;

				$path = explode( '?', $_SERVER['REQUEST_URI'] );

				if ( false !== strpos( $path[0], '/page/' ) ) {
					$p     = explode( '/', trim( $path[0], '/' ) );
					$paged = is_numeric( end( $p ) ) ? end( $p ) : $paged;
				}

				$pagination_args['current'] = $paged;
			}

			return self::get_template( 'jobs-pagination.php', $pagination_args );

		} else {

			return false;
		}
	}


    /**
     * Matador Button Classes
     *
     * Returns the button class for the proper context.
     *
     * @since  3.6.0
     *
     *
     * @param string|array $classes
     * @param string $context
     * @return string
     */
    public static function button_classes( $classes = 'matador-button', $context = 'primary' ) {

    	if ( empty( $classes ) ) {
    		if ( 'tertiary' === $context ) {
    			$classes = [ 'matador-button', 'matador-button-tertiary' ];
		    } elseif ( 'secondary' === $context ) {
    			$classes = [ 'matador-button', 'matador-button-secondary' ];
		    } else {
    			$classes = 'matador-button';
		    }
	    }

        return self::build_classes( $classes );
    }

	/**
	 * Build Classes String
	 *
	 * Takes an undefined number of strings and arrays and creates a string
	 * of classes to output into a class attribute.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function build_classes() {
		$args    = func_get_args();
		$classes = array();

		foreach ( $args as $arg ) {
			if ( is_array( $arg ) ) {
				foreach ( $arg as $arg_part ) {
					$classes[] = trim( esc_attr( $arg_part ) );
				}
			} elseif ( is_string( $arg ) ) {
				$classes[] = trim( esc_attr( $arg ) );
			}
		}

		return esc_attr( implode( ' ', $classes ) );
	}
}
