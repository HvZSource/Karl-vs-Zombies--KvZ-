<?php
ob_start();
session_start();
require_once('security.php'); 
require_once('../functions/functions.php'); 
require_once('../functions/quick_con.php'); 
?>

<html> 
<head> 
<link rel='stylesheet' type='text/css' href='style/main.css'>
</head>

<body> 
<h3>Navigation</h3>
<a href='flow.php' target='display'>Game Flow</a><br>
<a href='time.php' target='display'>Game Settings</a><br>
<a href='front.php' target='display'>Edit Front Page</a><br>
<a href='rules.php' target='display'>Edit Rules</a><br>
<a href='aplayers.php' target='display'>Edit Players</a><br>
<!--<a href='twitter_set.php' target='display'>Twitter</a><br>-->
<a href='mailer.php' target='display'>Mailing Lists</a><br>
<a href='account.php' target='display'>My Account</a><br>
<a href='logout.php' target=_top>Logout</a>
</body> 
</html>

<?php
ob_end_flush();
?>
