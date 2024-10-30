<?php

/**
 * Included files
  */
/* widget */
require ( BP_SITEWIDE_FEATURED_POSTS_PLUGIN_DIR . '/bp-sitewide-featured-posts-widgets.php' );

/* The filters file should create and apply filters to component output functions. */
//require ( WP_PLUGIN_DIR . '/bp-sitewide-featured-posts/bp-sitewide-featured-posts-filters.php' );

/**
 * Installs and/or upgrades
 */
function bp_sitewide_featured_posts_install() {
	global $wpdb, $bp;
	
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	 
	$sql[] = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."sitewide_featured_posts` (
	`featured_post_id` bigint(20) unsigned NOT NULL auto_increment,
	`blog_id` bigint(20),
	`site_id` bigint(20),
	`blog_public` int(2),
	`post_id` bigint(20),
	`post_author` bigint(20),
	`post_title` TEXT,
	`post_content` TEXT,
	`post_permalink` TEXT,
	`post_published_gmt` datetime NOT NULL default '0000-00-00 00:00:00',
	`post_published_stamp` VARCHAR(255),
	`post_modified_gmt` datetime NOT NULL default '0000-00-00 00:00:00',
	`post_modified_stamp` VARCHAR(255),
	PRIMARY KEY  (`featured_post_id`)
	) {$charset_collate};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
	
	dbDelta($sql);
	
	update_site_option( 'bp-sitewide-featured-posts-db-version', BP_SITEWIDE_FEATURED_POSTS_DB_VERSION );
}
	
/**
 * bp_sitewide_featured_posts_installed()
 */
function bp_sitewide_featured_posts_check_installed() {	
	global $wpdb, $bp;

	if ( !is_site_admin() )
		return false;
	
	//require ( WP_PLUGIN_DIR . '/bp-sitewide-featured-posts/sitewide-featured-posts-admin.php' );

	//add_submenu_page( 'bp-general-settings', __( 'Featured Posts', 'bp-featured-posts' ), __( 'Sitewide Featured Posts Admin', 'bp-sitewide-featured-posts' ), 'manage-options', 'bp-sitewide-featured-posts-settings', 'bp_sitewide_featured_posts_admin' );	

	/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-sitewide-featured-posts-db-version') < BP_EXAMPLE_DB_VERSION )
		bp_sitewide_featured_posts_install();
}
add_action( 'admin_menu', 'bp_sitewide_featured_posts_check_installed' );

/**
* non BP functions
*/

