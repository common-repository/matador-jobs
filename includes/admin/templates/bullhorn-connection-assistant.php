<?php
/**
 * Admin Template : Bullhorn Connection Assistant
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace matador;

/**
 * Defined prior to inclusion
 * @var $progress string
 */

?>

<div class="wrap">

	<h1 class="matador-settings-page-title">
		<?php echo esc_html( apply_filters( 'matador_bullhorn_connection_assistant_page_title', esc_html__( 'Bullhorn Connection Assistant', 'matador-jobs' ) ) ); ?></h1>


	<div class="bullhorn-api-assistant">
		<?php
		if ( 'complete' !== $progress ) {
			Template_Support::get_template( 'bullhorn-connection-assistant-progress.php', array( 'progress' => $progress ), '', true, true );
		}
		?>

		<section class="matador-settings-section">

			<form method="post" id="bullhorn-connection-assistant" class="matador-settings-form">

				<?php wp_nonce_field( Matador::variable( 'bh-api-assistant', 'nonce' ), Matador::variable( 'bh-api-assistant', 'nonce' ) ); ?>

				<?php Template_Support::get_template( "bullhorn-connection-assistant-{$progress}.php", array(), '', true, true ); ?>

			</form>

		</section>

	</div>

</div>
