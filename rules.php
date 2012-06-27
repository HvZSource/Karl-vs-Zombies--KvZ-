<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat');
 
$sql = my_quick_con($config) or die("MySQL problem");
$table_t = $config['time_table'];
// Get game settings
$ret = mysql_query("SELECT zone, feed_limit, starve_time FROM $table_t") or die(mysql_error());
$row = mysql_fetch_assoc($ret);
date_default_timezone_set($row['zone']);
$feed_limit = $row['feed_limit'];
$starve_time = $row['starve_time'];
// get the rules content and save it into the session
if(!isset($_SESSION['content']['rules'])) {
$result = mysql_query("SELECT value FROM $config[content_table] WHERE keyword='rules'");
$_SESSION['content']['rules'] = mysql_result($result, 0);
}
 
$rules = $_SESSION['content']['rules'];
?>
 
<?php include('template_top.php'); ?>
 
<p><strong><h1>Humans Vs Zombies Rules:</h1></strong></p>
<p><b>Game Settings:</b> Zombies must feed within <?= $starve_time; ?> hours | Tags can be shared with <?= $feed_limit; ?> zombie<?= $feed_limit ==1 ? '' : 's'; ?><br>&nbsp;</p>
<p>
<?php print $rules; ?>
</p>
 
<?php include('template_bottom.php'); ?>
 
