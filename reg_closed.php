<?php
ob_start();
session_start();
require_once('functions/load_config.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.dat');
include('template_top.php'); ?>
<h1>Registration is Closed :(</h1>
<p>You waited too long, you should register sooner next time!</p>

<?php include('template_bottom.php'); 

ob_end_flush();
?>
