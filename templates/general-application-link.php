<?php
/**
 * Template: The General Application Link
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/general-application-link.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2022, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defined before include:
 *
 * @var string $url
 * @var string $label
 */
?>
<div class="matador-general-application-link-container">
	<?php
	/**
	 * Action Matador Template General Application Link Before
	 *
	 * Runs before everything else at the start of a "General Application Link" function or shortcode call.
	 *
	 * @since 3.8.0
	 *
	 * @param string $url
	 * @param string $label
	 */
	do_action( 'matador_template_general_application_link_before', $url, $label );
	?>

	<a href="<?php echo esc_url( $url ); ?>"
	   class="<?php matador_button_classes(); ?> matador-general-application-link" rel="button">
		<?php if ( $label ) : ?>
			<?php echo esc_html( $label ); ?>
		<?php else: ?>
			<?php esc_html_e( 'Submit a General Application', 'matador-jobs' ); ?>
		<?php endif; ?>
	</a>

	<?php
	/**
	 * Action Matador Template General Application Link After
	 *
	 * Runs after everything else at the start of a "General Application Link" function or shortcode call.
	 *
	 * @since 3.8.0
	 *
	 * @param string $url
	 * @param string $label
	 */
	do_action( 'matador_template_general_application_link_after', $url, $label );
	?>
</div>
