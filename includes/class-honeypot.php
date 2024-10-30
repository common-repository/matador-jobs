<?php
/**
 * Matador / Honeypot
 *
 * This class creates and handles the Anti-Spam Honeypot for Matador's forms.
 *
 * @link        https://matadorjobs.com/
 * @since       3.3.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Core
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

/**
 * Class Honeypot
 *
 * @since  3.3.0
 */
class Honeypot {

	/**
	 * Variable: Honey
	 *
	 * Stores the key (name) of the Honey Pot honey field
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private static $honey = 'job-seeker';

	/**
	 * Method: Constructor
	 *
	 * Class constructor. Adds filters and actions.
	 * @since 3.3.0
	 */
	public function __construct() {
		if ( Matador::setting( 'applications_honeypot' ) ) {
			add_action( 'matador_application_after_fields', array( __CLASS__, 'trap' ) );
			add_action( 'matador_application_handler_start', array( __CLASS__, 'reject' ) );
			add_filter( 'matador_application_handler_start_ignored_fields', array( __CLASS__, 'ignore' ) );
		}
	}

	/**
	 * Method: Trap
	 *
	 * Lays the trap.
	 *
	 * @since 3.3.0
	 */
	public static function trap() {
		list( $args, $template ) = Helper::form_field_args( array(
			'type'       => 'text',
			'label'      => __( 'People looking for jobs should not put anything here.', 'matador-jobs' ),
			'class'      => self::$honey,
			'attributes' => array(
				'tabindex' => '-1',
			),
		), self::$honey );
		Template_Support::get_template_part( 'field', $template, $args, 'form-fields', false, true );
	}

	/**
	 * Method: Catch
	 *
	 * Catches the spam bots when they trigger the trap.
	 *
	 * @since 3.3.0
	 *
	 * @var array $request
	 */
	public static function reject( $request ) {
		if ( ! empty( $request[ self::$honey ] ) ) {
			new Event_Log( 'honeypot-caught-spam', __( 'Matador detected a user filling out the anti-spam fields and rejected the application.', 'matador-jobs' ) );
			// Purposely ambiguous error message. Don't want the cheaters knowing why.
			wp_die( esc_html( __( 'There was an error. Contact the site administrator.', 'matador-jobs' ) ) );
		}
	}

	/**
	 * Method: Ignore
	 *
	 * Adds the honey field to the list of ignored form inputs
	 *
	 * @since 3.3.0
	 *
	 * @var array $ignored
	 *
	 * @return array
	 */
	public static function ignore( $ignored ) {
		$ignored[] = self::$honey;
		return $ignored;
	}
}
