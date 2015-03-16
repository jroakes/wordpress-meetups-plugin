
jQuery(document).ready(function() {

    	
	twitterhtml 		= jQuery('.twitter-feed .tweets');
	twitterscroll 		= jQuery('.scroll-container');
	
	twitterhtml.mixItUp({
		selectors: {target: 'li'},
		controls: {enable: false},
		load: {sort: 'rank:desc id:asc'},
		animation: {duration: 2000}
	});	
	
	// Run Twitter polling
	setInterval(function() {
	    var twitterdata = MeetupAjax.twitter_request( function(response){
	    	
	    	//console.log(twitterdata);
	    	var elems 	 	= MeetupAjax.build_list(response);
	    	var rankUpdated = MeetupAjax.adjust_counts(response);
	    	var existing 	= jQuery('.twitter-feed .tweets').find('li');
	    	var status		= jQuery('.twitter-feed .status');
	    	
	    	if (elems){
	    		if (existing.length > 7){
	    			
	    			if( status.is(':visible')) {
					    
					    var currenttext 	= status.html();
					    var currentcount 	= (currenttext.split(" "))[0];
					    
					    status.html( elems.length + parseInt(currentcount) + " New Posts Added.");

					    
					}else{

						status.html(elems.length + " New Posts Added.").slideDown( 600 );	
						
					}
					
						    			
	    		}
	    		
	    		jQuery('.twitter-feed .tweets').mixItUp('append', elems,  {sort: 'rank:desc id:asc'} );	
	    		
	    		
	    	}else{
	    		
	    		jQuery('.twitter-feed .tweets').mixItUp('sort', 'rank:desc id:asc');
	    		
	    	}
	    	

	    	
	    });

	}, 40 * 1000);
	
	// Set up scrolling
	twitterscroll.perfectScrollbar({suppressScrollX:true});
	

	var $ = document.querySelector.bind(document);
	window.onload = function () {
		var container = $('.scroll-container');

		container.addEventListener('scroll', function (e) {
			if (container.scrollTop === container.scrollHeight - container.clientHeight) {
				jQuery('.twitter-feed .status').slideUp( 600 );
			}
		});
	};


	
	twitterhtml.find( "li .data .vote" ).live( "click", function() {
												MeetupAjax.vote_tweet( jQuery(this).parents('li'), function(response){
														
													jQuery('.twitter-feed .tweets').mixItUp('sort', 'rank:desc id:asc');
													
												});
												
											});
		
    

});





var MeetupAjax = MeetupAjax || {};

MeetupAjax.twitter_request = function(callback) {
	
	var response;

    jQuery.post(
        ajax_object.ajax_url,
        {
            'action'		: 'request',
            'ajax_do'		: 'new_tweets',
            'ajax_post_id'	: ajax_object.ajax_post_id,
            'ajax_noonce'	: ajax_object.meetupNonce
        },
        function(response) {
        		
			// Grab the response and make it an obj
            var response 	= jQuery.parseJSON(response);

		    if ( typeof response.error != "undefined"){
		    	alert(response.error);
		    	return callback(false);
		    }
		    
		   		return callback(response);     

            });
           

}




// Pass Tobj.new_tweets to build the new list items and return list items
MeetupAjax.build_list = function(twitterdata) {
	
	
	var Tnew 	= twitterdata.new_tweets;

	
	if ( Tnew && Tnew.length > 0 && Tnew instanceof Array ){

		var list = "";
		
		list += "<ul>\n";
		
		for ( var data in Tnew) {
			if ( jQuery("#" + Tnew[data]['id']).length == 0 ){
		   		list += "<li id='" + Tnew[data]['id'] + "' data-id='" + Tnew[data]['id'] + "' data-rank='0' class='mix' ><span class='description' ><strong>" + Tnew[data]['user'] + ":</strong> " + Tnew[data]['description'] + "</span><span class='data' ><span class='vote'></span><span class='count'><span>Count</span><span class='num' >0</span></span></span></li>\n";	
			}
		}
		list += "</ul>";
	
		//console.log(list);
		
		// Grab the new tweets
	    response 	= jQuery(jQuery.parseHTML(list)).find('li');
	    	    
	
	    // If there are new tweets..
	    if ( response.length > 0 ){
		   
	      return response;
	        	              	
	   }
	
	}
   	
   	return false;
   	
}



// Pass updated ajax data to adjust vote amounts
MeetupAjax.adjust_counts = function(twitterdata) {

    var Vupd  = twitterdata.updated_counts;
    
    
    // If there are post to update.. update them....
    if ( (typeof Vupd === "object") && (Vupd !== null) ){

		for(var id in Vupd) {
			//Debug: console.log("Update: " + id + ":" + Vupd[id]);
			jQuery("#"+ id + " .data  .num").html( Vupd[id] );
			jQuery("#"+ id ).attr('data-rank', Vupd[id] );
		} 
			           
      	return true;  	              	
   }
   
   return false;

}


MeetupAjax.vote_tweet = function(tweet, callback) {

    jQuery.post(
        ajax_object.ajax_url,
        {
            'action'		: 'request',
            'ajax_do'		: 'vote_tweet',
            'ajax_post_id'	: ajax_object.ajax_post_id,
            'ajax_tweet_id' : tweet.attr('id'),
            'ajax_noonce'	: ajax_object.meetupNonce
        },
        function(response) {
					
			// Grab the response and make it an obj
            var response 	= jQuery.parseJSON(response);
                            
            // If there are new tweets.. append them....
            if ( response.status == "success" ){
 
	           tweet.find('.data .num').html(response.vote_count);
	           tweet.attr('data-rank', response.vote_count);
	           tweet.find('.data .vote').addClass('voted');
               return callback(true);
                	
            }else{
            	
            	//alert('Already Voted');
            	return callback(false);
            	
            }


            });
 
														          
            

}