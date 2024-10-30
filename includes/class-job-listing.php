<?php
/**
 * Matador / Job Listing Post Type
 *
 * @link        https://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @author      Matador Software LLC, Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017-2021 Matador Software, LLC
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

/**
 * Job Listing custom post type
 *
 * @since 3.0.0
 */
class Job_Listing {

	/**
	 * Class constructor
	 *
	 * @access  public
	 * @since   3.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'create_post_type' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'job_id_url_support' ), 10, 1 );
		add_action( 'pre_get_posts', array( __CLASS__, 'disable_feed' ), 10, 1 );

		add_action( 'current_screen', array( __CLASS__, 'current_screen' ) );
		add_action( 'admin_menu', array( $this, 'remove_add_new_from_submenu' ) );
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );

		add_filter( 'comments_open', array( $this, 'close_comments' ), 10, 2 );

		add_filter( 'matador_job_application_content', array( __CLASS__, 'job_application_content_default' ), 5 );
		add_filter( 'matador_job_detail_content', array( __CLASS__, 'job_detail_content_default' ), 5 );

		// Loads JSON LD into WP Head
		add_filter( 'wp_head', array( __CLASS__, 'jsonld' ), 5 );

		add_filter( 'manage_' . self::key() . '_posts_columns', array( $this, 'columns_add' ) );
		add_filter( 'manage_edit-' . self::key() . '_sortable_columns', array( $this, 'columns_sortable' ) );
		add_action( 'manage_' . self::key() . '_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'sort_columns' ) );

		add_action( 'pre_get_posts', array( $this, 'sort_results' ) );
		add_filter( 'pre_get_posts', array( $this, 'search_query' ) );

		add_action( 'save_post_' . self::key(), array( __CLASS__, 'save_post' ), 10, 3 );
		add_action( 'transition_post_status', array( __CLASS__, 'transition_post_status' ), 10, 3 );
		add_action( 'before_delete_post', array( __CLASS__, 'delete_post' ), 10, 2 );

		add_action( 'media_buttons', array( __CLASS__, 'add_media_button' ) );
		add_action( 'manage_posts_extra_tablenav', array( __CLASS__, 'add_sync_now_button_to_job_listings_table' ), 10, 1 );

		add_action( 'matador_save_job', array( $this, 'save_job_jsonld' ), 10, 2 );

		// @todo: write our own metabox/document data so this isn't necessary issue #228
		add_action( 'current_screen', array( __CLASS__, 'override_acf_hidden_meta' ) );

		// manual job sync
		add_action( 'wp_ajax_matador_api_job_sync', array( $this, 'matador_api_job_sync' ) );

		add_action( 'wp', function() {
			if ( is_post_type_archive( self::key() ) || is_singular( self::key() ) ) {
				remove_action( 'wp_head', 'feed_links_extra', 3 );
			}
		});
	}

	/**
	 * Override ACF's Hiding of the Custom Meta Boxes
	 *
	 * When our users load Advanced Custom Fields (ACF), that plugin as of version 5.6.1 will disable the WordPress
	 * custom fields meta box. Since our users need these, we are going to re-enable these.
	 *
	 * @todo: write our own metabox/document data so this isn't necessary (issue #228)
	 *
	 *
	 * @since 3.5.4
	 *
	 * @return void
	 */
	public static function override_acf_hidden_meta() {

		$screen = get_current_screen();

		if ( class_exists( 'ACF' ) && self::key() === $screen->post_type ) {
			add_filter( 'acf/settings/remove_wp_meta_box', '__return_false' );
		}
	}

