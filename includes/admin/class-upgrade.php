<?php
/**
 * Matador / Upgrade
 *
 * This creates an upgrade page
 *
 * @link        https://matadorjobs.com/
 * @since       3.5.6
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

final class Upgrade {

	/**
	 * Variable: Screen
	 *
	 * Saves the screen id of the Matador Settings screen
	 * @since 3.5.6
	 * @var string
	 */
	public $screen;

	/**
	 * Constructor
	 *
	 * @since 3.5.6
	 *
	 * @return void
	 */
	public function __construct() {

		if ( ! current_user_can( 'manage_options' ) ) {

			return;
		}

		add_filter( '_matador_plugin_upgrades', function( $upgrades ) {
			$upgrades['3.5.6'] = __(
				'<p>Matador Jobs 3.5.6 corrected an issue that may require you to update your theme. Please see our <a href="https://matadorjobs.com/support/documentation/upgrading-to-3-5-6/" target="_blank">upgrade guide</a> before completing the upgrade. The upgrade is irreversible. The upgrade will be automatically applied with version 3.7.0.</p>', 'matador-jobs' );
			return $upgrades;
		} );

		if ( ! self::has_upgrade() ) {

			return;
		}

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
		add_action( 'current_screen', array( $this, 'save' ) );
	}

	/**
	 * Add Upgrade Page
	 *
	 * Adds a menu item for Upgrades inside of Matador Jobs post type menu.
	 *
	 * @since 3.5.6
	 *
	 * @uses CLASS:page()
	 * @uses WordPress add_submenu_page()
	 *
	 * @return void
	 */
	public function admin_menu() {
		/**
		 * Filter: Upgrade Matador Admin Page Title
		 *
		 * Change the Upgrade Matador Admin page title.
		 *
		 * @since 3.5.6
		 *
		 * @param string $title
		 *
		 * @return string
		 */
		$title = apply_filters( 'matador_upgrade_page_title', _x( 'Upgrade Matador', 'Upgrade Matador Admin Page Title', 'matador-jobs' ) );

		/**
		 * Filter: Upgrade Matador Admin Page Menu Label
		 *
		 * Change the Matador Settings page title.
		 *
		 * @since 3.5.6
		 *
		 * @param string $title
		 *
		 * @return string
		 */
		$label = apply_filters( 'matador_upgrade_page_menu_label', _x( 'Upgrade', 'Upgrade Matador Admin Page Menu Label', 'matador-jobs' ) );

		$this->screen = add_submenu_page(
			'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ), // Parent Page Slug
			esc_html( $title ), // Page Title
			esc_html( $label ), // Menu Title
			'manage_options', // User capability to see the page.
			Matador::variable( 'upgrade_key' ), // Sub page slug
			array( __CLASS__, 'page' ) //Callable function to output content for the page.
		);
	}

	/**
	 * Render Page
	 *
	 * Calls the template to render the upgrade page.
	 *
	 *
	 * @since 3.5.6
	 *
	 * @uses Template_Support::get_template()
	 * @uses CLASS::has_upgrade()
	 *
	 * @return void
	 */
	public static function page() {
		Template_Support::get_template( 'upgrade.php', array( 'upgrades' => self::has_upgrade() ), null, true, true );
	}

	/**
	 * Save Settings Page
	 *
	 * @since 3.5.6
	 *
	 * @return void
	 */
	public function save() {

		if ( ! self::has_upgrade() ) {

			return;
		}

		if ( get_current_screen()->id !== $this->screen ) {

			Admin_Notices::add( '<a href="' . esc_url( Matador::variable( 'upgrade_url' ) ) . '">' . __( 'Please click here to complete the Matador Jobs upgrade.', 'matador-jobs' ) . '</a>' );

			return;
		}

		if (
			isset( $_REQUEST['_wpnonce'] )
			&&
			check_admin_referer( Matador::variable( 'options', 'nonce' ) )
		) {
			foreach ( self::has_upgrade() as $version => $description ) {

				$flag = str_replace( '.', '-', $version );

				if ( $_REQUEST[ $flag ] ) {

					Matador::setting( $flag . '-upgrade-incomplete', false );

					return;
				}
			}
		}
	}

	/**
	 * (Get) Upgrades
	 *
	 * Gets an array of upgrades version numbers and html descriptions of the upgrades.
	 *
	 *
	 * @since 3.5.6
	 *
	 * @retun array
	 */
	private static function upgrades() {

		/**
		 * Protected Filter: Matador Plugin Upgrades
		 *
		 * NOTE: This filter should not be used by 3rd-party developers.
		 *
		 * Used by Matador Software to created upgrade notices and routines.
		 *
		 * @since 3.5.6
		 *
		 * @var array Key/Value array with version and description of upgrades
		 *
		 * @return array
		 */
		return apply_filters( '_matador_plugin_upgrades', array() );
	}

	/**
	 * Has Upgrades?
	 *
	 * Checks list of upgrades and determines if the site has any unapplied upgrades. Returns an array of unapplied
	 * upgrades with upgrade version number as key and description as value. When no upgrades are pending, an empty
	 * array is returned.
	 *
	 *
	 * @since 3.5.6
	 *
	 * @retun array
	 */
	private static function has_upgrade() {

		$should_upgrade = array();

		foreach ( self::upgrades() as $version => $description ) {

			$flag = str_replace( '.', '-', $version ) . '-upgrade-incomplete';

			if ( Matador::setting( $flag ) ) {
				$should_upgrade[ $version ] = $description;
			}
		}

		return $should_upgrade;
	}
}
