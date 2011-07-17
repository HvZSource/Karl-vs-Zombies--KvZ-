<?php
ob_start();
session_start();

require_once('security.php');
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat'); 
$sql = my_quick_con($config) or die("SQL problem"); 
$table_u = $config['user_table'];
$game_name = $config['game_name'];
$id = $_SESSION['id'];
$ret = mysql_query("SELECT pic_path FROM $table_u WHERE id='$id';"); 
$c_pic_path = mysql_fetch_assoc($ret);
$c_pic_path = $c_pic_path['pic_path'];
?>
<?php include('template_top.php'); ?>

<?php
if($_POST['submit'] == 'Change Password') {
	$ret = mysql_query("SELECT password FROM $table_u WHERE id='$id';");
	$pass_ret = mysql_fetch_assoc($ret); 
	$pass_ret = $pass_ret['password'];
	$pass_cur = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_original']));
	$pass_new = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_new']));
	$pass_con = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_confirm']));
	print "<table width=100% height=100%><tr><td align=center valign=center>";
	if($pass_ret == $pass_cur) {
		if(strlen($_POST['pass_new']) >= 4 && strlen($_POST['pass_new']) <= 20) {
			if($pass_new == $pass_con) {
				$ret = mysql_query("UPDATE $table_u SET password = '$pass_new' WHERE id='$id';");
				print "Password successfully changed.<br>";
				print "<a href='account.php'>Back</a>";
			} else {
				print "The passwords you entered did not match."; 
				print "<a href='account.php'>Back</a>";
			}
		} else {
			print "Your new password must be between 4 and 20 alphanumerics.<br>";
			print "<a href='account.php'>Back</a>";
		}
	} else {
		print "The password you entered was incorrect.<br>"; 
		print "<a href='account.php'>Back</a>";
	}
	print "</td></tr></table>";
} else if($_POST['submit'] == 'Upload') {
	$ret = mysql_query("SELECT fname, lname FROM $table_u WHERE id='$id';");
	$row = mysql_fetch_row($ret); 
	$extension = basename($_FILES['new_pic']['name']);
	$sub_ex = explode(".", $extension); 
	$extension = strtolower($sub_ex[sizeof($sub_ex) - 1]);
	$target_path = "pics/$game_name/$row[0]_$row[1].$extension";
	print "<table width=100% height=100%><tr><td align=center valign=center>";
	if(($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif')) {
		if(move_uploaded_file($_FILES['new_pic']['tmp_name'], $target_path)) {
			$ret = mysql_query("UPDATE $table_u SET pic_path = '/$target_path' WHERE id='$id';");
			print "Picture successfully uploaded.<br>"; 
			print "<a href='account.php'>Back</a>";
		} else {
			print "There was an error uploading.<br>";
			print "<a href='account.php'>Back</a>";
		}
	} else {
		print "The file you attempted to upload was not properly formatted.<br>";
		print "<a href='account.php'>Back</a>";
	}
	print "</td></tr></table>";
} else {
?>
<body>
<h1>My Account:</h1>
<form method=POST action=<?php echo $PHP_SELF; ?>>
<table>
<tr><td colspan=2 align=center><b><h2>Change Password:</h2></b></td></tr>
<tr>
<td>Original Password:</td>
<td><input type='password' name='pass_original' size=20 maxlength=20></td>
</tr>
<tr>
<td>New Password:</td>
<td><input type='password' name='pass_new' size=20 maxlength=20></td>
</tr>
<tr>
<td>Confirm New Password:</td>
<td><input type='password' name='pass_confirm' size=20 maxlength=20></td>
</tr>
<tr><td colspan=2 align=center>
<input type='submit' name='submit' value='Change Password'>
</td></tr>
</table>
</form>

<form method=POST enctype='multipart/form-data' action=<?php echo $PHP_SELF; ?>>
<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>
<table>
<tr>
<td><img src='<?php echo $c_pic_path; ?>' height=200></td>
<td>
Upload a new picture: <input type='file' size=30 name='new_pic'><br>
<small>
Acceptable formats are JPEG, JPG, and GIF.<br>
The file must be under 1 MB.<br>
Pictures are scaled to 200 pixel height.<br>
Abuse of the lack of a width setting will see you banned.<br>
Your picture should be of you, but this is not required.<br>
Any picture deemed inappropriate will be removed, and repeated attempts to upload said picture may result in a ban.<br>
</small><br><br>
<center>
<input type='submit' name='submit' value='Upload'>
</center>
</td>
</tr>
</table>
</form>
<center><br><br>
<font size="3">Your ID is <?php echo $id; ?>.</font><br>
</center>
<?php
}
?>
<?php include('template_bottom.php'); ?>


<?php
mysql_free_result($ret);
mysql_close($sql);
ob_end_flush();
?>
