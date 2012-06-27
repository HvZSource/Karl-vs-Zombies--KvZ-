<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.dat');
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$table_t = $config['time_table'];
$sql = my_quick_con($config) or die("MySQL problem");

// Get game settings
$ret = mysql_query("SELECT zone, starve_time FROM $table_t");
$row = mysql_fetch_assoc($ret);
date_default_timezone_set($row['zone']);
$starve_time = $row['starve_time'];

// Get OZ Revealed setting
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$reveal_oz = mysql_result($ret, 0);
if($_SESSION['oz_revealed'] != $reveal_oz) {
	$_SESSION['oz_revealed'] = $reveal_oz;
}

// update the starvation times and oz reveal (after 5 minutes, not on every page refresh)
$now = time();
$last_starvation_update = isset($_SESSION['last_starvation_update']) ? $_SESSION['last_starvation_update'] : $now;
if($last_starvation_update >= $now + 900) {
	// Kill starved zombies
	mysql_query("UPDATE $table_u SET state = 0, starved = feed + INTERVAL $starve_time hour
			WHERE state < 0 AND now() > feed + INTERVAL $starve_time hour AND starved = '0000-00-00 00:00:00';");
	
	$_SESSION['last_starvation_update'] = time();
}

$state_translate = array('-3'=>'horde', '-2'=>'horde (original)', '-1'=>'horde', '0'=>'deceased', '1'=>'resistance', '2'=>'resistance');

$admin = 0;
if(isset($_SESSION['pass_hash'])) $admin = 1;

if($_POST['submit'] == 'Refresh') {
	$post_faction_array = array('a'=>'1 = 1', 'r'=>'state > 0', 'h'=>'state < 0', 'd'=>'state = 0'); 
	if(!$reveal_oz) { 
		$post_faction_array['r'] = 'state > 0 OR state = -2';
		$post_faction_array['h'] = 'state = -1 OR state = -3';
	}
	$post_sort_by_array = array('ln'=>'lname', 'fn'=>'fname', 'ks'=>'kills', 'kd'=>'killed', 'fd'=>'feed', 'sd'=>'starved');
	$post_order_array = array('a'=>'ASC', 'd'=>'DESC');

	$faction = $post_faction_array[$_POST['faction']];
	if(!isset($faction)) $faction = '1 = 2';
	$sort_by = $post_sort_by_array[$_POST['sort_by']];
	if(!isset($sort_by)) $sort_by = 'lname';
	$order = $post_order_array[$_POST['order']];
	if(!isset($order)) $order = 'DESC';

	$show_pics = $_POST['show_pics'];
	$show_kills = $_POST['show_kills'];
	$show_killed = $_POST['show_killed'];
	$show_feed = $_POST['show_feed']; 
	$show_starved = $_POST['show_starved']; 
} else {
        $faction = '1 = 1';
	$sort_by = 'lname';
	$order = 'ASC'; 
	$show_pics = 0;
	$show_kills = 1; 
	$show_killed = 0;
	$show_feed = 1; 
	$show_starved = 0;
}

?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='../style/main.css'>
<link rel='stylesheet' type='text/css' href='../style/styles.css'>
<link rel='stylesheet' type='text/css' href='../style/admin.css'>
</head>

<body>
<h3>Player List</h3>
<form name="playerListForm" method="POST" action="aplayers.php">
<center>

<?php
$faction_array = array('a'=>'All', 'r'=>'Resistance', 'h'=>'Horde', 'd'=>'Deceased');
$sort_by_array = array('ln'=>'Last Name', 'fn'=>'First Name', 'ks'=>'Kills', 'kd'=>'Time Killed', 'fd'=>'Last Feeding', 'sd'=>'Time Starved');
$order_array = array('a'=>'Ascending', 'd'=>'Descending');
print "<select name='faction'>";
while(list($k,$v) = each($faction_array)) {
	print "<option value='$k'";
	if($_POST['faction'] == $k) print "selected";
	print ">$v</option>";
}
print "</select><select name='sort_by'>";
while(list($k,$v) = each($sort_by_array)) {
	print "<option value='$k'";
	if($_POST['sort_by'] == $k) print " selected";
	print ">$v</option>";
}
print "</select><select name='order'>";
while(list($k,$v) = each($order_array)) {
	print "<option value='$k'";
	if($_POST['order'] == $k) print "selected";
	print ">$v</option>";
}
print "</select>";
?>

<input type='submit' name='submit' value='Refresh'><br>
<input type='checkbox' name='show_pics' value='1' <?php if($show_pics) print "checked"; ?>> Pictures
<input type='checkbox' name='show_kills' value='1' <?php if($show_kills) print "checked"; ?>> Kills
<input type='checkbox' name='show_killed' value='1' <?php if($show_killed) print "checked"; ?>> Time Killed
<input type='checkbox' name='show_feed' value='1' <?php if($show_feed) print "checked"; ?>> Last Fed
<input type='checkbox' name='show_starved' value='1' <?php if($show_starved) print "checked"; ?>> Time Starved
</center>

<br />
<table width="90%" cellspacing="0" class="data-table" align="center">
<tr>
<?php
if($show_pics) print "<th>Picture</th>";
?>
<th>Name</th>
<th>Team</th>
<?php
if($show_kills) print "<th>Kills</th>";
if($show_killed) print "<th>Time of Death</th>";
if($show_feed) print "<th>Last Fed</th>";
if($show_starved) print "<th>Starvation Time</th>";
if($admin) print "<th>Edit</th>";
if($admin) print "<th>Status</th>";
?>
</tr>

<?php
$ret = mysql_query("SELECT fname, lname, state, killed_by, killed, feed, kills, starved, pic_path, id, active FROM $table_u WHERE $faction ORDER BY $sort_by $order;");
if($ret && ($rows = mysql_num_rows($ret)) > 0)
{
	for($i = 0; $i < $rows; $i++) {
		$row_id = ($i % 2) + 1;
		print '<tr class="row'.$row_id.'">';
		
		$row = mysql_fetch_assoc($ret);
 	
		if($show_pics) {
			print '<td align="center">';
			if(strlen($row['pic_path']) > 0) {
				print '<img src="'.$row['pic_path'].'" class="player-pic '.$state_translate[$row['state']].'-pic">';
			} else {
				print "no image<br>available";
			}
			print "</td>";
		}
		
		print '<td align="center">'.$row['fname'].' '.$row['lname'].'</td>';
		
		print '<td align="center">';
		if($row['state'] == -2 && !$reveal_oz) {
			print '<span class="resistance">resistance</span>';
		} else {
			print '<span class="'.$state_translate[$row['state']].'">'.$state_translate[$row['state']].'</span>';
		}
		print "</td>";
		
		if($show_kills) {
			print '<td align="center">';
			if($row['state'] == -2 && !$reveal_oz) {
				print "0";
			} else {
				print $row['kills'];
			}
			print "</td>";
		}
		
		if($show_killed) {
			print '<td align="center">';
			if($row['state'] <= 0 && ($row['state'] != -2 || $reveal_oz)) {
				print $row['killed'];
			}
			print "</td>";
		}
		
		if($show_feed) {
			print '<td align="center">';
			if($row['state'] <= 0 && ($row['state'] != -2 || $reveal_oz)) { 
				print $row['feed'];
			}
			print "</td>";
		}
		
		if($show_starved) {
			print '<td align="center">';
			if($row['state'] == 0) { 
				print $row['starved'];
			}
			print "</td>";
		}
		
		if($admin) print "<td align='center'><a href='edit_player.php?id=".$row['id']."'>edit</a></td>";
		if($row['active']) {
			print "<td align='center'><a href='deactivate_player.php?id=".$row['id']."'>deactivate</a></td>";
		} else {
			print "<td align='center'><a href='activate_player.php?id=".$row['id']."'>activate</a> | <a href='delete_player.php?id=".$row['id']."'>delete</a></td>";
		}
		print "</tr>";
	}
} else {
	print '<tr><td colspan="7" align="center"> There are no records to display</td></tr>';
}
?>


</table>
<center><i>
<?php
	$num = mysql_num_rows($ret); 
	print "$num players listed";
?>
</i></center>
</form>
</body>
</html>

<?php
mysql_free_result($ret);
mysql_close($sql);
ob_end_flush();
?>
