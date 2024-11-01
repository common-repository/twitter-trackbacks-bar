<?php
/*
Plugin Name: Twitter Trackbacks Bar
Plugin URI: http://www.alecsy.fr/creer-son-site-internet/plugins-wordpress/twitter_trackbacks_bar
Description: Twitter Trackbacks Bar is plugin to integrate tweets that mention your post into your blog. It separates the trackbacks and the pings of comments. Each tweet  is comes with reply and retweet's links to get more readers engaged in your  conversation's story. This plugin must be installed with Wordpress's plugin Topsy, that integrate your tweets in comments in your blog.
Version: 1.0
Author: Alecsy
Author URI:  http://www.alecsy.fr
Copyright: Copyright 2010 by EntreWeb. This software is distributed under the terms of the GNU GPL as defined in http://www.gnu.org/licenses/gpl.html.
*/

/*  Twitter Trackbacks Bar
	
	Twitter Trackbacks Bar is plugin to integrate tweets that mention your post into your blog. It separates the trackbacks and the pings of comments. Each tweet  is comes with reply and retweet's links to get more readers engaged in your  conversation's story. This plugin must be installed with Wordpress's plugin Topsy, that integrate your tweets in comments in your blog.

	Author Alexis César (Alecsy)
	http://www.alecsy.fr
	
	Thanks a lot at MoreTechTips (http://www.moretechtips.net/2009/11/twitter-trackbacks-widget-jquery-plugin.html) for the JS's file (jquery.twittertrackbacks-1.0.min.js) and at John Godley (http://urbangiraffe.com/) for the two functions ping_comments and ping_comments_number.
	
	Released under the GPL license
	http://www.gnu.org/licenses/gpl.html
	
	**********************************************************************
	This program is distributed in the hope that it will be useful, but
	     WITHOUT ANY WARRANTY; without even the implied warranty of
	        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	**********************************************************************
*/

	add_action('init', 'ttb_init');
	function ttb_init() {
		load_plugin_textdomain('twitter_trackback', 'wp-content/plugins/twitter_trackback/lang');
	}
	
	// Load WP-Config File If This File Is Called Directly
	if (!function_exists('add_action')) {
		$wp_root = '../../..';
		if (file_exists($wp_root.'/wp-load.php')) {
			require_once($wp_root.'/wp-load.php');
		} else {
			require_once($wp_root.'/wp-config.php');
		}
	}
	
	$opt_name = 'ttb_view_trackbacks';

	if ( !is_admin() ) {

	    // Read in existing option value from database
	    list($opt_val, $opt_val_2, $opt_val_3) = explode(";", get_option( $opt_name ));

		/* Insert CSS file */ 
    	function add_ttb_css() {
			global $opt_val_2;
			
			if ( is_single() ) {        
        		if ($opt_val_2 == "fl") :
					$myStyleUrl = WP_PLUGIN_URL . '/twitter_trackback/twitter_trackback_fl.css';
    	    		$myStyleFile = WP_PLUGIN_DIR . '/twitter_trackback/twitter_trackback_fl.css';
				elseif ($opt_val_2 == "ttb") :
					$myStyleUrl = WP_PLUGIN_URL . '/twitter_trackback/twitter_trackback_ttb.css';
        			$myStyleFile = WP_PLUGIN_DIR . '/twitter_trackback/twitter_trackback_ttb.css';
				else :
					$myStyleUrl = WP_PLUGIN_URL . '/twitter_trackback/twitter_trackback_tb.css';
    	    		$myStyleFile = WP_PLUGIN_DIR . '/twitter_trackback/twitter_trackback_tb.css';
				endif;
				
        		if ( file_exists($myStyleFile) ) {
            	wp_register_style('twitter_trackback', $myStyleUrl);
            	wp_enqueue_style( 'twitter_trackback');
        		}
	      	}
    	}
		
		// Insert JS files
		function add_ttb_js() {
			global $opt_val_3;
						
			if ( is_single() ) {
        		if ($opt_val_3 == 1) : // Si l'option 3 est à No on insère le JS
				$myScriptUrl = WP_PLUGIN_URL . '/twitter_trackback/jquery.min.js';
				$myScriptFile = WP_PLUGIN_DIR . '/twitter_trackback/jquery.min.js';
				if ( file_exists($myScriptFile) ) {
					wp_register_script('twitter_jquery', $myScriptUrl);
					wp_enqueue_script('twitter_jquery');
				}
				endif;

				$myScriptUrl = WP_PLUGIN_URL . '/twitter_trackback/jquery.twittertrackbacks-1.0.min.js';
				$myScriptFile = WP_PLUGIN_DIR . '/twitter_trackback/jquery.twittertrackbacks-1.0.min.js';
				if ( file_exists($myScriptFile) ) {
					wp_register_script('twitter_trackback', $myScriptUrl);
					wp_enqueue_script('twitter_trackback');
				}
			}
		}
		// Fin JS files

		function ping_comments ($comments)
		{
			global $pings, $comment;
 
			// Initialise the variables
			$pings = $newcomments = array ();
 
			// Loop through existing comments
			foreach ($comments AS $comment)
			{
				if (get_comment_type () == 'comment')
				$newcomments[] = $comment;
				else
				$pings[] = $comment;
			}
 
			// Return the comments without any pings
			return $newcomments;
		}
 
		// Insert div trackbacks
		function add_ttb_html ($comments) {

			global $comment, $wpdb, $post, $opt_val, $opt_val_2;
			
			// Select trackbacks number
			$sel_tr_pi = $wpdb->get_var($wpdb->prepare("SELECT Count(*) FROM $wpdb->comments WHERE comment_approved = '1' AND ( comment_type = 'pingback' OR comment_type = 'trackback' ) AND comment_post_ID = '%s'", $post->ID));		    

				echo '<div class="comments" id="trackbacks">';
				if ($opt_val == "title") :
					echo '<h3 id="trackbacks-title">';
					printf(_n( 'One Trackback to %2$s', '%1$s Trackbacks to %2$s', $sel_tr_pi), $sel_tr_pi ,'<em>' . get_the_title() . '</em>');
					echo '</h3>';				
				endif;

				if ($opt_val_2 == "fl") :
					echo "<div class=\"twitter-trackbacks\" options=\"{
   							url:'" . get_permalink() . "' 
							,n:8 
							,show_n:0
							,inf_tip:1
						}\"></div>";
				elseif ($opt_val_2 == "ttb") :
					echo "<div class=\"twitter-trackbacks\" options=\"{
	url:'" . get_permalink() . "'
	,n:9
	,show_n:3
	,stay_time:8000
	,animate:'height'
	,inf_only:1
	,inf_tip:1
	}\">loading..</div>";
				else :
					echo "<div class=\"twitter-trackbacks\" options=\"{
							url:'" . get_permalink() . "'
						}\"></div>";
				endif;
				echo '</div>';
		}	
		// Fin div trackbacks


		// Adjust comments number so comments_number() function is correct
		function ping_comments_number ($num)
		{
			global $pings;
			return $num - count ($pings);
		}
 
		// Hook into WordPress filters
		add_action('wp_print_styles', 'add_ttb_css');
	 	add_action('wp_print_scripts', 'add_ttb_js');

		add_filter ('get_comments_number', 'ping_comments_number');
		add_filter ('comments_array', 'ping_comments');
		add_filter('comments_template', 'add_ttb_html');		
		
	}


	else {
	
		add_action('admin_menu', 'ttb_menu');

		function ttb_menu() {

			add_options_page('Twitter Trackbacks Bar Options', 'TTB', 'manage_options', 'ttb', 'ttb_options');

		}

		function ttb_options() {
			global $opt_name;
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			// variables for the field and option names 
		    $hidden_field_name = 'ttb_submit_hidden';
		    $data_field_name = 'ttb_view_tp';
			$data_field_name_2 = 'ttb_view_type';
			$data_field_name_3 = 'ttb_off_jquery';

		    // See if the user has posted us some information
		    // If they did, this hidden field will be set to 'Y'
		    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
	    	    // Read their posted value
		        $op_value = $_POST[ $data_field_name ] . ";" . $_POST[ $data_field_name_2 ] . ";" . $_POST[ $data_field_name_3 ];

		        // Save the posted value in the database
		        update_option( $opt_name, $op_value );
				// Put an settings updated message on the screen
				?>
				<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
				<?php
		    }

		    // Read in existing option value from database
		    list($opt_val, $opt_val_2, $opt_val_3) = explode(";", get_option( $opt_name ));

			?>
			<div class="wrap">
	        <h2>Twitter TrackBacks Bar Options</h2>
    	    <p><?php _e('Thank you to use','twitter_trackback') ?> Twitter TrackBacks Bar.
            <?php _e('I invite you to visit the plugin page on the website of the author','twitter_trackback') ?> : <a href="http://www.alecsy.fr/creer-son-site-internet/plugins-wordpress/twitter_trackbacks_bar" target="_blank">Twitter TrackBacks Bar</a>.</p>

            <p>&nbsp;</p>
            <form name="form1" method="post" action="">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

			<p><?php _e("View title with the number of trackbacks ?", 'twitter_trackback' ); ?>  
			<select name="<?php echo $data_field_name; ?>">
            	<option value="title" size="20"<?php if ($opt_val=="title") echo " selected"; ?>><?php _e("With title", 'twitter_trackback' ); ?></option>
                <option value="notitle" size="20"<?php if ($opt_val=="notitle") echo " selected"; ?>><?php _e("Without title", 'twitter_trackback' ); ?></option>
            </select>
			</p>

			<p><?php _e("What behavior want you for this plugin ?", 'twitter_trackback' ); ?>  
			<select name="<?php echo $data_field_name_2; ?>">
            	<option value="tb" size="20"<?php if ($opt_val_2=="tb") echo " selected"; ?>><?php _e("Trackbacks Bar", 'twitter_trackback' ); ?></option>
                <option value="ttb" size="20"<?php if ($opt_val_2=="ttb") echo " selected"; ?>><?php _e("Triple Trackbacks Bar", 'twitter_trackback' ); ?></option>
                <option value="fl" size="20"<?php if ($opt_val_2=="fl") echo " selected"; ?>><?php _e("Fixed List", 'twitter_trackback' ); ?></option>
            </select>
			</p>

			<p><?php _e("Turn off Jquery :", 'twitter_trackback' ); ?>  
			<select name="<?php echo $data_field_name_3; ?>">
            	<option value="2" size="20"<?php if ($opt_val_3=="2") echo " selected"; ?>><?php _e("Yes", 'twitter_trackback' ); ?></option>
                <option value="1" size="20"<?php if ($opt_val_3=="1") echo " selected"; ?>><?php _e("No", 'twitter_trackback' ); ?></option>
            </select><br />
            <small><?php _e("Normally, Wordpress uses a version of Jquery.", 'twitter_trackback' ); ?> <?php _e("But Twitter TrackBacks Bar, has its version available if the Wordpress's JQuery is broken.", 'twitter_trackback' ); ?> <?php _e("You can with this option, turn on or turn off  Jquery", 'twitter_trackback' ); ?></small>
            </p>

			<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
			</p>

			</form>
            <?php
		}
	}

?>