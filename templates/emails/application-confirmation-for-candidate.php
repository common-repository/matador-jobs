<?php
/**
 * Template: Application Confirmation for Candidate (Email)
 *
 * Override this theme by copying it to yourtheme/matador/application-confirmation-for-candidate.php.
 *
 * Use this template to inject HTML and structure or override the email content.
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0, as 'email-applicant-content.php'
 * @since       3.2.0, as 'application-confirmation-for-candidate.php'
 *
 * @package     Matador Jobs
 * @subpackage  Templates
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2020 Jeremy Scott, Paul Bearne
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
 * - firstname
 * - lastname
 * - fullname
 * - email
 * - phone
 * - message
 * - sitename
 * - address (as array, use Mustache dot notation)
 * - application (as array, use Mustache dot notation)
 * - content (HTML formatted human-readable application with human-relevant fields, eg: name, not ip address)
 * - applied_jobs (HTML formatted list or applied job(s))
 *
 * ** Extensions may provide additional fields. **
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
 * @var string $firstname
 * @var string $lastname
 * @var string $fullname
 * @var string $email
 * @var string $phone
 * @var string $message
 * @var string $post_content
 * @var string $sitename
 * @var array $address
 * @var array $application full Application array
 *
 * ** Extensions may provide additional fields. **
 */
?>

<p><?php esc_html_e( 'Hello', 'matador-jobs' ); ?> {{firstname}}!</p>

<p>
	<?php esc_html_e( 'Thank you for your application with {{sitename}}.', 'matador-jobs' ); ?>
</p>

{{#application.jobs}} <!-- If this applicant applied to a job. -->
<p>
	<?php esc_html_e( 'We received your application for the following role(s):', 'matador-jobs' ); ?>
</p>
{{{applied_jobs}}}
{{/application.jobs}} <!-- End if this applicant applied to a job. -->

<p><?php esc_html_e( 'Below is a copy of the information you provided during your application:', 'matador-jobs' ); ?></p>

{{{post_content}}}

<p><?php esc_html_e( 'We will review your application and reach out if we find a match or require more information.', 'matador-jobs' ); ?></p>
