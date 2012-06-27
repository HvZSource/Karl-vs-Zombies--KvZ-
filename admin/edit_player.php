<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$game_name = $config['game_name'];
$sql = my_quick_con($config) or die("MySQL problem");
$table_t = $config['time_table'];
// Set default time zone
$ret = mysql_query("SELECT zone FROM $table_t");
while($row = mysql_fetch_array($ret))
	   date_default_timezone_set($row['zone']);
$table_u = $config['user_table'];
$pid = $_GET['id'];

?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>

<script type="text/javascript">
function setTimeNow(fieldname) {
	var f = document.editPlayerForm;
	
	var today = new Date();
	
	var year = today.getFullYear();
	var mon = (today.getMonth() + 1);
	mon = (mon < 10) ? "0" + mon : mon;
	var day = today.getDate();
	day = (day < 10) ? "0" + day : day;
	
	var hour = today.getHours();
	hour = (hour < 10) ? "0" + hour : hour;
	var mins = today.getMinutes();
	mins = (mins < 10) ? "0" + mins : mins;
	var secs = today.getSeconds();
	secs = (secs < 10) ? "0" + secs : secs;
	
	eval("f." + fieldname + ".value = '" + year + "-" + mon + "-" + day + " " + hour + ":" + mins + ":" + secs + "'");
}
</script>

