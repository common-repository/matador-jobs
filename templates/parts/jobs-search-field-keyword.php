<?php
/**
 * Template: Jobs Search Form Keyword Field
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-search-field-keyword.php
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
?>

<div class="matador-search-form-field-group matador-search-form-field-keyword">

	<label for="matador_s">
		<?php
		/**
		 * Filter: Matador Search From Keyword Field Label Text
		 *
		 * Modifies the text of the label for the Keyword Field
		 *
		 * @since 3.3.0
		 */
		$label = apply_filters( 'matador_search_form_keyword_field_label_text', '' );

		/**
		 * Filter: Matador Search From Keyword Field Screen Reader Text
		 *
		 * Modifies the text of the screen reader text for the Keyword Field, which is ignored
		 * when a text label is present.
		 *
		 * @since 3.3.0
		 */
		$screen_reader_text = apply_filters(
			'matador_search_form_keyword_field_screen_reader_text',
			__( 'Key Word or Key Words', 'matador-jobs' )
		);
		?>

		<?php if ( ! $label ) : ?>

			<span class="matador-screen-reader-text">
			<?php echo esc_html( $screen_reader_text ); ?>
		</span>

		<?php endif; ?>

		<?php echo esc_html( $label ); ?>

	</label>

	<?php
	/**
	 * Filter: Matador Search From Placeholder Text
	 *
	 * Modifies the text of the placeholder text for the keyword field input.
	 *
	 * @since 3.3.0
	 */
	$placeholder = apply_filters(
		'matador_search_form_keyword_field_placeholder',
		esc_html__( 'Search Jobs', 'matador-jobs' )
	);

	$value = isset( $_REQUEST['matador_s'] ) ? esc_attr( $_REQUEST['matador_s'] ) : ''; // WPCS: CSRF ok.

	?>

	<input type="text" id="matador_s" name="matador_s" value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"/>

</div>
