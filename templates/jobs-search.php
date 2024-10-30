<?php
/**
 * Template: Jobs Search Form
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/jobs-search.php
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 * @since       3.3.0 template rewritten, several new filters added
 * @since       3.7.0 added support for the new external ID search field
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021, Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use matador\Job_Taxonomies;

/**
 * Defined before include:
 * @var array $fields
 * @var mixed $class
 */

?>

<?php
/**
 * Action: Matador Search From Before
 *
 * Fires before the opening markup for the [matador_search] shortcode and matador_search() function.
 *
 * @since 3.0.0
 */
do_action( 'matador_search_form_before' );
?>

<div class="<?php matador_build_classes( 'matador-search-form-container', $class ); ?>">

	<?php
	/**
	 * Action: Matador Search From Before Form
	 *
	 * Fires after opening markup and before the form for the [matador_search] shortcode and matador_search() function.
	 *
	 * @since 3.3.0
	 */
	do_action( 'matador_search_form_before_form' );
	?>

	<form role="search" method="get" class="matador-search-form"
		action="<?php echo esc_url( matador_get_the_jobs_link() ); ?>">

		<?php
		/**
		 * Action: Matador Search From Before Fields
		 *
		 * Fires before all fields inside the <form> tag of the [matador_search] shortcode and matador_search()
		 * function.
		 *
		 * @since 3.3.0
		 */
		do_action( 'matador_search_form_before_fields' );
		?>

		<?php foreach ( $fields as $field ) : ?>

			<?php
			/**
			 * Action: Matador Search From Before Field
			 *
			 * Fires before a given field in the [matador_search] shortcode and matador_search() function form.
			 *
			 * @since 3.3.0
			 */
			do_action( 'matador_search_form_before_field', $field );
			?>

			<?php if ( 'keyword' === $field ) : ?>

				<?php matador_get_template_part( 'jobs-search-field', 'keyword' ); ?>

			<?php elseif ( 'id' === $field ) : ?>

				<?php matador_get_template_part( 'jobs-search-field', 'id' ); ?>

			<?php elseif ( 'reset' === $field ) : ?>

				<?php matador_get_template_part( 'jobs-search-field', 'reset' ); ?>

			<?php elseif ( in_array( $field, Job_Taxonomies::registered_taxonomies(), true ) ) : ?>

				<?php matador_get_template_part( 'jobs-search-field', 'taxonomy', array( 'field' => $field ) ); ?>

			<?php else : ?>

				<?php
				/**
				 * Action: Matador Search From Field
				 *
				 * Fires if a field is passed that is not a taxonomy or 'text'. Assuming all fields are whitelisted per
				 * the function that calls this template, this is how developers can add additional fields to the search
				 * form.
				 *
				 * @since 3.3.0
				 */
				do_action( 'matador_search_form_field', $field );
				?>

			<?php endif; ?>

			<?php
			/**
			 * Action: Matador Search From After Field
			 *
			 * Fires after a given field in the [matador_search] shortcode and matador_search() function form.
			 *
			 * @since 3.3.0
			 */
			do_action( 'matador_search_form_after_field', $field );
			?>

		<?php endforeach; ?>

		<?php
		/**
		 * Action: Matador Search From Before Submit
		 *
		 * Fires after opening markup and before the form for the [matador_search] shortcode and matador_search()
		 * function.
		 *
		 * @since 3.3.0
		 */
		do_action( 'matador_search_form_before_submit' );
		?>

		<?php matador_get_template_part( 'jobs-search-field', 'submit' ); ?>

		<?php
		/**
		 * Action: Matador Search From After Fields
		 *
		 * Fires after all fields, including the submit button, inside the <form> tag of the [matador_search] shortcode
		 * and matador_search() function.
		 *
		 * @since 3.3.0
		 */
		do_action( 'matador_search_form_after_fields' );
		?>

	</form>

	<?php
	/**
	 * Action: Matador Search From After Form
	 *
	 * Fires before closing markup and after the form for the [matador_search] shortcode and matador_search() function.
	 *
	 * @since 3.3.0
	 */
	do_action( 'matador_search_form_after_form' );
	?>

</div>

<?php
/**
 * Action: Matador Search From After
 *
 * Fires after closing markup for the [matador_search] shortcode and matador_search() function.
 *
 * @since 3.0.0
 */
do_action( 'matador_search_form_after' );
?>
