<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem");
$con="UPDATE $config[content_table] SET value ='$_POST[front]' WHERE keyword='front';";

if (!mysql_query($con,$sql))
  {
  die('Error: ' . mysql_error());
  }
echo "<body bgcolor='#000000'><center><font color='FFFFFF'>Front Page Saved</font></center></body>";

mysql_close($sql)
?>

