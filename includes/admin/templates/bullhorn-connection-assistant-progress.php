<?php
/**
* Admin Template : Bullhorn Connection Assistant Progress
*
* @link        https://matadorjobs.com/
* @since       3.0.0
*
* @package     Matador Jobs
* @subpackage  Admin/Templates
* @author      Matador Software, LLC, Jeremy Scott, Paul Bearne
* @copyright   (c) 2017-2021 Matador Software, LLC
*
* @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
*/

namespace matador;

/**
 * Defined before include:
 * @var string $progress
 */

if ( 1 < count( explode( '-', $progress ) ) ) {
	$parts    = explode( '-', $progress );
	$progress = $parts[0];
}
?>

	<ol class="progress">
		<li class="prepare <?php echo 'prepare' === $progress ? 'active' : null; ?>" >
			<?php esc_html_e( 'Prepare', 'matador-jobs' ); ?>
		</li>

<!--		<li class="datacenter --><?php //echo 'datacenter' === $progress ? 'active' : null; ?><!--">-->
<!--			--><?php //esc_html_e( 'Datacenter', 'matador-jobs' ); ?>
<!--		</li>-->

		<li class="credentials <?php echo 'credentials' === $progress ? 'active' : null; ?>">
			<?php esc_html_e( 'Credentials', 'matador-jobs' ); ?>
		</li>

		<li class="callback <?php echo 'callback' === $progress ? 'active' : null; ?>">
			<?php esc_html_e( 'Callback URI', 'matador-jobs' ); ?>
		</li>

		<li class="authorize <?php echo 'authorize' === $progress ? 'active' : null; ?>">
			<?php esc_html_e( 'Authorize', 'matador-jobs' ); ?>
		</li>
	</ol>
