<?php
/**
 * Matador Jobs Lite
 *
 * @package     Matador Jobs
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   2016-2023 Matador Software, LLC
 * @license     GPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name: Matador Jobs Lite
 * Plugin URI:  https://matadorjobs.com
 * Description: Connect your Bullhorn Account with your WordPress site and display your jobs on your WordPress site.
 * Version:     3.8.19
 * Requires at least: 5.5.0
 * Tested up to: 6.6.1
 * Requires PHP: 5.6
 * Author:      Matador Software LLC, Jeremy Scott, Paul Bearne
 * Author URI:  https://matadorjobs.com
 * Text Domain: matador-jobs
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Matador Jobs Lite Board is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Matador Jobs Lite Board is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Matador Jobs Board. If not, see <http://www.gnu.org/licenses/>.
 */

namespace matador;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0.0
 *
 * @return void
 */
if ( ! function_exists( 'matador\run_matador' ) ) {
	function run_matador() {
		include_once plugin_dir_path( __FILE__ ) . 'class-matador.php';
		$matador = new Matador();
		$matador->instance();
	}
	run_matador();
} else {
	add_action( 'admin_init', function () {
		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html__( 'Multiple versions of the Matador Jobs plugin are active and only one is allowed at a time. Deactivate (and remove) the versions you will no longer use.', 'matador-jobs' )
		);
	} );
}
