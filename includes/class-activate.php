<?php
/**
 * Matador / Install & Setup
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

use WP_Query;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activate {

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 */
	public function __construct() {
		register_activation_hook( Matador::$file, array( __CLASS__, 'activate' ) );
		if ( Matador::setting( 'matador_version' ) !== Matador::VERSION ) {
			self::activate();
		}
	}

	/**
	 * Plugin Activator
	 *
	 * Runs all activation-related functions.
	 *
	 * @since    3.0.0
	 */
	public static function activate() {
		self::check_compatibility( '4.7', '5.6' );
		self::create_resume_directory();
		self::update();
		self::defaults();
		self::downgrade();
		set_transient( Matador::variable( 'flush_rewrite_rules', 'transients' ), true, 30 );
	}

	/**
	 * Check Compatibility
	 *
	 * Compares version numbers for current and minimum required PHP and WP.
	 *
	 * @since    3.0.0
	 *
	 * @param float $wp  WordPress minimum Version Number
	 * @param float $php PHP minimum Version Number
	 */
	public static function check_compatibility( $wp, $php ) {

		global $wp_version;

		$flags = null;

		// Checks for PHP version
		if ( version_compare( PHP_VERSION, $php, '<' ) ) {
			$flags['PHP'] = $php;
		}

		// Checks for WP version
		if ( version_compare( $wp_version, $wp, '<' ) ) {
			$flags['WordPress'] = $wp;
		}

		// If there is a flag, return an error.

		if ( $flags ) {

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'] ) && isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			$message = null;
			$i       = 1;
			$errors  = count( $flags );

			foreach ( $flags as $requirement => $version ) {
				// translators: %1$s is requirement, %2$s is version
				$message .= sprintf( esc_html__( '%1$s version %2$s or greater', 'matador-jobs' ), $requirement, $version );

				// If the list has more than one item, add separators
				if ( $i < $flags ) {

					if ( ( $errors > 2 ) && ( $i < $errors ) ) {
						$message .= ', ';
					}

					if ( ( $errors - 1 ) === $i ) {
						$message .= esc_html__( 'and', 'matador-jobs' ) . ' ';
					}
				}

				$i ++;
			}

			deactivate_plugins( Matador::$file );
			// translators: %s is the missing PHP / WP version
			wp_die( sprintf( esc_html__( 'We\'re sorry, but Matador Jobs Board plugin requires %s. Please update your system.', 'matador-jobs' ), esc_html( $message ) ) );
		}
	}

	/**
	 * Creates the Resume Directory
	 *
	 * @since 3.0.0
	 * @since 3.1.0 fires an Admin Notice now, instead of a die()
	 * @since 3.4.0 Now adds empty index.php files to improve security should Apache directory indexes be turned on.
	 */
	private static function create_resume_directory() {
		$directory = Matador::variable( 'uploads_cv_dir' );
		if ( ! file_exists( $directory ) && ! wp_mkdir_p( $directory ) ) {
			Admin_Notices::add( __( 'Matador was unable to make the folder where it puts applications and its log files. Please create a folder inside <kbd>wp-content</kbd> called <kbd>matador_uploads</kbd> and sets its owner appropiately and its permissions to <kbd>644</kbd>', 'matador-jobs' ), 'error', 'matador-folder-create-failed' );
		}
		$index_file = $directory . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'log_file_path' ) . '/index.php';
		touch( $index_file );

		$index_file = Matador::variable( 'json_file_path' ) . '/index.php';
		touch( $index_file );
	}

	/**
	 * Setups Default Settings
	 *
	 * @since    3.0.0
	 */
	private static function defaults() {

		if ( ! Matador::$settings->has_settings() ) {
			$settings = array();
			$fields   = Settings_Fields::instance()->get_just_fields();
			foreach ( $fields as $field => $args ) {
				if ( ! empty( $args['default'] ) ) {
					$settings[ $field ] = $args['default'];
				}
			}
			Matador::$settings->update( $settings );
			Matador::reset();
		}
	}

	/**
	 * Upgrades Users from Old Versions
	 *
	 * @since 3.0.0
	 * @since 3.1.0 added upgrade for 3.0.x to 3.1.0
	 * @since 3.4.0 added upgrade for 3.0.x to 3.4.0
	 * @since 3.5.0 added upgrade for 3.0.x to 3.5.0
	 * @since 3.5.6 added upgrade for 3.3.0 to 3.5.6
	 * @since 3.7.0 removed upgrade for 3.3.0 to 3.5.6
	 * @since 3.7.0 added upgrade for 3.0.0 to 3.7.0
	 */
	private static function update() {

		if ( Matador::setting( 'matador_version' ) ) {
			$old_settings    = false;
			$current_version = Matador::setting( 'matador_version' );
		} else {
			$old_settings = get_option( 'bullhorn_settings' );
			if ( false === $old_settings ) {
				return false;
			} else {
				$current_version = '2.4.0';
			}
		}

		if ( version_compare( $current_version, Matador::VERSION, '=' ) ) {
			return false;
		}

		// Upgrade from 2.x to 3.0.0
		if ( version_compare( $current_version, '3.0.0', '<' ) && false !== $old_settings ) {

			$settings = array();

			foreach ( $old_settings as $key => $value ) {

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
			$details['old_settings'] = $old_settings;

			set_transient( 'matador_upgrade_email', $details );

			add_action( 'wp_loaded', array( __class__, 'send_upgrade_notice' ), 100 );

			delete_option( 'bullhorn_settings' );

			//deactivate_plugins( plugin_basename( 'bh-staffing/bullhorn-2-wp.php' ) );
		}

		// Upgrade from 3.0.x to 3.1.x
		if ( version_compare( $current_version, '3.1.0', '<' ) ) {

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

		// Upgrade to 3.4.x
		if ( version_compare( $current_version, '3.4.0', '<' ) ) {

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

		// Upgrade to 3.5.x
		if ( version_compare( $current_version, '3.5.0', '<' ) ) {

			// New Bullhorn Category Field should be 'categories' for existing users.
			Matador::setting( 'bullhorn_category_field', 'categories' );

			//
			// Add the _matador_source and _matador_source_id meta
			//
			$existing = array();

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
						$bh_id = get_post_meta( $post_id, 'bullhorn_job_id', true );
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
			} // End while().

			foreach ( $existing as $id => $bullhorn_id ) {
				update_post_meta( $id, '_matador_source', 'bullhorn' );
				update_post_meta( $id, '_matador_source_id', $bullhorn_id );
			}
		} // endif Version Compare 3.5

		if ( version_compare( $current_version, '3.7.0', '<' ) ) {

			wp_clear_scheduled_hook( 'matador_application_sync' );
			wp_clear_scheduled_hook( 'matador_job_sync' );

		} // endif Version Compare 3.7

		Matador::setting( 'matador_version', Matador::VERSION );

		return true;
	}

	/**
	 * Downgrades a Pro User to a Lite User
	 *
	 * @since 3.1.0
	 */
	public static function downgrade() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'matador-jobs/matador-jobs.php' ) ) {
			return;
		}

		$downgrade_these = array( 'notify_admin', 'logging', 'jsonld_hiring_organization', 'jsonld_salary', 'applications_accept' );
		$settings        = array();

		if ( Matador::$settings->has_settings() ) {
			$fields = Settings_Fields::instance()->get_just_fields();
			foreach ( $fields as $field => $args ) {
				if ( in_array( $field, $downgrade_these, true ) ) {
					if (
						'logging' === $field
						&&
						in_array( Matador::setting( 'logger' ), array( '0', '1', '2' ), true )
					) {
						continue;
					}

					if ( ! empty( $args['default'] ) ) {
						$settings[ $field ] = $args['default'];
					} else {
						$settings[ $field ] = null;
					}
				}
			}
			Matador::$settings->update( $settings );
			Matador::reset();
		}
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