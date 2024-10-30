<?php
/**
 * Template: Admin Notification for Consent Object Permission
 *
 * @link        https://matadorjobs.com/
 * @since       3.7.7
 *
 * @package     Matador Jobs
 * @subpackage  Templates / Emails
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software LLC
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use matador\Matador;

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

<p>
	<?php esc_html_e( 'The Matador Jobs Plugin is unable to save Candidate Consent data due to a permissions issue.', 'matador-jobs' ); ?>
	<?php esc_html_e( 'To minimize disruptions, for the next 24 hours, new applications will continue to be processed into Bullhorn without Candidate Consent data.', 'matador-jobs' ); ?>
	<?php esc_html_e( 'We strongly recommend you resolve this issue as soon as possible to remain compliant with user data privacy and consent law, regulations, and policies.', 'matador-jobs' ); ?>
</p>

<p>
	<?php echo sprintf( '<a href="%s">%s</a>', esc_html__( 'For more information on this issue, including how to resolve it, please visit our help documentation article on the topic.', 'matador-jobs' ), esc_url( 'https://docs.matadorjobs.com/articles/candidate-consent-management/' ) ); ?>
</p>

<p>
<?php esc_html_e( "If after following the instructions in the help document, you determine you need to contact Bullhorn support, the following information will be required.", 'matador-jobs' ); ?>
</p>
<ul>
	<li>
		<strong><?php esc_html_e( 'API Client ID', 'matador-jobs' ); ?></strong>: <?php echo esc_attr( Matador::credential( 'bullhorn_api_client' ) ); ?>
	</li>
	<li>
		<strong><?php esc_html_e( 'API Username', 'matador-jobs' ); ?></strong>: <?php echo esc_attr( Matador::credential( 'bullhorn_api_user' ) ); ?>
	</li>
	<?php
	/**
	 * Matador Applicant Candidate Consent Object Name
	 *
	 * @see Application_Sync::candidate_consent() for Documentation
	 */
	$consent_object_location = apply_filters( 'matador_applicant_candidate_consent_object_name', '' ) ?: get_transient( Matador::variable( 'consent_object' , 'transients' ) );

	if ( $consent_object_location ) :
	?>
	<li>
		<strong><?php esc_html_e( 'Consent Object "Name"', 'matador-jobs' ); ?></strong>: <?php echo esc_attr( $consent_object_location ); ?>
	</li>
	<?php endif; ?>
</ul>
