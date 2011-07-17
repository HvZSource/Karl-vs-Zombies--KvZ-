<?php
ob_start();
session_start();
require_once('../functions/load_config.php');
require_once('security.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.dat');
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$sql = my_quick_con($config) or die("MySQL problem"); 
$ret = mysql_query("UPDATE $table_u SET state = -4 WHERE now() > feed + INTERVAL 2 day;"); 
$ret = mysql_query("UPDATE $table_u SET starved = feed + INTERVAL 2 day WHERE state = -4;");
$ret = mysql_query("UPDATE $table_u SET state = 0 WHERE state = -4;");
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$reveal_oz = mysql_fetch_assoc($ret);
$reveal_oz = $reveal_oz['value'];
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
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body>
<h3>Player List</h3>
<form method=POST action=<?php echo $PHP_SELF; ?>>
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
<table width=100% border>
<tr>
<?php
if($show_pics) print "<td>Picture</td>";
?>
<td>Name</td>
<td>Team</td>
<?php
if($show_kills) print "<td>Kills</td>";
if($show_killed) print "<td>Time of Death</td>";
if($show_feed) print "<td>Last Fed</td>";
if($show_starved) print "<td>Starvation Time</td>";
if($admin) print "<td></td>";
?>
</tr>

<?php
$ret = mysql_query("SELECT fname, lname, state, killed_by, killed, feed, kills, starved, pic_path, id FROM $table_u WHERE $faction ORDER BY $sort_by $order;"); 
for($i = 0; $i < mysql_num_rows($ret); $i++) {
	
	$row = mysql_fetch_array($ret);
	if($show_pics) {
		print "<td>";
		if(strlen($row[8]) > 0) {
			print "<center><img src='$row[8]' height=200></center>";
		} else {
			print "<center>no image<br>available</center>";
		}
		print "</td>";
	}
	print "<td>$row[0] $row[1]</td><td>";
	if($row[2] == -2 && !$reveal_oz) {
		print "resistance";
	} else {
		print $state_translate[$row[2]];
	}
	if($show_kills) {
		print "<td>";
		if($row[2] == -2 && !$reveal_oz) {
			print "0";
		} else {
			print "$row[6]";
		}
		print "</td>";
	}
	if($show_killed) {
		print "<td>";
		if($row[2] <= 0 && ($row[2] != -2 || $reveal_oz)) {
			print $row[4];
		}
		print "</td>";
	}
	if($show_feed) {
		print "<td>";
		if($row[2] <= 0 && ($row[2] != -2 || $reveal_oz)) { 
                        print $row[5];
                }
		print "</td>";
	}
	if($show_starved) {
		print "<td>";
		if($row[2] == 0) { 
                        print $row[7];
                }
		print "</td>";
	}
	if($admin) print "<td align=center><a href='edit_player.php?id=$row[9]'>edit</a></td>";
	print "</tr>";

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
