<?php
/**
 * Matador / Settings
 *
 * This contains the settings structure and provides functions to manipulate saved settings.
 * This class is extended to create and validate field input on the settings page.
 *
 * @link        https://matadorjobs.com/
 * @since       3.1.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Admin
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2018-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

final class Settings_Actions {

	/**
	 * Magic Method: Constructor
	 *
	 * Class constructor prepares 'key' and 'data' variables.
	 *
	 * @since 3.1.0
	 */
	public function __construct() {
		add_action( 'matador_options_after_set', array( __CLASS__, 'trigger_rewrite_flush' ), 10, 2 );
		add_action( 'matador_options_after_set', array( __CLASS__, 'trigger_cache_flush' ), 10, 2 );
		add_action( 'matador_options_before_set_license_core', [ __CLASS__, 'deactivate_license' ], 10, 2 );
	}

	/**
	 * Trigger Rewrites Flush
	 *
	 * Determines if a field assigned as a rewrite is valid, and if so,
	 * sets a transient to flush rewrite rules.
	 *
	 * @since 3.1.0
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return void
	 */
	public static function trigger_rewrite_flush( $key, $value ) {

		if ( ! $value && ! $key ) {
			return;
		}

		$triggers = array( 'post_type_slug_job_listing' );

		foreach ( Matador::variable( 'job_taxonomies' ) as $name => $taxonomy ) {
			$triggers[] = strtolower( 'taxonomy_slug_' . $name );
		}

		/**
		 * Filter: Rewrite Triggers
		 *
		 * Allows us to add settings keys to the list of triggers, so that
		 * when the setting is changed, a flush_rewrite_rules will be triggered
		 * on the next admin page load.
		 *
		 * @since 3.1.0
		 */
		$triggers = apply_filters( 'matador_options_rewrite_triggers', $triggers );

		if ( in_array( strtolower( $key ), $triggers, true ) ) {
			new Event_Log( 'options-trigger-rewrite', __( 'A setting that affects rewrites was changed. Rewrites will be flushed on next admin load.', 'matador-jobs' ) );
			set_transient( Matador::variable( 'flush_rewrite_rules', 'transients' ), true, 30 );
		}
	}

	/**
	 * Trigger Cache Flush
	 *
	 * Determines if a field should trigger a cache flush, and if so, removes the cache trigger before
	 * next sync.
	 *
	 * @since 3.6.0
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return void
	 */
	public static function trigger_cache_flush( $key, $value ) {

		unset( $value );

		$cache_triggers = array(
			'bullhorn_description_field',
			'bullhorn_is_public',
			'bullhorn_category_field',
			'jsonld_hiring_organization',
			'jsonld_salary',
			'post_type_slug_job_listing_each',
			'notify_type',
			'bullhorn_date_field',
			'bullhorn_location_remote_term',
			'bullhorn_location_term',
		);

		/**
		 * Filter: cache Triggers
		 *
		 * Allows us to add settings keys to the list of triggers, so that
		 * when the setting is changed, a the date last modified for jobs is removed
		 * so on the next sync all posts are updated.
		 *
		 * @since 3.6.0
		 */
		$cache_triggers = apply_filters( 'matador_options_rewrite_cache_triggers', $cache_triggers );

		if ( in_array( strtolower( $key ), $cache_triggers, true ) ) {
			delete_metadata( 'post', 0, '_matador_source_date_modified', '', true );
			new Event_Log( 'options-trigger-date-last-modified-cleared', __( 'A setting that affects job content was changed. A full sync will will be conducted at the next scheduled time.', 'matador-jobs' ) );
		}
	}

	/**
	 * Deactivate Licenses On Setting Change
	 *
	 * Triggers License deactivation on a change.
	 *
	 * @since 3.1.0
	 * @since 3.8.0. Updated parameters and largely moved logic for deactivation away
	 *
	 * @param string $new
	 * @param string $old
	 *
	 * @return string
	 */
	public static function deactivate_license( $new = '', $old = '' ) {

		if ( $new !== $old && 'valid' === Matador::setting( 'license_core_status' ) ) {
			Updater::deactivate( $old );
		}

		return $new;
	}
}
