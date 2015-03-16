<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Template_Functions
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 */

 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;




/**
 * Returns boolean based on whether the current user voted for a tweet
 * @since 1.0
 */
function check_voted($tweetid){
	global $meetupdata;
	return $meetupdata->check_voted($tweetid);
	
}

/**
 * Returns the vote count for a single tweet
 * @since 1.0
 */
function vote_count($tweetid){
	global $meetupdata;
	return $meetupdata->get_votes($tweetid)? $meetupdata->get_votes($tweetid) : 0;
	
}

/**
 * Returns array of stored tweets
 * @since 1.0
 */
function get_tweets(){
	global $meetupdata;
	return $meetupdata->get_tweet_record();
	
}

/**
 * Returns the post id for the current post
 * TODO: Is this needed?
 * @since 1.0
 */
function get_meetup_post_id(){
	global $meetupdata;
	return $meetupdata->get_post_id();
	
}

/**
 * Formats an UL list to be used in template files.
 * @since 1.0
 */
function list_tweets(){
	
	$tweets = get_tweets();
	
	$response = "";
	
	$response .= "<ul class='tweets' >\n";
	
	if (count($tweets) > 0 ){
	    foreach ((array) $tweets as $tweet => $data ) {
	    	
	    	if ($tweet == 0 ) continue;
			
	    	$voted     	= check_voted($tweet) ? 'voted' : '';
			$vote_count = vote_count($tweet);
	       	$response .= "<li id='" . $tweet . "' data-id='" . $tweet . "' data-rank='" . $vote_count . "' class='mix' ><span class='description' ><strong>" . $data['user'] . ":</strong> " . $data['description'] . "</span><span class='data' ><span class='vote " . $voted . "'></span><span class='count'><span>Count</span><span class='num' >" . $vote_count . "</span></span></span></li>\n";
	    }
	}
	$response .= "</ul>";
	
    
    return $response;
    
}


/**
 * Replaces the the_content call with our content
 * @since 1.0
 * TODO: Need to build out this area.
 */
 
	 function the_meetups_content () {
	 	 
	    if ( is_singular('meetups') ) {
		
		include MEETUPS_TEMPLATE_DIR . "full-width.php";
        $content = meetups_full_width($content);
		
		}

    echo $content;
}
 
	 
