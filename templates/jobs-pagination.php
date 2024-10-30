<?php
/**
 * Template: Jobs Pagination
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/jobs-pagination.php.
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
 * @var array $args
 */

$args['before_page_number'] = '<span class="matador-screen-reader-text">' . esc_html__( 'Page ', 'matador-jobs' ) . ' </span>';
$args['after_page_number']  = '';
?>

<div class="matador-pagination">
	<?php echo wp_kses_post( paginate_links( $args ) ); ?>
</div>
