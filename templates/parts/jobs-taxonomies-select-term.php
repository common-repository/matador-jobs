<?php
/**
 * Template: Taxonomy Terms as Select Term
 *
 * Override this theme by copying it to wp-content/themes/{yourtheme}/matador/parts/jobs-taxonomies-select-term.php.
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
 * @var array   $taxonomy
 * @var WP_Term $term
 * @var string  $as
 * @var string  $method
 * @var bool    $multi
 * @var bool    $show_all_option
 * @var mixed   $class
 * @var array   $terms
 * @var array   $args (contains all the above in one array)
 */
?>

<?php
$url     = matador_get_term_link( $term, $taxonomy['key'], $method, $multi );
$current = matador_is_current_term( $term, $taxonomy['key'] ) ? true : false;
?>

<option value="<?php echo esc_attr( $term->slug ); ?>"
	<?php echo 'value' !== $method ? 'data-url="' . esc_url( $url ) . '"' : ''; ?>
	<?php selected( $current ); ?>
>
	<?php
	/**
	 * Filter: Matador Taxonomy Term Term Label
	 *
	 * @since 3.3.0
	 *
	 * @var string
	 * @var \WP_Term $term
	 * @var array $args
	 */
	echo esc_html( apply_filters( 'matador_taxonomy_terms_term_label', $term->name, $term, $args ) );
	?>
</option>
