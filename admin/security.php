<?php
if(!isset($_SESSION['user']) || !isset($_SESSION['pass_hash'])) {
	header('location:login.php');
}
?>
