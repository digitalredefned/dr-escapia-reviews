<?php
/*
Plugin Name: Digital Redefined Escapia Review Plugin
Plugin URI: http://www.DigitalRedefined.com
Description: Imports reviews and sends notifications for submitting reviews
Version: 2020.11.01
Author: James McDermott
Author URI: https://www.DigitalRedefined.com
Text Domain: dr-escapia
License: Closed/Commercial
*/
! defined( 'ABSPATH' ) && exit;

add_action(
	'plugins_loaded',
	array( drEscapiaReviewsPlugin::get_instance(), 'plugin_setup' )
);


wp_schedule_event( strtotime('02:00:00'), 'daily', 'dr_run_review_email_request_processor' );



//function myprefix_custom_cron_schedule( $schedules ) {
//    $schedules['every_six_hours'] = array(
//        'interval' => 21600, // Every 6 hours
//        'display'  => __( '2a.m. Every Day' ),
//    );
//    return $schedules;
//}
//add_filter( 'cron_schedules', 'myprefix_custom_cron_schedule' );

//Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'dr_review_email_request_hook' ) ) {
	wp_schedule_event( strtotime('02:00:00'), 'daily', 'dr_review_email_request_hook' );
}

///Hook into that action that'll fire every six hours
add_action( 'dr_review_email_request_hook', 'dr_run_review_email_request_processor' );

function dr_run_review_email_request_processor(){
	error_log( 'Review Email Request Cron...');

	DRReviewEmailProcessor::processEmailNotifications();

}


register_activation_hook( __FILE__, array( 'drEscapiaReviewsPlugin', 'activation_hook' ) );
class drEscapiaReviewsPlugin {
	public static $version = '2020.09.01';

	/**
	 * Characters there we replace in the files.
	 *
	 * @var array
	 */
	protected static $file_replace = array( '.php', '_', '-', ' ' );

	/**
	 * Constructor
	 *
	 * @since  0.0.1
	 */
	public function __construct() {

		$this->page_sections = array();

		add_action( 'whitelist_options', array( $this, 'whitelist_custom_options_page' ),11 );
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  plugins_loaded
	 * @since    05/02/2013
	 */
	public function plugin_setup() {

		$this->load_classes();

		if ( ! is_admin() ) {
			return null;
		}

		$this->admin_pages_dir = plugin_dir_path( __FILE__ ) . 'patterns';


	}

	public static function activation_hook() {

	}

	public static function init() {

		self::register_scripts();
	}

	/**
	 * Points the class, singleton.
	 *
	 * @access public
	 * @since  0.0.1
	 */
	public static function get_instance() {

		static $instance;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}
	/**
	 * Scans the plugins subfolder and include files.
	 *
	 * @since   05/02/2013
	 * @return  void
	 */
	protected function load_classes() {

		// Load required classes.
		foreach ( glob( __DIR__ . '/admin-pages/*.php' ) as $path ) {

			require_once $path;
		}

		foreach ( glob( __DIR__ . '/includes/*.php' ) as $path ) {

			require_once $path;
		}

	}


	/**
	 * If SCRIPT_DEBUG constant is set, uses the un-minified version.
	 *
	 * @since  Unknown
	 * @access public
	 */
	public static function register_scripts() {
//		$base_url =DRESCCommon::get_base_url();
//		$version= drEscapiaPlugin::$version;
//
//		wp_register_script( 'drec_tooltip_init', $base_url . "/js/tooltip_init.js", array( 'jquery-ui-tooltip' ), $version );
//		wp_register_style( 'drec_tooltip', $base_url . "/css/tooltip.css", array( 'drec_font_awesome' ), $version );
//		wp_register_style( 'drec_font_awesome', $base_url . "/css/font-awesome.css", null, $version );
	}

}