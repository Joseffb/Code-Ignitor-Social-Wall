var def_txt = "What's on your mind?";

jQuery(document).ready(function(){
	
	set_def_text();

    jQuery('.expand_comment').click(function() {
        event.preventDefault();
        jQuery('#id_'+this.id).toggle();
    });

	jQuery('#link_tab').click(function() {
		jQuery('#main_comment_share').hide();
		jQuery('#main_link_share').show();
	});
	
	jQuery('#main_comment_tab').click(function() {
		jQuery('#main_comment_share').show();
		jQuery('#main_link_share').hide();
	});
	
	jQuery('#main_share_btn').click(function() {
		
		comment = jQuery('#main_comment').val();
		
		if(comment != def_txt) {
			
			jQuery('#main_box_loader').show();
			
			jQuery.ajax({
				 data: 'comment='+comment+'&form=main_comment_req',
				 url:  "/stream/new_share",
				 type: "POST",
				 success: function(results) { jQuery('#comment_history').prepend(results); set_def_text('a');},
				 complete: function(results) { jQuery('#main_box_loader').hide(); }
			});
		}
		
	});

    jQuery('.comment_reply_button').click(function() {

        comment = jQuery('#input_'+this.id).val();
        wrapper = '#wrapper_id_'+this.id;
        boxLoader = '#box_loader_'+this.id;
        if(comment != def_txt) {

            jQuery(boxLoader).show();

            jQuery.ajax({

                data: { reply_comment: comment, form: "reply_comment_req", stream_id: this.id},
                url:  "/stream/reply_share",
                type: "POST",
                success: function(results) { jQuery(wrapper).prepend(results); set_def_text('a');},
                complete: function(results) { jQuery(boxLoader).hide(); }
            });
        }

    });
	
	jQuery('#main_comment').focus(function() {set_def_text('r');});
	jQuery('#main_comment').blur(function() {set_def_text();});
	
	/*
	jQuery('#link_share_btn').click(function() {
		
		comment = jQuery('#main_link').val();
		
		if(comment != def_txt) {
			
			jQuery('#main_box_loader').show();
			
			jQuery.ajax({
				 data: 'url='+comment+'&form=main_comment_req',
				 url:  "/stream/get_link",
				 type: "POST",
				 success: function(results) { jQuery('#comment_history').prepend(results); set_def_text('a');},
				 complete: function(results) { jQuery('#main_box_loader').hide(); }
			});
		}
		
	});
	*/
	
	// delete event
	jQuery('#link_share_btn').livequery("click", function(){
	 
		if(!isValidURL(jQuery('#main_link').val()))
		{
			alert('Please enter a valid url.');
			return false;
		}
		else
		{
			jQuery('#link_box_loader').show();
			comment = jQuery('#main_link').val();
			
			jQuery.ajax({
				 data: 'url='+comment+'&form=main_comment_req',
				 url:  "/stream/get_link",
				 type: "POST",
				 success: function(results) {
					 
					 if(results.match(/^--VID--/)) {
						 res = results.replace(/^--VID--/,'');
						 jQuery('#comment_history').prepend(res);
					 }
					 else {
						 if(results != '') {
							 jQuery('#hold_post').html('');
							 jQuery('#hold_post').prepend(results);
							 jQuery('#hold_post').show();
							 
							 jQuery('.images img').hide();
							 jQuery('#load').hide();
							 jQuery('img#1').fadeIn();
							 jQuery('#cur_image').val(1);
						 }
					 }
				 },
				 complete: function(results) { jQuery('#link_box_loader').hide(); }
			});
		}
	});	
	
	// next image
	jQuery('#next_prev_img').livequery("click", function(){
	 
		var firstimage = jQuery('#cur_image').val();
		jQuery('#cur_image').val(1);
		jQuery('img#'+firstimage).hide();
		
		if(firstimage <= jQuery('#total_images').val())
		{
			firstimage = parseInt(firstimage)+parseInt(1);
			jQuery('#cur_image').val(firstimage);
			jQuery('img#'+firstimage).show();
		}
	});
	
	// prev image
	jQuery('#prev_prev_img').livequery("click", function(){
	 
		var firstimage = jQuery('#cur_image').val();
	 
		jQuery('img#'+firstimage).hide();
		if(firstimage>0)
		{
			firstimage = parseInt(firstimage)-parseInt(1);
			jQuery('#cur_image').val(firstimage);
			jQuery('img#'+firstimage).show();
		}
	 
	});	
	
	// watermark input fields
	jQuery(function(jQuery){
	   jQuery("#main_link").Watermark("http://");
	});
	jQuery(function(jQuery){
	 
		jQuery("#main_link").Watermark("watermark","#369");
	 
	});
	
	function UseData() {
	  jQuery.Watermark.HideAll();
	  jQuery.Watermark.ShowAll();
	}
});

function set_def_text(flag) {
	if(flag && flag == 'r')
		jQuery('#main_comment').val('');
	else if(flag && flag == 'a')
		jQuery('#main_comment').val(def_txt);
	else if(jQuery('#main_comment').val() == '')
		jQuery('#main_comment').val(def_txt);
}

function isValidURL(url){
	var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

	if(RegExp.test(url)){
		return true;
	}else{
		return false;
	}
}