<?php 
session_start();
require_once('security.php');
?>
<html>
<head>
<title>admin hub</title>
</head>
<frameset cols='150,*'>
	<frame name='navbar' src='navbar.php'>
	<frame name='display' src='flow.php'>
</frameset>
</html>
