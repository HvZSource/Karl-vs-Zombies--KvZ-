<?php
ob_start();
session_start();
require_once('functions/functions.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.php');
include('template_top.php'); ?>
<h1>Game Over!</h1>
          <p>I hope you had fun!</p>

<?php include('template_bottom.php'); 

ob_end_flush();
?>
