<?php
if(!current_user_can("administrator"))
{
	die("Accessed Denied");
}
include("donate.php");
$msg = '';
if(isset($_REQUEST['verify']))
{	
	include_once("wp-db.php");	
	$processed = verify_post_ratings();
	
	$msg = 'Successfully verified ' . $processed . ' post(s).';	

}
else if(isset($_REQUEST['submit']))
{
	$auto = 0;
	if(isset($_REQUEST['auto_insert']))
		$auto = 1;
	$duration = 0;
	if(isset($_REQUEST['duration']))
		$duration = $_REQUEST['duration'];
	update_option('wpmr_autoinsert', $auto);	
	update_option('wpmr_duration', $duration);	
	$msg = 'Successfully saved the options';	
}
else
{
	$auto_val = get_option('wpmr_autoinsert');
	if($auto_val)
		$auto = 1;
	else
		$auto = 0;
}



?>
<div class="wrap">
<h2>Options</h2>
<div><br/><b><?php echo $msg; ?></b><br/></div>
<div>Click on Verify button below to verify all the posts for ratings. Note: It may take a while to verify depending on the number of posts.</div>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
	<input type="hidden" name="verify" value="1" />
	<input type="submit" name="submit" class="button" value="Verify Posts Ratings &raquo;" />
</form>
<hr/>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">

<table class="form-table">
	<tr>
		<th scope="row">Auto Insert</th>
		<td><input type="checkbox" name="auto_insert" value="1" <?php echo $auto?'checked':'' ?>/></td>
		<td>Automatically insert the ratings associated with a post before its content. Note: If unchecked you need to manually insert the shortcode [wpmrrating] to your post's edit page to display the ratings.</td>
	</tr>
    <tr>
		<th scope="row">Rating Gap</th>
		<td><input type="text" name="duration" value="1" <?php echo $duration ?>/></td>
		<td>The duration in minutes for which to prevent user from voting on the same post again. (Note: The plugin creates a cookie on the client computer to keep track of last vote. The function might not work if the user has disabled cookie or has delete cookie manually)</td>
	</tr>
</table>    

<input type="submit" name="submit" class="button" value="Save Changes" />
</form>