	/**
	 * Job ID URL Support
	 *
	 * A need of some users is the ability to have a remote job id be an option in the URL scheme, primarily
	 * when they are supporting advertising via Indeed.
	 *
	 * @see Endpoints::add_query_vars() where query vars 'xid' and 'xsource' are registered
	 *
	 * @since 3.6.0
	 *
	 * @param $query
	 *
	 * @return void
	 */
	public static function job_id_url_support( $query ) {

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( ! (
			( ! empty( $query->query['post_type'] ) && self::key() === $query->query['post_type'] )
			|| ! empty( $query->query[ self::key() ] )
		) ) {
			return;
		}

		$external_id = 0;

		if ( isset( $query->query[ self::key() ] ) && is_numeric( $query->query[ self::key() ] ) ) {
			$external_id = absint( $query->query[ self::key() ] );
		} elseif ( empty( $query->query[ self::key() ] ) && get_query_var( 'xid', false ) ) {
			$external_id = absint( get_query_var( 'xid' ) );
		}

		$external_source = '';

		if ( get_query_var( 'xsource', false ) ) {
			$external_source = sanitize_key( get_query_var( 'xsource' ) );
		}

		if ( ! $external_id ) {

			return;
		}

		if ( $external_source && 'bullhorn' !== $external_source ) {

			return;
		}

		// Instantiate Variables
		global $wpdb;
		$job  = null;
		$loop = isset( $_REQUEST['loop'] ) ? (int) $_REQUEST['loop'] : 0;
		$sql  = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", '_matador_source_id', $external_id );

		if ( count( $wpdb->get_col( $sql ) ) > 0 ) {

			foreach ( $wpdb->get_col( $sql ) as $found_id ) {

				if ( $external_source && get_post_meta( $found_id, '_matador_source', true ) !== $external_source ) {
					continue;
				}

				if ( 'publish' === get_post_status( $found_id ) && self::key() === get_post_type( $found_id ) ) {
					$job = $found_id;
					break;
				}
			}
		}

		if ( empty( $job ) ) {

			if ( get_transient( Matador::variable( 'doing_sync', 'transients' ) ) ) {
				if ( $loop < 6 ) {
					Logger::add( 'info', 'request_external_id_during_sync', __( 'A user or agent requested a job page via an external id, which was not found, while a sync was running. Will hold request and redirect back.', 'matador-jobs' ) );
					sleep( 5 );
					wp_safe_redirect( add_query_arg( 'loop', ++ $loop ) );
					exit();
				} else {
					Logger::add( 'info', 'request_external_id_during_sync_fail', __( 'A user or agent requested a job page via an external id, which was not found, while a sync was running. Maximum number of loops allowed.', 'matador-jobs' ) );

					return;
				}
			}

			Logger::add( 'info', 'request_external_id_start_sync', __( 'A user or agent requested a job page via an external id, which was not found, and we will run a sync to find the role.', 'matador-jobs' ) );

			add_filter( 'matador_bullhorn_import_the_job_where', function ( $where ) use ( &$external_id ) {
				return $where . ' AND id=' . (int) $external_id;
			} );

			add_filter( 'matador_bullhorn_delete_missing_job_on_import', '__return_false' );

			// when sync a job by ID we don't what to allow it to be skipped
			remove_all_filters( 'matador_bullhorn_import_skip_job_on_update' );

			$bullhorn = new Bullhorn_Import();

			try {
				$bullhorn->sync();
			} catch ( Exception $exception ) {
				Logger::add( 'info', 'request_external_id_sync_failed', __( 'A user or agent requested a job page via an external id, which was not found, and sync failed.', 'matador-jobs' ) );

				return;
			}

			if ( count( $wpdb->get_col( $sql ) ) > 0 ) {

				foreach ( $wpdb->get_col( $sql ) as $found_id ) {

					if ( $external_source && get_post_meta( $found_id, '_matador_source', true ) !== $external_source ) {
						continue;
					}

					if ( 'publish' === get_post_status( $found_id ) && self::key() === get_post_type( $found_id ) ) {
						Logger::add( 'info', 'request_external_id_sync_success', __( 'A user or agent requested a job page via an external id, which was not found, but sync located.', 'matador-jobs' ) );
						$job = $found_id;
						break;
					}
				}
			}
		}

		if ( $job ) {

			$query->set( 'p', (int) $job );
			$query->set( 'post_type', null );

		} else {

			return;
		}
	}

	/**
	 * Post Type Key
	 *
	 * Returns the post type key.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 changed how this function works. Made static and public.
	 *
	 *
	 * @return string
	 */
	public static function key() {
		return Matador::variable( 'post_type_key_job_listing' );
	}

	/**
	 * Is Post a Job
	 *
	 * Checks if the current/passed post is a Job.
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param int $post_id The ID of the local (WordPress) post/job. Default null. Optional.
	 *
	 * @return bool
	 */
	public static function is_post_a_job( $post_id = null ) {

		global $post_type;

		$type = '';

		if ( is_numeric( $post_id ) ) {
			$type = get_post_type( $post_id );
		}

		if ( empty( $type ) ) {
			$type = $post_type;
		}

		if ( self::key() !== $type ) {

			return false;
		}

		return true;
	}

