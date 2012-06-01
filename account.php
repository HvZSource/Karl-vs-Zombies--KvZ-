<?php
ob_start();
session_start();

require_once('security.php');
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat'); 
$sql = my_quick_con($config) or die("SQL problem"); 
$table_v = $config['var_table'];
$table_u = $config['user_table'];
$table_t = $config['time_table'];
// Get game settings
$ret = mysql_query("SELECT zone, starve_time FROM $table_t");
$row = mysql_fetch_assoc($ret);
date_default_timezone_set($row['zone']);
$starve_time = $row['starve_time'];
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-closed';"); 
$reg_closed = mysql_result($ret, 0);
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-open';"); 
$reg_open = mysql_result($ret, 0);
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='game-started';"); 
$game_started = mysql_result($ret, 0);
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';"); 
$oz_revealed = mysql_result($ret, 0);
$game_name = $config['game_name'];
$id = $_SESSION['id'];
$ret = mysql_query("SELECT * FROM $table_u WHERE id='$id';"); 
$userrow = mysql_fetch_assoc($ret);
$c_pic_path = $userrow['pic_path'] !== null? $userrow['pic_path'] : 'images/zom_no_photo.jpg';
?>
<?php include('template_top.php'); ?>

<?php
if($_POST['submit'] == 'Save Changes') {
	print "<table width=100% height=100%>";
	if($_POST['oz_opt'] == 1) {
		// Update OZ Pool Opt-In
		mysql_query("UPDATE $table_u SET oz_opt = 1 WHERE id='$id';");
		print "<tr><td align=center valign=center>You are in the Original Zombie pool.</td></tr>";
	} else {
		mysql_query("UPDATE $table_u SET oz_opt = 0 WHERE id='$id';");
		print "<tr><td align=center valign=center>You are not in the Original Zombie pool.</td></tr>";
	}

	if($_POST['email'] != '' && $_POST['email'] != $userrow['email']) {
		// Update email address
		mysql_query("UPDATE $table_u SET email = '" . mysql_real_escape_string($_POST['email']) . "' WHERE id='$id';");
		print "<tr><td align=center valign=center>Your email address has been updated.</td></tr>";
	}

	if($_POST['fname'] != '' && $_POST['lname'] != '' && ($_POST['fname'] != $userrow['fname'] || $_POST['lname'] != $userrow['lname'])) {
		// Update Name
		mysql_query("UPDATE $table_u SET fname = '" . mysql_real_escape_string($_POST['fname']) 
			. "', lname = '" . mysql_real_escape_string($_POST['lname']) . "' WHERE id='$id';");
		print "<tr><td align=center valign=center>Your name has been changed to " . $_POST['fname']	. " " . $_POST['lname'] . ".</td></tr>";
	}

	if($_POST['pass_new'] != '' && $_POST['pass_confirm'] !== '') {
		// Update User Password
		$pass_ret = $userrow['password'];
		$pass_cur = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_original']));
		$pass_new = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_new']));
		$pass_con = md5(ereg_replace("[^A-Za-z0-9]","",$_POST['pass_confirm']));
		print "<tr><td align=center valign=center>";
		if($pass_ret == $pass_cur) {
			if(strlen($_POST['pass_new']) >= 4 && strlen($_POST['pass_new']) <= 20) {
				if($pass_new == $pass_con) {
					mysql_query("UPDATE $table_u SET password = '$pass_new' WHERE id='$id';");
					print "Password successfully changed.<br>";
				} else {
					print "The passwords you entered did not match.<br>"; 
				}
			} else {
				print "Your new password must be between 4 and 20 alphanumerics.<br>";
			}
		} else {
			print "The password you entered was incorrect.<br>"; 
		}
		print "</td></tr>";
	}
	
	if(is_uploaded_file($_FILES['new_pic']['tmp_name'])) {
		// Upload new photo
		$extension = basename($_FILES['new_pic']['name']);
		$sub_ex = explode(".", $extension); 
		$extension = strtolower($sub_ex[sizeof($sub_ex) - 1]);
		$target_path = "pics/$game_name/{$userrow['fname']}_{$userrow['lname']}.$extension";
		print "<tr><td align=center valign=center>";
		if(($extension == 'jpg') || ($extension == 'jpeg') || ($extension == 'gif')) {
			if(move_uploaded_file($_FILES['new_pic']['tmp_name'], $target_path)) {
				mysql_query("UPDATE $table_u SET pic_path = '$target_path' WHERE id='$id';");
				print "Picture successfully uploaded.<br>"; 
			} else {
				print "There was an error uploading.<br>";
			}
		} else {
			print "The file you attempted to upload was not properly formatted.<br>";
		}
		print "</td></tr>";
	}
	print "</table>";
	print "<center><a href='account.php'>Back</a></center>";

} else if($_POST['submit'] == 'Join Game!') {
	// Activate user to join current game
	$valid_id = 0; 
	$c_list = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
	srand(); 

	while($valid_id == 0) {
		$new_id = '';
		for($i = 0; $i < $config['id_length']; $i++) {
			$n = rand() % 35;
			$new_id .= substr($c_list, $n, 1); 
		}
		$ret = mysql_query("SELECT id FROM $table_u WHERE id='$new_id';");
		if(mysql_num_rows($ret) == 0) {
			$valid_id = 1; 
		}
	}
	$_SESSION['id'] = $new_id;
	mysql_query("UPDATE $table_u SET id = '$new_id', active = 1 WHERE id='$id';");
	print "<table width=100% height=100%><tr><td align=center valign=center>";
		print "You're registered for the upcoming game. Have fun!<br>";
		print "<br>Your new ID is: <b>" . $new_id . "</b><br><br>"; 
		print "Please write it down and carry it with you during the game. If a zombie tags you, please give them your ID. <br>You can also find your ID in the My Account section of the site.<br>";
		print "<a href='account.php'>Back</a>";
	print "</td></tr></table>";
} else {
?>
<h1><?=$userrow['fname']; ?>'s Account:</h1>
<? if($userrow['active'] && $game_started) { 
	echo '<b>This Game:</b><br>';
	if ($userrow['state'] == 1 || ($userrow['state'] == -2 && !$oz_revealed)) {
		$ret = mysql_query("SELECT TIMEDIFF(NOW(), killed) alive FROM $table_u WHERE active ORDER BY alive DESC LIMIT 1;"); 
		$alive = mysql_result($ret, 0);
		echo 'Time alive: ' . $alive . '<br>';
	}
	if($userrow['state'] == -1 || ($userrow['state'] == -2 && $oz_revealed)) {
		$ret = mysql_query("SELECT TIMEDIFF(feed + INTERVAL $starve_time hour, NOW()), killed_by FROM $table_u WHERE id = '$id' AND active;") or die(mysql_error()); 
		$z_row = mysql_fetch_array($ret);
		$alive = $z_row[0];
		if($userrow['state'] == -2) {
			$killed_by = 'contagion';
		} else {
			$killed_by = $z_row[1];
			$ret = mysql_query("SELECT concat(fname, ' ', lname) FROM $table_u WHERE id = '$killed_by' AND active;") or die(mysql_error()); 
			$killed_by = mysql_result($ret, 0);
		}
		echo 'Kills: ' . $userrow['kills'] . ', Time until starvation: ' . $alive . ', Killed by ' . $killed_by . ' at ' . $userrow['killed'] . '<br>';
	}
	if(!$oz_revealed) {
		$ret = mysql_query("SELECT kills, TIMEDIFF(feed + INTERVAL $starve_time hour, NOW()) alive FROM $table_u WHERE state = -3 AND active;"); 
		$oz_row = mysql_fetch_array($ret);
		$oz_kills = $oz_row[0];
		$oz_alive = $oz_row[1];
		echo '<br>Original Zombie:<br>'; 
		echo 'Kills: ' . $oz_kills . ', Time until starvation: ' . $oz_alive . '<br>';
	}
	echo '<br>';
 } ?>
<b>Career:</b><br>
Lifetime kills: <?= $userrow['lifetime_kills'] + $userrow['kills']; ?>, Games completed: <?= $userrow['games_completed']; ?>
<br>
<?php if(!$userrow['active'] && !$reg_closed) { ?>
<form method=POST action="account.php">
<table border=1>
<tr><td colspan=2 align=center><b><h2>Registration is Open!</h2></b></td></tr>
<tr>
<td>Registration is open for the next HvZ game. If you'd like to<br>join (you know you want to) just click the "Join Game" button!</td>
<td><input type='submit' name='submit' value='Join Game!' >
</td></tr>
</table>
</form>
<? } ?>

<form method="POST" enctype="multipart/form-data" action="account.php">
<table border=1>
<tr><td colspan=2 align=center><b><h2>Your Settings</h2></b></td></tr>
<tr>
<td>In OZ Pool:</td>
<td><input type='checkbox' name='oz_opt' value='1' <?= $userrow['oz_opt'] ? 'checked' : ''; ?>/></td>
</tr>
<tr>
<td>Email Address:</td>
<td><input type='text' name='email' size="20" value="<?= $userrow['email']; ?>" /></td>
</tr>
<tr>
<td>Name:</td>
<td><input type='text' name='fname' size="20" value="<?= $userrow['fname']; ?>" /><small>First</small><br>
	<input type='text' name='lname' size="20" value="<?= $userrow['lname']; ?>" /><small>Last</small></td>
</tr>
<tr>
<td>Change Password:</td>
<td><input type='password' name='pass_original' size="20" maxlength="20" /><small>Current password</small><br>
<input type='password' name='pass_new' size=20 maxlength=20 /><small>New Password</small><br>
<input type='password' name='pass_confirm' size=20 maxlength=20><small>Confirm New Password</small></td>
</tr>
<tr>
<td><img src='<?php echo $c_pic_path; ?>' height=200></td>
<td>
<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>
Upload a new picture:<br><input type='file' size=30 name='new_pic'><br>
<small>
Acceptable formats are JPEG, JPG, and GIF.<br>
The file must be under 1 MB.<br>
Pictures are scaled to 200 pixel height.<br>
Abuse of the lack of a width setting will see you banned.<br>
Your picture should be of you, but this is not required.<br>
Any picture deemed inappropriate will be removed, and repeated<br>attempts to upload said picture may result in a ban.<br>
</small><br><br>
<center>
<input type='submit' name='submit' value='Save Changes'>
</center>
</td>
</tr>
</table>
</form>
<center><br><br>
<?php if(!$userrow['active']) { ?>
<font size="3">You aren't registered for a current game.</font><br>
<? } else { ?>
<font size="3">Your ID is <span id="id" style="display: none; font-weight: bold;"><?= $id; ?></span><a id="show" href="javascript:void(0);" onClick="javascript:document.getElementById('id').style.display='inline';document.getElementById('show').style.display='none';">show</a></font><br>
<? } ?>
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
