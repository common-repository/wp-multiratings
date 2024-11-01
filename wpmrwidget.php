<?php
class WpmrWidget extends WP_Widget  {  
    function WpmrWidget() {  
        parent::WP_Widget(false, 'WPMR Rated Posts');  
    }  
	function form($instance) {
	$title = esc_attr($instance['title']);  
	$number_posts = esc_attr($instance['number_posts']);
	$rating_type_id = esc_attr($instance['rating_type_id']);
	$order = esc_attr($instance['rating_order']);
	$rating_types = $this->get_rating_types();

	if(empty($number_posts))
		$number_posts = 5;
?>  
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p> 
        <p>
        <label for="<?php echo $this->get_field_id('number_posts'); ?>"><?php _e('Number of posts to display:'); ?></label>
        <input size=3 id="<?php echo $this->get_field_id('number_posts'); ?>" name="<?php echo $this->get_field_name('number_posts'); ?>" type="text" value="<?php echo $number_posts; ?>" /></p> 
        <p>
        <label for="<?php echo $this->get_field_id('rating_type_id'); ?>"><?php _e('Rating type:'); ?></label>        
        <select id="<?php echo $this->get_field_id('rating_type_id'); ?>" name="<?php echo $this->get_field_name('rating_type_id'); ?>"> 
		<?php
			foreach($rating_types as $type):
		?>
        		<option value="<?php echo $type->rec_id ?>" <?php echo $rating_type_id==$type->rec_id?'selected':'' ?>><?php echo $type->rating_name ?> </option>
        <?php	
			endforeach;
		?>
          
        </select>
        </p> 
        <p>
        <label for="<?php echo $this->get_field_id('rating_order'); ?>"><?php _e('Post Order'); ?></label>
        <select id="<?php echo $this->get_field_id('rating_order'); ?>" name="<?php echo $this->get_field_name('rating_order'); ?>"> 
            	<option value="ASC" <?php echo $order=='ASC'?'selected':'' ?>>Most rated at top</option>
                <option value="DESC" <?php echo $order=='DESC'?'selected':'' ?>>Least rated at top</option>
        </select>
        </p>
         
<?php  
    } 
	
	
        // outputs the options form on admin  
    
	
	function get_rating_types()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
		
		$sql = "SELECT * FROM " . $table_name;
		$rating_types = $wpdb->get_results($wpdb->prepare($sql));
		return $rating_types;
	}

	function update($new_instance, $old_instance) {  
        // processes widget options to be saved  
		if(empty($new_instance['number_posts']))
			$new_instance['number_posts'] = 5;
        return $new_instance;  
    }  
	function widget($args, $instance) {

		$posts = $this->get_wpmr_rated_posts($instance['rating_type_id'], $instance['rating_order'], $instance['number_posts']);
		

		$args['title'] = $instance['title'];
		echo $args['before_widget'] . $args['before_title'] . $args['title'] . $args['after_title'];
		
		echo "<ul>";

			while($posts->have_posts())
			{
				$posts->the_post();
				?>
				<li><a href="<?php echo get_permalink(get_the_ID()) ?>"><?php the_title(); ?></a></li>	
				<?php 
			}
		echo "</ul>";
		echo $args['after_widget'];
		
    }  
	
	/*Gets the count number of rated post for a given rating type sorted by a given order*/
	function get_wpmr_rated_posts($rating_id, $order, $count = 0)
	{		
		$avg_key = "_wpmr_" . $rating_id . '_avg';
		$args = array(		
			"meta_key"=>$avg_key,
			"orderby"=>"meta_value_num",
			"order"=>$order,
			"numberposts"=>$count
		);
		$query = new WP_Query($args);
		return $query;
	}
}  
function wpmr_register_widgets() {
	register_widget( 'WpmrWidget' );
}

add_action( 'widgets_init', 'wpmr_register_widgets' );
?>