<?php
/**
 * Template: Jobs Search Form ID Field
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-search-field-id.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates
  * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2020 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="matador-search-form-field-group matador-search-form-field-id">

	<label for="matador_xid">
		<?php
		/**
		 * Filter: Matador Search From ID Field Label Text
		 *
		 * Modifies the text of the label for the ID Field
		 *
		 * @since 3.7.0
		 */
		$label = apply_filters( 'matador_search_form_id_field_label_text', '' );

		/**
		 * Filter: Matador Search From ID Field Screen Reader Text
		 *
		 * Modifies the text of the screen reader text for the ID Field, which is ignored
		 * when a text label is present.
		 *
		 * @since 3.7.0
		 */
		$screen_reader_text = apply_filters(
			'matador_search_form_id_field_screen_reader_text',
			__( 'Job ID', 'matador-jobs' )
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
	 * Filter: Matador Search From ID Field Placeholder Text
	 *
	 * Modifies the text of the placeholder text for the ID field input.
	 *
	 * @since 3.7.0
	 */
	$placeholder = apply_filters(
		'matador_search_form_id_field_placeholder',
		esc_html__( 'Job ID', 'matador-jobs' )
	);

	$value = isset( $_REQUEST['matador_xid'] ) ? esc_attr( $_REQUEST['matador_xid'] ) : ''; // WPCS: CSRF ok.

	?>

	<input type="text" id="matador_xid" name="matador_xid" value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"/>

</div>
