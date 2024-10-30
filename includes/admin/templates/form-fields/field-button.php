<?php
/**
 * Admin Template Part : Button
 *
 * Admin button. This template can not overridden.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates/Parts
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

/**
 * Defined before include:
 * @var string $type
 * @var string $name
 * @var string $value
 * @var string $class
 * @var string $label
 */
?>

<button
	type="<?php echo empty( $type ) ? 'submit' : esc_attr( $type ); ?>"
	<?php echo ! empty( $name ) ? 'name="' . esc_attr( $name ) . '"' : null; ?>
	<?php echo ! empty( $value ) ? 'value="' . esc_attr( $value ) . '"' : null; ?>
	<?php echo ! empty( $novalidate ) ? 'formnovalidate' : null; ?>
	class="<?php echo empty( $class ) ? 'button-primary' : esc_attr( $class ); ?>">
	<?php echo esc_html( $label ); ?>
</button>
