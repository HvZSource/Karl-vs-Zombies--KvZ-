<?php
ob_start();
session_start();
require_once('functions/functions.php');
require_once('functions/quick_con.php');
$config = load_config('settings/config.php');
include('template_top.php'); ?>
<h1>Don't even try it!</h1>
          <p>Come on... the game hasn't even started yet!</p>

<?php include('template_bottom.php'); 

ob_end_flush();
?>
