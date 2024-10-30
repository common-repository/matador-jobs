<?php
/**
 * Matador / Sync / Job Sync Trait
 *
 * Contains shared functions related to syncing Jobs
 *
 * @link        https://matadorjobs.com/
 * @since       3.8.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Sync
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2022, Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador\MatadorJobs\Sync\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// PHP CORE
use DateTimeImmutable;
// WP CORE
use WP_Query;
// MATADOR
use matador\Matador;
use matador\Event_Log;

/**
 * Trait: Jobs
 *
 * @since 3.8.0
 */
trait LocalJobs {

	/**
	 * Get Local Jobs IDs
	 *
	 * @uses https://developer.wordpress.org/reference/classes/wp_query/
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	protected function get_local_jobs() : bool {

		$this->data['existing'] = [];
		$this->data['duplicates'] = [];

		while ( true ) {

			// Things we need
			$limit    = 100;
			$offset   = $offset ?? 0;
			$existing = $existing ?? [];
			$dupes    = $dupes ?? [];

			// WP Query Args.
			$args = [
				'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			];

			// Add WP Query Args for Source, if provided
			if ( ! empty( static::$source ) ) {
				$args['meta_query'] = [
					'relation' => 'AND',
					[
						'key'     => '_matador_source',
						'value'   => static::$source,
						'compare' => '=',
					],
					[
						'key'     => '_matador_source_id',
						'compare' => 'EXISTS',
						'type'    => 'NUMERIC',
					],
				];
			}

			// WP Query
			$posts = new WP_Query( $args );

			if ( $posts->have_posts() && ! is_wp_error( $posts ) ) {

				foreach ( $posts->posts as $post_id ) {

					$source_id = get_post_meta( $post_id, '_matador_source_id', true );

					if ( isset( $this->data['existing'][ $source_id ] ) ) {
						$this->data['duplicates'][] = $post_id;
					} else {
						$this->data['existing'][ $source_id ] = $post_id;
					}
				}

				// If the size of the result is less than the limit, break, otherwise increment and re-run
				if ( $posts->post_count < $limit ) {

					break;
				}

				$offset += $limit;

			} else {

				break;
			}
		}

		new Event_Log( 'sync-found-local-jobs', 'Sync found ' . count( $this->data['existing'] ). ' existing local jobs and ' . count( $this->data['duplicates'] ) . ' duplicate jobs' );

		$posts = null;

		wp_reset_postdata();

		return true;
	}

	/**
	 * Get Local Job
	 */

	/**
	 * Get Latest Synced Local Job
	 *
	 * @since 3.8.0
	 *
	 * @return DateTimeImmutable
	 */
	protected function get_latest_synced_job() : DateTimeImmutable {

		if ( ! empty( $this->data['latest_sync'] ) ) {
			return $this->data['latest_sync'];
		}

		$this->data['latest_sync'] = (new DateTimeImmutable())->setTimestamp( 0 );

		if ( get_transient( Matador::variable( 'doing_sync_no_cache', 'transients' ) ) ) {
			return $this->data['latest_sync'];
		}

		$query_args = [
			'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'meta_key'       => '_matador_source_date_modified',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC'
		];

		// Add WP Query Args for Source, if provided
		if ( ! empty( static::$source ) ) {
			$query_args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => '_matador_source',
					'value'   => static::$source,
					'compare' => '=',
				],
				[
					'key'     => '_matador_source_id',
					'compare' => 'EXISTS',
					'type'    => 'NUMERIC',
				],
			];
		}

		$posts = new WP_Query( $query_args );

		if ( $posts->have_posts() && ! is_wp_error( $posts ) ) {
			$last_modified = get_post_meta( $posts->get_posts()[0], '_matador_source_date_modified', true );
			if ( ! empty( $last_modified ) ) {
				$this->data['latest_sync'] = (new DateTimeImmutable())->setTimestamp( (int) $last_modified );
			}
		}

		return $this->data['latest_sync'];
	}

	/**
	 * Update Job
	 */

	/**
	 * Delete Job
	 *
	 * @uses https://developer.wordpress.org/reference/functions/wp_delete_post/
	 *
	 * @since 3.8.0
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	protected static function delete_local_job( int $post_id ) : bool {

		return (bool) wp_delete_post( $post_id, true );
	}

	/**
	 * Delete Jobs
	 *
	 * @since 3.8.0
	 *
	 * @param array $post_ids
	 *
	 * @return bool
	 */
	protected static function delete_local_jobs( array $post_ids ) : bool {

		foreach ( $post_ids as $post_id ) {

			if ( ! self::delete_local_job( $post_id ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Remove Duplicate Jobs
	 *
	 * @since 3.8.0
	 *
	 * @return bool
	 */
	protected function remove_duplicate_local_jobs() : bool {

		if ( empty( $this->data['duplicates'] ) ) {

			return true;
		}

		$limit = 25;

		if ( count( $this->data['duplicates'] ) > $limit ) {
			$batch_to_delete = array_splice( $this->data['duplicates'], 0, $limit );
		} else {
			$batch_to_delete = $this->data['duplicates'];
			$this->data['duplicates'] = [];
		}

		$ids_string = implode( ', ', array_values( $batch_to_delete ) );

		new Event_Log( 'matador-bullhorn-import-duplicate-jobs-remove', sprintf( __( 'Deleting Duplicate Jobs with WordPress IDs of %1$s.', 'matador-jobs' ), $ids_string ) );

		foreach ( $batch_to_delete as $to_delete ) {

			self::delete_local_job( $to_delete );
		}

		if ( ! empty( $this->data['duplicates'] ) ) {

			return false;
		}

		return true;
	}
}