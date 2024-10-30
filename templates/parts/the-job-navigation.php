<?php
/**
 * Template: The Job Navigation
 *
 * Controls the output of the matador_the_job_navigation() function which is used by various templates and functions.
 * Override this in your theme by copying it to wp-content/themes/your-theme-folder/matador/parts/the-job-navigation.php
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

/**
 * Defined before include:
 *
 * @var int|null $id ID of the Job
 * @var array $buttons Array of buttons.
 * @var string $rule Conditional rule that established the set of buttons.
 * @var string $context Template context.
 */
?>

<?php
/**
 * Action Matador Template Job Navigation Before
 *
 * @since 3.4.0
 *
 * @param int|null $id ID of the Job
 * @param array $buttons Array of buttons.
 * @param string $rule Conditional rule that established the set of buttons.
 * @param string $context Template context.
 */
do_action( 'matador_template_job_navigation_before', $buttons, $rule, $context, $id );
?>

<nav class="matador-job-navigation">

	<?php
	/**
	 * Action Matador Template Job Navigation Before List
	 *
	 * @since 3.4.0
	 *
	 * @param int|null $id ID of the Job
	 * @param array $buttons Array of buttons.
	 * @param string $rule Conditional rule that established the set of buttons.
	 * @param string $context Template context.
	 */
	do_action( 'matador_template_job_navigation_before_list', $buttons, $rule, $context, $id );
	?>

	<ul>

		<?php
		/**
		 * Action Matador Template Job Navigation List Start
		 *
		 * @since 3.4.0
		 *
		 * @param int|null $id ID of the Job
		 * @param array $buttons Array of buttons.
		 * @param string $rule Conditional rule that established the set of buttons.
		 * @param string $context Template context.
		 */
		do_action( 'matador_template_job_navigation_list_start', $buttons, $rule, $context, $id );
		?>

		<?php foreach ( $buttons as $button => $label ) : ?>

			<?php
			/**
			 * Action Matador Template Job Navigation Before List Item
			 *
			 * @since 3.4.0
			 *
			 * @param int|null $id ID of the Job
			 * @param string $button The name of the next button.
			 * @param string $rule Conditional rule that established the set of buttons.
			 * @param string $context Template context.
			 */
			do_action( 'matador_template_job_navigation_list_before', $button, $rule, $context, $id );
			?>

			<?php
			switch ( $button ) :
				case 'job':
					$url = matador_get_the_job_link( $id );
					break;
				case 'apply':
					$url = matador_get_the_job_apply_link( $id );
					break;
				case 'jobs':
					$url = matador_get_the_jobs_link();
					break;
				default:
					$url = '';
					break;
			endswitch;

			/**
			 * Filter Matador Template Job Navigation Button URL
			 *
			 * @since 3.4.0
			 *
			 * @param string $url
			 * @param string $button
			 * @param int|null $id ID of the Job
			 * @param string $rule Conditional rule that established the set of buttons.
			 * @param string $context Template context.
			 *
			 * @return string $url
			 */
			$url = apply_filters( 'matador_template_job_navigation_button_url', $url, $button, $id, $rule, $context );
			?>

			<?php if ( ! empty( $url ) ) : ?>

				<?php
				$i = isset( $i ) ? $i : 0;
				if ( 0 === $i++ ) {
					$button_class   = 'matador-button';
					$button_context = 'primary';
				} else {
					$button_class   = 'matador-button matador-button-secondary';
					$button_context = 'secondary';
				}
				?>

				<li>
					<a href="<?php echo esc_url( $url );?>" rel="button"
                       class="<?php matador_button_classes( $button_class, $button_context  );?>"
						title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>

			<?php endif; ?>

			<?php
			/**
			 * Action Matador Template Job Navigation List After
			 *
			 * @since 3.4.0
			 *
			 * @param int|null $id ID of the Job
			 * @param string $button The name of the next button.
			 * @param string $rule Conditional rule that established the set of buttons.
			 * @param string $context Template context.
			 */
			do_action( 'matador_template_job_navigation_list_after', $button, $rule, $context, $id );
			?>

		<?php endforeach; ?>

		<?php
		/**
		 * Action Matador Template Job Navigation End of List
		 *
		 * @since 3.4.0
		 *
		 * @param int|null $id ID of the Job
		 * @param array $buttons Array of buttons.
		 * @param string $rule Conditional rule that established the set of buttons.
		 * @param string $context Template context.
		 */
		do_action( 'matador_template_job_navigation_list_end', $buttons, $rule, $context, $id );
		?>

	</ul>

	<?php
	/**
	 * Action Matador Template Job Navigation After List
	 *
	 * @since 3.4.0
	 *
	 * @param int|null $id ID of the Job
	 * @param array $buttons Array of buttons.
	 * @param string $rule Conditional rule that established the set of buttons.
	 * @param string $context Template context.
	 */
	do_action( 'matador_template_job_navigation_after_list', $buttons, $rule, $context, $id );
	?>

</nav>

<?php
/**
 * Action Matador Template Job Navigation After
 *
 * @since 3.4.0
 *
 * @param int|null $id ID of the Job
 * @param array $buttons Array of buttons.
 * @param string $rule Conditional rule that established the set of buttons.
 * @param string $context Template context.
 */
do_action( 'matador_template_job_navigation_after', $buttons, $rule, $context, $id );
?>