	/**
	 * Create Post Type
	 *
	 * @access  public
	 * @since   3.0.0
	 */
	public function create_post_type() {

		/**
		 * Filter: Jobs Post Type Labels
		 *
		 * @since   1.0.0
		 */
		$labels = apply_filters( 'matador_post_type_labels_jobs', array(
			'name'               => esc_html_x( 'Job Listings', 'Jobs Post Type General Name', 'matador-jobs' ),
			'singular_name'      => esc_html_x( 'Job Listing', 'Jobs Post Type Singular Name', 'matador-jobs' ),
			'add_new'            => esc_html__( 'Add New', 'matador-jobs' ),
			'add_new_item'       => esc_html__( 'Add New Job', 'matador-jobs' ),
			'edit_item'          => esc_html__( 'Edit Job', 'matador-jobs' ),
			'new_item'           => esc_html__( 'New Job', 'matador-jobs' ),
			'view_item'          => esc_html__( 'View Job', 'matador-jobs' ),
			'search_items'       => esc_html__( 'Search Jobs', 'matador-jobs' ),
			'not_found'          => esc_html__( 'No jobs found', 'matador-jobs' ),
			'not_found_in_trash' => esc_html__( 'No jobs found in Trash', 'matador-jobs' ),
			'parent_item_colon'  => '',
			'all_items'          => esc_html__( 'Job Listings', 'matador-jobs' ),
			'menu_name'          => esc_html__( 'Matador Jobs', 'matador-jobs' ),
		) );

		/**
		 * Filter: Jobs Post Type Rewrites
		 *
		 * @since   1.0.0
		 */
		$rewrite = apply_filters( 'matador_post_type_rewrites_jobs', array(
			'slug'       => Matador::variable( 'post_type_slug_job_listing' ),
			'with_front' => false,
			'pages'      => true,
			'feeds'      => true,
		) );

		/**
		 * Filter: Jobs Post Type Supports
		 *
		 * @since   1.0.0
		 */
		$supports = apply_filters( 'matador_post_type_supports_jobs', array(
			'title',
			'editor',
			'excerpt',
			'custom-fields',
		) );


		$args = array(
			'description'         => esc_html__( 'Jobs Listings for the Matador Jobs Board.', 'matador-jobs' ),
			'labels'              => $labels,
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 19,
			'menu_icon'           => 'dashicons-nametag',
			'hierarchical'        => false,
			'supports'            => $supports,
			'has_archive'         => true,
			'rewrite'             => $rewrite,
			'query_var'           => true,
			'can_export'          => true,
			// Prevent Users from Creating/Editing/Deleting Applications
			'map_meta_cap'        => true,
			'capability_type'     => 'post',
		);

		// allow local jobs to be created
		if ( true !== apply_filters( 'matador_allow_local_jobs', true ) ) {
			$args['capabilities'] = array(
				'create_posts' => 'do_not_allow',
			);
		}

		/**
		 * Filter: Jobs Post Type Args
		 *
		 * @since   1.0.0
		 */
		register_post_type( self::key(), apply_filters( 'matador_post_type_args_jobs', $args ) );
	}

