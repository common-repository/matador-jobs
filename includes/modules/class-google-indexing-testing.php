<?php
/**
 * matador.
 * User: Paul
 * Date: 2019-01-12
 *
 */

namespace matador;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Google_Indexing_Testing {

	public function __construct() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'matador_menu' ) );
	}

	public static function matador_menu() {
		add_submenu_page(
			'',
			__( 'Welcome', 'matador-jobs' ),
			__( 'Welcome', 'matador-jobs' ),
			'manage_options',
			'matador_google_test',
			array( __CLASS__, 'matador_render_google_text_page' )
		);
	}

	public static function matador_render_google_text_page() {

		?>
		<style>
			#matador_api_test_output_form label {
				width: 10%;
				display: inline-block;
			}

		</style>

		<h1>Debug and Test the Google Index API</h1>
		<a href="https://matadorjobs.com/support/documentation/setting-up-google-indexing/">Setting Up Google Indexing
			Help</a>

		<?php

		// turn google index on

		$index = new Google_Indexing_Api();

		$lastest_job = get_posts( 'post_type=' . Matador::variable( 'post_type_key_job_listing' ) . '&posts_per_page=1' );

		$permalink = $lastest_job[0]->guid;

		echo '<pre>';
		$index->add( $permalink, true );

		echo '</pre>';
	}
}