<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/functions.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.php');
$sql = my_quick_con($config) or die("MySQL problem");

$table_u = $config['user_table'];
$pid = $_GET['id'];

?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>
<body>
<h2>Deactivate Player</h2>
<?php
if($_POST['submit'] == 'Deactivate') {
	$pid = $_POST['id'];
	$ret = mysql_query("SELECT fname, lname, username FROM $table_u WHERE id='$pid';");
	$row = mysql_fetch_array($ret);
	if(mysql_query("UPDATE $table_u set active = 0 WHERE id = '$pid';")) {	
		echo "Sucessfully deactivated player " . $row['fname'] . " " . $row['lname'] . ".<br><br>You will be re-directed in 3 seconds...";
		header("refresh: 4; url=aplayers.php");
		echo '<br><br>';
	} else {
		echo("FAILED trying to deactivate player " . $row['fname'] . " " . $row['lname'] . ".");
	}
} else {
	$ret = mysql_query("SELECT fname, lname, username FROM $table_u WHERE id='$pid';");
	$row = mysql_fetch_array($ret);
	echo("Are you sure you want to deactivate player " . $row['fname'] . " " . $row['lname'] . "?<br />");
?>
<br />
<form method=POST action="deactivate_player.php">
<input type="hidden" name="id" value="<?= $pid; ?>" />
<input type='submit' name='submit' value='Deactivate'></td></tr>
</form>


<?php
mysql_free_result($ret);
}
?>
</body>
</html>

<?php
mysql_close($sql);
ob_end_flush();
?>
