<?php
/**
 * Admin Template : Settings
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

?>

<div class="wrap">

	<?php
	/**
	 * Matador Settings Page Settings Structure
	 *
	 * @wordpress-filter
	 *
	 * @since 3.8.15
	 *
	 * @param string $structure String for the desired settings structure. Default 'settings'.
	 * @return string
	 */
	$settings_structure = apply_filters( 'matador_settings_page_structure', 'settings' );
	$settings_fields    = Settings_Fields::instance()->get_settings_fields_with_structure( $settings_structure );
	?>

	<h1 class="matador-settings-page-title">
		<?php echo esc_html( apply_filters( 'matador_settings_page_title', esc_html__( 'Matador Settings', 'matador-jobs' ) ) ); ?>
	</h1>

	<h2 class="matador-nav-tabs nav-tab-wrapper">

		<?php

		$tab_index = 1;

		foreach ( Settings_Fields::instance()->get_settings_tabs() as $tab => $label ) {

			/**
			 * Dynamic Filter: Modify the tab label.
			 *
			 * @since   3.0.0
			 *
			 * @param   string $label Tab Label
			 */
			$label = apply_filters( 'matador_settings_tab_label' . $tab, esc_attr( $label ) );

			$active = ( 1 === $tab_index ++ ) ? 'nav-tab-active' : '';

			echo wp_kses_post( '<a href="#matador-settings-tab-' . sanitize_key( $tab ) . '" class="nav-tab ' . $active . ' ">' . $label . '</a>' );
		}

		unset( $tab_index );

		?>

	</h2>

	<form method="post" id="general_options_form" class="matador-settings-form">

		<?php

		wp_nonce_field( Matador::variable( 'options', 'nonce' ) );

		$tab_index = 1;
		foreach ( $settings_fields as $tab => $sections ) {

			?>


			<div id="matador-settings-tab-<?php echo esc_attr( $tab ); ?>"
				class="matador-settings-tab tab-container"
				<?php echo ( 1 === $tab_index ++ ) ? '' : 'style="display:none"'; ?> >

				<?php
				/**
				 * Dynamic Action: Adds content before first tab sections.
				 *
				 * @since   3.0.0
				 */
				do_action( "matador_settings_before_tab_$tab" );

				foreach ( $sections[1] as $section => $fields ) {

					?>

					<div id="matador-settings-section-<?php echo esc_attr( $section ); ?>" class="matador-settings-section">


						<h4 class="matador-settings-section-title matador-settings-section-title-<?php echo esc_attr( $section ); ?>">
							<?php
							/**
							 * Dynamic Filter: Modify the title of the section.
							 *
							 * @since   3.0.0
							 *
							 * @param   string $fields first item in array is General Tab Title
							 */
							echo esc_html( apply_filters( 'matador_settings_section_title_' . $section, $fields[0] ) );
							?>
						</h4>

						<?php
						/**
						 * Dynamic Action: Add content before the fields of the section.
						 *
						 * @since   3.0.0
						 */
						do_action( "matador_settings_section_before_$section" );

						foreach ( $fields as $field => $args ) {

							if ( is_array( $args ) ) {

								/**
								 * Matador Admin Settings Should Skip Field
								 *
								 * @wordpress-filter matador_settings_should_skip_field
								 *
								 * @since 3.8.15
								 *
								 * @param bool $skip True or false whether to skip field. Defaults false.
								 * @param string $field Key name of field.
								 * @param array $args Array of field args.
								 * @return bool
								 */
								$skip = apply_filters( 'matador_settings_should_skip_field', false, $field, $args );

								/**
								 * Matador Admin Settings Should Skip {Field}
								 *
								 * @wordpress-filter matador_settings_should_skip_{$field}
								 *
								 * @since 3.8.15
								 *
								 * @param bool $skip True or false whether to skip field. Defaults false.
								 * @param array $args Array of field args.
								 * @return bool
								 */
								$skip = apply_filters( "matador_settings_should_skip_$field", $skip, $args );

								if ( $skip ) {

									continue;
								}

								/**
								 * Matador Admin Settings Before {Each} Field
								 *
								 * @wordpress-action matador_settings_before_field_{$field}
								 *
								 * @since 3.8.2
								 */
								do_action( 'matador_settings_before_field_' . $field );

								list( $args, $template ) = Options::form_field_args( $args, $field );

								Template_Support::get_template_part( 'field', $template, $args, 'form-fields', true, true );

								/**
								 * @wordpress-action Matador Admin Settings After {Each} Field
								 *
								 * @since 3.8.2
								 */
								do_action( 'matador_settings_after_field_' . $field );
							}

						}

						/**
						 * Dynamic Action: Add content before the fields of the section.
						 *
						 * @since   3.0.0
						 */
						do_action( 'matador_settings_section_after_' . $section );

						?>

					</div>

					<?php

				} // End foreach().

				/**
				 * Dynamic Action: Adds content after last tab sections.
				 *
				 * @since   3.0.0
				 */
				do_action( 'matador_settings_after_tab_' . $tab );

				?>

			</div>

			<?php

		} // End foreach().

		unset( $i );

		?>

		<input type="hidden" value="1" name="admin_notices">
		<input type="submit" name="general_options_submit" id="general-options-form-submit" class="button button-primary" value="<?php echo esc_html__( 'Save Changes', 'matador-jobs' ); ?>">

	</form>

</div>
