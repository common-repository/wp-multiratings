<?php
if(!current_user_can("administrator"))
{
	die("Accessed Denied");
}
include("donate.php");
include_once("wp-db.php");
?>
<div class="wrap">
<h2>Rating Types</h2>
</div><!--.wrap-->
<?php 
$rating_types = get_rating_types();
?>
<div style="float:left;margin-bottom:10px;text-align:right;width:100%"><a class="button" href="admin.php?page=wp-multiratings/multiratings-types-post.php">Add New Rating Type</a></div> 
<table class="widefat">
		<thead>
			<tr>
				<th width="10%">ID</th>
				<th width="15%">Rating Name</th>
				<th width="8%">Auto Start</th>
				<th width="8%">Auto Lock</th>
				<th width="10%">Maximum Rating</th>	
				<th width="10%">Image</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				if(empty($rating_types)):?>
				<i>You haven't added any rating types. Click on Add button at top right corner to create a new type</i>
			<?php
				else:
				
				foreach($rating_types as $type):
			?>
			<tr>
				<td><?php echo $type->rec_id ?> </td>
				<td><?php echo $type->rating_name ?> </td>
				<td><?php echo $type->starts_on_enabled ?> </td>
				<td><?php echo $type->expires_on_enabled ?> </td>
				<td><?php echo $type->max_rating_value ?> </td>
				<td><img src="<?php echo  $type->rating_image_url . '/on16.png' ?>" /> </td>
				<td><a href="admin.php?page=wp-multiratings/multiratings-types-post.php&mode=edit&id=<?php echo $type->rec_id ?>">Edit</a>&middot;<a onclick="return confirm('Remove this rating type? The action can\'t be undone.');" href="admin.php?page=wp-multiratings/multiratings-types-post.php&remove=<?php echo $type->rec_id ?>">Remove</a></td>
			</tr>
			<?php endforeach; 
			endif;
			?>
		</tbody>
</table>