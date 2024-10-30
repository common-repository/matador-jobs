<?php
/**
 * Matador / Updater
 *
 * This wraps our plugin updater integration library for both the core
 * and child plugins.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 * @since       3.8.0 Largely rewritten. Class now contains licensing logic. In fact @todo rename it?
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use matador\MatadorJobs\SoftwareLicensing\Update;

/**
 * Class Updater
 *
 * @since 3.0.0
 */
class Updater {

	/**
	 * Updater constructor
	 *
	 * @since 3.0.0
	 *
	 * @param integer $plugin_id
	 * @param string  $plugin_file
	 * @param string  $plugin_version
	 *
	 * @return void
	 */
	// public function __construct( int $plugin_id = 0, string $plugin_file = '', string $plugin_version = '' ) { // @todo 3.9.0
	public function __construct( $plugin_id = 0, $plugin_file = '', $plugin_version = '' ) {

		// @todo 3.9.0 unnecessary with PHP 8.1 req
		if ( ! is_numeric( $plugin_id ) ) {

			return;
		}

		if ( ! is_admin() ) {

			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			return;
		}

		if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {

			return;
		}

		// If no args are passed, we assume we are querying core
		if ( 0 === $plugin_id ) {
			$plugin_id = Matador::ID;
		}
		if ( empty( $plugin_file ) ) {
			$plugin_file    = Matador::$file;
		}
		if ( empty( $plugin_version ) ) {
			$plugin_version = Matador::VERSION;
		}

		new Update( Matador::LICENSES_HOST, $plugin_file, [
			'version' => $plugin_version,
			'license' => Matador::setting( 'license_core' ),
			'item_id' => $plugin_id,
			'author'  => 'Matador Software',
			'url'     => home_url(),
			/**
			 * Filter: Matador Jobs Beta Test Updates
			 *
			 * @since 3.0.0
			 *
			 * @param bool $should
			 *
			 * @return bool
			 */
			'beta'    => apply_filters( 'matador_jobs_beta_test_updates', false ),
		] );
	}

	/**
	 * Activate License
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function activate() {

		$license = Matador::setting( 'license_core' );

		if ( ! $license ) {

			return;
		}

		// data to send in our API request
		$params = [
			'edd_action'  => 'activate_license',
			'license'     => $license,
			'item_id'     => Matador::ID,
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		];

		$args = [
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $params
		];

		$response = wp_remote_post( Matador::LICENSES_HOST, $args );

		if ( is_wp_error( $response ) ) {

			// Translators: Placeholder 1 is WordPress error code. Placeholder 2 is previously translated WordPress error message.
			new Event_Log( 'options-license-activation-internal-error', wp_kses( sprintf( __( 'Cannot reach MatadorJobs.com licensing service due to internal error. WordPress error code %1$s and error message "%2$s."', 'matador-jobs' ), '<code>' . $response->get_error_code() . '</code>', '<code>' . $response->get_error_message() . '</code>' ) ) );

			$license_status = 'error';

		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {

			// At this time, the updater script on MatadorJobs.com will always return 200 unless it cannot be reached, ie: HTTP 500 or HTTP 404.
			new Event_Log( 'options-license-activation-external-error', esc_html__( 'MatadorJobs.com licensing service responded with error. Please try again later.', 'matador-jobs' ) );

			$license_status = 'error';

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				$license_status = $license_data->error;

			} else {

				$license_status = 'valid';

			}
		}

		new Event_Log( 'options-license-activation', sprintf( esc_html__( 'Matador Jobs Pro activation request responded with: %s', 'matador-jobs' ), self::get_license_status_description( $license_status ) ) );

		Matador::setting( 'license_core_status', $license_status );
	}

	/**
	 * Deactivate License
	 *
	 * @since 3.8.0
	 *
	 * @param string $key The old key being deactivated
	 *
	 * @return void
	 */
	public static function deactivate( $key ) {

		if ( empty( $key ) && ! is_string( $key ) ) {

			return;
		}

		new Event_Log( 'matador-pro-license-key-deactivate', sprintf( esc_html__( 'Deactivating license key %s from url %s.', 'matador-jobs' ), $key, home_url() ) );

		// data to send in our API request
		$params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $key,
			'item_id'     => Matador::ID,
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		$args = [
			'timeout'   => 15,
			'sslverify' => false,
			// Let this succeed or fail in the background.
			'blocking'  => false,
			'body'      => $params,
		];

		wp_remote_post( Matador::LICENSES_HOST, $args );

		Matador::setting( 'license_core_status', 'invalid' );
	}

	/**
	 * Check License
	 *
	 * Checks in the background if a license activation is still valid.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function check_license() {

		$license = Matador::setting( 'license_core' );

		if ( ! $license ) {

			return;
		}

		$params = [
			'edd_action'  => 'check_license',
			'license'     => $license,
			'item_id'     => Matador::ID,
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		];

		$args = [
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $params,
		];

		$response = wp_remote_post( Matador::LICENSES_HOST, $args );

		if ( is_wp_error( $response ) ) {

			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'valid' === $license_data->license ) {

			return;
		}

		Matador::setting( 'license_core_status', 'invalid' );
	}

	/**
	 * Get License Status Description
	 *
	 * @since 3.8.0
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public static function get_license_status_description( $status = '' ) {

		$license = Matador::setting( 'license_core' );

		switch ( $status ) {

			case 'expired':
				$renewal_url = trailingslashit( Matador::LICENSES_HOST ) . 'checkout/?download_id=' . Matador::ID . '&edd_license_key=' . $license;
				return sprintf( __( 'Your license key is expired. <a href="%s" target="_blank">Renew your license</a>.', 'matador-jobs' ), $renewal_url );

			case 'disabled':
			case 'revoked':
				return __( 'Your license key has been disabled. This is often due to a refund or billing issue.', 'matador-jobs' );

			case 'missing':
				return __( 'Invalid license.', 'matador-jobs' );

			case 'invalid':
			case 'site_inactive':
				return __( 'Your license is not active for this URL.', 'matador-jobs' );

			case 'item_name_mismatch':
				return __( 'This appears to be an invalid license key for this item.', 'matador-jobs' );

			case 'no_activations_left':
				$manage_url = trailingslashit( Matador::LICENSES_HOST ) . 'account';
				return sprintf( __( 'Your license key has reached its activation limit. <a href="%s" target="_blank">Manage Your License</a>.', 'matador-jobs' ), $manage_url );

			case 'valid':
				return __( 'Your license is valid and activated. Your site will receive automatic updates.', 'matador-jobs' );

			default:
				return __( 'An error occurred, please try again.', 'matador-jobs' );
		}
	}
}
