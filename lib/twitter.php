<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Twitter_API
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 * Special Thanks: DzeryCZ
 */
 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;

require_once MEETUPS_DIR . 'OAuth/twitteroauth.php';

 
if( !class_exists( 'RSEO_Twitter' )){

class RSEO_Twitter {

    private $_api_keys 		= array();
    private $_numOfTweets 	= 10;


    public function __construct() {
    	
        $this->_get_keys();

    }


    /**
	 * Sets the api key property
	 * @since 1.0
	 */
    private function _get_keys() {
        $this->_api_keys['consumer_id'] = get_option('consumer_id');
        $this->_api_keys['consumer_secret'] = get_option('consumer_secret');
        $this->_api_keys['access_token'] = get_option('access_token');
        $this->_api_keys['access_token_secret'] = get_option('access_token_secret');
    }


	

     /**
	 * Performs API call to twitter via TwitterOAuth
	 * @since 1.0
	 */

    public function twitter_search($searchterm, $last = false) {
    	
		
	// Get out if no search term.
	if ( !$searchterm || $searchterm == "" ){
		return $twitter_feed['error'] = "No Search Term Specified";
	}
	
	
		
		//Set up the return array
		$twitter_feed	= array();
			
		// Gets the last tweet id and packages for api.
		$since_id 		= $last ? "&since_id=" . $last : "";

		// Connection to Twitter
        $connection = new TwitterOAuth($this->_api_keys['consumer_id'], $this->_api_keys['consumer_secret'], $this->_api_keys['access_token'], $this->_api_keys['access_token_secret']);
        $search_feed3 = "https://api.twitter.com/1.1/search/tweets.json?q=%23" . $searchterm . "&count=" . $this->_numOfTweets . "&result_type=recent" . $since_id;
        $reponse = $connection->get($search_feed3);
		

        if ($reponse instanceof WP_Error){
        	return $twitter_feed['error'] = "WP_Error";

        }

        if (isset($reponse->errors)) {
            
            switch ($reponse->errors[0]->code) {
                case 32: $twitter_feed['error'] = 'Please check setting Twitter API in Theme Options -> Advanced?';
                    break;
                case 88: $twitter_feed['error'] = 'Rate limit exceeded, please check "Actualize every X minutes" item in Twitter J&W Widget. Recommended value is 60.';
                    break;
                case 215: $twitter_feed['error'] = 'Don`t you have set Twitter API in Theme Options -> Advanced?';
                    break;
                default: $twitter_feed['error'] = 'Error Code: ' . $reponse->errors[0]->code;
                    break;
            }
            
            //Return the error
            return $twitter_feed;
            
        } else {

            // Gets the status part of the response	
			$statuses 		= $reponse->statuses;
			
			if (count($statuses)){

	            foreach ($statuses as $i => $tweet) {
	            	
					// Only add in unique tweets and not retweets.
					if ( isset($tweet->id_str) && $tweet->id_str && $tweet->retweet_count < 1 ){
	            		
						$twitter_parsed_data = array();	
	                    $twitter_parsed_data['description'] = sanitize_text_field($tweet->text)/*. " Retweeted: " . $tweet->retweeted*/;
	                    $twitter_parsed_data['date'] 		= $tweet->created_at;
	                    $twitter_parsed_data['user'] 		= sanitize_text_field($tweet->user->name);
	                    $twitter_parsed_data['img'] 		= esc_url($tweet->user->profile_image_url);
						$twitter_parsed_data['id'] 			= $tweet->id_str;
					
						array_push($twitter_feed, $twitter_parsed_data);
						
					}
					
							
	            }
			
			}

			
			
            
        }
		
		// Reversed here so they will be in the correct order for ajax response
		return array_reverse($twitter_feed);
			
		

    }// End twitter_search


}

}


?>