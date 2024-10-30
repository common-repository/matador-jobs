<?php
/**
 * Template: Job Aside
 *
 * Controls the output of the matador_job_aside and [matador_job] shortcode. Override this theme by copying it to
 * wp-content/themes/your-theme-folder/matador/job-aside.php.
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
 * @var string|array $class         Classes for the aside object.
 * @var int          $content_limit Limit for excerpt
 * @var bool         $job_info      Whether to show the job info.
 * @var bool         $apply_buttons Whether to show the apply button(s)/job navigation.
 */

$context = 'aside';

/**
 * Matador Job Before (Action)
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param string $context
 * @param int    $index
 */
do_action( 'matador_job_before', 'aside', 0 );
?>

<aside class="<?php matador_build_classes( 'matador-job-aside', $class ); ?>">

	<?php
	/**
	 * Matador Job Before Content
	 *
	 * After the jobs' wrapper, before the template content.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added param $context
	 *
	 * @param string $context
	 */
	do_action( 'matador_job_before_content', $context );
	?>

	<header class="matador-job-header">

		<?php if ( isset( $fields['title'] ) && $fields['title'] ) : ?>
			<h4 class="entry-title matador-job-title">
				<?php matador_the_job_title_link( null, $context ); ?>
			</h4>
		<?php endif; ?>

		<?php if ( isset( $fields['info'] ) && $fields['info'] ) : ?>
			<?php matador_the_job_info( array(), array(), $context ); ?>
		<?php else : ?>
			<?php matador_the_job_info( $fields, array(), $context ); ?>
		<?php endif; ?>

		<?php
		/**
		 * Matador Job Header
		 *
		 * After the title and the Meta Header, before </header>
		 *
		 * @since 3.4.0
		 *
		 * @param string $context
		 */
		do_action( 'matador_job_header', $context );
		?>

	</header>

	<div class="matador-job-description">

		<?php if ( isset( $fields['content'] ) && $fields['content'] ) : ?>
			<p><?php matador_the_job_description( null, $content_limit, $context ); ?></p>
		<?php endif; ?>

		<?php
		/**
		 * Matador Job Content
		 *
		 * After the content, if included, and before </div class='matador-job-description'>
		 *
		 * @since 3.4.0
		 *
		 * @param string $context
		 */
		do_action( 'matador_job_content', $context );
		?>

	</div>

	<footer class="matador-job-footer">

		<?php if ( isset( $fields['link'] ) && $fields['link'] ) : ?>
			<?php matador_the_job_navigation(); ?>
		<?php endif; ?>

		<?php
		/**
		 * Matador Job Footer
		 *
		 * After the job navigation, if included, and before </footer>
		 *
		 * @since 3.4.0
		 *
		 * @param string $context
		 */
		do_action( 'matador_job_footer', $context );
		?>

	</footer>

	<?php
	/**
	 * Matador Job After Content
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added param $context
	 *
	 * @param string $context
	 */
	do_action( 'matador_job_after_content', $context );
	?>
</aside>

<?php
/**
 * Matador Job After (Action)
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param int    $index
 * @param string $context
 */
do_action( 'matador_job_after', $context, 0 );
