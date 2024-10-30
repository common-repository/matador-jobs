<?php
/**
 * Admin Template Part : Get Pro
 *
 * Template for the special settings field called 'Get Pro'. This template can not overridden.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates/Parts
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="matador-go-pro">

	<h3><?php esc_html_e( 'Process Applications, Get World-Class Support, Gain Access to Awesome Add-Ons', 'matador-jobs' ); ?></h3>

	<p>
		<?php
		esc_html_e( 'Matador Jobs Lite, formerly known as &quot;Bullhorn Staffing Job Listing for WP&quot;	was never supposed to be anything more than a project we built for one client and offered for free to the WordPress community. Two years later, you, our users, have proven to us that our little plugin wasn\'t so little anymore.', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( 'We spent nearly a year completely rewriting our plugin, leaving no stone unturned. The result is something drastically different than where we started, but also ready for the big leagues. We believe we\'ve built something incredible that simultaneously is the easiest-to-use, most reliable, and most fully-featured Bullhorn integration out there.', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( 'Because of the size and scope of this project, we are proud to debut a premium product. We\'ve put valuable features inside our premium offering, including:', 'matador-jobs' );
		?>
	</p>

	<ul class="checklist">
		<li><?php esc_html_e( 'More Ways to Customize Your Job Feed', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Applications that are Automatically Added to Bullhorn', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Pick-and-Choose Extensions to Extend Functionality', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Integration with WP Job Manager', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Smart Caching of Data So You Don\'t Miss Out When Bullhorn is Down', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( '300+ Filters and Hooks', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Installation and Configuation Support Included', 'matador-jobs' ); ?></li>
		<li><?php esc_html_e( 'Unlimited Email Support', 'matador-jobs' ); ?></li>
	</ul>

	<p>
		<?php
		esc_html_e( 'There are tons of features! Find out more about Matador Jobs Pro and Pro Plus.', 'matador-jobs' );
		?>
	</p>

	<p>
		<a class="button button-primary" target="_blank" href="https://matadorjobs.com/">
			<?php esc_html_e( 'Learn More', 'matador-jobs' ); ?>
		</a>
	</p>

	<?php if ( matador\Matador::setting( 'bullhorn_grandfather' ) ) { ?>

		<h3><?php esc_html_e( 'Note: Your Site Is In Legacy Mode', 'matador-jobs' ); ?></h3>

		<p>
			<?php
			esc_html_e( 'When you updated your site, we determined you are one of our Legacy users. Thanks for being with us for so long! New users of the "Lite" version will not have access to Application Processing. Beware that if you reset your data, install the plugin on a new site, or delete the plugin, you may lose access to Application Processing without an upgrade to Pro or Pro Plus. That said, if upgrade, we\'ve added tons of features around Application Processing to make them more reliable, including local caching of applications to ensure no data loss.', 'matador-jobs' );
			?>
		</p>

	<?php } ?>

</div>
