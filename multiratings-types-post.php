<?php
if(!current_user_can("administrator"))
{
	die("Access Denied");
}
include("donate.php");
include_once("wp-db.php");
$rating_name = '';
$auto_start = '';
$auto_expire = '';
$max_rating = 5;
$image ='';
$categories = '';

if(isset($_REQUEST['remove'])):
	delete_rating_type($_REQUEST['remove']);
?><script>window.location.href='<?php echo get_bloginfo('url') . '/wp-admin/admin.php?page=wp-multiratings/multiratings-types.php';?>';</script>
<?php
elseif(isset($_POST['submit'])):
	$rating_name = $_POST['rating_name'];
	if(empty($rating_name))
	{
		$rating_name = 'Post Rating';
	}
	$auto_start=0;
	$auto_expire=0;
	if(isset($_POST['auto_start']))
		$auto_start = 1;
	else
		$auto_start = 0;
		
	if(isset($_POST['auto_expire']))
		$auto_expire = 1;
	else
		$auto_expire = 0;	
	
	
	$max_rating = $_POST['max_rating_value'];
	$category_items = $_POST['rating_category'];
	$categories = ';';
	$image = $_POST['rating_image_url'];
	if(!empty($category_items))
	{
		foreach($category_items as $item)
			$categories .= $item . ';';
	}

	if(isset($_REQUEST["mode"]) && isset($_REQUEST["id"])):
		update_rating_type($rating_name,$auto_start,$auto_expire,$max_rating,$image,$categories,$_REQUEST["id"]);
	else:
		insert_rating_type($rating_name,$auto_start,$auto_expire,$max_rating,$image,$categories);		
	endif;		
?>		
	<script>
		window.location.href='<?php echo get_bloginfo('url') . '/wp-admin/admin.php?page=wp-multiratings/multiratings-types.php';?>';
    </script>
<?php
	exit;
else:

if(isset($_REQUEST["mode"]) && isset($_REQUEST["id"]))
{
	$type = get_rating_type($_REQUEST["id"]);
	$rating_name = $type->rating_name;
	$auto_start = $type->starts_on_enabled==0?"":"checked";

	$auto_expire = $type->expires_on_enabled==0?"":"checked";
	$max_rating = $type->max_rating_value;
	$image = $type->rating_image_url;
	$categories = $type->category;
}

?>
<div class="wrap">
<h2>Rating Types</h2>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">

<table class="form-table">
	<tr>
		<th scope="row">Rating Name</th>
		<td><input type="text" name="rating_name" value="<?php echo $rating_name; ?>"/></td>
		<td>(The name of the rating)</td>
	</tr>
	<tr>
		<th scope="row">Auto Start Enabled</th>
		<td><input type="checkbox" name="auto_start" <?php echo $auto_start ?> value="on"/></td>
		<td>(Enables rating system for a post from specified date. Should have a custom field wp_%rating name%_start_date set to start date)</td>
	</tr>
	<tr>
		<th scope="row">Auto Expire Enabled</th>
		<td><input type="checkbox" name="auto_expire" <?php echo $auto_expire ?> value="on"/></td>
		<td>(Disables rating system for a post from specified date. Should have a custom field wp_%rating name%_end_date set to end date)</td>
	</tr>
	<tr>
		<th scope="row">Maximum Rating Value</th>
		<td><input type="text" name="max_rating_value" value="<?php echo $max_rating ?>"/></td>
		<td>(The maximum value of the rating)</td>
	</tr>
	<tr>
		<th scope="row">Rating Image</th>
		<td>
		<?php 
			$image = PLUGIN_DIR . '/images/stars';
		?>
		<ul>
			<li><input type="radio" name="rating_image_url" checked="checked" value="<?php echo $image ?>"/><img src="<?php echo $image . '/on16.png' ?>"/> </li>
		</ul>

		
		</td>
		<td>(The image that will be shown for rating)</td>
	</tr>
	<tr>
		<th scope="row">Category</th>
		<td>
		<ul>
		<?php
			  $cat_args = array('orderby'=>'name','order'=>'ASC','hide_empty'=>0);
			  $all_categories = get_categories($cat_args);
			  
			  foreach($all_categories as $category)
			  {
			  	$check = '';

			  	if(strpos($categories,';' . $category->cat_ID . ';') !== false){				
					$checked = 'checked';
				}
				
		?>
		<li><input <?php echo $checked; ?> type="checkbox" id="chkCategory_<?php echo $category->cat_name ?>" name="rating_category[]" value="<?php echo $category->cat_ID ?>" /><label for="chkCategory_<?php echo $category->cat_name ?>"><?php echo $category->cat_name ?></label> </li>
		<?php
			unset($checked);
			  }
		?>		
			
		</ul>
			
		</td>
		<td valign="top">(The post categories for which the rate system is to be enabled.)</td>
	</tr>
	<tr>
		<th scope="row"></th>
		<td><input type="submit" class="button" name="submit" value="Submit"/>
		</td>
	</tr>
	</table>
</form>
</div>
<?php endif; ?>