<?php
/**
 * Matador / Admin Notices
 *
 * Manages admin notices.
 *
 * @link        https://matadorjobs.com/
 * @since       1.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) || class_exists( 'Admin_Notices' ) ) {
	exit;
}

class Admin_Notices {

	/*
	 *
	 */
	public static $transient_key = 'matador_admin_notices';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   3.0.0 pre
	 */
	public function __construct() {
		add_action( 'admin_notices', array( __CLASS__, 'career_portal_domain_root_admin_alert' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * @param $message string
	 * @param $type string
	 * @param $name string
	 */
	public static function add( $message, $type = 'info', $name = null ) {

		$possible_types = array( 'success', 'info', 'warning', 'error' );

		if ( ! in_array( $type, $possible_types, true ) ) {
			$type = 'info';
		}

		$notices = get_transient( self::$transient_key ) ?: array();

		if ( ! $notices ) {
			$notices = [];
		}

		if ( empty( $notices ) || self::is_not_duplicate( $notices, $name ) ) {
			$notices[] = array(
				'message' => $message,
				'type'    => $type,
				'name'    => $name,
			);
			Logger::add( $type, $name, $message );
		}

		set_transient( self::$transient_key, $notices );
	}

	/**
	 * @param $name
	 */
	public static function remove( $name ) {

		// Notify the Admin of Successful Save
		$notices = get_transient( self::$transient_key ) ?: array();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $key => $notice ) {
				if ( array_key_exists( 'name', $notice ) && $name === $notice['name'] ) {
					unset( $notices[ $key ] );
				}
			}
			if ( ! empty( $notices ) ) {
				set_transient( self::$transient_key, $notices );
			} else {
				delete_transient( self::$transient_key );
			}
		}
	}

	/**
	 * Admin Notices
	 *
	 * @since   3.0.0
	 */
	public function admin_notices() {

		$matador_admin_notices = get_transient( self::$transient_key );

		if ( null === Matador::setting( 'bullhorn_api_assistant' ) && get_current_screen()->id !== 'matador-job-listings_page_connect-to-bullhorn' ) {
			?>
			<div class="notice notice-info is-dismissible matador-welcome-admin-notice">
				<img src="<?php echo esc_url( Matador::$path . 'assets/images/matador-jobs-avatar-on-white.png' ); ?>" alt="Matador Jobs plugin logo"/>
				<span>
					<?php echo wp_kses_post( __( 'Welcome to Matador Jobs!', 'matador-jobs' ) . ' ' . __( 'To get started, connect your site to Bullhorn!', 'matador-jobs' ) ); ?>
				</span>
				<a class="button button-primary" href="<?php echo esc_url( admin_url() . 'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ) . '&page=connect-to-bullhorn' ); ?>">
					<?php esc_html_e( 'Open Bullhorn Connection Assistant', 'matador-jobs' ); ?>
				</a>
			</div>
			<?php
		}

		if ( get_current_screen()->parent_file === 'edit.php?post_type=matador-job-listings' ) {

			if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {
				printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
					esc_attr( 'info' ),
					wp_kses_post( __( 'Matador is currently syncing jobs and applications. Reload your admin page for updates.', 'matador-jobs' ) )
				);
			}

			if ( get_transient( Matador::variable( 'doing_app_sync', 'transients' ) ) ) {
				printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
					esc_attr( 'info' ),
					wp_kses_post( __( 'Matador is currently syncing applications. Reload your application page for updates.', 'matador-jobs' ) )
				);
			}

			if ( $matador_admin_notices ) {
				foreach ( $matador_admin_notices as $notice ) {
					printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
						esc_attr( $notice['type'] ),
						wp_kses_post( $notice['message'] )
					);
				}
				delete_transient( self::$transient_key );
			}
		}
	}

	private static function is_not_duplicate( $notices = array(), $name = null ) {

		if ( ! $name || empty( $notices ) ) {
			return true;
		}

		foreach ( $notices as $notice ) {
			if ( array_key_exists( 'name', $notice ) && $name === $notice['name'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Career Portal Domain Root Mismatch Notice
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function career_portal_domain_root_admin_alert() {

		/**
		 * @wordpress-filter Should Check Bullhorn careerPortalDomainRoot
		 * @see includes\class-admin-tasks.php::check_career_portal_domain_root() for documentation
		 */
		if ( ! apply_filters( 'matador_health_check_bullhorn_careerPortalDomainRoot_should_check', true ) ) {
			return;
		}

		if ( 1 === (int) Matador::setting( 'bullhorn_ignore_career_portal_root' ) ) {
			return;
		}

		if ( get_transient( Matador::variable( 'bullhorn_career_portal_remind_24', 'transients' ) ) ) {
			return;
		}

		if ( -1 !== (int) get_transient( Matador::variable( 'bullhorn_career_portal_domain_root', 'transients' ) ) ) {
			return;
		}

		$message[] = __( 'Your Matador Jobs job board URL on this site is not set in Bullhorn as your "Career Portal" URL. If this site operates as your primary job board, this will impact the performance of other Bullhorn integrations.', 'matador-jobs' );
		// Translators: placeholder is a URL.
		$message[] = sprintf( __( 'If this site is your primary job board, your careerPortalDomainRoot should be %s.', 'matador-jobs' ), '<code>' . trailingslashit( get_home_url() . '/' . Matador::setting( 'post_type_slug_job_listing' ) ) . '{?}</code>' );
		$message[] = __( 'See our help documentation site for more information.', 'matador-jobs' );
		$message[] = '<br /><br />';

		$button    = '<a class="button button-primary" href="%s">%s</a>';
		$url       = add_query_arg( 'recheck_domain_root', true );
		$message[] = sprintf( $button, $url, esc_html__( 'Retest', 'matador-jobs' ) );

		$button    = '<a class="button button-secondary" href="%s">%s</a>';
		$url       = add_query_arg( 'hide_domain_root_check', true );
		$message[] = ' ' . sprintf( $button, $url, esc_html__( 'Remind Me Tomorrow', 'matador-jobs' ) );

		$button    = '<a class="button button-secondary" target="_blank" href="%s">%s</a>';
		$url       = 'https://docs.matadorjobs.com/articles/bullhorn-careerportaldomainroot-setting/';
		$message[] = sprintf( $button, $url, esc_html__( 'Help Doc Article', 'matador-jobs' ) );

		Admin_Notices::add( implode( ' ', $message ), 'notice', 'bullhorn-career_portal_domain_root' );
	}
}
