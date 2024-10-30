<?php
/**
 * Template: Taxonomy Terms as Select All Item
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-taxonomies-select-all.php.
 *
 * @link        https://matadorjobs.com/
 * @since       3.3.0, extracted from original template since 3.0.0
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
 * @var array  $taxonomy
 * @var string $as
 * @var string $method
 * @var bool   $multi
 * @var bool   $show_all_option
 * @var mixed  $class
 * @var array  $terms
 * @var array  $args (contains all the above in one array)
 */
?>

<?php
if ( $method && 'filter' === $method ) {
	$url     = remove_query_arg( $taxonomy['key'], matador\Helper::get_nopaging_url() );
	$current = empty( $_GET[ $taxonomy['key'] ] ); // WPCS: CSRF ok.
} else {
	$url     = matador_get_the_jobs_link();
	$current = ! is_tax();
}
?>
<option value=""
	<?php echo 'value' !== $method ? 'data-url="' . esc_url( $url ) . '"' : ''; ?>
	<?php selected( $current ); ?>
>
	<?php
	if ( 'filter' === $method ) {
		$label = __( 'Show All', 'matador-jobs' );
	} else {
		$label = __( 'All', 'matador-jobs' ) . ' ' . $taxonomy['plural'];
	}
	/**
	 * Filter: Matador Taxonomy All Term Name
	 *
	 * @since 3.3.0
	 *
	 * @var string
	 * @var \WP_Term $term
	 * @var array $args
	 */
	echo esc_html( apply_filters( 'matador_taxonomy_terms_all_term_label', $label, $args ) );
	?>
</option>
