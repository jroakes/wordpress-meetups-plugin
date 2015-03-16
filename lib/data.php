<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Data_Manip_And_Storage
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 */
 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;


if( !class_exists( 'RSEO_Data' )){

class RSEO_Data {
	

	private $_twitter;
	private $_current_user;
	private $_user_id;
	private $_postid;

	//Caching
	private $_cacheInterval = 30; // Secs
	private $_tweetRecord;	
		

	// Error Reporting...
	public $error	= FALSE;
	
	
	function __construct($postID = false) {
		
		global $post;	
		
		$this->_current_user	= wp_get_current_user();
		$this->_user_id			= $this->_current_user->ID;
		
		// Try to grab post->ID if not sent.
		$this->_postid			= $postID ? $postID: $post->ID;
		
		// Load Twitter Class
		include 'twitter.php';
		$this->_twitter = new RSEO_Twitter();
		
		// Grab the tweet record.
		$tweetRecord			= $this->_getData('meetups_twitter_record');
		
		// Set the tweet record.
		$this->_tweetRecord		= $tweetRecord && $tweetRecord != "" ? json_decode($tweetRecord, TRUE) : FALSE;
		
		
	}
	

	/**
	 * Returns the stored twitter search term
	 * @since 1.0
	 */
	public function get_search_term(){
		
		return sanitize_key( $this->_getData('meetups_twitter_search') );
		
	}

	/**
	 * Queries the Twitter class for search items
	 * @since 1.0
	 */
	public function twitter_search($searchterm = false){
		
		$searchterm		= $searchterm ? $searchterm : $this->get_search_term();
		$since 			= $this->check_since();
		$twitter    	= $this->_twitter;
		
		return $twitter->twitter_search($searchterm, $since);
		
	}
	
	/**
	 * Returns the stored twitter tweet limit
	 * @since 1.0
	 */
	public function get_limit(){
		
		$limit = sanitize_key( $this->_getData('meetups_twitter_limit') );
		
		return $limit ? (int)$limit : MEETUPS_DEFAULT_LIMIT;
		
	}

	/**
	 * Returns the post->ID
	 * @since 1.0
	 */		
	public function get_post_id(){
		
		return $this->_postid;
		
	}

	/**
	 * Returns the stored record of tweets
	 * @since 1.0
	 */		
	public function get_tweet_record(){
		
		return $this->_tweetRecord;
		
	}

	/**
	 * Returns the number of stored tweets
	 * @since 1.0
	 */
	public function get_tweet_record_count(){
		
		return count($this->_tweetRecord);
		
	}

	/**
	 * Updates the stored tweets with new tweets and updated vote counts.
	 * @since 1.0
	 */		
	private function _update_tweet_record($data){
		
		if ( !$data || !count($data)  ){
			$this->error = "[data]: Twitter Feed data not correct.";
			return false;
			
		}
	
		// Grab the record of tweets
		$tweetRecord 	= $this->_tweetRecord;
		
		if ( $tweetRecord && !count($tweetRecord) ){
			$this->error = "[data]:Tweet record not stored as array. Data:" . $tweetRecord;
			return false;
		}		
		
		foreach ((array) $data as $tweet) {
			
			if ( isset($tweet['id']) && (int)$tweet['id'] > 0 && !isset( $tweetRecord[$tweet['id']]) ){
          
			  $tweetRecord[$tweet['id']] = Array(
			  										"description"	=> sanitize_text_field($tweet['description']),
			  										"date"			=> $tweet['date'],
			  										"user"			=> sanitize_text_field($tweet['user']),
			  										"img"			=> sanitize_text_field($tweet['img']),
			  										"voters"		=> Array()
			  									);
											
			}
		  
        }
		
		
		return $this->_save_tweet_record($tweetRecord);
		
}

	/**
	 * Updates the class property and db stored meta data
	 * @since 1.0
	 */	
	private function _save_tweet_record($tweetRecord){
		$this->_tweetRecord = $tweetRecord;
		$result =  $this->_setData('meetups_twitter_record', json_encode($tweetRecord));
			
	}


	
		
	
	/**
	 * Returns db meta data based on a passed key
	 * @since 1.0
	 */	
	private function _getData($name) {
        return get_post_meta( $this->_postid, $name, true );
    }

	/**
	 * Saves any passed data as meta data
	 * @since 1.0
	 */	
    private function _setData($name, $value) {
        return update_post_meta($this->_postid, $name, wp_slash($value) );
    }

	/**
	 * Stores the id of the last processed tweet
	 * @since 1.0
	 */	
    private function _process_since ($feed) {
        
		if (count($feed)){
			
			$sortArray = array();
			
			foreach ($feed as $item ){
				//echo $item['id'];
				$sortArray[] = $item['id'] ;
				
			}
			
			sort($sortArray);
			
			return array_pop($sortArray);
			
		}
    }
	
	/**
	 * Returns cached data or false if cache time is past
	 * @since 1.0
	 */		
	public function check_cache (){
		
		$interval	= $this->_cacheInterval;
		$lastSave	= $this->_getData('last_save');
		$feed		= $this->_getData('twitter_cache');
		
		// If the last save time + 30 is less than the current time
		if ( $feed && $lastSave && ( $lastSave + ($interval) ) > time() ){
			return json_decode($feed, TRUE);
		}else{
			return false;
		}
		
	}

	/**
	 * Returns the last tweet id
	 * @since 1.0
	 */		
	public function check_since (){

		return $this->_getData('since_id');
		
	}
	
	/**
	 * Sets the cache from newly proccessed api data
	 * @since 1.0
	 */		
	public function set_cache ( $feed ){
		
		if ($feed and count($feed)){
			// Save Cache data
			$this->_setData('twitter_cache', json_encode($feed) );
	        $this->_setData('last_save', time());
			
			// Set the last id for the next api call.
			$this->_setData('since_id', $this->_process_since ($feed) );
			
			//Will update the master tweet record here.
			$this->_update_tweet_record($feed);
		}
		
		return $feed;
		
	}
	


	

	/**
	 * Gets the vote count for a particular post
	 * @since 1.0
	 */		
	public function get_votes($tweetid){
		
		// Grab the record of tweets
		$tweetRecord 	= $this->_tweetRecord;
		
		return isset($tweetRecord[$tweetid]['voters'])? count($tweetRecord[$tweetid]['voters']) : false;

		
	}
	
	/**
	 * Returns the record of votes for ajax
	 * @since 1.0
	 */		
	public function get_updated_votes(){
		
		return json_decode($this->_getData('meetups_vote_record')? $this->_getData('meetups_vote_record') : "", TRUE);

		
	}
	
		
	/**
	 * Returns the user id's that voted for a particluar post in an array
	 * @since 1.0
	 */		
	public function get_vote_users($tweetid){
		
		// Grab the record of tweets
		$tweetRecord 	= $this->_tweetRecord;
		
		return isset($tweetRecord[$tweetid]['voters'])? (array) $tweetRecord[$tweetid]['voters'] : false;
		
		
	}

	/**
	 * Returns boolean on whether the current user voted on a tweet
	 * @since 1.0
	 */			
	public function check_voted($tweetid){
				
		
		$voters		= $this->get_vote_users($tweetid)? $this->get_vote_users($tweetid) : Array();
		$user_ID	= $this->_user_id;
					
		return in_array($user_ID, $voters)? true : false;

		
	}
	
	
	/**
	 * Updated vote on a particular tweet. Returns the new vote count
	 * @since 1.0
	 */	
	public function update_vote($tweetid){
		
		// Grab the record of tweets
		$tweetRecord 	= $this->_tweetRecord;

		$user 			= $this->_user_id;
		$voted			= $this->check_voted($tweetid);
		
		$response		= Array();
			
		
		if (!$voted){
			
			if (isset($tweetRecord[$tweetid]['voters'])){
				// Update existing array
				array_push($tweetRecord[$tweetid]['voters'], $user);
			} else{
				// Create new array
				$tweetRecord[$tweetid]['voters'] = Array($user);
			}
			
			
			$this->_save_tweet_record($tweetRecord);
			$this->_save_updated_votes($tweetid, $this->get_votes($tweetid));
			
			$response['status']	= "success";
			$response['vote_count']	= $this->get_votes($tweetid);
			
		}else{
			$response['status']		= "fail";
			$response['vote_count']	= $this->get_votes($tweetid);
		}

		return $response;
		
	}

	/**
	 * Updates the count for tweets in the vote record
	 * @since 1.0
	 */		
	private function _save_updated_votes($tweetID, $count){
		
		$data = json_decode($this->_getData('meetups_vote_record')? $this->_getData('meetups_vote_record') : "", TRUE);
		
		$data[$tweetID]	= $count;
		
		return $this->_setData('meetups_vote_record', json_encode($data));
		
	}


	
} // End Class

}
