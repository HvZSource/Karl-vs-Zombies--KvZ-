<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php'); 
$config = load_config('../settings/config.dat');
$table_u = $config['user_table'];
$table_v = $config['var_table'];
$sql = my_quick_con($config) or die("MySQL problem"); 
$ret = mysql_query("UPDATE $table_u SET state = -4 WHERE TIMESTAMP 'now' > feed + INTERVAL '2 days';"); 
$ret = mysql_query("UPDATE $table_u SET starved = feed + INTERVAL '2 days' WHERE state = -4;");
$ret = mysql_query("UPDATE $table_u SET state = 0 WHERE state = -4;");
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='oz-revealed';");
$reveal_oz = mysql_fetch_assoc($ret);
$reveal_oz = $reveal_oz['value'];
?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body>
<h3>player list</h3>
<form method=POST action="mailer.php">
<?php
$faction_array = array('a'=>'All', 'r'=>'Resistance', 'h'=>'Horde', 'd'=>'Deceased');
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
	$post_faction_array = array('a'=>'1 = 1', 'r'=>'state > 0', 'h'=>'state < 0', 'd'=>'state = 0');
	if(!$reveal_oz) {
                $post_faction_array['r'] = 'state > 0 OR state = -2';
        }
	$faction = $post_faction_array[$_POST['faction']];
	$ret = mysql_query("SELECT email FROM $table_u WHERE $faction;");
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
