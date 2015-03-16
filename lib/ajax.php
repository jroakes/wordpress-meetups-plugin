<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Ajax
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 */
 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;


 
if( !class_exists( 'RSEO_Ajax' )){

class RSEO_Ajax {
	
	
	private $_twitter;
	private $_data;



    public function __construct() {

        add_action('wp_ajax_request', 	array($this, 'request'));
		
		
		if (isset($_POST['ajax_post_id'])){
			// Load Data Class
			include 'data.php';
			$this->_data = new RSEO_data($_POST['ajax_post_id']);
		}

    }



    /**
	 * Handles the ajax request
	 * @since 1.0
	 */

    public function request() {
    	
		$nonce = $_POST['ajax_noonce'];
		
		if ( ! wp_verify_nonce( $nonce, 'meetup-nonce' ) )
        die ( 'Busted!');
		
    	
		// Key identifiers
		$do   		= sanitize_key($_POST['ajax_do']);
		$data 		= $this->_data;
		$twitter    = $this->_twitter;
		$result		= Array();
		
		if ($do == "new_tweets" ){
			
			// Grab the search term from the post
			$postid		 	= $data->get_post_id();
			$limit			= $data->get_limit();
			$count			= $data->get_tweet_record_count();
			$cache 			= $data->check_cache();
			$updates 		= $data->get_updated_votes();
			
			if ($cache){
				
				$result['new_tweets'] = $cache;
				
			}else{
				
				if ($count < $limit){
				
					$feed	= $data->twitter_search();
					
					if (isset($feed['error'])){
						$result['error'] = "Twitter Error: " . $feed['error'];
					}elseif (count($feed)){
						$result['new_tweets'] = $data->set_cache ( $feed );
					}else{
						$result['new_tweets'] = "No New Tweets";
					}
					
					if ($data->error){
						$result['error'] = $data->error;
					}
				}else{
					$result['new_tweets'] = "Tweet Limit Reached";
				}
				
			}
			
			$result['tweet_record']		= $data->get_tweet_record();
			$result['updated_counts']	= $updates ? $updates : "";
			
			$result = $this->_prepareJSON($result);	
			
		} elseif ($do == "vote_tweet" ){
			
			// Grab the tweet ID
			$tweetID   	= sanitize_key($_POST['ajax_tweet_id']);
			$result		= $data->update_vote($tweetID);
			$result 	= $this->_prepareJSON($result);		
			
			
		}elseif ($do == "reset_data"){
			
			// ignore the request if the current user doesn't have
		    // sufficient permissions
		    if ( current_user_can( 'edit_posts' ) ) {
				
				$postid		 		= $data->get_post_id();
				$result				= Array();
				$result['status'] 	= $this->_resetPostTwitterData( $postid );
				$result 			= $this->_prepareJSON($result);
				
			}
			

		}
				
				
		$this->_response($result);
		
		
    }

   /**
	 * Handles the ajax response
	 * @since 1.0
	 */
	private function _response( $result ){
		
		echo $result;
        die();
        
        
	}

   /**
	 * Prepares the data for trasmission
	 * @since 1.0
	 */
	private function _prepareJSON( $data ){
				     
        return json_encode($data);
        
	}

   /**
	 * Resets the stored twitter data for a particular post
	 * @since 1.0
     * TODO: Move to Data.php	
	 */
	private function _resetPostTwitterData( $id ){
		    
        $result = update_post_meta( $id , 'meetups_twitter_record' , "" );
		$result = $result && update_post_meta( $id , 'twitter_cache' , "" );
		$result = $result && update_post_meta( $id , 'meetups_vote_record' , "" );
		$result = $result && update_post_meta( $id , 'last_save' , "" );
		$result = $result && update_post_meta( $id , 'since_id' , "" );
		
		return $result;
        
	}
			
}

}

// Start the class
new RSEO_Ajax();
?>