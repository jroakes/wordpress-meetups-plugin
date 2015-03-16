
jQuery(document).ready(function() {

    	
	reset_button = jQuery('#meetups_reset');
	
	reset_button.click(function(event) {
										event.preventDefault();
										
										if (confirm('Are you sure you want to delete all the stored Twitter data?')) {
											MeetupAjax.reset_data( function(response){
													
												jQuery("<div style='display:none;display: inline-block; margin-left: 20px;padding: 5px;color: green;' id='reset_status'>Data Cleared</div>")
														.insertAfter('#meetups_reset')
														.fadeIn()
														.delay( 4000 )
														.fadeOut();
	
											});
										} else {
										    // Do nothing!
										}

										
									});  

});


var MeetupAjax = MeetupAjax || {};

MeetupAjax.reset_data = function(callback) {
	
	var response;
	console.log("Ajax URL: " + ajax_object.ajax_url);
	
    jQuery.post(
        ajax_object.ajax_url,
        {
            'action'		: 'request',
            'ajax_do'		: 'reset_data',
            'ajax_post_id'	: ajax_object.postid,
            'ajax_noonce'	: ajax_object.meetupNonce
        },
        function(response) {
        		
			// Grab the response and make it an obj
            var response 	= jQuery.parseJSON(response);

		    if ( typeof response.error != "undefined"){
		    	alert(response.error);
		    	return callback(false);
		    }
		    
		    console.log(response);
		    
		   	return callback(response);     

            });
           

}