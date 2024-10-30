<?php
/**
 * Template: Job Partial for Jobs Table
 *
 * Override this theme by copying it to /wp-content/themes/{yourtheme}/matador/parts/jobs-table-job.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.4.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates/Parts
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
 * @var $index
 * @var $args
 */

$context = 'table';

/**
 * Action Matador Job Before
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param string $context
 * @param int    $index
 */
do_action( 'matador_job_before', $context, $index );

$classes = array(
	'matador-job',
	'matador-job-' . $context,
	'matador-job-' . get_the_ID(),
	'matador-job-nth-' . $index,
	0 === $index % 2 ? 'matador-job-even' : 'matador-job-odd',
);
?>

<tr class="<?php matador_build_classes( $classes ); ?>">

	<?php foreach ( $fields as $field => $label ) : ?>

		<td class="<?php echo 'matador-job-' . esc_attr( $field ); ?>">

			<?php

			switch ( $field ) :

				// Fields that might be passed into $fields array but don't apply to table layout.
				case 'info':
					break;

				case 'title':
					matador_the_job_title_link( get_the_ID(), 'table-cell' );
					break;

				case 'content':
					matador_the_job_description( get_the_ID(), $content_limit, 'table-cell' );
					break;

				case 'link':
					matador_the_job_navigation( get_the_ID(), 'table-cell' );
					break;

				case 'date':
					matador_the_job_posted_date( null, get_the_ID(), 'table-cell' );
					break;

				default:
					if ( has_filter( "matador_job_field_$field" ) ) {
						do_action( "matador_job_field_$field", $context );
					} else {
						matador_the_job_meta( $field, get_the_ID(), $context );
					}
					break;

			endswitch;

			?>

		</td>

	<?php endforeach; ?>

</tr>

<?php
/**
 * Matador Jobs After Job
 *
 * @since 3.0.0
 * @since 3.4.0 added params
 *
 * @param int    $index
 * @param string $context
 */
do_action( 'matador_job_after', $context, $index );
