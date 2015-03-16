<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Load
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 */
 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;


// Load Twitter Class
require_once 'twitter.php';
 
 
if( !class_exists( 'RSEO_Meetups' )){
 
class RSEO_Meetups{
	
	private $_api_keys 		= array();
 
	/**
	 * constructor
	 * @since 1.0
	 */
 
	public function __construct() {
 		
		// Start CPTs
		add_action( 'init', 		    	 	array( $this, 'register_posttype') );
		add_action( 'init', 		    	 	array( $this, 'start_ajax') );
		add_filter( 'post_updated_messages', 	array( $this, 'custom_post_type_messages'));
		add_action( 'admin_head',            	array( $this, 'custom_post_type_help' ) );
		
		// Enqueue Scripts
		add_action( 'wp_enqueue_scripts', 		array( $this, 'enqueue_scripts') );
		add_action( 'admin_enqueue_scripts',	array( $this, 'admin_enqueue_scripts') );
		add_action(	'wp_enqueue_scripts', 		array( $this, 'local_scripts') );
		
		// Add metabox data
		add_action( 'add_meta_boxes', 			array( $this, 'add_meta_box') );
		add_action( 'save_post',      			array( $this, 'save_meta_box_data' ) );
		
		// Add Settings Data
		add_action('admin_menu', 				array($this, 'add_options_page'));
        add_action('admin_init', 				array($this, 'register_settings'));
		add_action('admin_init', 				array($this, 'register_settings'));
		
		//Init Meetups data
		add_action('template_redirect', 		array($this, 'template_data'));
		
		// Add Active/Deactive hooks
		register_activation_hook( MEETUPS_DIR . "wp-meetups.php" , array( $this, 'plugin_activation' ) );
		register_deactivation_hook( MEETUPS_DIR . "wp-meetups.php" , array( $this, 'plugin_deactivation' ) );
		
		// Add Plugin setting link
		add_filter("plugin_action_links_" . MEETUPS_PLUGIN_BASENAME, array( $this, 'plugin_settings_link') );
		
		//Get API Keys
		$this->_get_keys();
 
	}	
	
	
	/**
	 * Set the API Key needed by the Admin setting page
	 * @since 1.0
	 */		
    private function _get_keys() {
        $this->_api_keys['consumer_id'] = get_option('consumer_id');
        $this->_api_keys['consumer_secret'] = get_option('consumer_secret');
        $this->_api_keys['access_token'] = get_option('access_token');
        $this->_api_keys['access_token_secret'] = get_option('access_token_secret');
    }


	/**
	 * Sets up the Global meetupdata - Called here after global $post is set.
	 * @since 1.0
	 */
	public function template_data() {

		if ( is_singular( 'meetups' ) || is_admin() ){
			global $post, $meetupdata;
			
			//Include Data Class
			require_once 'data.php';
			$meetupdata = new RSEO_Data($post->ID);	
			
			//Include Template Functions
			require_once 'template_functions.php';		

		}

	}


	/**
	 * Loads ajax after current user is established
	 * @since 1.0
	 */
	public function start_ajax() {

		if ( is_singular( 'meetups' ) || is_admin() ){
			
			//Include Ajax
			require_once 'ajax.php';
				
		}

	}
 
	/**
	 * register_posttype
	 * @since 1.0
	 */
 
	public function register_posttype() {
 
		$labels = array(
					'name'               => __('Meetups', 'cpt_domain'),
					'singular_name'      => __('Meetup', 'cpt_domain'),
					'menu_name'          => __('Meetups', 'cpt_domain'),
					'name_admin_bar'     => __('Meetups', 'cpt_domain'),
					'add_new'            => __('Add new', 'cpt_domain'),
					'add_new_item'       => __('Add new Meetup', 'cpt_domain'),
					'new_item'           => __('New Meetup', 'cpt_domain'),
					'edit_item'          => __('Edit Meetup', 'cpt_domain'),
					'view_item'          => __('View Meetup', 'cpt_domain'),
					'all_items'          => __('All Meetups', 'cpt_domain'),
					'search_items'       => __('Search Meetups', 'cpt_domain'),
					'parent_item_colon'  => __('Meetup parent', 'cpt_domain'),
					'not_found'          => __('No Meetup found', 'cpt_domain'),
					'not_found_in_trash' => __('No Meetup in trash', 'cpt_domain')
		);
 
 
		$slug = get_theme_mod( 'ctp_example_permalink' );
  		$slug = ( empty( $slug ) ) ? 'meetups' : $slug;
 
 
 		$args = array( 
			'public'        => true, 
			'labels'        => $labels,
			'description'   => __('Meetups', 'cpt_domain'),
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 5,
			'has_archive' 	=> true,
			'supports'      => array( 'title', 'editor', 'thumbnail'),
			'rewrite'     	=> array( 'slug' => $slug )
 
		);
 
		register_post_type( 'meetups', $args );
 
	}
 
	/**
	 * custom_post_type_messages
	 * @since 1.0
	 */
 
	public function custom_post_type_messages( $messages ) {
 
		$post = get_post();
 
			$messages['cpt_examples'] = array(
				0  => '',
				1  => __('Meetup updated', 'cpt_domain'),
				2  => __('Custom field updated', 'cpt_domain'),
				3  => __('Custom field deleted', 'cpt_domain'),
				4  => __('Meetup updated', 'cpt_domain'),
				5  => isset( $_GET['revision'] ) ? sprintf( __('Meetup restored to revision', 'cpt_domain') . ' %s',wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __('Meetup published', 'cpt_domain'),
				7  => __('Meetup saved', 'cpt_domain'),
				8  => __('Meetup sent', 'cpt_domain'),
				9  => sprintf(
					__('Meetup programed for', 'cpt_domain') . ': <strong>%1$s</strong>.',
					date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
				),
				10 => __('Draft Meetup updated', 'cpt_domain')
			);
 
 
		return $messages;
 
	}
 
	/**
	 * custom_post_type_help
	 * @since 1.0
	 */
 
	public function custom_post_type_help() {
 
 
		$screen = get_current_screen();
 
		if ( $screen->post_type != 'meetups' ) {
			return;
		}
 
		$basics = array(
			'id'      => 'cpt_examples_basics',
			'title'   => 'Basic help Meetups',
			'content' => '<p>From this section you can view a list of Meetups.</p>
                          <p>Click on one of them to Edit or click on Add New to create a new one.</p>'
		);
 
		$formatting = array(
			'id'      => 'cpt_examples_formating',
			'title'   => 'Creation of a Meetup',
			'content' => '<p>Fill in the form with title and text .</p>'
 
		);
 
		$screen->add_help_tab( $basics );
		$screen->add_help_tab( $formatting );
 
 	}
 

	 

	/**
	 * Enqueue scripts and styles.
	 * @since 1.0
	 */
	function enqueue_scripts() {

		if (is_singular( 'meetups' )){
			// Add Style	
			wp_enqueue_style( 'meetups-style', MEETUPS_URI . 'css/style.css',  array(), '1.0' );
	        
			//Add scripts
			wp_enqueue_script( 'meetups-plugins', MEETUPS_URI . 'js/plugins.js', array('jquery'), '1.0' );
	        wp_enqueue_script('meetups-scripts', MEETUPS_URI . 'js/scripts.js', array('jquery','meetups-plugins' ), '1.0');
			
		}

	}

	/**
	 * Admin Enqueue scripts and styles.
	 * @since 1.0
	 */
	 
	function admin_enqueue_scripts($hook) {
		
	    if ( 'post.php' != $hook ) {
	        return;
	    }
		
		global $post;
	
	    wp_enqueue_script( 'meetups-admin-scripts', MEETUPS_URI . 'js/admin-scripts.js', array('jquery'), '1.0' );
		
		wp_localize_script( 'meetups-admin-scripts', 'ajax_object', array(
		    // URL to wp-admin/admin-ajax.php to process the request
		    'ajax_url'         => admin_url( 'admin-ajax.php' ),
		    'meetupNonce' 	   => wp_create_nonce( 'meetup-nonce' ),
		    'postid'           => $post->ID
		    )
		);
		
	}

	/**
	 * Localize Scripts.. Adds parameters to scripts
	 * @since 1.0
	 */
	function local_scripts()  {
	 
		 if (is_singular( 'meetups' )){
			 	global $post;
				wp_localize_script( 'meetups-scripts', 'ajax_object', array( 
					'ajax_url' 			=> admin_url( 'admin-ajax.php' ),
					'meetupNonce' 		=> wp_create_nonce( 'meetup-nonce' ),
					'ajax_post_id' 		=> $post->ID 
					) 
				);
        }
			
	}
	

	/**
	 * add_meta_box
	 *  @since 1.0
	 */
	public function add_meta_box(){
	 
		add_meta_box( 'meetups_meta', 'Meetups Extra Info', array( $this, 'display_meta_form' ), 'meetups', 'advanced', 'high' );
	}
 
	/**
	 * display_meta_form
	 *  @since 1.0	
	 */
	 
	public function display_meta_form( $post ) {
	 
		wp_nonce_field( 'meetups_meta_box', 'meetups_meta_box_nonce' );
	 
			$google_video_url 	 = get_post_meta( $post->ID, 'meetups_google_video_url', true );
			$twitter_data		 = get_post_meta( $post->ID, 'meetups_twitter_search', true );
			$twitter_limit		 = get_post_meta( $post->ID, 'meetups_twitter_limit', true );
	 
			echo '<div class="wrap">';
			echo '<label for="meetups_google_video_url">' . translate( 'Google Video URL (Iframe SRC)', 'meetups' ) . '</label> <br/>';
			echo '<input class="large-text" type="text" id="meetups_google_video_url" name="meetups_google_video_url" value="' . esc_attr( $google_video_url ) . '"   />';
			echo '</div>';
	 
			echo '<div class="wrap">';
			echo '<label for="meetups_twitter_search">' . translate( 'Twitter Search Word ', 'meetups' ) . '</label>  <br/>';
			echo '<input class="text" type="text" id="meetups_twitter_search" name="meetups_twitter_search" value="' . esc_attr( $twitter_data ) . '"   />';
			echo '</div>';

			echo '<div class="wrap">';
			echo '<label for="meetups_twitter_search">' . translate( 'Twitter Search Limit ', 'meetups' ) . '</label>  <br/>';
			echo '<input class="text" type="text" id="meetups_twitter_limit" name="meetups_twitter_limit" value="' . esc_attr( $twitter_limit ) . '"   />';
			echo '<div class="note" style="color:red; font-size:.9em;">This is approximate. No new tweets will be pulled from the Twitter api if over limit.</div>';
			echo '</div>';
						
			echo '<div class="wrap">';
			echo '<label for="meetups_reset_data">' . translate( 'Reset Twitter Data ', 'meetups' ) . '</label>  <br/>';
			echo '<input name="meetups_reset_data" class="button button-primary button-large" id="meetups_reset" accesskey="p" value="Reset Twitter Data" type="submit" >';
			
			echo '</div>';
	}
	 
 

	/**
	 * save_meta_box_data
	 * function called on save_post hook to sanitize and save the data
	 * @since 1.0	
	 */
	 
	public function save_meta_box_data( $post_id ){
	 
	  // Check if nonce is set.
	    if ( ! isset( $_POST['meetups_meta_box_nonce'] ) ) {
		  return;
	    }
	 
	  // Verify that the nonce is valid.
	    if ( ! wp_verify_nonce( $_POST['meetups_meta_box_nonce'], 'meetups_meta_box' ) ) {
		   return;
	    }
	 
	  // If autosave, don't do anything
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		  return;
	    }
	 
	  // Check the user's permissions.
	    if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'meetups' ) {
	            if ( ! current_user_can( 'edit_page', $post_id ) ) {
			     return;
		    }
	 
	    } else {
	            if ( ! current_user_can( 'edit_post', $post_id ) ) {
			     return;
		    }
	    }
	 
	   // Save the information into the database
	       if ( isset( $_POST['meetups_google_video_url'] ) ) {
	 
	             $google_video_url = sanitize_text_field( $_POST['meetups_google_video_url'] );
		     update_post_meta( $post_id, 'meetups_google_video_url', $google_video_url );
		}
	 
	       if ( isset( $_POST['meetups_twitter_search'] ) ) {
	 
	             $twitter_data = sanitize_text_field( $_POST['meetups_twitter_search'] );
		     update_post_meta( $post_id, 'meetups_twitter_search', $twitter_data );
		}

	       if ( isset( $_POST['meetups_twitter_limit'] ) ) {
	 
	             $twitter_data = sanitize_text_field( $_POST['meetups_twitter_limit'] );
		     update_post_meta( $post_id, 'meetups_twitter_limit', $twitter_data );
		}	 
	 
	}


	/**
	 * add_options_page
	 * adds options page
	 * @since 1.0	
	 */

    public function add_options_page() {
        add_options_page('Wordpress Meetups Setup', 'Wordpress Meetups Setup', 'edit_plugins', 'wordpress_meetups', array($this, 'settings_page'));
    }

	/**
	 * Add the settings link to the Plugins page
	 * adds options page
	 * @since 1.0	
	 */
	
	public function plugin_settings_link($links) { 
	  $settings_link = '<a href="options-general.php?page=wordpress_meetups">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}


	/**
	 * register_settings
	 * registers api settings
	 * @since 1.0	
	 */
	 
    function register_settings() {
        //register our settings
        register_setting('ta-settings-group', 'consumer_id');
        register_setting('ta-settings-group', 'consumer_secret');
        register_setting('ta-settings-group', 'access_token');
        register_setting('ta-settings-group', 'access_token_secret');
    }


	/**
	 * settings_page
	 * html page for api settings.
	 * @since 1.0	
	 */
	 
    public function settings_page() {
        ?>

        <div class="wrap">
            <h2>Meetups: Twitter Api keys</h2>
            <p>Enter Keys and Tokens from your Twitter app below.  Go <a target='_blank' href='https://apps.twitter.com/'>here</a> to set up your app and get your credentials.</p>

            <form method="post" action="options.php">
                <?php settings_fields('ta-settings-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Consumer key</th>
                        <td><input type="text" name="consumer_id" size="65" value="<?php echo $this->_api_keys['consumer_id']; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Consumer secret</th>
                        <td><input type="text" name="consumer_secret" size="65" value="<?php echo $this->_api_keys['consumer_secret']; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Access token</th>
                        <td><input type="text" name="access_token" size="65" value="<?php echo $this->_api_keys['access_token']; ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Access token secret</th>
                        <td><input type="text" name="access_token_secret" size="65" value="<?php echo $this->_api_keys['access_token_secret']; ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>

			<h2>Meetups: Installation</h2>
			
			<p>Add:</p>
			<p><code>
				if (is_single('meetups')){ <br>
					the_meetups_content(); <br>
				} <br>
			</code></p>
			<p>In the single.php or single-meetups.php template file.  The template file should be full width.</p>
			<p>The plugin is designed to work only for the 'meetups' Custom Post Type.  It will not work if embedded in pages.</p>
			<p>Ideally, a full-width template file is created called <strong>single-meetups.php</strong></p>
			
			<h2>Meetups: Twitter Test</h2>
			<p>A list of tweets with the keyword 'wordpress' will appear below if the keys above are correct.</p>
			
            <div class="titter_ajax">

                    <?php 
                    	echo $this->_twitter_test()?$this->_twitter_test() : "<p>Enter Twitter Api details above.</p><p>Get your API details <a target='_blank' href='https://apps.twitter.com/'>here</a>."; 
                    ?>

                </ul>
            </div>



        </div>
        <?php
    }

	/**
	 * Formats an UL list to be used in template files.
	 * @since 1.0
	 */
	private function _twitter_test(){
		
		//Include Data Class
		require_once 'twitter.php';
		$twitter = new RSEO_Twitter();	
		
		$tweets = $twitter->twitter_search('wordpress');
		
		$response = "";
		
		$response .= "<ul class='tweets' >\n";
		
		if (count($tweets) > 0 ){
		    foreach ((array) $tweets as $tweet => $info ) {
		    	
		    	if ($tweet == 0 ) continue;
				
		       	$response .= "<li id='" . $tweet . "' data-id='" . $tweet . "' class='mix' ><span class='description' ><strong>" . $info['user'] . ":</strong> " . $info['description'] . "</span></li>\n";
		    }
		}else{
			return FALSE;
		}
		$response .= "</ul>";
		
	    
	    return $response;
	    
	}


	/**
	 * Do stuff on activation.
	 * @since 1.0	
	 */
	public function plugin_activation() {
		// Do activation stuff
	}

	/**
	 * Clean up stuff on deactivation
	 * @since 1.0	
	 */
	public function plugin_deactivation( ) {
		//tidy up
	}


 
}// class
 
}


// Init class
new RSEO_Meetups();