	/**
	 * Content Filter for Post Type
	 *
	 * Filters the content for single job posts to insert a customizable link
	 * to the form where the user can submit their resume.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content
	 *
	 * @return string $content
	 */
	public static function the_content( $content = null ) {

		if ( get_post_type() !== self::key() ) {

			return $content;
		}

		/**
		 * Filter Matador Doing Custom Loop
		 *
		 * Is Matador running a custom (generally inside a shortcode) loop?
		 *
		 * @since 3.4.0
		 * @since 3.8.18 Added the $content parameter.
		 *
		 * @param bool   $is_matador_doing_custom_loop Defaults to value of is_feed().
		 * @param string $content The content.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'matador_doing_custom_loop', is_feed(), $content ) ) {

			return $content;
		}

		if ( is_single() ) {

			if ( 'complete' === get_query_var( 'matador-apply', false ) && ( 'create' === Matador::setting( 'applications_confirmation_method' ) ) ) {

				return Template_Support::get_template( 'job-single-confirmation.php' );
			} elseif ( get_query_var( 'matador-apply', false ) && ( 'create' === Matador::setting( 'applications_apply_method' ) ) ) {

				return Template_Support::get_template( 'job-single-application.php' );
			} else {

				return Template_Support::get_template( 'job-single.php', array( 'content' => $content ) );
			}
		} else {

			return Template_Support::get_template( 'jobs-archive-job.php', array( 'content' => $content ), 'parts' );
		}

		return $content;
	}

	/**
	 * Disable comments on post type.
	 *
	 * @param bool $comments_setting contains site default settings for comments open/closed
	 * @param int $post_id post id of current post
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function close_comments( $comments_setting, $post_id ) {

		// Get Post Type On Current Post
		$post_type = get_post_type( $post_id );

		if ( self::key() === $post_type ) {
			$comments_setting = false;
		}

		return $comments_setting;
	}

	/**
	 * Jobs update messages.
	 *
	 * Added warning to all messages that updates shouldn't be made on the wordpress backend.
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Amended post update messages with new CPT update messages.
	 *
	 * @todo should these be removed if we prevent user from editing the job?
	 */
	public function update_admin_messages( $messages ) {

		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[ self::key() ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Job updated. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			2  => esc_html__( 'Custom field updated. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			3  => esc_html__( 'Custom field deleted. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			4  => esc_html__( 'Job updated. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Job restored to revision from %s. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Job published. Job listings should be added only on the Bullhorn ATS. Your update will be removed on the next sync.', 'matador-jobs' ),
			7  => esc_html__( 'Job saved. Job listings should be added only on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			8  => esc_html__( 'Job submitted. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
			9  => sprintf(
			// translators: placeholder for date.
				esc_html__( 'Job scheduled for: <strong>%1$s</strong>.', 'matador-jobs' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'matador-jobs' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Job draft updated. Job listings should be updated on Bullhorn ATS. Your update will be overwritten on the next sync.', 'matador-jobs' ),
		);
		if ( $post_type_object->publicly_queryable ) {
			$permalink                  = get_permalink( $post->ID );
			$view_link                  = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View job', 'matador-jobs' ) );
			$messages[ $post_type ][1]  .= $view_link;
			$messages[ $post_type ][6]  .= $view_link;
			$messages[ $post_type ][9]  .= $view_link;
			$preview_permalink          = add_query_arg( 'preview', 'true', $permalink );
			$preview_link               = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview job', 'matador-jobs' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Jobs admin help messages.
	 *
	 * Added contextual help for jobs to warn users jobs shouldn't be added on the wordpress backend.
	 *
	 * @since 1.0.0
	 *
	 * @todo should these be removed if we prevent user from editing the job?
	 */
	public function add_help_tab() {

		$screen = get_current_screen();

		if ( self::key() !== $screen->post_type ) {
			return;
		}

		$args = array(
			'id'      => self::key(),
			'title'   => esc_html__( 'Jobs Post Type Warning', 'matador-jobs' ),
			'content' => sprintf( '<p>%s</p>', esc_html__( 'You should not add jobs to the Bullpen Jobs Board. Jobs are meant to be imported from your ATS and should be added/removed/edited there.  Jobs added via the WordPress interface will automatically be removed on the next sync.', 'matador-jobs' ) ),
		);

		$screen->add_help_tab( $args );
	}

	/**
	 * Add Matador Search to Query
	 *
	 * Detects the presence of the matador_s ( Matador Search ) variable
	 * and passes it to search.
	 *
	 * @param object $query
	 *
	 * @since 3.0.2
	 * @since 3.7.0 added support for matador_xid searches
	 *
	 * @return mixed
	 */
	public function search_query( $query ) {

		if ( ! $query->is_main_query() || ! is_archive() ) {

			return $query;
		}

		if ( isset( $query->query['post_type'] ) && self::key() !== $query->query['post_type'] ) {

			return $query;
		}

		if ( isset( $_REQUEST['matador_s'] ) ) {

			if ( is_numeric( $_REQUEST['matador_s'] ) && (int) $_REQUEST['matador_s'] > 0 ) {

				$meta_query = [
					[
						'key'     => '_matador_source_id',
						'value'   => (int) $_REQUEST['matador_s'],
						'type'    => 'NUMERIC',
						'compare' => '=',
					],
				];

				$query->set( 'meta_query', $meta_query );

			} else {

				$query->query_vars['s'] = esc_attr( $_REQUEST['matador_s'] );

			}
		}

		if ( ! empty( $_REQUEST['matador_xid'] ) && is_numeric( $_REQUEST['matador_xid'] ) ) {

			$meta_query = [
				[
					'key'     => '_matador_source_id',
					'value'   => (int) $_REQUEST['matador_xid'],
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
			];

			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
	 * Handle the sorting for job listing columns.
	 *
	 * @param object $query by ref
	 * @since 3.7.0
	 * @return void
	 */
	public function sort_columns( $query ) {
		if ( ! is_admin() ) {

			return;
		}

		$orderby = $query->get( 'orderby');

		if ( 'bhid' == $orderby ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_matador_source_id' );
		}
	}


	/**
	 * Allow job listings to be sorted by a specified setting by the admin.
	 *
	 * @param object $query
	 * @since 3.0.2
	 * @return object $query
	 */
	public function sort_results( $query ) {

		if ( ! Matador::setting( 'sort_jobs' ) && ! Matador::setting( 'order_jobs' ) ) {

			return $query;
		}

		$should_sort = false;

		// Check if we are in our taxonomy
		foreach ( Matador::variable( 'job_taxonomies' ) as $taxonomy ) {
			if ( $query->is_tax( $taxonomy['key'] ) && $query->is_main_query() ) {
				$should_sort = true;
				break;
			}
		}

		// Check if we are in our post type archive
		if ( ! $should_sort && $query->is_post_type_archive( self::key() ) && $query->is_main_query() ) {
			$should_sort = true;
		}

		// Check if we are in a custom query, its of our post type, and the query param orderby is NOT set.
		//
		// A developer can create a custom query with ordering and future blocks or shortcodes could offer ordering
		// settings in the block options or shortcode parameters, so don't override that.
		if ( ! $should_sort && self::key() === $query->get( 'post_type' ) && empty( $query->get( 'orderby' ) ) && empty( $query->get( 'order' ) ) ) {
			$should_sort = true;
		}

		// Apply sorting rules
		if ( $should_sort ) {

			$setting = Matador::setting( 'sort_jobs' );
			$setting_field = Settings_Fields::instance()->get_field( 'sort_jobs' );
			$options = array_keys( $setting_field['options'] );

			if ( in_array( $setting, $options, true ) ) {
				// default rules
				switch ( $setting ) {
					case 'random':
						$query->set( 'orderby', 'rand' );
						break;
					case 'name':
						$query->set( 'orderby', 'post_title' );
						break;
					case 'bullhorn_id':
						$query->set( 'orderby', 'meta_value_num' );
						$query->set( 'meta_key', '_matador_source_id' );
						break;
					case 'date':
					default:
						break;
				}
				// custom rules support
				$query = apply_filters( 'matador_jobs_orderby_rule_' . $setting, $query );

				if ( 'ASC' === Matador::setting( 'order_jobs' ) && empty( $query->get( 'order' ) ) ) {
					$query->set( 'order', 'ASC' );
				}
			}
		}
		return $query;
	}

	/**
	 * JSON+LD
	 *
	 * Wraps the JSON+LD in a script tag for inclusion in WP Head
	 *
	 * @since 3.6.0
	 * @since 3.8.0 Adds validation to the string is valid JSON
	 *
	 * @param int  $id    The WordPress post ID. Default null.
	 * @param bool $echo  Whether to echo or return the JSON+LD
	 *
	 * @return null|string
	 */
	public static function jsonld( $id = null, $echo = true ) {

		$jsonld = self::get_jsonld( $id );

		if ( empty( $jsonld ) || ! Helper::is_json( $jsonld ) ) {

			return;
		}

		$jsonld = '<script type="application/ld+json">' . $jsonld . '</script>';

		if ( $echo ) {

			if ( null === $id && ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {

				return;
			}

			echo $jsonld;

			return;
		}

		return $jsonld;
	}

	/**
	 * Get JSON+LD
	 *
	 * Gets the JSON+LD object from the DB
	 *
	 * @since 3.0.0
	 * @since 3.1.0 added jsonld_disabled check
	 * @since 3.4.0 jsonld_disabled changed to jsonld_enabled
	 * @since 3.6.0 Function was rebuilt to only provide the object, does not output with script tag, returns only, deprecated $echo param
	 *
	 * @param int  $id         The WordPress post ID. Default null.
	 * @param bool $deprecated @deprecated 3.6.0
	 *
	 * @return null|string
	 */
	public static function get_jsonld( $id = null, $deprecated = null ) {

		unset( $deprecated );

		if ( ! Matador::setting( 'jsonld_enabled' ) ) {

			return;
		}

		if ( ! is_singular( Matador::variable( 'post_type_key_job_listing' ) ) ) {

			return;
		}

		$jsonld = get_post_meta( get_the_ID(), 'jsonld', true );

		$depth   = apply_filters( 'bullhorn_json_ld_depth', 1024 );
		$options = apply_filters( 'bullhorn_json_ld_options', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {
			$options = $options | JSON_PRETTY_PRINT;
		}

		$jsonld = apply_filters( 'bullhorn_json_ld_full_array', $jsonld );

		return wp_json_encode( $jsonld, $options, $depth );
	}




	public static function remove_add_new_from_submenu() {
		remove_submenu_page( 'edit.php?post_type=matador-job-listings', 'post-new.php?post_type=matador-job-listings' );
	}

	public function columns_add( $columns ) {
		$columns['matador-bullhorn-id'] = esc_html__( 'Bullhorn ID', 'matador-jobs' );

		return $columns;
	}

	public function columns_sortable( $columns ) {
		$columns['matador-bullhorn-id'] = 'bhid';

		return $columns;
	}

	public function columns_content( $column, $post_id ) {
		switch ( $column ) {
			case 'matador-bullhorn-id':

				$bullhorn_server_url = Helper::get_client_cluster_url();
				if ( false !== $bullhorn_server_url ) {
					printf( '<a href="%1$sBullhornSTAFFING/OpenWindow.cfm?Entity=JobOrder&id=%2$s" target="_blank" title="%3$s">%2$s</a>',
						esc_url( $bullhorn_server_url ),
						absint( get_post_meta( $post_id, '_matador_source_id', true ) ),
						esc_html__( 'Open in Bullhorn', 'matador-jobs' )
					);
				} else {
					echo esc_html( get_post_meta( $post_id, '_matador_source_id', true ) );
				}

				break;
		}
	}

	/**
	 * Update JSON LD on local change
	 *
	 * Clients can modify title and description locally if they desire and those changes will affect structured data.
	 * This allows us to update the structured data, but only when the post is updated locally and not via sync.
	 *
	 * @since 3.0.4
	 *
	 * @param int      $post_id The local (WordPress) post/job ID.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function save_job_jsonld( $post_id, $post ) {
		/**
		 * Filter Bullhorn Doing Jobs Sync
		 *
		 * Allows us to add actions and filters to local-only saves.
		 *
		 * @since 3.1.0
		 *
		 * @var bool
		 */
		if ( apply_filters( 'matador_bullhorn_doing_jobs_sync', false ) ) {

			return;
		}

		$current_json = get_post_meta( $post_id, 'jsonld', true );

		if( is_array( $current_json ) ){
			$current_json['description'] = $post->post_content;
			$current_json['title']       = $post->post_title;

			update_post_meta( $post_id, 'jsonld', $current_json );
		}

	}

	/**
	 * Add a edit in bullhorn button
	 */

	public static function add_media_button() {
		$post_id     = get_the_ID();
		$bullhorn_id = get_post_meta( $post_id, '_matador_source_id', true );
		if ( false !== $bullhorn_id && get_post_type( $post_id ) === self::key() ) {
			if ( false !== Helper::get_client_cluster_url() ) {
				printf( '<a href="%1$sBullhornSTAFFING/OpenWindow.cfm?Entity=JobOrder&id=%2$s" target="_blank" title="%3$s" class="button "><img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" height="16px" style="margin-top: -4px; height: 16px" /> %4$s</a>',
					esc_url( Helper::get_client_cluster_url() ),
					absint( $bullhorn_id ),
					esc_html__( 'Open in Bullhorn', 'matador-jobs' ),
					esc_html__( 'Edit the Job in Bullhorn', 'matador-jobs' )
				);
			}
			// The filter can remove a user's ability to sync jobs. We cannot test for the
			// filter's output, should it have been applied conditionally, due to the there
			// not being access to the Bullhorn Job object at this time.
			if ( ! has_filter( 'matador_bullhorn_import_skip_job_on_update' ) ) {
				printf( '<button id="matador-sync-job" data-bhid="%1$s" data-nonce="%3$s" title="%2$s" class="button"><img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" height="16px" style="margin-top: -4px; height: 16px" /> %2$s</button>',
					absint( $bullhorn_id ),
					esc_html__( 'Sync this Job', 'matador-jobs' ),
					wp_create_nonce( 'matador_sync_job' )
				);
			}
		}
	}

	/**
	 * Add 'Sync Now' Button to Jobs Listings Table
	 *
	 * @since 3.1.0
	 * @since 3.4.0 converted to static
	 *
	 *
	 * @param $which
	 */
	public static function add_sync_now_button_to_job_listings_table( $which ) {
		if ( get_current_screen()->id === 'edit-' . self::key() && 'top' === $which ) {
			$is_connected = Matador::setting( 'bullhorn_api_is_connected' ) ?: false;
			$url          = wp_nonce_url( add_query_arg( 'post_type', self::key(), admin_url( 'edit.php' ) ), 'matador-sync', 'sync' );

			/**
			 * Filter: Matador Bullhorn Job Listing Show Sync Buttons
			 *
			 * @since 3.7.0
			 *
			 * @param bool Default true
			 * @return bool
			 */
			if ( apply_filters( 'matador_bullhorn_job_listing_show_sync_buttons', true ) && $is_connected ) {
				printf( '<a href="%1$s" id="%2$s" title="%4$s" style="display: inline-block; position: relative; padding-left: 26px;" class="%2$s %3$s">%5$s %4$s</a>', esc_url( $url ), 'sync', 'button', esc_html__( 'Sync Jobs Now', 'matador-jobs' ), '<img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" height="16px" style="position:absolute; top: 5px; left: 4px;  height: 16px" />' );
			}
		}

	}

	/**
	 * Current Screen Actions
	 *
	 * Conditional to run items when on the current screen.
	 *
	 * @since unknown
	 *
	 * @return void
	 */
	public static function current_screen() {

		if ( 'edit-' . self::key() === get_current_screen()->id ) {
			if ( isset( $_GET['sync'] ) && wp_verify_nonce( $_GET['sync'], 'matador-sync' ) ) {
				Admin_Tasks::import_sync_now( admin_url( 'edit.php?post_type=' . self::key() ) );
			}
		}
	}

	/**
	 * Save Post
	 *
	 * Function to be called on the save_post action to make room for the matador_add_job and matador_update_job hook
	 * registrations.
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an existing post being updated or not.
	 */
	public static function save_post( $post_id, $post, $update ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( $update ) {
			/**
			 * Matador Update Job Action
			 *
			 * Works just like save_post action in core WordPress where $update is true, but only on Matador Job
			 * Listings.
			 *
			 * @see WordPress Action save in post.php:wp_save_post()
			 *
			 * @since 3.4.0
			 *
			 * @param int      $post_id The local (WordPress) ID of the Job Listing being deleted.
			 * @param \WP_Post $post    Post object.
			 */
			do_action( 'matador_update_job', $post_id, $post );
		} else {
			/**
			 * Matador Create Job Action
			 *
			 * Works just like save_post action in core WordPress where $update is false, but only on Matador Job
			 * Listings.
			 *
			 * @see WordPress Action save in post.php:wp_save_post()
			 *
			 * @since 3.4.0
			 *
			 * @param int      $post_id The local (WordPress) ID of the Job Listing being deleted.
			 * @param \WP_Post $post    Post object.
			 */
			do_action( 'matador_add_job', $post_id, $post );
		}

		/**
		 * Matador Save Job Action
		 *
		 * Works just like save_post action in core WordPress but without the $update parameter, and only on Matador Job
		 * Listings.
		 *
		 * @see WordPress Action delete_post in post.php:wp_delete_post()
		 *
		 * @since 3.4.0
		 *
		 * @param int      $post_id The local (WordPress) ID of the Job Listing being deleted.
		 * @param \WP_Post $post    Post object.
		 */
		do_action( 'matador_save_job', $post_id, $post );
	}

	/**
	 * Transition Post Status
	 *
	 * Function to be called on the transition_post_status action to make room for the matador_transition_job_status
	 * hook registration.
	 *
	 * @since 3.4.0
	 *
	 *
	 * @param string $new     The new status for the post.
	 * @param string $old     The old status for the post.
	 * @param int    $post_id The ID for the post.
	 */
	public static function transition_post_status( $new, $old, $post_id ) {

		if ( ! self::is_post_a_job( $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		/**
		 * Matador Transition Post Status Action
		 *
		 * Works just like transition_post_status action in core WordPress, but only on Matador Job Listings
		 *
		 * @see WordPress Action transition_post_status in post.php:transition_post_status()
		 *
		 * @since 3.4.0
		 *
		 * @param string $new     The new status for the job.
		 * @param string $old     The old status for the job.
		 * @param int    $post_id The local (WordPress) ID for the job.
		 */
		do_action( 'matador_transition_job_status', $new, $old, $post_id );
	}

	/**
	 * Delete Post
	 *
	 * Function to be called on the delete_post action to make room for the matador_delete_job hook registration.
	 *
	 * @since 3.4.0
	 * @since 3.8.7 Added $post parameter
	 *
	 * @param int $post_id The local (WordPress) ID for the job.
	 * @param WP_Post $post The WordPress Post object for the job
	 */
	public static function delete_post( $post_id, $post ) {

		if ( ! self::is_post_a_job( $post_id ) ) {
			return;
		}

		/**
		 * Matador Delete Job Action
		 *
		 * Works just like delete_post action in core WordPress, but only on Matador Job Listings
		 *
		 * @see WordPress Action delete_post in post.php:wp_delete_post()
		 *
		 * @since 3.4.0
		 * @since 3.8.7 Added $post parameter
		 *
		 * @param int $post_id The local (WordPress) ID of the Job Listing being deleted.
		 * @param WP_Post $post The WordPress Post object for the job
		 */
		do_action( 'matador_delete_job', $post_id, $post );
	}

	/**
	 * AJAX Single Job Sync
	 *
	 * Run the job import directly. If BHID in $request just one job is synced
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	public function matador_api_job_sync() {
		// Handle request then generate response using WP_Ajax_Response
		check_ajax_referer( 'matador_sync_job', 'nonce' );

		if ( isset( $_REQUEST['bhid'] ) && ! empty( absint( $_REQUEST['bhid'] ) )  && 0 < absint( $_REQUEST['bhid'] ) ) {
			add_filter( 'matador_bullhorn_import_the_job_where', function ( $where ){
				$where .= 'AND id=' . absint( $_REQUEST['bhid'] );
				return $where;
			} );
			add_filter( 'matador_bullhorn_delete_missing_job_on_import', '__return_false' );
			// when sync a job by ID we don't what to allow it to be skipped
			remove_all_filters('matador_bullhorn_import_skip_job_on_update' );
		}

		$bullhorn = new Bullhorn_Import();
		try {
			$bullhorn->sync();
		} catch ( Exception $exception ) {
			wp_die();
		}
		// Don't forget to stop execution afterward.
		wp_die();
	}

	/**
	 * Disable Core WordPress XML Feeds for Job Listings
	 *
	 * RSS is hardly used anymore and when it comes to publishing jobs, there are many bespoke XML feeds. To help users
	 * curate their syndication, we disable certain core feeds. To re-enable, unhook this function by calling
	 * `remove_action( 'pre_get_posts', array( 'matador\Job_Listing', 'disable_feed' ), 10 );`
	 *
	 * @since 3.7.0
	 *
	 * @param object $query
	 *
	 * @return object
	 */
	public static function disable_feed( $query ) {

		if ( ! $query->is_feed() ) {

			return;
		}

		if ( ! in_array( Matador::variable( 'post_type_key_job_listing' ), (array) $query->get( 'post_type' ) ) ) {

			return;
		}

		/**
		 * Filter: Matador Jobs Listings Disabled Feeds
		 *
		 * List of default feeds disabled by Matador. To re-enable one or a few, use this filter.
		 *
		 * @since 3.7.0
		 *
		 * @param array $disabled_feeds.
		 */
		$disabled_feeds = apply_filters( 'matador_jobs_listing_disabled_feeds', [ 'feed', 'rss', 'rss2', 'rdf', 'atom' ] );

		if ( ! in_array( $query->get( 'feed' ), $disabled_feeds ) ) {

			return;
		}

		if ( current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'This feed is disabled by Matador Jobs for Job Listing posts.', 'matador-jobs' ) );
		} else {
			wp_die( __( 'This feed is unavailable.', 'matador-jobs' ) );
		}
	}

}
