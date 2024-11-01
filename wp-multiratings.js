//attach rating system on load
$(document).ready(function(){
			
			$("div[rel='wpmrrate']").each(function(){

				var id = $(this).attr("id"); 
				$(this).mouseenter(function(){
				$("#" + id + " #internal_progress").css("display","none");
				$("#" + id + "_anchor_container").css("display","block");
											});
				var hover_image = $(this).attr("hover_image");
				var on_image = $(this).attr("on_image");
				var off_image = $(this).attr("off_image");
				$("#" + id + "_anchor_container a").each(function(){
					$(this).mouseover(function(){ 
											   
					$("#" + id + "_anchor_container a img").attr("src", off_image);
					var curLink= $(this).attr("rel");

					for(var index=1; index< curLink;index++)
						$("#" + id + "_anchor_container #a" + index + " img").attr("src",on_image);
					$("#" + id + "_anchor_container #a" + curLink + " img").attr("src",hover_image);							   
											   });											  
					
						
				});
			});
			$("div[rel='wpmrrate']").mouseleave(function(){
				var id = $(this).attr("id"); 
				$("#" + id + " #internal_progress").css("display","block");
				$("#" + id + "_anchor_container").css("display","none");				
			});
			
});

function wp_mr_send_rating(post,rating_type,rating,response)
{
	$("#" + response).animate({opacity:0},500,
							  function()
							  {
								  _send_rating(post,rating_type,rating,response);
							  }
							  );
}
function _send_rating(post,rating_type,rating,response)
{
	ajax_url = WPMRAjax.ajaxurl;
	$.ajax({
		type:"POST",
		url:ajax_url,
		data:"action=wpmr-rate&id=" +post + "&type=" + rating_type + "&rating=" + rating,
		success:function(msg){
			$("#" + response).html(msg);
			$("#" + response).animate({opacity:1},500);
		}
	});

}