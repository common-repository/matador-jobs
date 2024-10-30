<?php
/**
 * Matador / Email / Application Notification Email Trait
 *
 * Trait to extend notification application emails with an application data processing
 *
 * @link        http://matadorjobs.com/
 *
 * @since       3.6.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Email
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2018, Jeremy Scott, Paul Bearne
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait ApplicationNotificationEmailTrait
 *
 * @package MatadorJobs\Email
 *
 * @since 3.6.0
 */
trait ApplicationMessagesTrait {

	/**
	 * Email Template Args
	 *
	 * After being passed an application data object and the application WordPress ID, generate an array of args for the
	 * template.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $application
	 *
	 * @return array
	 */
	public static function template_args( array $application = [] ) {

		if ( empty( $application ) ) {
			return [];
		}

		$application = self::jobs_formatted_titles( $application );

		/**
		 * Filter: Matador Email Application Notifications Template Args
		 *
		 * Filter the array of args passed into Mustache and to the templates for Application Notifications. Is used by
		 * both applicant and recruiter templates.
		 *
		 * @since 3.6.0
		 *
		 * @var array $args
		 *
		 * @return array
		 */
		return apply_filters( 'matador_email_application_notifications_template_args', [
			'firstname'       => ! empty( $application['name']['firstName'] ) ? empty( $application['name']['firstName'] ) : '', // @since 3.6.0
			'lastname'        => ! empty( $application['name']['lastName'] ) ? empty( $application['name']['lastName'] ) : '', // @since 3.6.0
			'fullname'        => ! empty( $application['name']['fullName'] ) ? $application['name']['fullName'] : '', // @since 3.0.0
			'email'           => ! empty( $application['email'] ) ? $application['email'] : '', // @since 3.0.0
			'phone'           => ! empty( $application['phone'] ) ? $application['phone'] : '', // @since 3.0.0
			'address'         => ! empty( $application['address'] ) ? $application['address'] : '', // @since 3.0.0
			'user_message'    => ! empty( $application['message'] ) ? $application['message'] : '', // @deprecated 3.6.0, @since 3.0.0
			'message'         => ! empty( $application['message'] ) ? $application['message'] : '', // @since 3.0.0
			'titles_html'     => ! empty( $application['applied_jobs'] ) ? $application['applied_jobs'] : '', // @deprecated 3.6.0, @since 3.0.0
			'applied_jobs'    => ! empty( $application['applied_jobs'] ) ? $application['applied_jobs'] : '', // @since 3.0.0
			'sitename'        => get_option( 'blogname', __( 'Matador Jobs-powered job board', 'matador-jobs' ) ),
			'local_post_data' => $application, // @deprecated 3.6.0, @since 3.0.0
			'application'     => $application, // @since 3.0.0
			'post_content'    => ! empty( $application['wpid'] ) ? apply_filters( 'the_content', get_post_field( 'post_content', $application['wpid'] ) ) : '', // @since 3.0.0
			// add Bullhorn Candidate ID
			// add Submission ID get_post_meta( $wpid, '_bullhorn_id' );
		] );
	}

	/**
	 * Applied Jobs Titles Formatted
	 *
	 * After being passed an application data object, generate the formatted job titles "{Job Name} (ID: {BHID})" and
	 * append to the object, along with an html list of jobs for the template.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $application
	 *
	 * @return array
	 */
	public static function jobs_formatted_titles( array $application = [] ) {

		if ( empty( $application ) || empty( $application['jobs'] ) ) {

			return $application;
		}

		/**
		 * Filter: Matador Email Application Notifications Formatted Job Titles Open
		 *
		 * Filter the opening tag of the formatted job titles (applied jobs) value passed to the email templates.
		 *
		 * @since 3.6.0
		 *
		 * @var string of HTML
		 *
		 * @return string of HTML
		 */
		$applied_jobs = apply_filters( 'matador_email_application_notifications_formatted_job_titles_wrap_open', '<ul>' );

		foreach ( $application['jobs'] as $index => $job ) {

			/**
			 * Filter: Matador Email Application Notifications Formatted Job Titles Each
			 *
			 * Filter the formatting of the applied jobs formatted title for the jobs array and used to create the
			 * formatted job titles string.
			 *
			 * @since 3.6.0
			 *
			 * @var string sprint formatting string for up to two args, #1 being job title, #2 being Remote Job ID
			 *
			 * @return string printf formatting string for up to two args, #1 being job title, #2 being Remote Job ID
			 */
			$applied_jobs_each_format = apply_filters( 'matador_email_application_notifications_formatted_job_titles_each', '%1$s (BH: %2$s)' );

			$application['jobs'][ $index ]['formatted'] = sprintf( $applied_jobs_each_format, $job['title'], absint( $job['bhid'] ) );

			/**
			 * Filter: Matador Email Application Notifications Formatted Job Titles Each Wrap
			 *
			 * Filter the formatting of the wrapper the applied jobs formatted title is pushed into to create the
			 * formatted job titles string.
			 *
			 * @since 3.6.0
			 *
			 * @var string printf formatting string for one arg, the formatted job string
			 *
			 * @return string
			 */
			$applied_jobs_each_wrap = apply_filters( 'matador_email_application_notifications_formatted_job_titles_wrap_each', '<li>%1$s</li>' );

			$applied_jobs .= sprintf( $applied_jobs_each_wrap, $application['jobs'][ $index ]['formatted'] );
		}

		/**
		 * Filter: Matador Email Application Notifications Formatted Job Titles Open
		 *
		 * Filter the opening tag of the formatted job titles (applied jobs) value passed to the email templates.
		 *
		 * @since 3.6.0
		 *
		 * @var array $args
		 *
		 * @return array
		 */
		$applied_jobs .= apply_filters( 'matador_email_application_notifications_formatted_job_titles_wrap_close', '</ul>' );

		$application['applied_jobs'] = $applied_jobs;

		return $application;
	}

	/**
	 * Attach Application Files
	 *
	 * After being passed an application data object and the instance of Email, attach the files in the
	 * application files array.
	 *
	 * @since 3.6.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $application
	 *
	 * @return array
	 */
	public static function attachments( array $application = [] ) {
		if ( empty( $application ) ) {

			return [];
		}

		$attachments = [];

		if ( ! empty( $application['files'] ) ) {
			foreach ( $application['files'] as $file ) {
				if ( file_exists( $file['path'] ) ) {
					$attachments[] = $file['path'];
				}
			}
		}

		return $attachments;
	}

}
