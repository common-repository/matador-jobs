<?php
/**
 * Template: Admin Notification for Bullhorn Disconnected Email
 *
 * @link        https://matadorjobs.com/
 *
 * @since       3.2.0
 * @since       3.8.0 added 'url' variable
 *
 * @package     Matador Jobs
 * @subpackage  Templates / Emails
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2022, Matador Software, LLC
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
 * - url
 * - error
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
 * @var string $error
 * @var string $url
 */
?>

<p><em><?php esc_html_e( 'Notice for', 'matador-jobs' ); ?>:</em> {{sitename}} ({{url}})</p>

<p><?php esc_html_e( 'Hello Administrator', 'matador-jobs' ); ?>,</p>

<p><?php esc_html_e( 'Matador Jobs Plugin on is experiencing a connection issue on your site that requires your intervention.', 'matador-jobs' ); ?></p>

<p><?php esc_html_e( 'Matador Jobs sends this email to administrators after three consecutive connection failures, and once daily thereafter.', 'matador-jobs' ); ?></p>

<p><?php esc_html_e( 'Below is a copy of the error message, which may be helpful in resolving the issue.', 'matador-jobs' ); ?></p>

<p><em>{{error}}</em></p>

<p>
	<?php esc_html_e( 'For advice on resolving connection issues, review our help document on the topic.', 'matador-jobs' ); ?>
	<a href="https://docs.matadorjobs.com/articles/reconnecting-reauthorizing-matador-with-bullhorn/"><?php esc_html_e( 'Go to help docs.', 'matador-jobs' ); ?></a>
</p>
