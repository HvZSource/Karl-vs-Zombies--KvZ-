<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php'); 
$config = load_config('settings/config.dat');
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$table_t = $config['time_table'];
$sql = my_quick_con($config) or die("MySQL problem"); 
// Set default time zone
$ret = mysql_query("SELECT zone FROM $table_t");
while($row = mysql_fetch_array($ret))
	   date_default_timezone_set($row['zone']);
$ret = mysql_query("UPDATE $table_u SET state = -4 WHERE now() > feed + INTERVAL 2 day;"); 
$ret = mysql_query("UPDATE $table_u SET starved = feed + INTERVAL 2 day WHERE state = -4;");
$ret = mysql_query("UPDATE $table_u SET state = 0 WHERE state = -4;");
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$reveal_oz = mysql_fetch_assoc($ret);
$reveal_oz = $reveal_oz['value'];
$state_translate = array('-3'=>'Zombie', '-2'=>'Original Zombie', '-1'=>'Zombie', '0'=>'Starved', '1'=>'Human', '2'=>'Human');
$admin = 0;
if(isset($_SESSION['pass_hash'])) $admin = 1;

if($_POST['submit'] == 'Refresh') {
	$post_faction_array = array('a'=>'1 = 1', 'r'=>'state > 0', 'h'=>'state < 0', 'd'=>'state = 0'); 
	if(!$reveal_oz) { 
		$post_faction_array['r'] = 'state > 0 OR state = -2';
		$post_faction_array['h'] = 'state = -1 OR state = -3';
	}
	$post_sort_by_array = array('ln'=>'lname', 'fn'=>'fname', 'ks'=>'tags', 'kd'=>'taggeded', 'fd'=>'shared', 'sd'=>'starved');
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
	$show_pics = 1;
	$show_kills = 1; 
	$show_killed = 0;
	$show_feed = 1; 
	$show_starved = 0;
}
?>
<?php include('template_top.php');
 
?>


<h3>Player List</h3>
<?php
$zom = mysql_query("SELECT * FROM $table_u WHERE `state`=-1");
$dead = mysql_query("SELECT * FROM $table_u WHERE `state`=0");
$hum = mysql_query("SELECT * FROM $table_u  WHERE `state`=1");
$hnum = mysql_num_rows($hum);
if ( $hnum > "1" ) {
        echo "$hnum Humans<br>";
}
if ( $hnum == "1" ) {
        echo "$hnum Human<br>";
}
$znum = mysql_num_rows($zom);
if ( $znum > "1" ) {
        echo "$znum Zombies<br>";
}
if ( $znum == "1" ) {
        echo "$znum Zombie<br>";
}
$dnum = mysql_num_rows($dead);
if ( $dnum > "0" ) {
        echo "$dnum Deceased<br>";
}
?>

<form method=POST action=<?php echo $PHP_SELF; ?>>
<center>
<?php

$faction_array = array('a'=>'All', 'r'=>'Humans', 'h'=>'Zombies', 'd'=>'Starved');
$sort_by_array = array('ln'=>'Last Name', 'fn'=>'First Name', 'ks'=>'Tags', 'kd'=>'Time Tagged', 'fd'=>'Last Feeding', 'sd'=>'Time Starved');
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
<input type='checkbox' name='show_kills' value='1' <?php if($show_kills) print "checked"; ?>> Tags
<input type='checkbox' name='show_killed' value='1' <?php if($show_killed) print "checked"; ?>> Time Tagged
<input type='checkbox' name='show_feed' value='1' <?php if($show_feed) print "checked"; ?>> Last Fed
<input type='checkbox' name='show_starved' value='1' <?php if($show_starved) print "checked"; ?>> Time Starved
</center>
<table width=100% border>
<tr>
<?php
if($show_pics) print "<td>Picture</td>";
?>
<td>Name</td>
<td>Affiliation</td>
<?php
if($show_kills) print "<td>Tags</td>";
if($show_killed) print "<td>Time of Tag</td>";
if($show_feed) print "<td>Last Feeding Time</td>";
if($show_starved) print "<td>Time of Starvation</td>";
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
		print "Human";
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

<?php include('template_bottom.php');
mysql_free_result($ret);
mysql_close($sql);
ob_end_flush();
?>
