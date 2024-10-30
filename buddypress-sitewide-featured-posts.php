<?php
/*
Plugin Name: BuddyPress Sitewide Featured Posts
Plugin URI:  http://dev.benoitgreant.be/blog/category/buddypress/buddypress-sitewide-featured-posts/
Description: Choose and display featured posts (sitewide) in a widget
Version: 0.3
Revision Date: february 21, 2010
Requires at least: WPMU 2.9, BuddyPress 1.2
Tested up to: WPMU 2.9.1.1, BuddyPress 1.2
License: (Classifieds: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Author: G.Breant
Author URI: http://dev.benoitgreant.be
Site Wide Only: true
*/


/*** Make sure BuddyPress is loaded ********************************/
if ( !function_exists( 'bp_core_install' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) )
		require_once ( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
	else
		return;
}
/*******************************************************************/

function bp_sitewide_featured_posts_init() {
	define ( 'BP_SITEWIDE_FEATURED_POSTS_IS_INSTALLED', 1 );
	define ( 'BP_SITEWIDE_FEATURED_POSTS_VERSION', '0.1' );
	define ( 'BP_SITEWIDE_FEATURED_POSTS_DB_VERSION', '0.1' );
	define ( 'BP_SITEWIDE_FEATURED_POSTS_PLUGIN_NAME', 'buddypress-sitewide-featured-posts' );
	define ( 'BP_SITEWIDE_FEATURED_POSTS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_SITEWIDE_FEATURED_POSTS_PLUGIN_NAME );
	define ( 'BP_SITEWIDE_FEATURED_POSTS_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_SITEWIDE_FEATURED_POSTS_PLUGIN_NAME );


	/////////

	// lets do it
	require_once 'bp-sitewide-featured-posts.php';
}

if ( defined( 'BP_VERSION' ) )
	bp_sitewide_featured_posts_init();
else
	add_action( 'bp_init', 'bp_sitewide_featured_posts_init' );

?>