<?php
/**
 * Template: The Job Field
 *
 * Controls the output of the matador_the_job_field() function and [matador_job_field] shortcode, which are
 * used by various templates and functions. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/parts/the-job-field.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="matador-job-confirmation">

	<?php
	$headline = __( 'Thank you for your application.', 'matador-jobs' );
	$headline = apply_filters( 'matador_job_confirmation_headline', $headline );

	if ( $headline ) :
		?>

		<h4><?php echo esc_html( $headline ); ?></h4>

		<?php
	endif;

	$message = __( 'We will be getting back with you shortly. In the meanwhile, try reviewing our other positions.', 'matador-jobs' );
	$message = apply_filters( 'matador_job_confirmation_message', $message );
	?>

	<p><?php echo esc_html( $message ); ?></p>

</div>
