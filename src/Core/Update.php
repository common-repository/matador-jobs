<?php
/**
 * Matador / Update
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0 as matador\Activate
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador US LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2022 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador\MatadorJobs\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Query;
use matador\Matador;

final class Update {

	/**
	 * Old Version
	 *
	 * @since 3.8.0
	 *
	 * @var float
	 */
	private $old_version = 0.0;

	/**
	 * Old Settings
	 *
	 * @since 3.8.0
	 *
	 * @var array
	 */
	private $old_settings = [];

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 */
	public function __construct() {

		if ( Matador::setting( 'matador_version' ) === Matador::VERSION ) {
			return;
		}

		if ( Matador::setting( 'matador_version' ) ) {
			$this->old_settings = false;
			$this->old_version  = Matador::setting( 'matador_version' );
		} else {
			$this->old_settings = get_option( 'bullhorn_settings' );
			if ( false === $this->old_settings ) {
				return;
			} else {
				$this->old_version = '2.4.0';
			}
		}

		$this->update_300();
		$this->update_310();
		$this->update_340();
		$this->update_350();
		$this->update_370();
		$this->update_380();
		$this->update_382();
		$this->update_3810();
		$this->update_3811();

		Analytics::event( 'Instance Updated', [ 'old_version' => $this->old_version ] );

		Matador::setting( 'matador_version', Matador::VERSION );
	}

	/**
	 * Upgrade from < 3.0.0
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function update_300() {

		if ( ! ( version_compare( $this->old_version, '3.0.0', '<' ) && false !== $this->old_settings ) ) {
			return;
		}
		$settings = array();

		foreach ( $this->old_settings as $key => $value ) {

			switch ( $key ) {
				case 'client_id':
					$settings['bullhorn_api_client'] = $value;
					break;
				case 'client_secret':
					$settings['bullhorn_api_secret'] = $value;
					break;
				case 'thanks_page':
					$settings['thank_you_page'] = $value;
					break;
				case 'listings_page':
					$settings['application_page'] = $value;
					break;
				case 'listings_sort':
					$settings['sort_jobs'] = $value;
					break;
				case 'default_shortcode':
					$settings['default_shortcode'] = $value;
					if ( array_search( 'cv', $value, true ) ) {
						$settings['default_shortcode'][] = 'resume';
					}
					break;
				case 'send_email':
					$settings['recruiter_email'] = $value;
					if ( empty( $value ) ) {
						$settings['notify_recruiter'] = '0';
					} else {
						$settings['notify_recruiter'] = '1';
					}
					break;
				case 'cron_error_email':
					if ( true === $value ) {
						$settings['notify_admin'] = '1';
					} else {
						$settings['notify_admin'] = '0';
					}
					$settings['admin_email'] = get_bloginfo( 'admin_email' );
					break;
				case 'description_field':
					if ( 'description' === $value ) {
						$settings['bullhorn_description_field'] = 'description';
					} else {
						$settings['bullhorn_description_field'] = 'publicDescription';
					}
					break;
				case 'is_public':
					if ( true === $value ) {
						$settings['bullhorn_is_public'] = '1';
					} else {
						$settings['bullhorn_is_public'] = '0';
					}
					break;
				case 'mark_submitted':
					if ( true === $value ) {
						$settings['bullhorn_mark_application_as'] = 'submitted';
					} else {
						$settings['bullhorn_mark_application_as'] = 'lead';
					}

					break;
				case 'run_cron':
					if ( true === $value ) {
						$settings['bullhorn_auto_sync'] = '1';
					} else {
						$settings['bullhorn_auto_sync'] = '0';
					}
					break;
			}
		}

		$settings['bullhorn_grandfather'] = true;
		Matador::$settings->update( $settings );

		$old_credentials = get_option( 'bullhorn_api_access', array() );
		if ( ! empty( $old_credentials ) ) {
			update_option( 'bullhorn_api_credentials', $old_credentials );
		}

		// so we can contact you
		$details = array();

		$details['admin_email']  = get_option( 'admin_email' );
		$details['blogname']     = get_option( 'blogname' );
		$details['siteurl']      = get_option( 'siteurl' );
		$details['old_settings'] = $this->old_settings;

		set_transient( 'matador_upgrade_email', $details );

		add_action( 'wp_loaded', array( __class__, 'send_upgrade_notice' ), 100 );

		delete_option( 'bullhorn_settings' );
	}

	/**
	 * Update Routine for 3.1.0
	 *
	 * @since 3.1.0
	 *
	 * @return void
	 */
	private function update_310() {

		if ( version_compare( $this->old_version, '3.1.0', '<' ) ) {

			return;
		}

		$settings = array();

		if ( Matador::setting( 'license_core_key' ) ) {
			$settings['license_core']     = Matador::setting( 'license_core_key' );
			$settings['license_core_key'] = null;
		}

		if ( Matador::setting( 'bullhorn_api_is_connected' ) ) {
			$settings['bullhorn_api_client_is_valid'] = true;
		}

		Matador::$settings->update( $settings );
	}

	/**
	 * Update Routine for 3.4.0
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	private function update_340() {
		if ( ! version_compare( $this->old_version, '3.4.0', '<' ) ) {
			return;
		}

		// New Job Meta Header should be off for existing users.
		Matador::setting( 'show_job_meta', '0' );

		if ( Matador::setting( 'jsonld_disabled' ) ) {
			Matador::setting( 'jsonld_enabled', '0' );
		} else {
			Matador::setting( 'jsonld_enabled', '1' );
		}

		unset( Matador::$settings->jsonld_disabled );

		$index_file = Matador::variable( 'uploads_cv_dir' ) . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'log_file_path' ) . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'json_file_path' ) . '/index.php';
		touch( $index_file );
	}

	/**
	 * Update Routine for 3.5.0
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	private function update_350() {

		if ( ! version_compare( $this->old_version, '3.5.0', '<' ) ) {

			return;
		}

		// New Bullhorn Category Field should be 'categories' for existing users.
		Matador::setting( 'bullhorn_category_field', 'categories' );

		//
		// Add the _matador_source and _matador_source_id meta
		//
		$existing = array();

		//
		// Add _matador_source and _matador_source_id post meta
		//
		while ( true ) {
			$limit  = 100;
			$offset = isset( $offset ) ? $offset : 0;

			$args = array(
				'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'meta_key'       => 'bullhorn_job_id',
				'post_status'    => 'any',
				'fields'         => 'ids',
			);

			// WP Query
			$posts = new WP_Query( $args );

			if ( $posts->have_posts() && ! is_wp_error( $posts ) ) {

				foreach ( $posts->posts as $post_id ) {
					$bh_id                = get_post_meta( $post_id, 'bullhorn_job_id', true );
					$existing[ $post_id ] = $bh_id;
				}

				// If the size of the result is less than the limit, break, otherwise increment and re-run
				if ( $posts->post_count < $limit ) {
					break;
				} else {
					$offset += $limit;
				}
			} else {
				break;
			}
		}

		foreach ( $existing as $id => $bullhorn_id ) {
			update_post_meta( $id, '_matador_source', 'bullhorn' );
			update_post_meta( $id, '_matador_source_id', $bullhorn_id );
		}
	}


	/**
	 * Update for 3.7.0
	 *
	 * @since 3.7.0
	 *
	 * @return void
	 */
	private function update_370() {
		if ( ! version_compare( $this->old_version, '3.7.0', '<' ) ) {

			return;
		}
		wp_clear_scheduled_hook( 'matador_application_sync' );
		wp_clear_scheduled_hook( 'matador_job_sync' );

	}

	/**
	 * Update Routine for 3.8.0
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	private function update_380() {
		if ( ! version_compare( $this->old_version, '3.8.0', '<' ) ) {

			return;
		}
		Matador::setting( 'applications_general_apply_page', '-1' );
		Matador::setting( 'bullhorn_candidate_owner', 'api' );
		Matador::setting( 'application_terms_field', '0' );
		Matador::setting( 'terms_policy_page', '-1' );
		Matador::setting( 'bullhorn_ignore_career_portal_root', '0' );
	}

	/**
	 * Update Routine for 3.8.2
	 *
	 * @since 3.8.2
	 *
	 * @return void
	 */
	private function update_382() {
		if ( ! version_compare( $this->old_version, '3.8.2', '<' ) ) {

			return;
		}
		delete_transient( 'matador_ET_daily_instance_report' );
	}

	/**
	 * Update Routine for 3.8.10
	 *
	 * Deletes old settings `bullhorn_cluster_url` and `bullhorn_api_center`, pre-loads the new setting
	 * `bullhorn_api_cluster_id` from last login data, if available.
	 *
	 * @since 3.8.10
	 *
	 * @return void
	 */
	private function update_3810() {

		if ( ! version_compare( $this->old_version, '3.8.10', '<' ) ) {

			return;
		}

		unset( Matador::$settings->bullhorn_cluster_url );
		unset( Matador::$settings->bullhorn_api_center );

		$last_login = get_option( Matador::variable( 'bullhorn_session', 'transients' ), [] );

		if ( isset( $last_login['url'] ) ) {

			preg_match( '/rest(.*)\.bullhorn/U', $last_login['url'], $matches );

			if ( isset( $matches[1] ) ) {
				Matador::setting( 'bullhorn_api_cluster_id' , $matches[1] );
			}
		}
	}

	/**
	 * Update Routine for 3.8.11
	 *
	 * Deletes the no-name transient set by a typo in the Matador Monitor class.
	 *
	 * @since 3.8.11
	 *
	 * @return void
	 */
	private function update_3811() {

		if ( ! version_compare( $this->old_version, '3.8.11', '<' ) ) {

			return;
		}

		delete_transient( '' );
	}

	/**
	 * Send Upgrade Notice to Matador
	 *
	 * Sends an email notifying us of a site using Grandfather features from a 2.x version.
	 *
	 * @since 3.0.2
	 */
	public static function send_upgrade_notice() {
		$body = get_transient( 'matador_upgrade_email' );

		if ( function_exists( 'wp_mail' ) ) {
			wp_mail( 'grandfathered@matadorjobs.com', 'Bullhorn Grandfathered', wp_json_encode( $body ) );
		}

		delete_transient( 'matador_upgrade_email' );
	}

}