</head>
<?php
if($_POST['submit'] == 'Update Table Values') {
	$pid = $_POST['id'];
	$n_fname = $_POST['fname']; 
	$n_lname = $_POST['lname'];
	$n_email = $_POST['email'];
	$n_state = $_POST['state'];
	$n_killed_by = $_POST['killed_by'];
	$n_kills = $_POST['kills'];
	$n_killed = $_POST['killed'];
	$n_feed = $_POST['feed'];
	$n_starved = $_POST['starved'];
	
	$query = "UPDATE $table_u SET fname = '$n_fname', lname = '$n_lname', email = '$n_email', state = $n_state, killed_by = '$n_killed_by', kills = $n_kills WHERE id = '$pid'";
	$ret = mysql_query($query);
	
	if(strlen($n_killed) > 0) 	$ret = mysql_query("UPDATE $table_u SET killed = TIMESTAMP '$n_killed' WHERE id='$pid';");
	if(strlen($n_feed) > 0) 	$ret = mysql_query("UPDATE $table_u SET feed = TIMESTAMP '$n_feed' WHERE id='$pid';");
	if(strlen($n_starved) > 0) 	$ret = mysql_query("UPDATE $table_u SET starved = TIMESTAMP '$n_starved' WHERE id='$pid';");
	print "<body><table height=100% width=100%><tr><td align=center valign=center>";
	print 'Database values updated.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
	print "</td></tr></table></body>";
} else if($_POST['submit'] == 'Change Password') {
	$pid = $_POST['id'];
	print "<body><table height=100% width=100%><tr><td align=center valign=center>";
	$pass1 = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass1']));
	$pass2 = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass2'])); 

	if($pass1 == $pass2) {
		$ret = mysql_query("UPDATE $table_u SET password = '$pass1' WHERE id='$pid';");
		print 'Password updated.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
	} else {
		print 'The passwords you entered did not match.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
	}
	print "</td></tr></table></body>";
} else if($_POST['submit'] == 'Upload') {
	$pid = $_POST['id'];
	print "<body><table height=100% width=100%><tr><td align=center valign=center>";
	$ret = mysql_query("SELECT fname, lname FROM $table_u WHERE id='$pid';");
	$row = mysql_fetch_row($ret); 
	$extension = basename($_FILES['new_pic']['name']);
	$sub_ex = explode(".", $extension);
	$extension = strtolower($sub_ex[sizeof($sub_ex) - 1]);
	$target_path = "pics/$game_name/$row[0]_$row[1].$extension";
	if(($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif')) {
		if(move_uploaded_file($_FILES['new_pic']['tmp_name'], '../' . $target_path)) {
			$ret = mysql_query("UPDATE $table_u SET pic_path = '$target_path' WHERE id='$pid';");
			print 'Picture successfully uploaded.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
		} else {
			print 'There was an error uploading.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
		}
	} else {
		print 'The file you attempted to upload was not properly formatted.<br><a href="edit_player.php?id=' . $pid . '">Back to edit page</a>';
	}
	print "</td></tr></table></body>";
} else {
$ret = mysql_query("SELECT fname, lname, email, state, killed_by, kills, killed, feed, starved, pic_path, username FROM $table_u WHERE id='$pid';");
$row = mysql_fetch_row($ret);
?>
<body>
<table border>

<tr><td colspan=2 align=center>
<h4>Editing: <?php print "$row[0] $row[1] (ID: $pid)"; ?></h4>
</td></tr>

<tr><td width=50% valign=top>

<form name="editPlayerForm" method="POST" action="edit_player.php">
<input type="hidden" name="id" value="<?= $pid; ?>" />
<table>
<tr>
	<td>username:</td>
	<td><?php echo $row[10]; ?></td>
</tr>
<tr>
	<td>fname:</td>
	<td><input type='text' name='fname' size=20 value=<?php echo $row[0]; ?>></td>
</tr>
<tr>
	<td>lname:</td>
	<td><input type='text' name='lname' size=20 value=<?php echo $row[1]; ?>></td>
</tr>
<tr>
	<td>email:</td>
	<td><input type='text' name='email' size=20 value=<?php echo $row[2]; ?>></td>
</tr>
<tr>
	<td>state:</td>
	<td><select name='state'>
		<?php
			$st_sel = array('-2' => 'Orig. Zombie', '-1' => 'Zombie', '0' => 'Deceased', '1' => 'Resistance');
			
			if($row[3] == '-3') {
				print "<option value='-3'>Zombie</option>";
			} else {
				while(list($k,$v) = each($st_sel)) {
					print "<option value='$k'";
					if($row[3] == $k) print " selected";
					print ">$v</option>";
				}
			}
		?>
	</select></td>
</tr>
<?php
	$rest = array(
		'4' => 'killed_by', 
		'5' => 'kills',
		'6' => 'killed',
		'7' => 'feed',
		'8' => 'starved');
	while(list($k,$v) = each($rest)) {
		$add_time_button = ($k == 6 || $k == 7 || $k == 8) ? '&nbsp;<input type="button" onclick="setTimeNow(\''.$v.'\');" value="now" />' : "";
		print "<tr><td>$v:</td><td><input type='text' name='$v' size='20' value='$row[$k]' />".$add_time_button."</td></tr>\n";
	}
?>
<tr><td colspan=2 align=center><input type='submit' name='submit' value='Update Table Values'></td></tr>
</table>
</form>
<p>
time format: yyyy-mm-dd hh:mm:ss<br>
eg. 2007-04-11 21:07:49<br>
(24hr time)<br>

</td><td valign=top>

<center><form name="changePasswordForm" method=POST action="edit_player.php"><table>
<input type="hidden" name="id" value="<?= $pid; ?>" />
<tr><td>new password:</td><td><input type='password' name='pass1' size=20 maxlength=20></td></tr>
<tr><td>retype password:</td><td><input type='password' name='pass2' size=20 maxlength=20></td></tr>
<tr><td colspan=2 align=center><input type='submit' name='submit' value='Change Password'></td></tr>
</table></form>
<hr>
<img src='<?= '../' . $row[9]; ?>' height=200 alt='no image available'></img><p>
<form name="editPictureForm" method=POST enctype='multipart/form-data' action="edit_player.php">
<input type="hidden" name="id" value="<?= $pid; ?>" />
<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>
new picture: <input type='file' size=30 name='new_pic'><br>
<center><input type='submit' name='submit' value='Upload'></center>
</form>
</center>


</td>
</tr>
</table>
</body>

<?php
mysql_free_result($ret);
}
?>
</html>

<?php
mysql_close($sql);
ob_end_flush();
?>
