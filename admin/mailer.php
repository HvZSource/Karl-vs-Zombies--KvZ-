<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.dat');
$table_t = $config['time_table'];
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$sql = my_quick_con($config) or die("MySQL problem"); 
// Get game settings
$ret = mysql_query("SELECT zone, starve_time FROM $table_t") or die(mysql_error());
$row = mysql_fetch_assoc($ret);
date_default_timezone_set($row['zone']);
$starve_time = $row['starve_time'];
// Kill starved zombies
mysql_query("UPDATE $table_u SET state = 0, starved = feed + INTERVAL $starve_time hour
			WHERE state < 0 AND now() > feed + INTERVAL $starve_time hour AND starved = '0000-00-00 00:00:00' AND active;") or die(mysql_error());
// Get OZ Revealed setting
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';") or die(mysql_error());
$reveal_oz = mysql_result($ret, 0);
?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body>
<h3>player list</h3>
<form method=POST action="mailer.php">
<?php
$faction_array = array('a'=>'All', 'p'=>'All (active only)', 'r'=>'Resistance', 'h'=>'Horde', 'd'=>'Deceased');
print "<select name='faction'>";
while(list($k,$v) = each($faction_array)) {
	print "<option value='$k'";
	if($_POST['faction'] == $k) print "selected";
	print ">$v</option>";
}
?>
</select><br>
<input type='submit' name='submit' value='Generate'><br>
<textarea cols=60 rows=10>
<?php
if($_POST['submit'] == 'Generate') {
	$post_faction_array = array('a'=>'1 = 1', 'p'=>'active', 'r'=>'state > 0 AND active', 'h'=>'state < 0 AND active', 'd'=>'state = 0 AND active');
	if(!$reveal_oz) {
                $post_faction_array['r'] = 'state > 0 OR state = -2';
        }
	$faction = $post_faction_array[$_POST['faction']];
	$ret = mysql_query("SELECT email FROM $table_u WHERE $faction AND state != -3;") or die(mysql_error());
	for($i = 0; $i < mysql_num_rows($ret); $i++) {
		$row = mysql_fetch_assoc($ret); 
		print $row['email'];
		if($i < mysql_num_rows($ret) - 1) print ", ";
	}
}
?>
</textarea>
</body>

</html>

<?php
mysql_free_result($ret);
mysql_close($sql);
ob_end_flush();
?>
