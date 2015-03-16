<?php

/** 
 * @package    WordPress_Meetups_Plugin
 * @subpackage Full_Width_Template
 * @author     JR Oakes <jroakes@gmail.com>
 * @since      1.0
 */
 
// Exit if accessed directly
if ( !defined( 'MEETUPSLOADED' ) ) exit;

if (!function_exists('meetups_full_width')) {
	
function meetups_full_width(){
?>

<div id="wordpress-meetups">
	<div class = "meetup-video" >
		<div class = "meetup-iframe">
			<iframe width="560" height="315" src="<?php echo get_post_meta( get_the_ID(), 'meetups_google_video_url', true ); ?>" frameborder="0" allowfullscreen></iframe>
		</div>
		<h2><?php the_title(); ?><span></span></h2>
		<div class="author"><?php the_author(); ?></div>
		<div class = "meetup-iframe-content"> 
			<?php the_content(); ?>
		</div>
	</div>

	<div class = "meetup-twitter" >
		<div class = "twitter-post" >
			<a onclick="if(!document.getElementById('meetups_social_buttons')){window.open(this.href, 'mywin', 'left=50,top=50,width=600,height=350,toolbar=0'); return false;}" href="https://twitter.com/intent/tweet?button_hashtag=<?php echo get_post_meta( get_the_ID(), 'meetups_twitter_search', true ); ?>&text=My%20Question%20Is%3A" class="twitter-hashtag-button" data-size="large" >Post Question on Twitter</a>
			
		</div>
		<div class = "twitter-feed" >
			<div class = "scroll-container">
				<?php echo list_tweets(); ?>
			</div>
			<div class="status" ></div> 
		</div>
		<div class="twitter-hashtag">Use hashtag #<?php echo get_post_meta( get_the_ID(), 'meetups_twitter_search', true ); ?> </div>
		<?php 
			global $post;
			/* Debug...
			echo "Twitter Record: " . get_post_meta( $post->ID , 'meetups_twitter_record' , true );
			echo "<br><br>Since ID: " . get_post_meta( $post->ID , 'since_id' , true );
			echo "Twitter Record: " . get_post_meta( $post->ID , 'meetups_twitter_record' , true );
			echo "<br><br>Twitter Cache: " . get_post_meta( $post->ID , 'twitter_cache' , true );
			echo "<br><br>Last Save: " . get_post_meta( $post->ID , 'last_save' , true );
			 */
		?>
	</div>
</div>	

<?php } 

}?>