<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem"); 
$table_v = $config['var_table']; 
$table_u = $config['user_table'];
$err = 0; 
?>

<html>
<head>
<title>Pick Original Zombie</title>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body>
<?php

if($_POST['submit'] == 'Select Original Zombie') {

$oz = $_POST['oz_pick']; 
// users table 
$ret = mysql_query("UPDATE $table_u SET state = -2 WHERE id='$oz';");
$ret = mysql_query("INSERT INTO $table_u (id, fname, lname, state, kills) VALUES ('OriginalZombie','Original','Zombie', -3, 0);");


// variables table
$ret = mysql_query("UPDATE $table_v SET value = 1 WHERE keyword='oz-selected';");


$ret = mysql_query("SELECT fname, lname FROM $table_u WHERE id='$oz';");
$row = mysql_fetch_row($ret);
print "<table height=100% width=100%><tr><td align=center valign=center>";
print "$row[0] $row[1] has been selected as the original zombie.<br>"; 
print "<a href='flow.php'>Back to game flow</a>";
print "</td></tr></table>";
	
} else {
$ret = mysql_query("SELECT value FROM $table_v WHERE keyword='reg-closed';");
$reg_closed = mysql_fetch_assoc($ret);
$reg_closed = $reg_closed['value'];
if($reg_closed == 0) {
	$err = 1; 
	print "<table height=100% width=100%><tr><td align=center valign=center>User registration has not been completed.</td></tr></table>"; 
}

if($err == 0) {
$ret = mysql_query("SELECT fname, lname, id FROM $table_u WHERE oz_opt=1;");
?>

<form method=POST action=<?php echo $PHP_SELF; ?>>
<center>
<table>

<?php
for($i = 0; $i < mysql_num_rows($ret); $i++) {
	$row = mysql_fetch_assoc($ret); ?> 
		<tr>
		<td><input type='radio' name='oz_pick' value='<?php print $row['id']; ?>'></td>
		<td><?php print $row['fname'] . " " . $row['lname']; ?></td>
		</tr>
<?php
}
?>
</table>
<input type='submit' name='submit' value='Select Original Zombie'>
</center>
</form>

<?php
}
}
?>
</body>

<?php
mysql_free_result($ret);
mysql_close($sql); 
ob_end_flush();
?>
