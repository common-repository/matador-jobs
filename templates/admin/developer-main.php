<?php
/**
 * Template: Developer Main Page
 *
 * @link        https://matadorjobs.com/
 *
 * @since       3.7.8
 *
 * @package     Matador Jobs
 * @subpackage  Templates / Admin
 * @author      Matador US, LP, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2023, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use matador\MatadorJobs\Developer\DeveloperTools as Developer;

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Matador Developer Tools', 'matador-extension-developer' ); ?></h1>
	<p>
		<?php esc_html_e( 'The following developer and debugging tools are available.', 'matador-extension-developer' ); ?>
		<?php esc_html_e( 'Additional tools may be added with extensions.', 'matador-extension-developer' ); ?>
	</p>
	<ul>
	<?php foreach ( Developer::tools() as $tool ) : ?>
		<li>
		<?php printf( '<a href="%2$s">%1$s</a>', $tool['class']::name(), get_admin_url() . 'admin.php?page=' . Developer::SLUG . '-' . $tool['class']::SLUG ); ?>
		</li>
	<?php endforeach; ?>
	</ul>
</div>