<?php
/*
Plugin Name:Wp Multiratings
Plugin URI:http://www.geektrick.com/2011/11/19/step-by-step-procedure-to-use-wordpress-wp-multiratings-plugin/
Description: Allows wordpress administrators to have rating system for their wordpress posts and pages.
Version:1.0
Author: Anshul Sojatia
Author URI:http://www.geektrick.com/author/anshulsojatia/
Tags: wordpress post ratings, multiple ratings
*/

/*Widget for the sidebar*/
include('wpmrwidget.php');
/*The constants used for plugin*/
include_once("wp-constants.php");
/*The core functions for plugin*/
include_once("wp-db.php");


wp_enqueue_script("wp-multirating-jquery",plugins_url("/wp-multiratings/jquery.js"));
wp_enqueue_script("wp-multirating-script",plugins_url("/wp-multiratings/wp-multiratings.js"),array('jquery'));
wp_localize_script('wp-multirating-script','WPMRAjax',array('ajaxurl'=>admin_url('admin-ajax.php')));
add_action('wp_ajax_wpmr-rate','do_rating');
add_action('wp_ajax_nopriv_wpmr-rate','do_rating');


function do_rating()
{
		rate_post($_POST['id'], $_POST['rating'], $_POST['type']);		
		get_post_rating($_POST['id'],$_POST['type'],1);
		exit;
}

/*
Shortcode support
*/
add_shortcode('wpmrrating', 'shortcode_ratings');
function shortcode_ratings($attr)
{	get_post_rating(-1, -1, 1, 0);
}

/*automatic insertion*/
$auto_val = get_option('wpmr_autoinsert');
if($auto_val)
{
	add_filter('the_content', 'attach_ratings');
}	
function attach_ratings($content)
{
	get_post_rating(-1, -1, 1, 0);
	echo "<div style='clear:both'></div>";
	return $content;
}
/*
Installs the required database tables for the plugin while it's activated
*/

register_activation_hook(__FILE__,'wp_multiratings_install');
function wp_multiratings_install()
{
 	global $wpdb;
	$table_name1 = $wpdb->prefix . WP_MULTIRATING_POSTEDRATINGS_TABLE;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$sql1 = "CREATE TABLE IF NOT EXISTS " . $table_name1 . "(
			rec_id bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			post_title text,
			rating_type_id bigint(20),
			rated_on datetime,
			category text,
			rating int,
			rated_by bigint(20) NOT NULL,
			PRIMARY KEY  (rec_id)
			);";
	dbDelta($sql1);

	
	$table_name2 = $wpdb->prefix . WP_MULTIRATING_RATINGS_TABLE;
	$sql2 = "CREATE TABLE IF NOT EXISTS " . $table_name2 . "(
			rec_id bigint(20) NOT NULL AUTO_INCREMENT,
			rating_name text NOT NULL,
			starts_on_enabled boolean,
			expires_on_enabled boolean,
			max_rating_value int,
			category text,
			rating_image_url text,			
			PRIMARY KEY  (rec_id)
			);";

	dbDelta($sql2);
}

/*
Administration
*/
add_action("admin_menu","wp_multiratings_menu");
function wp_multiratings_menu()
{
	if(function_exists("add_menu_page")):
		add_menu_page("WP Multiratings","WP Multiratings","administrator","wp-multiratings\multiratings-types.php","");
	endif;
	
	if(function_exists("add_submenu_page")):		
		add_submenu_page("wp-multiratings\multiratings-types.php","WP Multiratings","Rating Types","administrator","wp-multiratings\multiratings-types.php","");
		add_submenu_page("wp-multiratings\multiratings-types.php","WP Multiratings","Add New","administrator","wp-multiratings\multiratings-types-post.php","");
		add_submenu_page("wp-multiratings\multiratings-types.php","WP Multiratings","Options","administrator","wp-multiratings\multiratings-options.php","");
	endif;
}

/*Gets relative plugin directory*/
function get_relative_plugin_dir()
{
	$wpurl = get_bloginfo("wpurl");

	$relative = str_replace($_SERVER['HTTP_HOST'],"",$wpurl);
	return $wpurl;
}

/*Include stylesheets to wordpress page*/
add_action('wp_print_styles','include_stylesheet_files');
function include_stylesheet_files()
{
	wp_enqueue_style("wp-multirating-style",plugins_url("/wp-multiratings/wp-multiratings.css"));
}

/*Include stylesheets to wordpress page*/
if(!is_admin())
	add_action('wp_print_scripts','include_script_files');
function include_script_files()
{
	wp_enqueue_script("wp-multirating-script",plugins_url("/wp-multiratings/jquery.js"));
	wp_enqueue_script("wp-multirating-script",plugins_url("/wp-multiratings/wp-multiratings.js"));
	
}


?>