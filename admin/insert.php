<?php
ob_start();
session_start();
require_once('security.php');
require_once('../functions/load_config.php');
require_once('../functions/quick_con.php');
$config = load_config('../settings/config.dat');
$sql = my_quick_con($config) or die("MySQL problem");
$con=sprintf("UPDATE %s SET value = '%s' WHERE keyword='rules';",
		     $config[content_table],
		     mysql_real_escape_string($_POST[rules]));

if (!mysql_query($con,$sql))
  {
  die('Error: ' . mysql_error());
  }
echo "<body bgcolor='#000000'><center><font color='FFFFFF'>Rule Set Saved</font></center></body>";

mysql_close($sql)
?>

