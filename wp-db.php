<?php
include("wp-constants.php");
/*
Rate a post for a user if he or she has not already voted for it
*/
function rate_post($post_id, $rating, $rating_type)
{

	global $wpdb;	
	$table_name = $wpdb->prefix . WP_MULTIRATING_POSTEDRATINGS_TABLE;
	
	$post_title = get_the_title($post_id);
	$categories = get_the_category($post_id);
	$category_str = ';';
	foreach($categories as $category)
	{
		$category_str .= $category->cat_ID . ";";
	}
	
	
	$user_id = -1;
	$rated_on = date("y-m-d" ,time());
	$duration = get_option('wpmr_duration');
	if(!$duration)
		$duration = 0;
	else
		$duration = $duration * 60;

	
		$wpdb->insert($table_name, array("post_id"=>$post_id,"post_title"=>$post_title,"rating_type_id"=>$rating_type,"rated_on"=>$rated_on,"category"=>$category_str,"rating"=>$rating,"rated_by"=>$user_id));
		
		$url_parts = parse_url(get_bloginfo("wpurl"));
		setcookie('rating_' . $rating_type . '_' . $post_id, serialize('true'),time() + $duration,'/', $url_parts["host"]);
		$rating_meta = get_rating_meta($post_id,$rating_type);
		$rating_obj = get_rating_type($rating_type);
		update_post_meta($post_id,'_wpmr_' . $rating_type . '_avg',$rating_meta["average"]);
		update_post_meta($post_id,'_wpmr_' . $rating_type . '_count',$rating_meta["count"]);
		update_post_meta($post_id,'_wpmr_' . $rating_type . '_max',$rating_obj->max_rating_value);
	
}
/*Getting the ratings meta data*/
function get_rating_meta($post_id,$rating_type)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_POSTEDRATINGS_TABLE;
	$sql = 'SELECT COUNT(*) FROM ' . $table_name . ' WHERE post_id=%d AND rating_type_id=%d';
	$count = $wpdb->get_var($wpdb->prepare($sql, $post_id,$rating_type));

	$sql = 'SELECT AVG(rating) FROM ' . $table_name . ' WHERE post_id=%d AND rating_type_id=%d';
	$avg = $wpdb->get_var($wpdb->prepare($sql, $post_id,$rating_type));

	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	$sql = 'SELECT rating_name FROM ' . $table_name . ' WHERE rec_id=%d';
	$name = $wpdb->get_var($wpdb->prepare($sql, $rating_type));
	return array("count"=>$count,"average"=>$avg, 'name'=>$name);
}

/*Verify post ratings*/
function verify_post_ratings()
{
	$args = array("numberposts"=>-1);
	$posts = get_posts($args);
	
	$processed = 0;
	foreach($posts as $post)
	{		
		
		$post_id = $post->ID;
		$rating_types = get_applicable_rating_types($post_id);
		
		foreach($rating_types as $rating_type)
		{
			
			$rating_meta = get_rating_meta($post_id,$rating_type->rec_id);
			
			update_post_meta($post_id,'_wpmr_' . $rating_type->rec_id . '_avg',$rating_meta["average"]);
			update_post_meta($post_id,'_wpmr_' . $rating_type->rec_id . '_count',$rating_meta["count"]);
			update_post_meta($post_id,'_wpmr_' . $rating_type->rec_id . '_max',$rating_type->max_rating_value);
		}
		$processed++;
	}
	return $processed;	
}

/*
Insert a new rating type
*/
function insert_rating_type($rating_name,$auto_start,$auto_expire,$max_rating,$image,$categories)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	$wpdb->insert($table_name, array("rating_name"=>$rating_name,"starts_on_enabled"=>$auto_start,"expires_on_enabled"=>$auto_expire,"max_rating_value"=>$max_rating,"category"=>$categories,"rating_image_url"=>$image));
}

/*Updates a rating type*/
function update_rating_type($rating_name,$auto_start,$auto_expire,$max_rating,$image,$categories,$rec_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
$wpdb->update($table_name, array("rating_name"=>$rating_name,"starts_on_enabled"=>$auto_start,"expires_on_enabled"=>$auto_expire,"max_rating_value"=>$max_rating,"category"=>$categories,"rating_image_url"=>$image),array("rec_id"=>$rec_id));
}
/*
Checks if the user has rated for a post
*/
function has_user_rated($post_id,$rating_type)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_POSTEDRATINGS_TABLE;
	global $current_user;
	get_currentuserinfo();
	$user_id = $current_user->ID;
	if($user_id == 0)
		return 0;
	
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE rating_type_id = %d AND rated_by=%d AND post_id=%d", $rating_type,	$user_id,$post_id));
	return $count;
}

/*Get the rating type with a nid*/
function get_rating_type($type_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	
	$sql = "SELECT * FROM " . $table_name . " WHERE rec_id=%d";
	$rating_types = $wpdb->get_results($wpdb->prepare($sql,$type_id));
	return $rating_types[0];
}

