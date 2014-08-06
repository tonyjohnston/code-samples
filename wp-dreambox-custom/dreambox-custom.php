<?php
/**
 * @package   Dreambox_Custom
 * @author    The Garrigan Lyman Group
 * @link      http://www.glg.com
 *
 * @wordpress-plugin
 * Plugin Name:       Dreambox Custom
 * Plugin URI:        http://www.dreambox.com
 * Description:       Customized functionality by The Garrigan Lyman Group
 * Version:           1.0.0
 * Author:            Tony Johnston
 * Author URI:        http://github.com/tonyjohnston
 * Text Domain:       dreambox-custom
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'public/class-dreambox-custom-public.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/class-responsive-tabbed-layout.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/class-content-previews.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/class-home-video-marquee.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/class-hub-video-marquee.php');
require_once( plugin_dir_path( __FILE__ ) . 'public/class-dreambox-custom-ipgeolocation.php');
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/class-product-previews.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/functions.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Dreambox_Custom', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Dreambox_Custom', 'deactivate' ) );

add_filter( 'scpt_show_admin_menu', create_function(null, 'return false;') );
add_action( 'plugins_loaded', array( 'Dreambox_Custom', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-dreambox-custom-admin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
	add_action( 'plugins_loaded', array( 'Dreambox_Custom_Admin', 'get_instance' ) );

}
