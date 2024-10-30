<?php
/**
 * Template: Jobs Search Form Submit Button
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-search-field-submit.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.3.0
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

/**
 * Action: Matador Search Submit Button Class
 *
 * Filters the class attribute of the submit button for the Matador Search form.
 *
 * @since 3.3.0
 * @deprecated 3.6.0 please use matador_template_button_classes with context 'primary'
 *
 * @param string $class
 *
 * @return string
 */
$button_class = apply_filters( 'matador_search_form_submit_button_class', 'matador-search-submit' );
?>
<div class="matador-search-form-field-group matador-search-form-field-submit">

	<button type="submit" id="matador-search-submit" class="<?php matador_button_classes( [ $button_class, 'matador-button' ], 'primary' ); ?>">
		<?php
		/**
		 * Filter: Matador Search From Submit Label
		 *
		 * Modifies the text of the Submit button
		 *
		 * @since 3.3.0
		 */
		echo wp_kses_post( apply_filters( 'matador_search_form_submit_label', __( 'Search', 'matador-jobs' ) ) );
		?>
	</button>

</div>
