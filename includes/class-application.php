<?php
/**
 * Matador Jobs Post Types
 *
 * @package     Matador Jobs Board
 * @subpackage  Functions
 * @copyright   (c) 2017-2021 Matador Software, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 *
 * @docs : action / filter
 */

namespace matador;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) || class_exists( 'Post_Type_Application' ) ) {
	exit;
}

/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 3.0.0
 * @return void
 *
 */
class Application {

	static private $key;

	/**
	 * Class constructor
	 *
	 * @access  public
	 * @since   3.0.0
	 */
	public function __construct() {

		self::post_type_key();

		add_action( 'init', array( __CLASS__, 'create_post_type' ) );
		add_filter( 'post_date_column_status', array( __CLASS__, 'post_date_column_status' ), 10, 4 );

		if ( 0 !== (int)Matador::setting( 'applications_sync' ) ) {
            add_action( 'manage_posts_extra_tablenav', array( $this, 'sync_now_button' ) );
		}

		add_action( 'manage_posts_extra_tablenav', array( $this, 'applications_delete_synced_button' ) );

        add_filter( 'manage_edit-' . self::$key . '_sortable_columns', array( __CLASS__, 'columns_sortable' ) );
        add_action( 'manage_' . self::$key . '_posts_custom_column', array( __CLASS__, 'columns_content' ), 10, 2 );
        add_action( 'pre_get_posts', array( __CLASS__, 'orderby' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'file_links_meta_box' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'sync_log_meta_box' ) );
        add_action( 'matador_new_job_application', array( __CLASS__, 'new_application_sync' ) );

        add_filter( 'manage_' . self::$key . '_posts_columns', array( __CLASS__, 'columns_add' ) );
        add_action( 'matador_submissions_table_status', array( __CLASS__, 'sync_single_button' ), 15 );
        add_action( 'matador_submissions_table_status', array( __CLASS__, 'sync_status_label' ) );

		add_action( 'media_buttons', [ __CLASS__, 'single_action_buttons' ] );
		add_action( 'matador_admin_submission_single_action_buttons', [ __CLASS__, 'application_single_action_buttons' ], 10, 2 );

		add_action( 'before_delete_post', array( $this, 'remove_files' ) );
	}

	/**
	 *
	 */
	private static function post_type_key() {
		self::$key = Matador::variable( 'post_type_key_application' );
	}

