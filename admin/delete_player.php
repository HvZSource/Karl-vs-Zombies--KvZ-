<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem");

$table_u = $config['user_table'];
$pid = $_GET['id'];

?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>
<?php
if($_POST['submit'] == 'Delete') {
	$ret = mysql_query("SELECT fname, lname, username FROM $table_u WHERE id='$pid';");
	while($row = mysql_fetch_array($ret)) {	
		echo("Sucessfully deleted player " . $row['fname'] . " " . $row['lname'] . ".");
	}
	mysql_query("DELETE FROM $table_u WHERE id = '$pid';");
}
else {
       $ret = mysql_query("SELECT fname, lname, username FROM $table_u WHERE id='$pid';");
while($row = mysql_fetch_array($ret))
{
	echo("Are you sure you want to delete player " . $row['fname'] . " " . $row['lname'] . "?<br />");
}
?>
<body>
<br />
<form method=POST action=<?php echo $PHP_SELF; ?>>
<input type='submit' name='submit' value='Delete'></td></tr>
</form>

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