/*Delete a rating type*/
function delete_rating_type($type_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	
	$sql = "DELETE FROM " . $table_name . " WHERE rec_id=%d";
	$wpdb->query($wpdb->prepare($sql,$type_id));	
}

/*Get all the rating types*/
function get_rating_types()
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	
	$sql = "SELECT * FROM " . $table_name;
	$rating_types = $wpdb->get_results($wpdb->prepare($sql));
	return $rating_types;
}
/*
Get average rating display for a post
*/

function get_post_rating($post_id=-1,$rating_type_id=-1, $display_summary=0,$disable_vote=0)
{


	global $post;
	if($post)
		$post_id = $post->ID;

	$selected_types = get_applicable_rating_types($post_id);
	foreach($selected_types as $type_name=>$type)
	{

		if($rating_type_id != -1)
		{
			if($type->rec_id != $rating_type_id)
			{
				continue;
			}
		}
		
		format_rating_display($post_id, $type, get_post_meta($post_id,'_wpmr_' . $type->rec_id. '_avg',true),$display_summary,$disable_vote);
	}	
}

/*Gets an array of all the rating type objects applicable to a give post*/
function get_applicable_rating_types($post_id)
{
	global $wpdb;
	
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;	

	$post_categories = wp_get_post_categories($post_id);
	
	$sql = "SELECT * FROM " . $table_name;
	$rating_types = $wpdb->get_results($wpdb->prepare($sql));
	$selected_types = array();//stores the filtered rating types
	//See each category and find the rating types of that category
	foreach($post_categories as $post_category)
	{
		foreach($rating_types as $rating_type)
		{
			if(strpos($rating_type->category, ";" . $post_category . ";") !== false)
			{
				//Check if this type has already been selected in previous iterations. If not select it
				if(!array_key_exists($rating_type->rating_name,$selected_types))
					$selected_types[$rating_type->rating_name] = $rating_type;
			}
		}
	}
	return $selected_types;
}
/*
Gets the rating id for a given rating type
*/
function get_rating_id($rating_type_name)
{
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;	
	$sql = "SELECT rec_id FROM " . $table_name . " WHERE rating_name=%s";
	
	$results = $wpdb->get_results($wpdb->prepare($sql, $rating_type_name));
	return $results[0]->rec_id;
}

/*
Formats the rating display
*/
function format_rating_display($post_id, $rating_type_obj, $avg = 0,$display_summary=0,$disable_vote=0)
{	
	$image_size = 16;
	$rating_id = $rating_type_obj->rec_id;
	$rating_name = $rating_type_obj->rating_name;
	$image_url = $rating_type_obj->rating_image_url;
	$rating_slug = get_slug($rating_name);
	$max = $rating_type_obj->max_rating_value;
	$internal_div_width_perc = 100 * ($avg / $max);
	$off_image_url = $image_url . '/off' . $image_size . '.png';
	$on_image_url = $image_url . '/on' . $image_size . '.png';
	$hover_image_url = $image_url . '/hover' . $image_size . '.png';
	$has_user_rated = 0;
	$is_user_logged_in = 1; // is_user_logged_in();
	
	
	$start_lock = false;
	$end_lock = false;
	$cur_date = time();


	//Check if the rating is enabled for vote
	if($rating_type_obj->starts_on_enabled)
	{
		$rating_start_date = get_post_meta($post_id, "WPMR_" . $rating_name . "_starts", true);		
		if(!empty($rating_start_date))
		{
			$start_date = strtotime($rating_start_date);
			if($cur_date > $start_date)
			{
				$start_lock = false;
			}
			else
				$start_lock = true;
			
		}
	}
	
	if($rating_type_obj->expires_on_enabled)
	{
		$rating_end_date = get_post_meta($post_id, "WPMR_" . $rating_name . "_ends", true);
		if(!empty($rating_end_date))
		{
			$end_date = strtotime($rating_end_date);
			if($cur_date > $end_date)
			{
				$end_lock = true;
			}
			else
				$end_lock = false;
			
		}

	}
	if($_COOKIE['rating_' . $rating_id . '_' . $post_id])
		$has_user_rated = 1;
	else
		$has_user_rated = 0;
		
	if(current_user_can("administrator"))
	{
		$should_vote = 1;
		$start_lock = false;
		$end_lock = false;
		$has_user_rated = false;
	}
	else	
		$should_vote = (1 - $has_user_rated) && $is_user_logged_in && (1 - $start_lock) && (1 - $end_lock) && (1-$disable_vote);
?>
<span id="spanRating_<?php echo $rating_slug . "_" . $post_id ?>" >

<div <?php if($should_vote) echo 'rel="wpmrrate" on_image="'. $on_image_url . '" off_image="' . $off_image_url . '" hover_image="' . $hover_image_url . '"'; else {  }  ?> id="divRating_<?php echo $rating_slug . "_" . $post_id ?>" class="wp-mr-rating-div-container" style="background-image:url('<?php echo $off_image_url ?>');width:<?php echo ($image_size * $max) ?>px; height:<?php echo $image_size; ?>px">

<div class="wp-mr-rating-div-container" id="internal_progress" style="background-image:url('<?php echo $on_image_url ?>');width:<?php echo $internal_div_width_perc ?>%; height:<?php echo $image_size; ?>px"></div><!--#internal_progress-->

<?php if($should_vote): ?>
<div id="divRating_<?php echo $rating_slug . "_" . $post_id ?>_anchor_container" class="wp-mr-rating-anchor-box">
	<?php 
		for($index=1;$index<$max+1;$index++)
		{
	?>
		<a id="a<?php echo $index; ?>" rel="<?php echo $index; ?>" onClick="wp_mr_send_rating('<?php echo $post_id ?>','<?php echo $rating_id ?>','<?php echo $index; ?>','spanRating_<?php echo $rating_slug . "_" . $post_id ?>');"	><img src="<?php echo $off_image_url ?>"></a>
	<?php
		}
	?>
</div><!--.wp-mr-rating-anchor-box-->	
<?php endif;///$should_vote ?>
</div><!--wp-mr-rating-div-container-->
<?php if($start_lock || $end_lock):?>

<span class="wp-mr-lock-red" title="The rating is locked for vote"></span>
<?php elseif($has_user_rated): ?>
<span class="wp-mr-lock-green" title="You have rated this post"></span>
<?php endif; ?>
<?php 
	if($display_summary == 1)
		echo get_formatted_rating_details($post_id,$rating_id); 
?>
</span>
<?php	
	
}//end of function format_rating_display

