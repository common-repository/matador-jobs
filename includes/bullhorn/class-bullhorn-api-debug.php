<?php
/**
 * matador.
 * User: Paul
 * Date: 2018-01-25
 *
 */

namespace matador;


if ( ! defined( 'WPINC' ) ) {
	die;
}

class Bullhorn_Api_Debug extends Bullhorn_Connection {

	static $log = array();

	public function __construct() {

		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			parent::__construct();
		}

		add_action( 'admin_menu', array( __CLASS__, 'matador_options_fields' ) );
		add_action( 'wp_ajax_matador_api_test', array( $this, 'matador_api_test' ) );
		add_action( 'wp_ajax_matador_api_job_sync_debug', array( $this, 'matador_api_job_sync' ) );
	}


	public static function matador_options_fields( $fields ) {

		add_submenu_page(
			null,
			__( 'Welcome', 'matador-jobs' ),
			__( 'Welcome', 'matador-jobs' ),
			'manage_options',
			'matador_api_debug',
			array( __CLASS__, 'matador_api_debug_render_hidden_page' )
		);

	}


	public static function matador_api_debug_render_hidden_page() {

		?>
        <style>
            #matador_api_test_output_form label {
                width: 10%;
                display: inline-block;
            }

        </style>

        <h1>Debug the Bullhorn API</h1>
        <a href="http://bullhorn.github.io/rest-api-docs">http://bullhorn.github.io/rest-api-docs</a>
        <i>request( $api_method = null, $params = array(), $http_method = 'GET', $body = null, $request_args = null )</i>
        <form id="matador_api_test_output_form">
            <label for="examples">Examples</label><br />
            <select name="examples" id="examples">
                <option value="clear">Load Example</option>
                <option value="JobOrder">query/JobOrder</option>
                <option value="settings">Settings</option>
                <option value="options">Options</option>
                <option value="meta">Meta</option>
                <option value="meta_candidate">meta/Candidate</option>

                <option value="get_candidate">entity/Candidate</option>

                <option value="get_skills">options/Skill</option>
                <option value="get_candidate_primarySkills">entity/Candidate/XX/primarySkills</option>
                <option value="set_candidate_primarySkills">Set: entity/Candidate/XX/primarySkills/XXX</option>

                <option value="get_categories">options/Category</option>
                <option value="get_candidate_categories">entity/Candidate/XX/categories</option>
                <option value="set_candidate_categories">Set: entity/Candidate/XX/categories/XXX,XX</option>

                <option value="?">add_more</option>

            </select>
            <br />
            <label for="api_method"> api_method: </label><br /><input name="api_method" id="api_method" style="width: 90%" />
            <label for="http_method"> http_method: (GET) </label><br /><select name="http_method" id="http_method">
                <option value="GET">GET</option>
                <option value="PUT">PUT</option>
                <option value="POST">POST</option>
                <option value="DELETE">DELETE</option>
            </select>
            <br />
            <label for="api_params"> params: (key|val~key|val) </label><br /><textarea name="api_params" id="api_params" style="width: 90%"></textarea>
            <label for="api_body"> body: (key|val~key|val) </label><br /><textarea name="api_body" id="api_body" style="width: 90%"></textarea>
            <label for="api_request_args"> request_args: (key|val~key|val) </label><br /><textarea name="api_request_args" id="api_request_args" style="width: 90%"></textarea>

            <br />
            <button id="matador_api_test" style="width: 10%">Run</button>
            <div  style="float: right;margin-right: 10%;">
                <input id="bhid" type="number" placeholder="bhid">
                <button id="job_sync"">debug job sync</button>
            </div>
        </form>

        <pre id="matador_api_test_output">
          <?php
          $credentials = get_option( Matador::variable( 'bullhorn_api_credentials_key' ) );

          echo '<!--
					';
          print_r( $credentials );
          echo serialize( $credentials );
          echo '
          -->';

          ?>
		</pre>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                jQuery('#matador_api_test').on('click', function (e) {
                    e.preventDefault();
                    jQuery('#matador_api_test_output').html('Loading');
                    var data = {
                        'action': 'matador_api_test',
                        'api_method': jQuery('#api_method').val(),
                        'http_method': jQuery('#http_method').val(),
                        'api_params': jQuery('#api_params').val(),
                        'api_body': jQuery('#api_body').val(),
                        'api_request_args': jQuery('#api_request_args').val(),
                        'nonce': '<?php echo wp_create_nonce( "matador_api_test" ); ?>'
                    };

                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery('#matador_api_test_output').html(response);
                    });
                });
                jQuery('#job_sync').on('click', function (e) {
                    e.preventDefault();
                    jQuery('#matador_api_test_output').html('Loading');
                    var data = {
                        'action': 'matador_api_job_sync_debug',
                        'nonce': '<?php echo wp_create_nonce( "matador_api_test" ); ?>',
                        'bhid': jQuery( '#bhid' ).val(),
                    };
                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery('#matador_api_test_output').html(response);
                    });
                });

                jQuery('#examples').on('change', function (e) {

                    switch (jQuery('#examples').val()) {


                        case 'clear':
                            jQuery('#api_method').val('');
                            jQuery('#http_method').val('');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break

                        case 'JobOrder':
                            jQuery('#api_method').val('query/JobOrder');
                            jQuery('#api_params').val("fields|id,title~ where|isOpen=true AND isDeleted=false AND status<>'Archive'~count|50");
                            jQuery('#http_method').val('GET');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;
                        case 'settings':
                            jQuery('#api_method').val('settings');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;

                        case 'options':
                            jQuery('#api_method').val('options');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;

                        case 'meta':
                            jQuery('#api_method').val('meta');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;


                        case 'meta_candidate':
                            jQuery('#api_method').val('meta/Candidate');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('fields|*');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;

                        case 'get_candidate':
                            jQuery('#api_method').val('entity/Candidate/XXXX');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('fields|id,name');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;

                        case 'get_skills':
                            jQuery('#api_method').val('options/Skill');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;
                        case 'get_candidate_primarySkills':
                            jQuery('#api_method').val('entity/Candidate/XX/primarySkills');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('fields|id,name');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;
                        case 'set_candidate_primarySkills':
                            jQuery('#api_method').val('entity/Candidate/XXXX/primarySkills/XXXX');
                            jQuery('#http_method').val('PUT');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;

                        case 'get_categories':
                            jQuery('#api_method').val('options/Category');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;
                        case 'get_candidate_categories':
                            jQuery('#api_method').val('entity/Candidate/XX/categories');
                            jQuery('#http_method').val('GET');
                            jQuery('#api_params').val('fields|id,name');
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;
                        case 'set_candidate_categories':
                            jQuery('#api_method').val('entity/Candidate/XX/categories/XXX,XXX');
                            jQuery('#http_method').val('PUT');
                            jQuery('#api_params').val();
                            jQuery('#api_body').val('');
                            jQuery('#api_request_args').val('');
                            break;


                    }


                });


            });
        </script>
		<?php

	}

	public function matador_api_job_sync() {
		// Handle request then generate response using WP_Ajax_Response
		check_ajax_referer( 'matador_api_test', 'nonce' );
		add_action( 'matador_event_log_before_write', array( __CLASS__, 'matador_event_write' ), 10, 3 );
		if ( isset( $_REQUEST['bhid'] ) && ! empty( absint( $_REQUEST['bhid'] ) )  && 0 < absint( $_REQUEST['bhid'] ) ) {
		    add_filter( 'matador_bullhorn_import_the_job_where', function ( $where ){
			    $where .= ' AND id=' . absint( $_REQUEST['bhid'] );
		        return $where;
            } );
		    add_filter( 'matador_bullhorn_delete_missing_job_on_import', '__return_false' );
		    // when sync a job by ID we don't what to allow it to be skipped
		    remove_all_filters('matador_bullhorn_import_skip_job_on_update' );
		}

		$bullhorn = new Bullhorn_Import();
		$bullhorn->sync();

		print_r( self::$log );
		// Don't forget to stop execution afterward.
		wp_die();
	}

	public static function matador_event_write( $code, $message ) {
		self::$log[] = $code . ':' . $message;
	}


	public function matador_api_test() {
		// Handle request then generate response using WP_Ajax_Response

		check_ajax_referer( 'matador_api_test', 'nonce' );
		$params     = array();
		$api_method = null;
		if ( isset( $_REQUEST['api_method'] ) && ! empty( $_REQUEST['api_method'] ) ) {
			$api_method           = sanitize_text_field( $_REQUEST['api_method'] );
			$params['api_method'] = $api_method;
		}

		$api_params = array();
		if ( isset( $_REQUEST['api_params'] ) && ! empty( $_REQUEST['api_params'] ) ) {
			$api_params           = $this->make_array( $_REQUEST['api_params'] );
			$params['api_params'] = $api_params;
		}

		$http_method = 'GET';
		if ( isset( $_REQUEST['http_method'] ) && ! empty( $_REQUEST['http_method'] ) ) {
			$http_method           = sanitize_text_field( $_REQUEST['http_method'] );
			$params['http_method'] = $http_method;
		}

		$body = null;
		if ( isset( $_REQUEST['api_body'] ) && ! empty( $_REQUEST['api_body'] ) ) {
			$body           = $this->make_json( $_REQUEST['api_body'] );
			$params['body'] = $body;
		}

		$request_args = null;
		if ( isset( $_REQUEST['api_request_args'] ) && ! empty( $_REQUEST['api_request_args'] ) ) {
			$request_args           = $this->make_array( $_REQUEST['api_request_args'] );
			$params['request_args'] = $request_args;
		}
		echo '<pre>';
		if ( null !== $api_method ) {
			/*
			* @param string $api_method string Bullhorn API method, default null
			* @param array $params array of API request parameters
			* @param string $http_method http verb for request
			* @param array|object $body data to be sent with request as JSON
			* @param array $request_args array of arguments for wp_remote_request() function
			 *
			 * request( $api_method = null, $params = array(), $http_method = 'GET', $body = null, $request_args = null )
			*/

			print_r( $params );

			try {

				$this->login();

				$request = $this->request( $api_method, $api_params, $http_method, $body, $request_args );

				print_r( $request );

				echo json_encode( $request );

			} catch ( Exception $e ) {

				print_r( $e );
			}
		} else {

			echo '$api_method is needed';
		}
		echo '</pre>';

		// Don't forget to stop execution afterward.
		wp_die();
	}

	function make_array( $string ) {

		$keys = explode( '~', $string );
		$a    = array();
		foreach ( $keys as $key_val ) {

			$k                  = explode( '|', $key_val );
			$a[ trim( $k[0] ) ] = str_replace( array( "\'", '\"' ), "'", trim( $k[1] ) );
		}

		return $a;
	}

	function make_json( $string ) {
		return json_decode( str_replace( [ "\'", '\"' ], '"', $string ) );
	}

}