function bp_sitewide_featured_posts_insert_update($tmp_post_ID){
	global $wpdb, $current_site;
	
	$tmp_blog_public = get_blog_status( $wpdb->blogid, 'public');
	$tmp_blog_archived = get_blog_status( $wpdb->blogid, 'archived');
	$tmp_blog_mature = get_blog_status( $wpdb->blogid, 'mature');
	$tmp_blog_spam = get_blog_status( $wpdb->blogid, 'spam');
	$tmp_blog_deleted = get_blog_status( $wpdb->blogid, 'deleted');
	
	$tmp_post = get_post($tmp_post_ID);

	if ($tmp_post->post_type == 'revision'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_post->post_status != 'publish'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_blog_archived == '1'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_blog_mature == '1'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_blog_spam == '1'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_blog_deleted == '1'){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_post->post_title == ''){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else if ($tmp_post->post_content == ''){
		bp_sitewide_featured_posts_delete($tmp_post_ID);
	} else {

	
		//delete post
		bp_sitewide_featured_posts_delete($tmp_post_ID);

		//post does not exist - insert site post

		$wpdb->query("INSERT INTO `".$wpdb->base_prefix."sitewide_featured_posts` (
		`blog_id` ,
		`site_id` ,
		`blog_public` ,
		`post_id` ,
		`post_author` ,
		`post_title` ,
		`post_content` ,
		`post_permalink` ,
		`post_published_gmt` ,
		`post_published_stamp` ,
		`post_modified_gmt` ,
		`post_modified_stamp`
		)
		VALUES (
		'".$wpdb->blogid."', '".$wpdb->siteid."', '".$tmp_blog_public."', '".$tmp_post_ID."', '".$tmp_post->post_author."', '".addslashes($tmp_post->post_title)."', '".addslashes($tmp_post->post_content)."', '".get_permalink($tmp_post_ID)."', '".$tmp_post->post_date_gmt."', '".strtotime($tmp_post->post_date_gmt)."', '".$tmp_post->post_modified_gmt."', '".time()."');");
	}
}
function bp_sitewide_featured_posts_delete($tmp_post_ID){
	global $wpdb;

	//delete site post
	$wpdb->query( "DELETE FROM ".$wpdb->base_prefix."sitewide_featured_posts WHERE post_id = '" . $tmp_post_ID . "' AND blog_id = '" . $wpdb->blogid . "'" );
}

function bp_sitewide_featured_posts_delete_by_post_id($tmp_site_post_ID, $tmp_blog_ID) {
	global $wpdb;
	//delete site post
	$wpdb->query( "DELETE FROM ".$wpdb->base_prefix."sitewide_featured_posts WHERE featured_post_id = '" . $tmp_site_post_ID . "'" );
}

function bp_sitewide_featured_posts_public_update(){
	global $wpdb;
	if ( $_GET['updated'] == 'true' ) {
		$wpdb->query("UPDATE " .  BP_SITEWIDE_FEATURED_POSTS_TABLE ." SET blog_public = '" . get_blog_status( $wpdb->blogid, 'public') . "' WHERE blog_id = '" . $wpdb->blogid . "' AND site_id = '" . $wpdb->siteid . "'");
	}
}
function bp_sitewide_featured_posts_change_remove($tmp_blog_ID){
	global $wpdb, $current_user, $current_site;
	//delete site posts
	$query = "SELECT * FROM ".$wpdb->base_prefix."sitewide_featured_posts WHERE blog_id = '" . $tmp_blog_ID . "' AND site_id = '" . $wpdb->siteid . "'";
	$blog_site_posts = $wpdb->get_results( $query, ARRAY_A );
	if (count($blog_site_posts) > 0){
		foreach ($blog_site_posts as $blog_site_post){
			bp_sitewide_featured_posts_delete_by_post_id($blog_site_post['featured_post_id'], $tmp_blog_ID);
		}
	}
}

//This adds the post to the table

function bp_sitewide_featured_posts_delete_post($post_id) {
	if(bp_sitewide_featured_posts_exists($post_id)) {
		bp_sitewide_featured_posts_delete($post_id);
	}
}

function bp_sitewide_featured_posts_handle($post_id) {

	// authorization
	if (!current_user_can('edit_post', $post_id))
		return $post_id;
		
	// origination and intention
	if (!wp_verify_nonce($_POST['bp_sitewide_featured_post_verify'], 'bp_sitewide_featured_post'))
		return $post_id;

	//save as featured
	if(isset($_POST['bp_sitewide_featured_post_check']) and $_POST['bp_sitewide_featured_post_check'] == "featured" and !bp_sitewide_featured_posts_exists($post_id)) {
		bp_sitewide_featured_posts_insert_update($post_id);
		
	//remove post
	}elseif(empty($_POST['bp_sitewide_featured_post_check'])){
		bp_sitewide_featured_posts_delete_post($post_id);
	}
}


//Checks if the post is already in the table
function bp_sitewide_featured_posts_exists($post_id,$blog_id=false) {
	global $wpdb;
	
	if (!$blog_id) {
		global $blog_id;
	}
	$check = "SELECT featured_post_id FROM ".$wpdb->base_prefix."sitewide_featured_posts WHERE post_id = $post_id AND blog_id=$blog_id";
	$result = $wpdb->get_var($check);
	
	return $result;
}

//Add Metabox
function bp_sitewide_featured_posts_metabox() {
    if ( function_exists('add_meta_box') ) {
        add_meta_box('bp-sitewide-featured-posts-settings',__('Feature this post Sitewide','bp-featured-posts'),'bp_sitewide_featured_posts_metabox_content','post','normal');
        add_meta_box('bp-sitewide-featured-posts-settings',__('Feature this post Sitewide','bp-featured-posts'),'bp_sitewide_featured_posts_metabox_content','page','normal');
    }
}

// checkbox on the admin page
function bp_sitewide_featured_posts_metabox_content() {
	global $post, $current_user;
	global $wpdb;
	
	
    get_currentuserinfo();
	
	//user is an admin
	if (!$current_user->allcaps['level_10']) return false;

	$extra = "";
	
	if(isset($post->ID)) {
		$post_id = $post->ID;
		if(bp_sitewide_featured_posts_exists($post_id)) { $extra = 'checked="checked"'; }
	}
	echo '<p><label for="featured">'.__( 'Add this post/page to Featured Posts', 'bp-featured-posts' ).'</label>
			<input type="checkbox" class="sldr_post" name="bp_sitewide_featured_post_check" value="featured" '.$extra.' />
			<input type="hidden" name="bp_sitewide_featured_post_verify" id="bp_sitewide_featured_post_verify" value="'.wp_create_nonce('bp_sitewide_featured_post').'" />
		</p>';

}

////
//Add Metabox
add_action('admin_menu', 'bp_sitewide_featured_posts_metabox');
//Save Featured
add_action('publish_post', 'bp_sitewide_featured_posts_handle');
add_action('publish_page', 'bp_sitewide_featured_posts_handle');
add_action('edit_post', 'bp_sitewide_featured_posts_handle');
//Delete Post
add_action('trash_post', 'bp_sitewide_featured_posts_delete_post');
add_action('delete_post', 'bp_sitewide_featured_posts_delete_post');
?>