/*Generates slug for a given term*/
function get_slug($term)
{
	$str = strtolower($term);
	$str = preg_replace("/[^a-z0-9\s-]/","", $str);
	$str = trim(preg_replace("/[\s-]+/", " ", $str));	
	$str = preg_replace("/\s/","_",$str);
	return $str;
}

/*gets the raing details*/
function get_rating_details($post_id, $rating_type)
{
	$total_votes = get_post_meta($post_id,'_wpmr_' . $rating_type . '_count',true);
	$avg_rating = get_post_meta($post_id,'_wpmr_' . $rating_type . '_avg',true);
	$max_rating = get_post_meta($post_id,'_wpmr_' . $rating_type . '_max',true);
	if(!is_numeric($total_votes))
		$total_votes = 0;
	if(!is_numeric($avg_rating))
		$avg_rating = 0;
	if(!is_numeric($max_rating))
		$max_rating = 0;
	return array("total"=>$total_votes,"average"=>$avg_rating,"maximum"=>$max_rating);
}
/*Formats a rating string for a particular post*/
function get_formatted_rating_details($post_id,$rating_type)
{
	$rating_details = get_rating_details($post_id,$rating_type);
	$total_votes = $rating_details['total'];
	$avg_rating = $rating_details['average'];
	$max_rating = $rating_details['maximum'];
	$meta = get_rating_meta($post_id, $rating_type);
	return "<div class='wp-mr-rating-summary'><p><strong>{$meta['name']}</strong><p><b>" . number_format($avg_rating,2) . "</b> out of " . $max_rating . "</p>Rated by " . $total_votes . " " . ($total_votes==1?"person":"persons") . '</div>';
	
}
/*Gets the count number of rated post for a given rating type sorted by a given order*/
function get_wpmr_rated_posts($category, $rating_id, $order, $count = 0, $meta_query = array())
{
	if($count < 1)
		$count = get_option("posts_per_page");

	$avg_key = "_wpmr_" . $rating_id . '_avg';
	global $paged;

	$args = array(		
		"meta_key"=>$avg_key,
		"orderby"=>"meta_value_num",
		"order"=>$order,
		"posts_per_page"=>$count,
		"paged"=>$paged,
		"meta_query"=>$meta_query
	);
	if(!empty($category))
	{
		$args['category_name'] = $category;
	}
	$query = new WP_Query($args);
	return $query;
}
/*Attach custom fields to post before it is saved*/
add_filter('content_save_pre','attach_custom_fields');
function attach_custom_fields($content)
{
	$post_id = get_the_ID();
	$rating_types =  get_applicable_rating_types($post_id);	

	foreach($rating_types as $type)
	{
		$total_votes = get_post_meta($post_id,'_wpmr_' . $type->rec_id . '_count',true);
		$avg_rating = get_post_meta($post_id,'_wpmr_' . $type->rec_id . '_avg',true);
		$max_rating = get_post_meta($post_id,'_wpmr_' . $type->rec_id . '_max',true);
		
		if($total_votes == '')
		{
			update_post_meta($post_id,'_wpmr_' . $type->rec_id . '_count',0);		
		}
		if($avg_rating == '')
		{		
			update_post_meta($post_id,'_wpmr_' . $type->rec_id . '_avg',0);
		}
		if($max_rating == '')
		{	
			update_post_meta($post_id,'_wpmr_' . $type->rec_id . '_max',$type->max_rating_value);		
		}


	}
	return $content;
}

add_filter("query_vars","attach_parameters");
function attach_parameters($qvars)
{
	$qvars[] = 'wpmrtype';
	return $qvars;
}

?>