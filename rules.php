<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat');
 
// get the rules content and save it into the session
if(!isset($_SESSION['content']['rules'])) {
$sql = my_quick_con($config) or die("MySQL problem");
$result = mysql_query("SELECT value FROM $config[content_table] WHERE keyword='rules'");
$row = mysql_fetch_assoc($result);
$_SESSION['content']['rules'] = $row['value'];
}
 
$rules = $_SESSION['content']['rules'];
?>
 
<?php include('template_top.php'); ?>
 
<p><strong><h1>Humans Vs Zombies Rules:</h1></strong>
<p>
<?php print $rules; ?>
</p>
 
<?php include('template_bottom.php'); ?>
 