	// Since 1.0
	public static function create_post_type() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'matador-jobs-pro/matador.php' ) ) {

			if ( ! Matador::setting( 'bullhorn_grandfather' ) ) {

				return;
			}
		}
		/**
		 * Filter: Application Post Type Labels
		 *
		 * @since   3.0.0
		 *
		 * @param array $labels
		 */
		$labels = apply_filters( 'matador_post_type_labels_application', array(
			'name'               => esc_html_x( 'Applications', 'Applications Post Type General Name', 'matador-jobs' ),
			'singular_name'      => esc_html_x( 'Application', 'Applications Post Type Singular Name', 'matador-jobs' ),
			'add_new'            => esc_html__( 'Add New', 'matador-jobs' ),
			'add_new_item'       => esc_html__( 'Add New Application', 'matador-jobs' ),
			'edit_item'          => esc_html__( 'Edit Application', 'matador-jobs' ),
			'new_item'           => esc_html__( 'New Application', 'matador-jobs' ),
			'view_item'          => esc_html__( 'View Application', 'matador-jobs' ),
			'search_items'       => esc_html__( 'Search Applications', 'matador-jobs' ),
			'not_found'          => esc_html__( 'No Applications found', 'matador-jobs' ),
			'not_found_in_trash' => esc_html__( 'No Applications found in Trash', 'matador-jobs' ),
			'parent_item_colon'  => '',
			'all_items'          => esc_html__( 'Applications', 'matador-jobs' ),
			'menu_name'          => esc_html__( 'Application', 'matador-jobs' ),
		) );

		/**
		 * Filter: Application Post Type Supports
		 *
		 * @since   3.0.0
		 */
		$supports = apply_filters( 'matador_post_type_supports_application', array(
			'title',
			'editor',
			'custom-fields',
		) );

		/**
		 * Filter: Application Post Type Args
		 *
		 * @since   3.0.0
		 */
		$args = apply_filters( 'matador_post_type_args_application', array(
			'description'         => esc_html__( 'Job Applications for the Matador Jobs Board.', 'matador-jobs' ),
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . Matador::variable( 'post_type_key_job_listing' ),
			'query_var'           => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => $supports,
			'can_export'          => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			// Prevent Users from Creating/Editing/Deleting Applications
			'map_meta_cap'        => true,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
		) );

		register_post_type( self::$key, $args );

	}

	public static function matador_post_type_args_application( $values ) {

		$values['show_ui']      = false;
		$values['show_in_menu'] = false;

		return $values;
	}

	/**
	 * @param $query \WP_Query
	 */
	public static function orderby( $query ) {

        if ( ! is_admin() ) {
            return;
        }

        $order_by = $query->get('orderby');

        if ( 'sync' === $order_by ) {
            $query->set('meta_key', Matador::variable('candidate_sync_status'));
            $query->set('orderby', 'meta_value');
        }
    }

	/**
	 * Submissions Columns Add
	 *
	 * @since 3.0.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function columns_add( $columns ) {
		$columns['matador-sync-status'] = esc_html__( 'Sync Status', 'matador-jobs' );

		return $columns;
	}

	/**
	 * Submissions Columns Sortable
	 *
	 * @since 3.0.0
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public static function columns_sortable( $columns ) {
		$columns['matador-sync-status'] = 'sync';

		return $columns;
	}

	/**
	 * Submissions Columns Content
	 *
	 * @since 3.0.0
	 *
	 * @param string $column
	 * @param int $post_id
	 *
	 * @return void
	 */
	public static function columns_content( $column, $post_id ) {
		switch ( $column ) {
			case 'matador-sync-status':
				do_action( 'matador_submissions_table_status', $post_id );
				break;
		}
	}

	/**
	 * Sync Single Submission Button
	 *
	 * @since 3.1.0 via code originally written inside another function.
	 * @since 3.7.0 as its own function
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public static function sync_single_button( $post_id ) {

		$type = strtolower( get_post_meta( $post_id, Matador::variable( 'submission_type' ), true ) );

		if ( ! empty( $type ) && 'application' !== $type ) {

			return;
		}

		$status = self::get_sync_status( $post_id );

		if ( 1 !== $status ) {
			$format = '<a style="float: right;" class="button" href="%1$s">%2$s</a>';
			$label  = ( -1 === $status || 2 === $status ) ? __( 'Sync Now', 'matador-jobs' ) : __( 'Re-try Sync', 'matador-jobs' );
			echo wp_kses_post( sprintf( $format, esc_url( self::get_sync_url( $post_id ) ), esc_html( $label ) ) );
		}
	}

	/**
	 * Get Sync URL
	 *
	 * @since 3.0.0
	 * @since 3.7.0 added $action parameter
	 *
	 * @param int    $id
	 * @param string $action
	 *
	 * @return string URL to trigger sync
	 */
	public static function get_sync_url( $id = 0, $action = 'application_sync' ) {

		if ( ! $id ) {
			$id = 'all';
		}

		if ( empty( $action ) ) {
			$action = 'application_sync';
		}

		$query_args  = array(
			'sync'      => $id,
			'post_type' => Matador::variable( 'post_type_key_application' ),
		);

		$sync_url    = wp_nonce_url( add_query_arg( $query_args, admin_url() . 'edit.php' ), $action, $action );

		return esc_url( $sync_url );
	}

	/**
	 * Get Delete Synced URL
	 *
	 * Gets the URL for the delete all synced applications URL.
	 *
	 * @since 3.7.0
	 *
	 * @return string URL to trigger sync
	 */
	public static function get_delete_synced_url() {

		$action = 'application_delete';

		$query_args  = array(
			'post_type' => Matador::variable( 'post_type_key_application' ),
		);

		$sync_url    = wp_nonce_url( add_query_arg( $query_args, admin_url() . 'edit.php' ), $action, $action );

		return esc_url( $sync_url );
	}

	/**
	 * Get Synced ID
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id
	 *
	 * @return int|null
	 */
	public static function get_synced_id( $post_id = 0 ) {
		$data = get_post_meta( $post_id, Matador::variable( 'candidate_data' ), true );

		return ( ! empty( $data ) && ! empty( $data->candidate->id ) ) ? $data->candidate->id : null;
	}

	/**
	 * Get Sync Status of Submission
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public static function get_sync_status( $post_id = 0 ) {
		return intval( get_post_meta( $post_id, Matador::variable( 'candidate_sync_status' ), true ) ) ?: 0;
	}

	/**
	 * @since 3.7.0
	 * @param $post_id
	 */
	public static function sync_status_label( $post_id ) {
		echo self::get_sync_status_label( $post_id );
	}

	/**
	 * @since 3.0.0
	 * @param $post_id
	 */
	public static function get_sync_status_label( $post_id ) {

		$status = self::get_sync_status( $post_id );

		switch ( $status ) {
			case -1:
				$label = esc_html__( 'Pending', 'matador-jobs' );
				break;
			case 1:
				if ( self::get_synced_id( $post_id ) ) {
					if ( false !== Helper::get_client_cluster_url() ) {
						$label = sprintf( '<a href="%1$sBullhornSTAFFING/OpenWindow.cfm?Entity=Candidate&id=%2$s" target="BullhornMainframe" title="%3$s">%4$s</a>',
							esc_url( Helper::get_client_cluster_url() ),
							absint( self::get_synced_id( $post_id ) ),
							esc_html__( 'Open in Bullhorn', 'matador-jobs' ),
							// Translators: placeholder has remote ID
							sprintf( __( 'Synced as Bullhorn Candidate %1$s', 'matador-jobs' ), absint( self::get_synced_id( $post_id ) ) )
						);
					} else {
						// Translators: placeholder is the remote (Bullhorn) ID of the candidate.
						$label = sprintf( __( 'Synced as Bullhorn Candidate %1$s', 'matador-jobs' ), absint( self::get_synced_id( $post_id ) ) );
					}
				} else {
					$label = esc_html__( 'Synced', 'matador-jobs' );
				}
                break;
			case 2:
				$label = esc_html__( 'Incomplete', 'matador-jobs' );
                break;
			case 3:
				$label = esc_html__( 'Automatic Sync Failed. Re-try.', 'matador-jobs' );
                break;
			case 5:
				$label = esc_html__( 'Unable to Sync. (Too Little Information).', 'matador-jobs' );
                break;
			case 6:
				$label = esc_html__( 'Candidate exists and is marked as "Private" in Bullhorn. Application must be manually created.', 'matador-jobs' );
                break;
			default:
				$label = esc_html__( 'Application record was not created properly. Contact Matador Support.', 'matador-jobs' );
                break;
		}
        /**
         * Filter: Sync Status Label Text
         *
         * @since   3.6.0
         * @deprecated 3.8.0
         *
         * @param string $label
         * @param int    $status
         * @param int    $post_id
         *
         * @retun  string $label
         */
		$label = apply_filters( 'matador_application_get_sync_status_label', $label, $status, $post_id  );

		/**
		 * Filter: Submission Sync Status Label Text
		 *
		 * @since   3.8.0
		 *
		 * @param string $label
		 * @param int    $status
		 * @param int    $post_id
		 *
		 * @retun  string $label
		 */
		return apply_filters( 'matador_submissions_sync_status_label', $label, $status, $post_id  );
	}

	/**
	 * Meta Box Register: File Links
	 *
	 * Adds a box with a list of files included with the entry and also the raw rendered data object. Call on action
	 * `add_meta_boxes` and uses `add_meta_box` to add a meta box to a classic editor edit post admin screen. Rendering
	 * by linked callable function `file_links_metabox_contents`.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/add_meta_boxes/
	 * @see https://developer.wordpress.org/reference/functions/add_meta_box/
	 * @see self::file_links_meta_box_contents()
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function file_links_meta_box() {

		$title = esc_html__( 'Attached Files', 'matador-jobs' );

		add_meta_box(
			Matador::variable( 'candidate_file_links' ),
			$title,
			array( __CLASS__, 'file_links_meta_box_contents' ),
			self::$key,
			'normal',
			'high'
		);
	}

	/**
	 * Meta Box Render: File Links
	 *
	 * Produces output for the File Links meta box. Called by add_meta_box on the add_meta_boxes action by
	 * self::file_links_meta_box().
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function file_links_meta_box_contents() {

		$data = get_post_meta( get_the_ID(), '_application_data', true );

		if ( empty( $data['files'] ) ) {
			printf( '<div class="matador-meta-scroll-box">%1$s</div>', esc_html__( 'No attached files', 'matador-jobs' ) );
		} else {
			foreach ( $data['files'] as $key => $file ) {
				printf( '<strong>%1$s:</strong> <a href="%2$s" target="_blank">%3$s</a>', ucwords( $key ), $file['url'], $file['file'] );
			}
		}

		$parsed_resume = get_post_meta( get_the_ID(), '_bullhorn_candidate_data', true );

		if ( ! empty( $parsed_resume ) && isset( $parsed_resume->candidate->description ) ) {
			echo '<div class="matador-meta-scroll-box"><pre style="white-space: pre-wrap">', $parsed_resume->candidate->description, '</pre></div>';
		}
	}

	public static function sync_log_meta_box() {
		$title = esc_html__( 'Bullhorn Activity Log', 'matador-jobs' ) . '<span class="' . Matador::variable( 'css_class_prefix' ) . '-logging-sync-state">' . self::get_sync_status_label( get_the_ID() ) . ' </span>';
		add_meta_box(
			Matador::variable( 'candidate_sync_log' ),
			$title,
			array( __CLASS__, 'sync_log_meta_box_contents' ),
			self::$key,
			'normal',
			'high'
		);
	}

	/**
	 * render HTML
	 */
	public static function sync_log_meta_box_contents() {

		$log      = get_post_meta( get_the_ID(), Matador::variable( 'candidate_sync_log' ), true );
		$contents = ( ! empty( $log ) ) ? $log : esc_html__( 'No log', 'matador-jobs' );
		printf( '<div class="matador-meta-scroll-box">%1$s</div>', wp_kses_post( wpautop( $contents ) ) );

		if ( ! empty( $_GET['matador_debug'] ) ) { // WPCS: CSRF ok.
			if ( 'clear' === $_GET['matador_debug'] ) { // WPCS: CSRF ok.
				delete_post_meta( get_the_ID(), Matador::variable( 'candidate_sync_log' ) );
			} else {
				echo '<div class="matador-meta-scroll-box"><pre style="white-space: pre-wrap">', esc_html( print_r( get_post_meta( get_the_ID() ), true ) ), '</pre></div>';
			}
		}
	}

	/**
	 * @param $status
	 * @param $post
	 *
	 * @return string
	 */
	public static function post_date_column_status( $status, $post ) {

		if ( get_post_type( $post->ID ) === self::$key ) {
			$status = wp_kses_post( 'Submitted' );
		}

		return $status;
	}

	/**
	 * Single Submission Actions
	 *
	 * Add buttons to the area above the TinyMCE editor on Matador submissions single edit screens by adding a plugin
	 * hook on top of WordPress Core's `media_button` hook.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/media_buttons/
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public static function single_action_buttons() {

		$post_id = get_the_ID();

		if ( get_post_type( $post_id ) !== self::$key ) {

			return;
		}

		$type = get_post_meta( $post_id, Matador::variable( 'submission_type' ), true );

		/**
		 * Action `matador_admin_submission_single_action_buttons`. Add buttons to the area above the TinyMCE editor on
		 * Matador submissions single edit screens.
		 *
		 * @wordpress-action
		 *
		 * @since 3.8.0
		 *
		 * @param int $post_id The ID of the Post
		 * @param string $type The submission type, ie: 'application'.
		 */
		do_action( 'matador_admin_submission_single_action_buttons', $post_id, $type );
	}

	/**
	 * Application Single Action Buttons
	 *
	 * Called on `matador_admin_submission_single_action_buttons` and adds a button that either links the user to the
	 * synced record in Bullhorn or gives them an option to run/rerun sync on the record.
	 *
	 * @see @wordpress-action matador_admin_submission_single_action_buttons
	 *
	 * @since 3.1.0
	 * @since 3.8.0 Updated to now fire on `matador_admin_submission_single_action_buttons` instead of `media_buttons`.
	 *              Renamed from add_media_button to `application_single_action_buttons`
	 *
	 * @param int    $post_id The ID of the Post
	 * @param string $type    The submission type, ie: 'application'
	 *
	 * @return void
	 */
	public static function application_single_action_buttons( $post_id = 0, $type = '' ) {

		if ( empty( $post_id ) || 'application' !== $type ) {

			return;
		}

		if ( self::get_synced_id( $post_id ) ) {
			if ( false !== Helper::get_client_cluster_url() ) {
				printf( '<a href="%1$sBullhornSTAFFING/OpenWindow.cfm?Entity=Candidate&id=%2$s" target="_blank" title="%3$s" class="button "><img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" height="16px" style="margin-top: -4px;  height: 16px" /> %4$s</a>',
					esc_url( Helper::get_client_cluster_url() ),
					absint( self::get_synced_id( $post_id ) ),
					esc_html__( 'Open in Bullhorn', 'matador-jobs' ),
					esc_html__( 'Open the Candidate in Bullhorn', 'matador-jobs' )
				);
			}
		} else {
			$status = self::get_sync_status( $post_id );
			if ( 1 !== $status ) {
				$format = '<a class="button" href="%1$s">%3$s %2$s</a>';
				$label  = ( - 1 === $status || 2 === $status ) ? __( 'Sync to Bullhorn', 'matador-jobs' ) : __( 'Re-try Sync', 'matador-jobs' );
				echo wp_kses_post( sprintf( $format, esc_url( self::get_sync_url( $post_id ) ), esc_html( $label ), '<img width="20px" src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" />' ) );
			}
		}

	}

	/**
	 * Sync Application to Bullhorn
	 *
	 * This function determines if an application, on save, should
	 * sync with Bullhorn.
	 *
	 * @since 3.1.0
	 *
	 * @param integer $application_id
	 */
	public static function new_application_sync( $application_id ) {

		if ( in_array( get_post_meta( $application_id, Matador::variable( 'submission_type' ), true ), array( 'lead', 'contact' ), true ) ) {

			return;
		}

		new Event_Log( 'application-new', __( 'A new candidate application was submitted. Application ID: ', 'matador-jobs' ) . $application_id );

		if ( -1 === (int) get_post_meta( $application_id, Matador::variable( 'candidate_sync_status' ), true ) ) {
			// 1 is submit in batches. -1 is submit immediately.
			if ( 1 === (int) Matador::setting( 'applications_sync' ) ) {
				new Event_Log( 'application-new-sync-later', __( 'A new candidate application was submitted and will be uploaded to Bullhorn in the next batch. Application ID: ', 'matador-jobs' ) . $application_id );
			} elseif ( -1 === (int) Matador::setting( 'applications_sync' ) ) {
				new Event_Log( 'application-new-sync-now', __( 'A new candidate application was submitted and will now be uploaded to Bullhorn. Application ID ', 'matador-jobs' ) . $application_id );
				new Application_Sync( $application_id );
			} else {
				new Event_Log( 'application-new-sync-off', __( 'A new candidate application was submitted but settings prevent it being automatically uploaded to Bullhorn. Application ID ', 'matador-jobs' ) . $application_id );
			}
		}
	}

	/**
	 * Add 'Sync Now' Button to Jobs Listings Table
	 * @since  3.4.0
	 */
	public function sync_now_button() {
		if ( get_current_screen()->id === 'edit-' . self::$key ) {
			$is_connected = Matador::setting( 'bullhorn_api_is_connected' ) ?: false;

			if ( $is_connected ) {
				$url = self::get_sync_url();
				$html = '<div class="alignleft actions"><a href="%1$s" id="%2$s" class="%2$s %3$s" title="%4$s">%5$s %4$s</a></div>';
				printf( $html, esc_url( $url ), 'sync', 'button matador-admin-post-type-action-button matador-admin-button-bullhorn', esc_html__( 'Sync Pending Now', 'matador-jobs' ), '<img src="https://app.bullhornstaffing.com/assets/images/circle-bull.png" />' );
			}
		}
	}

	/**
	 * Add 'Delete All Synced' Button to Jobs Listings Table
	 *
	 * @since  3.7.0
	 *
	 * @return void
	 */
	public function applications_delete_synced_button() {
		if ( get_current_screen()->id === 'edit-' . self::$key ) {
			$url = self::get_sync_url( 'all', 'applications_delete_synced' );
			$html = '<div class="alignleft actions"><a href="%1$s" id="%2$s" class="%2$s %3$s" title="%4$s" >%4$s</a></div>';
			printf( $html, esc_url( $url ), 'delete-synced-applications', 'button matador-admin-post-type-action-button', esc_html__( 'Delete Synced', 'matador-jobs' )  );
		}
	}

	/**
	 * Remove Application Files
	 *
	 * This function will take a post ID and remove files associated with it prior to post delete.
	 * @since 3.4.0
	 *
	 * @param int $post_id
	 */
	public function remove_files( $post_id ) {

		if ( get_post_type( $post_id ) !== self::$key ) {

			return;
		}

		$application_data = get_post_meta( $post_id, '_application_data', true );

		if ( ! isset( $application_data['files'] ) ) {

			return;
		}

		foreach ( $application_data['files'] as $files ) {
			if ( isset( $files['path'] ) && file_exists( $files['path'] ) && is_file( $files['path'] ) ) {

				if ( unlink( $files['path'] ) ) {
					// Translators: %s if file path
					Logger::add( 'info', 'application_file_removed', sprintf( __( 'The following file was removed as part of the delete Application routine: %s', 'matador-jobs' ), $files['path'] ) );
				} else {
					// Translators: %s if file path
					Logger::add( 'error', 'application_file_not_removed', sprintf( __( 'The following file was unable to be removed as part of the delete Application routine: %s', 'matador-jobs' ), $files['path'] ) );
				}
			}
		}
	}
}
