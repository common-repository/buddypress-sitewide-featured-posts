<?php

/* Register widgets for classifieds component */
function bp_sitewide_featured_posts_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Sitewide_Featured_Posts_Widget");') );	
}
add_action( 'plugins_loaded', 'bp_sitewide_featured_posts_register_widgets' );

class BP_Sitewide_Featured_Posts_Widget extends WP_Widget {
	function bp_sitewide_featured_posts_widget() {
		parent::WP_Widget( false, $name = __( 'Featured Sitewide Posts', 'bp-sitewide-featured-posts' ) );
	}

	function widget($args, $instance) {
		global $bp,$wpdb;
		
	    extract( $args );
		
		echo $before_widget;
		echo $before_title
		   . $widget_name 
		   . $after_title; ?>
		   <?php
		//=================================================//
		$query = "SELECT * FROM ".$wpdb->base_prefix."sitewide_featured_posts ORDER BY post_published_stamp DESC LIMIT " . $instance['number'];

		$posts = $wpdb->get_results( $query, ARRAY_A );
		
		if (count($posts) > 0):
		echo'<ul id="featured-posts" class="item-list">';
			foreach ( $posts as $post ) : 
				//allows you to modify the post content (ex. to add a "featured" image).
				$post=apply_filters('sitewide_featured_item',$post);
			?>
				<li>
				<?php if ($instance['avatars']) {?>
					<div class="item-avatar">
						<a href="<?php echo apply_filters( 'bp_sitewide_featured_post_permalink', $post['post_permalink'] ) ?>" title="<?php echo apply_filters( 'the_title', $post['post_title'] ) ?>"><?php echo bp_core_fetch_avatar( array( 'item_id' => $post['post_author'], 'type' => 'thumb') );?></a>
					</div>
				<?php }?>
					<div class="item">
						<h4 class="item-title"><a href="<?php echo apply_filters( 'bp_sitewide_featured_post_permalink', $post['post_permalink'] ) ?>" title="<?php echo apply_filters( 'the_title', $post['post_title'] ) ?>"><?php echo apply_filters( 'the_title', $post['post_title'] ) ?></a></h4>                           
							<div class="item-content"><?php 
							if ($instance['length']) {
								echo bp_create_excerpt($post['post_content'],$instance['length']);
							}else {
								echo $post['post_content'];
							}
							?></div>
						<div class="item-meta"><em><?php printf( __( 'by %s from the blog <a href="%s">%s</a>', 'buddypress' ), bp_core_get_userlink( $post['post_author'] ), get_blog_option( $post['blog_id'], 'siteurl' ), get_blog_option( $post['blog_id'], 'blogname' ) ) ?></em></div>
					</div>
				</li>
			<?php endforeach;

		else :

	?>

			<div class="widget-error">
				<?php _e('There are no featured posts to display.', 'bp-sitewide-featured-posts') ?>
			</div>

		<?php endif; 
		//=================================================//
		?>
			
		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['length'] = strip_tags( $new_instance['length'] );
		$instance['avatars'] = strip_tags( $new_instance['avatars'] );

		return $instance;
	}

	function form( $instance ) {

	
		$instance = wp_parse_args( (array) $instance, array( 'title'=>__( 'Featured Posts', 'bp-sitewide-featured-posts'),'number' => 5,'length'=>55,'avatars'=>1) );
		$title = strip_tags( $instance['title'] );
		$number = strip_tags( $instance['number'] );
		$length = strip_tags( $instance['length'] );
		$avatars = strip_tags( $instance['avatars'] );

		?>
        <p>   
			<label for="bp-sitewide-featured-posts-title" style="line-height:35px;display:block;"><?php _e( 'Title', 'buddypress' ); ?>:<br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo attribute_escape( $title ); ?>" type="text" style="width:95%;">
			</label>
		</p>
		<p>
			<label for="sitewide-featured-posts-number" style="line-height:35px;display:block;"><?php _e( 'Number of posts', 'bp-sitewide-featured-posts' ); ?>:<br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo attribute_escape( $number ); ?>" type="text" style="width:95%;">
			</label>
		</p>
		<p>
			<label for="sitewide-featured-posts-length" style="line-height:35px;display:block;"><?php _e( 'Text length', 'bp-sitewide-featured-posts' ); ?>:<br /><small><?php _e( 'Empty= show full posts', 'bp-sitewide-featured-posts' ); ?></small>
				<input class="widefat" id="<?php echo $this->get_field_id( 'length' ); ?>" name="<?php echo $this->get_field_name( 'length' ); ?>" value="<?php echo attribute_escape( $length ); ?>" type="text" style="width:95%;">
			</label>
		</p>
		<p>
			<label for="sitewide-featured-posts-avatars" style="line-height:35px;display:block;"><?php _e( 'Avatars', 'buddypress' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'avatars' ); ?>" id="<?php echo $this->get_field_id( 'avatars' ); ?>" style="width:95%;">
					<option value="1" <?php if ($avatars){ echo 'selected="selected"'; } ?> ><?php _e('Show'); ?></option>
					<option value="0" <?php if (!$avatars){ echo 'selected="selected"'; } ?> ><?php _e('Hide'); ?></option>
				</select>
			</label>
		</p>

		<input type="hidden" name="sitewide-featured-posts-submit" id="sitewide-featured-posts-submit" value="1" />


	<?php
	}
}
?>
