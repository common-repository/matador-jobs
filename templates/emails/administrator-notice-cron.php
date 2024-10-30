<?php
/**
 * Template: Admin Notification for Cron Error Email
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.0
 *
 * @package     Matador Jobs
 * @subpackage  Templates / Emails
  * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Mustache Templating
 *
 * This template is post-processed with Mustache templating engine.
 *
 * The following variables are available to Mustache:
 *
 * - sitename
 *
 * To use Mustache templating:
 *
 * - Include variable inline with text in double curly braces for HTML-escaped text, eg: "Hello {{name}}!"
 * - Include variable inline with text in triple curly braces for unescaped text, eg: "Hello {{{name}}}!"
 * - Include variable array with a dot to read the array's key's value, eg: "City: {{address.city}}, {{address.state}}.
 *
 * For more help, @see https://github.com/bobthecow/mustache.php/wiki/Mustache-Tags
 */

/**
 * These PHP variables are also available to this template, defined before includes:
 *
 * @var string $sitename
 */
?>

<p><?php esc_html_e( 'Hello Administrator', 'matador-jobs' ); ?>,</p>

<p><?php esc_html_e( 'Matador Jobs Plugin on is experiencing a cron issue on your site that requires your intervention.', 'matador-jobs' ); ?></p>

<p><?php esc_html_e( 'Matador Jobs sends this email to administrators after a cron task, which includes your regular sync to Bullhorn, is at least 10 minutes late, and once daily thereafter.', 'matador-jobs' ); ?></p>

<p>
	<?php esc_html_e( 'For advice on resolving cron issues, review our help document on the topic.', 'matador-jobs' ); ?>
	<a href="https://docs.matadorjobs.com/articles/understanding-the-wp-cro"><?php esc_html_e( 'Go to help docs.', 'matador-jobs' ); ?></a>
</p>
