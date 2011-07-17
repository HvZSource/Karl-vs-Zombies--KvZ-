<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat'); 
$sql = my_quick_con($config) or die("SQL problem"); 
$username = $_SESSION['user'];
$table_v = $config['var_table'];
?>

<html>
<head>
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>
<body bgcolor="#000000">

<?php
$q = "UPDATE $table_v SET value=0 WHERE keyword='reg-closed'";
mysql_query($q);
print "<br><br><br><center><font color='#FFFFFF'>Registration has now been reopened!  To close it, go back to the <a href='flow.php'>game flow</a> page.</font></center><br><br>";

?>
</body>
</html>
